(function(window){
    // the only one global variable
    var jegarn = function(){};
    window.jegarn = jegarn;
    jegarn.debug = true;
    // some tools
    jegarn.extends = function(d, b, a) {
        var p , o = d.constructor.prototype, h = {};
        if(d.constructor.name == "Object"){
            console.warn( "When you use the extends. You must make a method like 'XX.prototype.xxx=function(){}'. but not 'XX.prototype={xxx:function(){}}'.");
        }
        if (typeof d.__ll__parent__ == "undefined") {
            d.__ll__parent__ = [];
            d.__ll__parent__ = [];
        }
        d.__ll__parent__.push(b.prototype);
        for (p in o) {
            h[p] = 1;
        }
        for (p in b.prototype) {
            if (!h[p]) {
                o[p] = b.prototype[p];
            }
        }
        b.apply(d, a);
    };
    jegarn.json2str = function(jsonObj){
        return JSON.stringify(jsonObj);
    };
    jegarn.str2json = function (str){
        try{
            return JSON.parse(str);
        }catch (e){
            return null;
        }
    };
    jegarn.log = function(){
        if(jegarn.debug){
            console.log( Array.prototype.slice.call(arguments) );
        }
    };
    // packet, see php sdk
    jegarn.packet = (function(){
        var packet = function(){};
        packet.Base = (function(){
            var base = function(){
                this.from = null;
                this.to = null;
                this.type = null;
                this.content = null;
            };
            base.prototype.getPacketFromPacket = function(packet){
                if(packet.type && packet.type == this.type){
                    this.from = packet.from;
                    this.to = packet.to;
                    this.content = packet.content;
                    return this;
                }
                return null;
            };
            //noinspection JSUnusedGlobalSymbols
            base.prototype.isFromSystemUser = function(){
                return this.from == 'system';
            };
            base.prototype.setToSystemUser = function(){
                this.to = 'system';
            };
            return base;
        })();
        packet.Auth = (function(){
            var auth = function(){
                var s = this;
                jegarn.extends(s, packet.Base, []);
                this.from = 0;
                this.setToSystemUser();
                this.type = packet.Auth.type;
                this.content = {
                    uid : null,
                    account: null,
                    password: null,
                    status: null
                };
            };
            auth.prototype.getUid = function(){
                return this.content.uid;
            };
            //noinspection JSUnusedGlobalSymbols
            auth.prototype.setUid = function(uid){
                return this.content.uid = uid;
            };
            //noinspection JSUnusedGlobalSymbols
            auth.prototype.getAccount = function(){
                return this.content.account;
            };
            auth.prototype.setAccount = function(account){
                return this.content.account = account;
            };
            //noinspection JSUnusedGlobalSymbols
            auth.prototype.getPassword = function(){
                return this.content.password;
            };
            auth.prototype.setPassword = function(password){
                return this.content.password = password;
            };
            auth.prototype.getStatus = function(){
                return this.content.status;
            };
            //noinspection JSUnusedGlobalSymbols
            auth.prototype.setStatus = function(status){
                return this.content.status = status;
            };
            auth.type = 'auth';
            auth.STATUS_NEED_AUTH = 1;
            auth.STATUS_AUTH_SUCCESS = 2;
            auth.STATUS_AUTH_FAILED = 3;
            return auth;
        })();
        packet.Chat = (function(){
            return function(){
                var s = this;
                jegarn.extends(s, packet.Base, []);
                s.type = 'chat';
            };
        })();
        packet.GroupChat = (function(){
            var groupchat = function(){
                var s = this;
                jegarn.extends(s, packet.Base, []);
                s.type = 'groupchat';
                s.content = {group_id : null};
            };
            //noinspection JSUnusedGlobalSymbols
            groupchat.prototype.isSendToAll = function(){
              return 'all' == this.to;
            };
            groupchat.prototype.setSendToAll = function(){
              this.to = 'all';
            };
            groupchat.prototype.setGroupId = function(groupId){
                return this.content.group_id = groupId;
            };
            //noinspection JSUnusedGlobalSymbols
            groupchat.prototype.getGroupId = function(){
                return this.content.group_id;
            };
            return groupchat;
        })();
        packet.Chatroom = (function(){
            var chatroom = function(){
                var s = this;
                jegarn.extends(s, packet.Base, []);
                s.to = 'all';
                s.type = 'chatroom';
                s.content = {group_id : null};
            };
            chatroom.prototype.setGroupId = function(groupId){
                return this.content.group_id = groupId;
            };
            //noinspection JSUnusedGlobalSymbols
            chatroom.prototype.getGroupId = function(){
                return this.content.group_id;
            };
            return chatroom;
        })();
        packet.TextChat = (function(){
            var text = function(){
                var s = this;
                jegarn.extends(s, packet.Chat, []);
                s.content = {type: 'text', text : null};
            };
            text.prototype.getText = function(){
                return this.content.text;
            };
            text.prototype.setText = function(value){
                this.content.text = value;
            };
            return text;
        })();
        packet.TextGroupChat = (function(){
            var text = function(){
                var s = this;
                jegarn.extends(s, packet.GroupChat, []);
                s.content = {type: 'text', text : null};
            };
            text.prototype.getText = function(){
                return this.content.text;
            };
            text.prototype.setText = function(value){
                this.content.text = value;
            };
            return text;
        })();
        packet.TextChatroom = (function(){
            var text = function(){
                var s = this;
                jegarn.extends(s, packet.Chatroom, []);
                s.content = {type: 'text', text : null};
            };
            text.prototype.getText = function(){
                return this.content.text;
            };
            text.prototype.setText = function(value){
                this.content.text = value;
            };
            return text;
        })();
        return packet;
    })();
    // client connection manager
    jegarn.client = function(host, port, reconnectInterval){
        this.uid  = this.account = this.password = null;
        this.host = host;
        this.port = port;
        this.ws = null;
        this.running = false;
        this.sessionKey = 'session_id';
        this.sessionId = null;
        this.authorized = false;
        this.packetListener = function(packet, clientInstance){};
        // return false to prevent send to client
        this.sendListener = function(packet, clientInstance){};
        this.errorListener  = function(errorObject,clientInstance){};
        this.connectListener = function(clientInstance){};
        this.disconnectListener = function(evt,clientInstance){};
        this.reconnectHandle = null;
        this.reconnectInterval = reconnectInterval;
    };
    jegarn.client.prototype = {
        setUser:   function(account, password){
            this.account = account;
            this.password = password;
        },
        send: function(content){
            jegarn.log('client.send', content);
            return this.ws.send(content);
        },
        sendPacket : function(packet){
            jegarn.log('client.sendPacket', packet);
            if(this.running){
                if(packet.type == null || packet.to == null || (null == packet.from && null == this.uid)){
                    jegarn.log('client.sendPacket send valid packet, code wrong?');
                    this.errorListener(new this.errorObject(this.SEND_PACKET_VALID, packet), this);
                }else{
                    packet.from = packet.from != null ? packet.from : this.uid;
                    packet.content = packet.content != null ? packet.content : "";
                    if(false !== this.sendListener(packet, this)){
                        var data = {};
                        data[this.sessionKey] = this.sessionId;
                        data.from = packet.from;
                        data.to = packet.to;
                        data.type = packet.type;
                        data.content = packet.content;
                        return this.send(jegarn.json2str(data));
                    }else{
                        jegarn.log('client.sendPacket send listener prevented send');
                    }
                }
            }else{
                jegarn.log('client.sendPacket called when not running');
            }
            return false;
        },
        setPacketListener : function(listener){
            this.packetListener = listener;
        },
        setSendListener : function(listener){
            this.sendListener = listener;
        },
        setErrorListener : function(listener){
            this.errorListener = listener;
        },
        setConnectListener : function(listener){
            this.connectListener = listener;
        },
        setDisconnectListener: function(listener){
            this.disconnectListener = listener;
        },
        NETWORK_ERROR : 0,
        RECV_PACKET_CRASHED : 1,
        RECV_PACKET_TYPE : 2,
        AUTH_FAILED : 3,
        SEND_PACKET_VALID : 4,
        errorObject : function(code, message){
            this.code = code;
            this.message = message;
        },
        getPacket: function(data){
            if(data){
                if(typeof data.from == "undefined" || typeof data.to == "undefined" || typeof data.type == "undefined" || typeof data.content == "undefined"){
                    return null;
                }
                var p = new jegarn.packet.Base();
                p.from = data.from;
                p.to = data.to;
                p.type = data.type;
                p.content = data.content;
                return p;
            }else{
                return null;
            }
        },
        auth : function(){
            if(this.account == null || this.password == null){
                throw "account or password not config\n";
            }
            var authPacket = new jegarn.packet.Auth();
            authPacket.setAccount(this.account);
            authPacket.setPassword(this.password);
            this.sendPacket(authPacket);
        },
        onopen : function(evt){
            jegarn.log('client.onopen',evt);
            this.auth();
        },
        onclose : function(evt){
            jegarn.log('client.onclose', evt);
            this.running = false;
            this.disconnectListener(evt, this);
        },
        onmessage : function(evt){
            jegarn.log('client.onmessage', evt);
            var data = jegarn.str2json(evt.data);
            var packet = this.getPacket(data);
            if(packet){
                if(!this.authorized){
                    if(packet.type == jegarn.packet.Auth.type){
                        var authPacket = (new jegarn.packet.Auth()).getPacketFromPacket(packet);
                        switch(authPacket.getStatus()){
                            case jegarn.packet.Auth.STATUS_NEED_AUTH:
                                this.auth();
                                break;
                            case jegarn.packet.Auth.STATUS_AUTH_SUCCESS:
                                //noinspection JSUnresolvedVariable
                                this.sessionId = data.session_id;
                                this.uid = authPacket.getUid();
                                this.authorized = true;
                                this.connectListener(this);
                                break;
                            case jegarn.packet.Auth.STATUS_AUTH_FAILED:
                                this.errorListener(new this.errorObject(this.AUTH_FAILED, packet), this);
                                break;
                        }
                    }else{
                        this.errorListener(new this.errorObject(this.RECV_PACKET_TYPE, packet), this);
                    }
                }else{
                    this.packetListener(packet, this);
                }
            }else{
                this.errorListener(new this.errorObject(this.RECV_PACKET_CRASHED, evt), this);
            }
        },
        onerror : function(evt){
            jegarn.log('client.onerror',evt);
            this.running = false;
            this.errorListener(new this.errorObject(this.NETWORK_ERROR, evt), this);
            if(this.reconnectInterval && this.reconnectInterval > 0){
                if(null != this.reconnectHandle){
                    this.reconnectHandle = setTimeout(function () {
                        clearTimeout(this.reconnectHandle);
                        this.reconnectHandle = null;
                        this.reconnect()
                    }.bind(this), this.reconnectInterval);
                }
            }
        },
        connect : function(){
            if(this.host == null || this.port == null){
                throw "host or port not config\n";
            }
            if(!this.running){
                var url = (window.location.href.substring(0, 5) == 'https' ? 'wss://' : 'ws://') + this.host + ':' + this.port;
                this.ws = new WebSocket(url);
                this.ws.onopen = this.onopen.bind(this);
                this.ws.onmessage = this.onmessage.bind(this);
                this.ws.onclose = this.onclose.bind(this);
                this.ws.onerror = this.onerror.bind(this);
                this.running = true;
                jegarn.log('client.connect');
            }
        },
        reconnect : function(){
            if(!this.running){
                jegarn.log('client.reconnect');
                this.ws.onopen = this.ws.onmessage = this.ws.onclose = this.ws.onerror = null;
                this.ws = null;
                this.connect();
            }else{
                jegarn.log('client.reconnect called when not running');
            }
        }
    };
})(window);