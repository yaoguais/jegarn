(function(window, $){
    // only global variable
    var minions = function(){};
    window.minions = minions;
    // utils
    minions.ajax = $.ajax;
    minions.get = $.get;
    minions.post = $.post;
    minions.alert = $.alerts.alert;
    minions.confirm = $.alerts.confirm;
    minions.prompt = $.alerts.prompt;
    minions.context = window.context;
    minions.log = jegarn.log;
    minions.preventDefault = function(e){e.preventDefault();e.stopPropagation();};
    minions.context.init({ fadeSpeed: 100, filter: function ($obj){}, above: 'auto', preventDoubleContext: true, compress: false });
    // reset system class method
    Date.prototype.pattern = function(fmt) {
        var o = {
            "M+" : this.getMonth()+1,
            "d+" : this.getDate(),
            "h+" : this.getHours()%12 == 0 ? 12 : this.getHours()%12,
            "H+" : this.getHours(),
            "m+" : this.getMinutes(), //
            "s+" : this.getSeconds(),
            "q+" : Math.floor((this.getMonth()+3)/3),
            "S" : this.getMilliseconds()
        };
        var week = {
            "0" : "/u65e5",
            "1" : "/u4e00",
            "2" : "/u4e8c",
            "3" : "/u4e09",
            "4" : "/u56db",
            "5" : "/u4e94",
            "6" : "/u516d"
        };
        if(/(y+)/.test(fmt)){
            fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
        }
        if(/(E+)/.test(fmt)){
            fmt=fmt.replace(RegExp.$1, ((RegExp.$1.length>1) ? (RegExp.$1.length>2 ? "/u661f/u671f" : "/u5468") : "")+week[this.getDay()+""]);
        }
        for(var k in o){
            if(new RegExp("("+ k +")").test(fmt)){
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
            }
        }
        return fmt;
    };
    minions.htmlspecialchars = function(string, quote_style, charset, double_encode){
        //       discuss at: http://phpjs.org/functions/htmlspecialchars/
        var optTemp = 0,
            i = 0,
            noquotes = false;
        if (typeof quote_style === 'undefined' || quote_style === null) {
            quote_style = 2;
        }
        string = string || '';
        string = string.toString();
        if (double_encode !== false) {
            // Put this first to avoid double-encoding
            string = string.replace(/&/g, '&amp;');
        }
        string = string.replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        var OPTS = {
            'ENT_NOQUOTES'          : 0,
            'ENT_HTML_QUOTE_SINGLE' : 1,
            'ENT_HTML_QUOTE_DOUBLE' : 2,
            'ENT_COMPAT'            : 2,
            'ENT_QUOTES'            : 3,
            'ENT_IGNORE'            : 4
        };
        if (quote_style === 0) {
            noquotes = true;
        }
        if (typeof quote_style !== 'number') {
            // Allow for a single string or an array of string flags
            quote_style = [].concat(quote_style);
            for (i = 0; i < quote_style.length; i++) {
                // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
                if (OPTS[quote_style[i]] === 0) {
                    noquotes = true;
                } else if (OPTS[quote_style[i]]) {
                    optTemp = optTemp | OPTS[quote_style[i]];
                }
            }
            quote_style = optTemp;
        }
        if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
            string = string.replace(/'/g, '&#039;');
        }
        if (!noquotes) {
            string = string.replace(/"/g, '&quot;');
        }

        return string;
    };
    // manager
    minions.user = {
        OFFLINE: 0,
        ONLINE: 1
    };
    minions.userManager = {
        users: {
            system: {uid: 'system', nick: 'system', motto: 'make jegarn better!', avatar: 'images/avatar.jpg', 'present' : minions.user.ONLINE}
        },
        addUser: function(user){
            this.users[user.uid] = user;
        },
        removeUser: function(uid){
            if(this.isUserExists(uid)){
                delete this.users[uid];
            }
        },
        isUserExists: function(uid){
            return typeof this.users[uid] != "undefined";
        },
        getUser: function(uid){
            if(this.isUserExists(uid)){
                return this.users[uid];
            }else{
                // make sync user get request
                var user = null;
                minions.ajax({
                    url : '/api/user/info',
                    type: 'GET',
                    data: {
                        user_id: uid
                    },
                    async: false,
                    success: function(data){
                        if(minions.response.isSuccess(data)){
                            user = minions.response.getResponseBody(data);
                        }
                    }
                });
                if(user){
                    return user;
                }
            }
            minions.log('getUser uid lost',uid);
            return null;
        }
    };
    minions.roster = {
        STATUS_REQUEST: 0,
        STATUS_RECEIVE: 1,
        STATUS_UNSUBSCRIBE: 2,
        STATUS_AGREE: 3,
        STATUS_REFUSED: 4,
        STATUS_BLACK: 5,
        updateRoster: function(user, target_id, status, remark, group_id, rank){
            var roster = null;
            minions.ajax({
                url : '/api/roster/update',
                type: 'POST',
                data: {
                    uid : user.uid,
                    token: user.token,
                    target_id: target_id,
                    status: status,
                    remark: remark,
                    group_id: group_id,
                    rank: rank
                },
                async: false,
                success: function(data){
                    if(minions.response.isSuccess(data)){
                        roster = minions.response.getResponseBody(data);
                    }else{
                        minions.log('updateRoster request failed', data);
                    }
                }
            });
            if(!roster){
                minions.log('updateRoster failed', arguments);
            }
            return roster;
        },
        getRoster: function(user, target_id){
            var roster = null;
            minions.ajax({
                url : '/api/roster/info',
                type: 'GET',
                data: {
                    uid: user.uid,
                    token: user.token,
                    target_id: target_id
                },
                async: false,
                success: function(data){
                    if(minions.response.isSuccess(data)){
                        roster = minions.response.getResponseBody(data);
                    }else{
                        minions.log('getRoster request failed', data);
                    }
                }
            });
            if(!roster){
                minions.log('getRoster lost', arguments);
            }
            return roster;
        }
    };
    minions.userRosterGroupManager = {
        userRosterGroup: {},
        addUserRosterAll: function(uid, groups){
            this.userRosterGroup[uid] = groups;
            for(var i in groups){
                var group = groups[i];
                if(group['rosters'] && group['rosters'].length > 0){
                    var rosters = group['rosters'];
                    for(var j in rosters){
                        var roster = rosters[j];
                        if(roster['user']){
                            minions.userManager.addUser(roster['user']);
                        }
                    }
                }
            }
        },
        addUserRoster: function(uid, groupId, roster){
            var groups = this.userRosterGroup[uid];
            for(var i in groups){
                if(groups[i].group_id == groupId){
                    if(typeof this.userRosterGroup[uid][i]['rosters'] == "undefined"){
                        this.userRosterGroup[uid][i]['rosters'] = [];
                    }
                    this.userRosterGroup[uid][i]['rosters'].push(roster);
                    break;
                }
            }
        },
        removeUserRosterAll: function(uid){
            if(typeof this.userRosterGroup[uid] != "undefined"){
                delete this.userRosterGroup[uid];
            }
        },
        getUserRosterAll: function(uid){
            if(typeof this.userRosterGroup[uid] != "undefined"){
                return this.userRosterGroup[uid];
            }
            return null;
        }
    };
    minions.group = {
        TYPE_GROUP: 0,
        TYPE_CHATROOM: 1,
        STATUS_REQUEST: 0,
        STATUS_INVITED: 1,
        STATUS_UNSUBSCRIBE: 2,
        STATUS_AGREE: 3,
        STATUS_REFUSED: 4,
        STATUS_BLACK: 5,
        create: function(user,type,name,description,callback){
            minions.ajax({
                url : '/api/group/create',
                type: 'POST',
                data: {
                    uid: user.uid,
                    token: user.token,
                    type: type,
                    name: name,
                    description: description
                },
                async: true
            }).done(callback);
        },
        list: function(user, type, status, callback){
            minions.ajax({
                url : '/api/group/list',
                type: 'GET',
                data: {
                    uid: user.uid,
                    token: user.token,
                    type: type,
                    status: status
                },
                async: true
            }).done(callback);
        },
        join: function(user, group_id, callback){
            minions.ajax({
                url : '/api/group/join',
                type: 'POST',
                data: {
                    uid: user.uid,
                    token: user.token,
                    group_id: group_id
                },
                async: true
            }).done(callback);
        },
        info: function(user, group_id, callback){
            minions.ajax({
                url : '/api/group/info',
                type: 'GET',
                data: {
                    uid: user.uid,
                    token: user.token,
                    group_id: group_id
                },
                async: true
            }).done(callback);
        },
        update_user: function(user, gid, user_id, status, callback){
            minions.ajax({
                url : '/api/group/manage_user',
                type: 'POST',
                data: {
                    uid: user.uid,
                    token: user.token,
                    gid: gid,
                    user_id: user_id,
                    status: status,
                    permission: ''
                },
                async: true
            }).done(callback);
        },
        defaultGroupIcon : 'images/avatar.jpg'
    };
    // widget
    minions.widget = function(){};
    minions.widget.initBase = function(){
        // init window
        minions.widget.resizeWindow();
        $(window).resize(minions.widget.resizeWindow);
        $(window).bind("beforeunload", function() {
            return null;
        });
        // dock init, dock must be first for others register
        minions.widget.dock.init();
        // recommend init
        minions.widget.recommend.init();
        // login and register change
        minions.widget.regBox.init();
        // faq article
        minions.widget.faq.init();
        // chat box init
        minions.widget.chatBox.init();
        // make div draggable
        minions.widget.draggable.init();
        // init chat window
        minions.widget.chatWindow.init();
        // init marquee
        setInterval(function(){
            $('.marquee').each(function(){
                var text = $(this).attr('data-text');
                if(text || $(this).width() > 170){
                    var start = $(this).attr('data-start');
                    if( !text || text == '') {
                        text = $(this).text();
                        $(this).attr('data-text', text);
                    }
                    if(start){
                        start = parseInt(start);
                        start = start < 0 ? (start < -3 ? 0 : start-1) : (start < text.length / 2 ? start + 2 : -1);
                    }else{
                        start = 0;
                    }
                    $(this).attr('data-start', start);
                    $(this).text(text.substring(start < 0 ? 0 : start));
                }
            });
        },600);
    };
    minions.widget.resizeWindow = function(){
        $('#chat').css({'width': $(window).width() + 'px', 'height': $(window).height() + 'px'});
    };
    minions.widget.draggable = {
        initialed: false,
        init: function(){
            if(!this.initialed){
                this.initialed = true;
                $(document).on('mouseenter', '.draggable header', function(){
                    $('.draggable').draggable({containment: "window", handle : 'header', cursor: "move"});
                });
                // init draggable z-index
                $(document).on('mousedown', '.draggable header', function () {
                    $('.draggable').css('z-index', 997);
                    $(this).parents('.draggable').css('z-index', 998);
                });
            }
        }
    };
    //noinspection JSUnusedGlobalSymbols
    minions.widget.dock = {
        items: {},
        initialed: false,
        init: function(){
            if(!this.initialed){
                this.initialed = true;
                $('#chat').prepend('<div class="dock"></div><div class="dock-items"></div>');
                $('.dock').css('opacity',0.5);
                $('.dock>div').css('opacity', 0.8);
                $(document).on('click','.dock-items img',function(){
                    var newId = $(this).attr('id');
                    var targetId = newId.substring(minions.widget.dock.ID_PRE.length);
                    $('#'+targetId).toggle();
                });
                minions.widget.dock.register('recommends','images/dock-recommend-user.png');
            }
        },
        ID_PRE: 'dock-',
        show : function(targetId){
           $('.dock-items').find('#' + this.ID_PRE + targetId).show();
        },
        hide : function(targetId){
            $('.dock-items').find('#' + this.ID_PRE + targetId).hide();
        },
        register: function(targetId,image,prepend){
            var newId = this.ID_PRE + targetId;
            if(typeof this.items[newId] == "undefined"){
                this.items[newId] = targetId;
                if(prepend){
                    $('.dock-items').prepend('<img id="'+newId+'" src="'+image+'" />');
                }else{
                    $('.dock-items').append('<img id="'+newId+'" src="'+image+'" />');
                }
                return true;
            }
            return false;
        },
        unRegister: function(targetId){
            var newId = this.ID_PRE + targetId;
            if(typeof this.items[newId] != "undefined"){
                $('.dock-items').find('#' + newId).remove();
                delete this.items[newId];
            }
        }
    };
    minions.widget.recommend = {
        initialed: false,
        init: function(){
            if(!this.initialed){
                this.initialed = true;
                $('#chat').append(this.getDom());
                $(window).resize(minions.widget.recommend.onResize);
                this.onResize();
                this.initUser();
                this.initUserContext();
                this.initGroupChat();
                this.initChatRoom();
                this.initGroupContext();
            }
        },
        onResize: function(){
            $('#recommend-container').height($(window).height() - 150);
        },
        getDom: function(){
            var html = '';
            html += '<div id="recommends"><div id="recommend-container" class="scroll-bar">';
            html += '	<div id="recommend-user">';
            html += '		<header>Recommend Users</header>';
            html += '		<div class="recommend-user">';
            html += '		</div>';
            html += '	</div>';
            html += '	<div id="recommend-groupchat">';
            html += '		<header>Recommend GroupChat</header>';
            html += '		<div class="recommend-groupchat">';
            html += '		</div>';
            html += '	</div>';
            html += '	<div id="recommend-chatroom">';
            html += '		<header>Recommend ChatRoom</header>';
            html += '		<div class="recommend-chatroom">';
            html += '		</div>';
            html += '	</div>';
            html += '</div></div>';
            return html;
        },
        USER_ID_PRE : 'recommend-user-',
        getUserDom : function(user){
            var id = this.USER_ID_PRE + user.uid;
            var html = '';
            html +='<li id="'+id+'">';
            html +='    <a href="#"><img' + (user.present == minions.user.OFFLINE ? ' class="gray"' : '') + ' src="'+user.avatar+'" /></a>';
            html +='    <p class="name">'+user.nick+'</p>';
            html +='    <p class="desc"><span class="marquee">'+user.motto+'</span></p>';
            html +='</li>';
            return html;
        },
        GROUP_ID_PRE : 'recommend-group-',
        getGroupDom: function(group){
            var id = this.GROUP_ID_PRE + group.group_id;
            var dataId = ' data-group-id="' + group.group_id + '"';
            var dataType = 'data-group-type="' + group.type + '"';
            var html = '';
            html += '<li id="' + id + '" class="recommend-group"' + dataId + dataType + '>';
            html +='    <a href="#"><img src="'+group.icon+'" /></a>';
            //noinspection JSUnresolvedVariable
            html +='    <p class="name">'+group.name+'<span class="people">'+group.member_count+' members</span></p>';
            html +='    <p class="desc"><span class="marquee">'+group.description+'</span></p>';
            html +='</li>';
            return html;
        },
        removeUser: function(uid){
            var id = this.USER_ID_PRE + uid;
            console.log(id);
            $('#' + id).remove();
        },
        USER_CONTEXT_ID: 'recommend-user-context',
        initUserContext: function(){
            $(document).on('mouseenter','#recommends',function(){
                var attachId = '.recommend-user li';
                var $dropDown = $('#'+minions.widget.recommend.USER_CONTEXT_ID);
                if($dropDown.length > 0){
                    minions.context.destroy(attachId);
                    $dropDown.remove();
                }
                var data = [], groups = minions.userRosterGroupManager.userRosterGroup;
                for(var uid in groups){
                    var user = minions.userManager.getUser(uid);
                    var userSubMenu = {text: user.nick , data: user.uid, subMenu: [{header: 'Groups'}]};
                    for(var i in groups[uid]){
                        var group = groups[uid][i];
                        userSubMenu.subMenu.push({text: group.name, data: group.group_id, action: function($this, $target){
                            if(!$this.parent().hasClass('dropdown-submenu')){
                                var groupId = $this.attr('data');
                                if(groupId && groupId != ''){ // may be clicked other items
                                    var uid = $this.parent().parent().prev().attr('data');
                                    var target_id = $target.attr('id').substring(minions.widget.recommend.USER_ID_PRE.length);
                                    var user = minions.userManager.getUser(uid);
                                    minions.post('/api/roster/create',{
                                        uid: user.uid,
                                        token: user.token,
                                        target_id: target_id
                                    },function(data){
                                        if(minions.response.isSuccess(data)){
                                            minions.alert('already send your request');
                                        }else{
                                            minions.alert(minions.response.getMessage(data));
                                        }
                                    });
                                }
                            }
                        }});
                    }
                    data.push(userSubMenu);
                }
				if(data.length > 0){
					data.unshift({header: 'Add Friend'});
				}else{
					data.unshift({header: 'Login First'});
				}
                minions.context.attach(attachId, data, minions.widget.recommend.USER_CONTEXT_ID);
            });
        },
        initUser: function(){
            minions.get('/api/user/recommend',function(data){
                if(minions.response.isSuccess(data)){
                    var users = minions.response.getResponseBody(data);
                    var $recommendUser = $('#recommend-user .recommend-user');
                    $recommendUser.html('');
                    for(var i in users){
                        minions.userManager.addUser(users[i]);
                        $recommendUser.append(minions.widget.recommend.getUserDom(users[i]));
                    }
                }else{
                    minions.alert(minions.response.getMessage(data));
                }
            });
        },
        groups: [],
        initGroupChat: function(){
            minions.get('/api/group/recommend',{
              type: minions.group.TYPE_GROUP
            },function(data){
                if(minions.response.isSuccess(data)){
                    var groups = minions.response.getResponseBody(data);
                    var $group = $('#recommend-groupchat .recommend-groupchat');
                    $group.html('');
                    for(var i in groups) {
                        minions.widget.recommend.groups.push(groups[i]);
                        $group.append(minions.widget.recommend.getGroupDom(groups[i]));
                    }
                }else{
                    minions.alert(minions.response.getMessage(data));
                }
            });
        },
        initChatRoom: function(){
            minions.get('/api/group/recommend',{
                type: minions.group.TYPE_CHATROOM
            },function(data){
                if(minions.response.isSuccess(data)){
                    var groups = minions.response.getResponseBody(data);
                    var $group = $('#recommend-chatroom .recommend-chatroom');
                    $group.html('');
                    for(var i in groups) {
                        minions.widget.recommend.groups.push(groups[i]);
                        $group.append(minions.widget.recommend.getGroupDom(groups[i]));
                    }
                }else{
                    minions.alert(minions.response.getMessage(data));
                }
            });
        },
        GROUP_CONTEXT_ID: 'recommend-group-context',
        initGroupContext: function(){
            $(document).on('mouseenter','#recommends',function(){
                var attachId = '.recommend-group';
                var $dropDown = $('#'+minions.widget.recommend.GROUP_CONTEXT_ID);
                if($dropDown.length > 0){
                    minions.context.destroy(attachId);
                    $dropDown.remove();
                }
                var data = [], groups = minions.userRosterGroupManager.userRosterGroup;
                for(var uid in groups){
                    var user = minions.userManager.getUser(uid);
                    var userSubMenu = {text: user.nick , data: user.uid, subMenu: [{header: 'Options'}]};
                    userSubMenu.subMenu.push({text: 'Join Group', action: function($this, $target){
                        if(!$this.parent().hasClass('dropdown-submenu')){
                            var uid = $this.parent().parent().prev().attr('data');
                            var groupId = $target.attr('data-group-id');
                            var groupType = $target.attr('data-group-type');
                            var user = minions.userManager.getUser(uid);
                            minions.group.join(user,groupId,function(data){
                                if(minions.response.isSuccess(data)){
                                    // if is a chatroom, add it to chatBox
                                    if(groupType == minions.group.TYPE_CHATROOM){
                                        minions.widget.chatBox.addOneChatroom(uid,'all',groupId);
                                    }
                                    minions.alert('operation success');
                                }else{
                                    minions.alert(minions.response.getMessage(data));
                                }
                            });
                        }
                    }});
                    data.push(userSubMenu);
                }
                if(data.length > 0){
                    data.unshift({header: 'Menu'});
                }else{
                    data.unshift({header: 'Login First'});
                }
                minions.context.attach(attachId, data, minions.widget.recommend.GROUP_CONTEXT_ID);
            });
        }
    };
    minions.widget.faq = {
        initialed: false,
        ID: 'faq',
        init: function () {
            if (!this.initialed) {
                this.initialed = true;
                var dockObj = minions.widget.dock;
                dockObj.register(this.ID, 'images/dock-faq.png', false);
                $('#' + dockObj.ID_PRE + this.ID).click(function(){
                    window.open('http://yaoguais.com/article/jegarn/faq_zh-cn.html');
                });
            }
        }
    };
    minions.widget.regBox = {
        initialed: false,
        init: function(){
            if(!this.initialed){
                this.initialed = true;
                $('#chat').append(this.getDom());
                this.bindToggle();
                this.bindSubmit();
                minions.widget.dock.register('regLoginBox', 'images/dock-login.png', true);
                $(document).on('keydown','.register input', function(e){
                   if(e.keyCode == 13){
                       minions.preventDefault(e);
                       $('.register a.button').click();
                   }
                });
            }
        },
        show : function(){
            $('.register').show();
        },
        hide : function(){
            $('.register').hide();
        },
        getDom : function(){
            var html = '';
            html += '<div class="register" id="regLoginBox">';
            html += '        <h1>Registration</h1>';
            html += '        <form action="/">';
            html += '            <hr>';
            html += '            <label class="icon"><i class="icon-user"></i></label>';
            html += '            <input type="text" name="account" placeholder="Account" required/>';
            html += '            <label class="icon"><i class="icon-shield"></i></label>';
            html += '            <input type="password" name="password" placeholder="Password" required/>';
            html += '            <label class="icon reg-handel"><i class="icon-star"></i></label>';
            html += '            <input class="reg-handel" type="text" name="nick" placeholder="Nick" required/>';
            html += '            <label class="icon reg-handel"><i class="icon-tags"></i></label>';
            html += '            <input class="reg-handel" type="text" name="motto" placeholder="Motto" required/>';
            html += '            <p class="reg-notice">Already a member ? tips: multi account login supported</p>';
            html += '            <a href="#" class="reg-login">Go and login</a>';
            html += '            <a href="#" class="button">Register</a>';
            html += '        </form>';
            html += '    </div>';
            return html;
        },
        bindToggle : function(){
            $('.reg-login').click(function(){
                if($(this).text() == "Go and login"){
                    $('.register .reg-handel').hide();
                    $('.register h1').text('Sign In');
                    $(this).prev().text('Not a member ?');
                    $(this).next().text('Login');
                    $(this).text('click to register');
                }else{
                    $('.register .reg-handel').show();
                    $('.register h1').text('Registration');
                    $(this).prev().text('Already a member ? tips: multi account login supported');
                    $(this).next().text('Register');
                    $(this).text('Go and login');
                }
            });
        },
        bindSubmit : function(){
            $(document).on('click', '.register a.button',function(){
                minions.log('register box be clicked');
                if($(this).text() == 'Register'){
                    minions.widget.regBox.register();
                }else{
                    minions.widget.regBox.login();
                }
            });
        },
        register: function(){
            minions.log('register click');
            var account = $('.register input[name="account"]').val();
            if(!account || account == ""){
                minions.alert('account required');
                return;
            }
            var password = $('.register input[name="password"]').val();
            if(!password || password == ""){
                minions.alert('password required');
                return;
            }
            var nick = $('.register input[name="nick"]').val();
            if(!nick || nick == ""){
                minions.alert('nick required');
                return;
            }
            var motto = $('.register input[name="motto"]').val();
            minions.post('/api/user/create',{
                account: account,
                password: password,
                nick: nick,
                motto: motto
            },function(data){
                minions.log('register response: ', data);
                if(minions.response.isSuccess(data)){
                    minions.demo.destroy();
                    var user = minions.response.getResponseBody(data);
                    if(minions.widget.chat.addUser(user)){
                        minions.widget.regBox.hide();
                        minions.widget.recommend.removeUser(user.uid);
                        minions.widget.chatBox.createForNewUser(user);
                    }else{
                        minions.alert('user already login');
                    }
                }else{
                    minions.alert(minions.response.getMessage(data));
                }
            });
        },
        login: function(){
            minions.log('login click');
            var account = $('.register input[name="account"]').val();
            if(!account || account == ""){
                minions.alert('account required');
                return;
            }
            var password = $('.register input[name="password"]').val();
            if(!password || password == ""){
                minions.alert('password required');
                return;
            }
            minions.post('/api/user/login',{
                account: account,
                password: password
            },function(data){
                minions.log('login response: ', data);
                if(minions.response.isSuccess(data)){
                    minions.demo.destroy();
                    var user = minions.response.getResponseBody(data);
                    if(minions.widget.chat.addUser(user)){
                        minions.widget.regBox.hide();
                        minions.widget.recommend.removeUser(user.uid);
                        minions.widget.chatBox.createForNewUser(user);
                    }else{
                        minions.alert('user already login');
                    }
                }else{
                    minions.alert(minions.response.getMessage(data));
                }
            });
        }
    };
    //noinspection JSUnusedGlobalSymbols
    minions.widget.chatBox = {
        chatBoxList: [],
        initialed: false,
        init : function(){
            if(!this.initialed){
                this.initialed = true;
                $(document).on('click', '.chat-box footer li', function(){
                    $(this).siblings().removeClass('selected');
                    $(this).addClass('selected');
                    var $contain = $(this).parent().parent().prev();
                    $contain.find('>div').hide();
                    $contain.find('>div').eq($(this).index()).show();
                    var title = $(this).text();
                    $contain.parent().find('header h2').text(title);
                });
                $('.chat-box footer li').eq(1).click();
                $('.chat-box header img').css("opacity", 0.5);
                this.initConversation();
                this.initRoster();
                this.initGroupChat();
                this.initChatroom();
                // init connect status
                setInterval(function(){
                    var connectLabelLoop = ['connect.','connect..','connect...'];
                    $('.chat-box').each(function(){
                        var uid = $(this).attr('data-uid');
                        var $connectLabel = $(this).find('header span');
                        if(minions.widget.chat.isUserConnected(uid)){
                            $connectLabel.text('');
                        }else{
                            var i,j = 0, text = $connectLabel.text();
                            for(i in connectLabelLoop){
                                if(text == connectLabelLoop[i]){
                                    j = i + 1;
                                    break;
                                }
                            }
                            j = j % connectLabelLoop.length;
                            $connectLabel.text(connectLabelLoop[j]);
                        }
                    });
                }, 800);
            }
        },
        ID_PRE: 'chatbox-',
        getDom: function(user){
            var id = this.ID_PRE + user.uid;
            var html = '<div id="'+id+'" data-uid="' + user.uid + '" class="chat-box draggable"><header><h2>Message</h2><img src="'+user.avatar+'"/><span></span></header>';
            html += '<div class="chat-contain"><div class="chat-chat"></div><div class="chat-people"></div>';
            html += '<div class="chat-groupchat"></div> <div class="chat-chatroom"> </div></div>';
            html += '<footer><ul> <li class="chat-bl"><i class="icon-chat"></i>Message</li> <li><i class="icon-people"></i>Contact</li>';
            html += '<li><i class="icon-groupchat"></i>GroupChat</li><li class="chat-br"><i class="icon-chatroom"></i>ChatRoom</li></ul>';
            html += '</footer></div>';
            return html;
        },
        create : function(user){
            this.init();
            var html = this.getDom(user);
            $('#chat').append(html);
            var id = this.ID_PRE + user.uid;
            $('#'+id).find('footer ul li').eq(0).click();
            minions.widget.dock.register(this.ID_PRE + user.uid,user.avatar);
        },
        destroy: function(uid){
            $('#' + this.ID_PRE + uid).remove();
            minions.widget.dock.unRegister(this.ID_PRE + uid);
            // remove all chatwindow
            minions.widget.chatWindow.removeAll(uid);
        },
        createForNewUser: function(user){
            minions.userManager.addUser(user);
            this.create(user);
            this.loadRoster(user);
            this.loadGroupChat(user);
            this.loadChatroom(user);
        },
        // id design
        NOTIFICATION: 'n',
        CONVERSATION: 's',
        CONTACT: 'c',
        GROUPCHAT: 'g',
        CHATROOM: 'r',
        CHATWINDOW: 'w',
        DEFAULT_CHAR: 'X',
        SEPARATOR: 'U',
        getId: function(type,uid,target,chat,groupId,chatroomId){
            uid = uid || this.DEFAULT_CHAR;
            target = target || this.DEFAULT_CHAR;
            chat = chat || this.DEFAULT_CHAR;
            groupId = groupId || this.DEFAULT_CHAR;
            chatroomId = chatroomId || this.DEFAULT_CHAR;
            var data = [uid, target, chat, groupId, chatroomId];
            return type+data.join(this.SEPARATOR);
        },
        parseId : function(id){
            var str = id.substring(1);
            var info = str.split(this.SEPARATOR);
            return {
                type: id.substring(0,1),
                uid : info[0] != this.DEFAULT_CHAR ? info[0] : null,
                target: info[1] != this.DEFAULT_CHAR ? info[1] : null,
                chat : info[2] != this.DEFAULT_CHAR ? true : null,
                groupId: info[3] != this.DEFAULT_CHAR ? info[3] : null,
                chatroomId: info[4] != this.DEFAULT_CHAR ? info[4] : null
            };
        },
        convertId: function(id,type){
            return type + id.substring(1);
        },
        parseIdFromPacket: function(pkt, send){
            var packet = jegarn.packet;
            var id = '';
            var data = [];
            if(send){
                data.push(pkt.from);
            }else{
                data.push(pkt.to);
            }
            if(pkt.type == packet.Chat.TYPE){
                var chatPacket = (new packet.Chat).getPacketFromPacket(pkt);
                if(send){
                    data.push(chatPacket.to);
                }else{
                    data.push(chatPacket.from);
                }
                data.push(1);
                data.push(this.DEFAULT_CHAR);
                data.push(this.DEFAULT_CHAR);
            }else if(pkt.type == packet.GroupChat.TYPE){
                var groupchatPacket = (new packet.GroupChat).getPacketFromPacket(pkt);
                data.push(this.DEFAULT_CHAR);
                data.push(this.DEFAULT_CHAR);
                data.push(groupchatPacket.getGroupId());
                data.push(this.DEFAULT_CHAR);
            }else if(pkt.type == packet.Chatroom.TYPE){
                var chatroomPacket = (new packet.Chatroom).getPacketFromPacket(pkt);
                data.push(this.DEFAULT_CHAR);
                data.push(this.DEFAULT_CHAR);
                data.push(this.DEFAULT_CHAR);
                data.push(chatroomPacket.getGroupId());
            }else if(pkt.type == packet.Notification.TYPE){// will not use id
                if(send){
                    data.push(pkt.to);
                }else{
                    data.push(pkt.from);
                }
                data.push(this.DEFAULT_CHAR);
                data.push(this.DEFAULT_CHAR);
                data.push(this.DEFAULT_CHAR);
            }else{
                minions.log('parse packet to get id failed',pkt);
                return '';
            }
            return this.CHATWINDOW + data.join(this.SEPARATOR);
        },
        initConversation: function(){
            $(document).on('click', '.chat-box .chat-chat li',function(){
                var id = $(this).attr('id');
                if(id && id != ''){
                    var title = $(this).find('.name').text();
                    var chatBox = minions.widget.chatBox;
                    minions.widget.chatWindow.create(chatBox.convertId(id,chatBox.CHATWINDOW), title);
                }
            });
        },
        addConversationRoster: function(id){
            var conversationId = this.convertId(id, this.CONVERSATION);
            var idInfo = this.parseId(id);
            var targetId = null;
            if(idInfo.chat){
                targetId = this.convertId(id, this.CONTACT);
            }else if(idInfo.groupId){
                targetId = this.convertId(id, this.GROUPCHAT);
            }else{
                targetId = this.convertId(id, this.CHATROOM);
            }
            var $target = $('#' + targetId);
            if($target.length > 0){
                var uid = idInfo['uid'];
                var $conversationHandle = $('#'+this.ID_PRE+uid).find('.chat-chat');
                if($('#'+conversationId).length > 0){
                    $('#'+conversationId).prependTo($conversationHandle);
                }else{
                    var html = '<li id="'+conversationId+'">'+$('#'+targetId).html()+'</li>';
                    $conversationHandle.prepend(html);
                }
            }else{
                minions.log('addConversationRoster target lost',targetId);
            }
        },
        initFriendNotify: function(){
            $(document).on('click','.friend-notify .ok',function(){
                var $container = $(this).parents('.friend-notify');
                var targetId = $container.attr('data-target-id');
                var uid = $container.attr('data-uid');
                var user = minions.userManager.getUser(uid);
                var roster = minions.roster.getRoster(user, targetId);
                if(roster){
                    roster = minions.roster.updateRoster(user,targetId, minions.roster.STATUS_AGREE, roster.remark, roster.group_id, roster.rank);
                    if(roster){
                        minions.alert('operation success');
                        $container.remove();
                    }else{
                        minions.alert('operation failed');
                    }
                }
            });
            $(document).on('click','.friend-notify .no',function(){
                var $container = $(this).parents('.friend-notify');
                var targetId = $container.attr('data-target-id');
                var uid = $container.attr('data-uid');
                var subType = $container.attr('data-sub-type');
                if(subType == jegarn.packet.FriendRequestNotification.SUB_TYPE){
                    var user = minions.userManager.getUser(uid);
                    var roster = minions.roster.getRoster(user, targetId);
                    if(roster){
                        roster = minions.roster.updateRoster(user,targetId, minions.roster.STATUS_REFUSED, roster.remark, roster.group_id, roster.rank);
                        if(roster){
                            $container.remove();
                        }else{
                            minions.alert('operation failed');
                        }
                    }
                }else{
                    $container.remove();
                }
            });
        },
        addFriendNotification: function(pkt){
            var uid = pkt.to;
            var id = this.ID_PRE + uid;
            var targetId = pkt.getUserId();
            var target = minions.userManager.getUser(targetId);
            if(target){
                var subType = pkt.getSubType();
                var html = '';
                html +='<li class="notify friend-notify" data-sub-type="'+subType+'" data-uid="'+uid+'" data-target-id="'+targetId+'">';
                html +='    <a href="#"><img src="'+target.avatar+'"></a>';
                if( subType== jegarn.packet.FriendRequestNotification.SUB_TYPE){
                    html +='    <p class="desc marquee">'+target.nick+' request for making friends</p>';
                    html +='    <p class="button"><span class="ok">agree</span><span class="no">ignore</span></p>';
                }else if(subType == jegarn.packet.FriendRefusedNotification.SUB_TYPE){
                    html +='    <p class="desc marquee">'+target.nick+' refused your request</p>';
                    html +='    <p class="button"><span class="no">ignore</span></p>';
                }else if(subType == jegarn.packet.FriendAgreeNotification.SUB_TYPE){
                    html +='    <p class="desc marquee">you and '+target.nick+' are friends now</p>';
                    html +='    <p class="button"><span class="no">ignore</span></p>';
                }else{
                    minions.log('addFriendNotification subType not support', pkt);
                }
                html +='</li>';
                var $target = $('#'+id).find('.chat-chat');
                $target.prepend(html);
                // agree, then update roster
                if(subType == jegarn.packet.FriendAgreeNotification.SUB_TYPE){
                    var currentUser = minions.userManager.getUser(uid);
                    if(currentUser){
                        var roster = minions.roster.getRoster(currentUser, targetId);
                        if(roster){
                            roster.user = minions.userManager.getUser(roster.target_id);
                            minions.userRosterGroupManager.addUserRoster(uid, roster.group_id, roster);
                            minions.widget.chatBox.refreshUserRosterView(roster.uid);
                        }
                    }
                }
            }else{
                minions.log('addFriendNotification target is not found',pkt);
            }
        },
        initGroupNotify: function(){
            $(document).on('click','.group-notify .ok',function(){
                var $container = $(this).parents('.group-notify');
                var targetId = $container.attr('data-target-id');
                var uid = $container.attr('data-uid');
                var groupId = $container.attr('data-group-id');
                var user = minions.userManager.getUser(uid);
                minions.group.update_user(user, groupId, targetId, minions.group.STATUS_AGREE, function(data){
                    if(minions.response.isSuccess(data)){
                        minions.alert('operation success');
                        $container.remove();
                    }else{
                        minions.log('update_user request failed', data);
                        minions.alert('operation failed');
                    }
                });
            });
            $(document).on('click','.group-notify .no',function(){
                var $container = $(this).parents('.group-notify');
                var targetId = $container.attr('data-target-id');
                var uid = $container.attr('data-uid');
                var groupId = $container.attr('data-group-id');
                var subType = $container.attr('data-sub-type');
                if(subType == jegarn.packet.GroupRequestNotification.SUB_TYPE){
                    var user = minions.userManager.getUser(uid);
                    minions.group.update_user(user, groupId, targetId, minions.group.STATUS_REFUSED, function(data){
                        if(minions.response.isSuccess(data)){
                            minions.alert('operation success');
                            $container.remove();
                        }else{
                            minions.log('update_user request failed', data);
                            minions.alert('operation failed');
                        }
                    });
                }else{
                    $container.remove();
                }
            });
        },
        addGroupNotification: function (pkt) {
            var uid = pkt.to;
            var id = this.ID_PRE + uid;
            var targetId = pkt.getUserId();
            var groupId = pkt.getGroupId();
            var subType = pkt.getSubType();
            var $target = $('#'+id).find('.chat-chat');
            if(subType == jegarn.packet.GroupDisbandNotification.SUB_TYPE){
                var name = pkt.getGroupName();
                var html = '';
                html +='<li class="notify group-notify" data-sub-type="'+subType+'" data-uid="'+uid+'" data-group-id="'+groupId+'" data-target-id="'+targetId+'">';
                html +='    <a href="#"><img src="'+ minions.group.defaultGroupIcon +'"></a>';
                html +='    <p class="desc marquee">group "'+name+'" was deleted by owner</p>';
                html +='    <p class="button"></p>';
                html +='</li>';
                $target.prepend(html);
            }else{
                var user = minions.userManager.getUser(uid);
                minions.group.info(user, groupId, function (data) {
                    if(minions.response.isSuccess(data)){
                        var group = minions.response.getResponseBody(data);
                        var target = minions.userManager.getUser(targetId);
                        var html = '';
                        html +='<li class="notify group-notify" data-sub-type="'+subType+'" data-uid="'+uid+'" data-group-id="'+groupId+'" data-target-id="'+targetId+'">';
                        html +='    <a href="#"><img src="'+ group.icon +'"></a>';
                        if( subType== jegarn.packet.GroupRequestNotification.SUB_TYPE){
                            html +='    <p class="desc marquee">'+target.nick+' request for joining group "'+group.name+'"</p>';
                            html +='    <p class="button"><span class="ok">agree</span><span class="no">ignore</span></p>';
                        }else if(subType == jegarn.packet.GroupRefusedNotification.SUB_TYPE){
                            html +='    <p class="desc marquee">manager of group "'+group.name+'" refused your request</p>';
                            html +='    <p class="button"><span class="no">ignore</span></p>';
                        }else if(subType == jegarn.packet.GroupAgreeNotification.SUB_TYPE){
                            html +='    <p class="desc marquee">you are a member of group "'+group.name+'" now</p>';
                            html +='    <p class="button"><span class="no">ignore</span></p>';
                        }else if(subType == jegarn.packet.GroupQuitNotification.SUB_TYPE){
                            html +='    <p class="desc marquee">'+target.nick+' quited group "'+group.name+'"</p>';
                            html +='    <p class="button"><span class="no">ignore</span></p>';
                        }else{
                            minions.log('addGroupNotification subType not support', pkt);
                        }
                        html +='</li>';
                        $target.prepend(html);
                        // agree then add group to chatBox
                        if(subType == jegarn.packet.GroupAgreeNotification.SUB_TYPE){
                            minions.widget.chatBox.addOneGroupChat(uid,'all',groupId,group);
                        }
                    }else{
                        minions.log('addGroupNotification request failed', data);
                    }
                });
            }
        },
        initRoster: function(){
            // rosters open and close
            $(document).on('click','.list-group .group-info',function(){
                if($(this).hasClass('down')){
                    $(this).removeClass('down');
                    $(this).next('ul').hide();
                }else{
                    $(this).addClass('down');
                    $(this).next('ul').show();
                }
            });
            // add to conversation and create chat window
            $(document).on('click', '.chat-box .chat-people .list-group li',function(){
                var id = $(this).attr('id');
                var title = $(this).find('.name').text();
                var chatBox = minions.widget.chatBox;
                minions.widget.chatWindow.create(chatBox.convertId(id,chatBox.CHATWINDOW), title);
            });
            this.initFriendNotify();
            this.initGroupNotify();
        },
        getRosterGroupDom : function(groupInfo){
            var hasRoster = typeof groupInfo['rosters'] != "undefined";
            var count = hasRoster ? groupInfo['rosters'].length : 0;
            var html = '';
            html +='<li class="list-group">';
            html +='    <div class="group-info"><span>'+groupInfo['name']+'</span><span class="online">'+count+'/'+count+'</span></div>';
            html +='    <ul>';
            if(hasRoster){
                for(var i in groupInfo['rosters']){
                    var roster = groupInfo['rosters'][i];
                    var user = roster['user'];
                    if(user){
                        html += this.getRosterDom(user, roster);
                    }
                }
            }
            html +='    </ul>';
            html +='</li>';
            return html;
        },
        getRosterDom: function(target, roster){
            var id = this.getId(this.CONTACT,roster.uid,roster.target_id,1,null,null);
            var name = roster.remark ? roster.remark + '[' + target.nick + ']' : target.nick;
            var html ='        <li id="'+id+'">';
            html +='            <a href="#"><img' + (target.present == minions.user.OFFLINE ? ' class="gray"' : '') + ' src="'+target.avatar+'" /></a>';
            html +='            <p class="name">'+name+'</p>';
            html +='            <p class="desc"><span></span><span class="marquee">'+target.motto+'</span></p>';
            html +='        </li>';
            return html;
        },
        loadRoster: function(user){
            minions.get('/api/roster/list_all',{
                uid: user.uid,
                token: user.token
            },function(data){
                if(minions.response.isSuccess(data)){
                    var list = minions.response.getResponseBody(data);
                    minions.userRosterGroupManager.addUserRosterAll(user.uid, list);
                    minions.widget.chatBox.refreshUserRosterView(user.uid);
                }
            });
        },
        refreshUserRosterView: function(uid){
            var list = minions.userRosterGroupManager.getUserRosterAll(uid);
            var $roster = $('#' +this.ID_PRE + uid).find('.chat-people');
            $roster.html('');
            for(var i in list){
                $roster.append(this.getRosterGroupDom(list[i]));
            }
        },
        GROUPCHAT_CONTEXT_ID: 'groupchat-context',
        initGroupChat: function(){
            $(document).on('click', '.chat-box .chat-groupchat li',function(){
                var id = $(this).attr('id');
                if(id && id != ''){
                    var title = $(this).find('.name').text();
                    var chatBox = minions.widget.chatBox;
                    minions.widget.chatWindow.create(chatBox.convertId(id,chatBox.CHATWINDOW), title);
                }
            });
            minions.context.attach('.chat-groupchat', [{header: 'Menu'}, {
                text: 'Create Group',
                action: function ($this, $target) {
                    var option = $this.text();
                    if (option == 'Create Group') {
                        var uid = $target.parents('.chat-box').attr('data-uid');
                        $('.create-group').remove();
                        var html = minions.widget.chatBox.getCreateGroupDom(uid,minions.group.TYPE_GROUP);
                        $('#chat').append(html);
                    }
                }
            }], this.GROUPCHAT_CONTEXT_ID);
            // create request bind
            $(document).on('click', '.create-group .cancel',function(){
                $('.create-group').remove();
            });
            $(document).on('click', '.create-group .ok',function(){
                var $container = $(this).parents('.create-group');
                var name = $container.find('input[name="name"]').val();
                if(!name || name == ""){
                    minions.alert('name required');
                    return;
                }
                var desc = $container.find('textarea[name="description"]').val();
                if(!desc || desc == ""){
                    minions.alert('intro required');
                    return;
                }
                var uid = $container.attr('data-uid');
                var type = $container.attr('data-type');
                var user = minions.userManager.getUser(uid);
                minions.group.create(user,type,name,desc,function(data){
                    if(minions.response.isSuccess(data)){
                        $('.create-group').remove();
                        var group = minions.response.getResponseBody(data);
                        var id = minions.widget.chatBox.ID_PRE + uid;
                        var html = null;
                        if(type == minions.group.TYPE_GROUP){
                            var $groupchat = $('#'+ id).find('.chat-groupchat');
                            html = minions.widget.chatBox.getGroupChatDom(uid, null, group);
                            $groupchat.append(html);
                        }else{
                            var $chatroom = $('#'+ id).find('.chat-chatroom');
                            html = minions.widget.chatBox.getGroupChatDom(uid, null, group);
                            $chatroom.append(html);
                        }
                        minions.alert('create success');
                    }else{
                        minions.alert(minions.response.getMessage(data));
                    }
                });
            });
        },
        CHATROOM_CONTEXT_ID: 'chatroom-context',
        initChatroom: function(){
            $(document).on('click', '.chat-box .chat-chatroom li',function(){
                var id = $(this).attr('id');
                if(id && id != ''){
                    var title = $(this).find('.name').text();
                    var chatBox = minions.widget.chatBox;
                    minions.widget.chatWindow.create(chatBox.convertId(id,chatBox.CHATWINDOW), title);
                }
            });
            minions.context.attach('.chat-chatroom', [{header: 'Menu'}, {
                text: 'Create Room',
                action: function ($this, $target) {
                    var option = $this.text();
                    if (option == 'Create Room') {
                        var uid = $target.parents('.chat-box').attr('data-uid');
                        $('.create-group').remove();
                        var html = minions.widget.chatBox.getCreateGroupDom(uid,minions.group.TYPE_CHATROOM);
                        $('#chat').append(html);
                    }
                }
            }], this.CHATROOM_CONTEXT_ID);
        },
        getCreateGroupDom: function(uid, type){
            var dataUid = ' data-uid="'+uid+'"';
            var dataType = ' data-type="'+type+'"';
            var title = type == minions.group.TYPE_GROUP ? 'Create Group Chat' : 'Create Chat Room';
            var html = '';
            html +='<div class="create-group"' + dataUid + dataType + '>';
            html +='    <header><h2>'+title+'</h2></header>';
            html +='    <ul>';
            html +='        <li><label>name:</label><input name="name" type="text"></li>';
            html +='        <li><label>intro:</label><textarea name="description"></textarea></li>';
            html +='    </ul>';
            html +='    <footer>';
            html +='        <button class="ok btn">Create</button><button class="cancel btn">Cancel</button>';
            html +='    </footer>';
            html +='</div>';
            return html;
        },
        getGroupChatDom: function(uid, target, group){
            var id = this.getId(this.GROUPCHAT,uid,this.DEFAULT_CHAR,this.DEFAULT_CHAR,group.group_id,this.DEFAULT_CHAR);
            var html = '';
            html +='<li id="'+id+'">';
            html +='    <a href="#"><img src="'+group.icon+'" /></a>';
            html +='    <p class="name">'+group.name+'</p>';
            html +='    <p class="desc"><span class="marquee">'+group.description+'</span></p>';
            html +='</li>';
            return html;
        },
        addOneGroupChat: function(uid, target, groupId, group){
            var user = minions.userManager.getUser(uid);
            if(!group){
                minions.group.info(user,groupId,function(data){
                    if(minions.response.isSuccess(data)){
                        var group = minions.response.getResponseBody(data);
                        var html = minions.widget.chatBox.getGroupChatDom(uid, target, group);
                        var id = minions.widget.chatBox.ID_PRE + uid;
                        $('#'+id).find('.chat-groupchat').append(html);
                    }
                });
            }else{
                var html = minions.widget.chatBox.getGroupChatDom(uid, target, group);
                var id = minions.widget.chatBox.ID_PRE + uid;
                $('#'+id).find('.chat-groupchat').append(html);
            }
        },
        removeOneGroupChat: function(uid, target, groupId){
            var id = this.getId(this.GROUPCHAT,uid,this.DEFAULT_CHAR,this.DEFAULT_CHAR,groupId,this.DEFAULT_CHAR);
            $('#'+id).remove();
        },
        getChatroomDom: function(uid, target, group){
            var id = this.getId(this.CHATROOM,uid,this.DEFAULT_CHAR,this.DEFAULT_CHAR,this.DEFAULT_CHAR,group.group_id);
            var html = '';
            html +='<li id="'+id+'">';
            html +='    <a href="#"><img src="'+group.icon+'" /></a>';
            html +='    <p class="name">'+group.name+'</p>';
            html +='    <p class="desc"><span class="marquee">'+group.description+'</span></p>';
            html +='</li>';
            return html;
        },
        addOneChatroom: function(uid, target, groupId, group){
            var user = minions.userManager.getUser(uid);
            if(!group){
                minions.group.info(user,groupId,function(data){
                    if(minions.response.isSuccess(data)){
                        var group = minions.response.getResponseBody(data);
                        var html = minions.widget.chatBox.getChatroomDom(uid, target, group);
                        var id = minions.widget.chatBox.ID_PRE + uid;
                        $('#'+id).find('.chat-chatroom').append(html);
                    }
                });
            }else{
                var html = minions.widget.chatBox.getChatroomDom(uid, target, group);
                var id = minions.widget.chatBox.ID_PRE + uid;
                $('#'+id).find('.chat-chatroom').append(html);
            }
        },
        removeOneChatroom: function(uid, target, groupId){
            var id = this.getId(this.CHATROOM,uid,this.DEFAULT_CHAR,this.DEFAULT_CHAR,this.DEFAULT_CHAR,groupId);
            $('#'+id).remove();
        },
        loadGroupChat: function(user){
            minions.group.list(user,minions.group.TYPE_GROUP, minions.group.STATUS_AGREE, function(data){
               if(minions.response.isSuccess(data)){
                   var uid = user.uid;
                   var id = minions.widget.chatBox.ID_PRE + uid;
                   var $groupchat = $('#'+ id).find('.chat-groupchat');
                   var list = minions.response.getResponseBody(data);
                   for(var i in list){
                       $groupchat.append(minions.widget.chatBox.getGroupChatDom(uid, 'all', list[i]));
                   }
               }else{
                   minions.log('loadGroupChat error', data);
               }
            });
        },
        loadChatroom: function(user){
            minions.group.list(user,minions.group.TYPE_CHATROOM, minions.group.STATUS_AGREE, function(data){
                if(minions.response.isSuccess(data)){
                    var uid = user.uid;
                    var id = minions.widget.chatBox.ID_PRE + uid;
                    var $chatroom = $('#'+ id).find('.chat-chatroom');
                    var list = minions.response.getResponseBody(data);
                    for(var i in list){
                        $chatroom.append(minions.widget.chatBox.getChatroomDom(uid, 'all', list[i]));
                    }
                }else{
                    minions.log('loadGroupChat error', data);
                }
            });
        }
    };
    //noinspection JSUnusedGlobalSymbols
    minions.widget.chat = {
        config : {
            host: null,
            port : null,
            reconnect: 0
        },
        users : [],
        instances : {},
        isUserExists : function (key,value){
            for(var i in this.users){
                if(this.users[i][key] == value){
                    return true;
                }
            }
            return false;
        },
        addUser : function(model){
            if(!this.isUserExists('uid', model.uid)){
                this.users.push(model);
                this.connect(model);
                return true;
            }
            return false;
        },
        getUser : function (key,value){
            for(var i in this.users){
                if(this.users[i][key] == value){
                    return this.users[i];
                }
            }
            return null;
        },
        removeUser : function (key,value){
            for(var i in this.users){
                if(this.users[i][key] == value){
                    var uid = this.users[i].uid;
                    minions.widget.chatBox.destroy(uid);
                    this.instances[uid].close();
                    delete this.instances[uid];
                    return this.users.splice(i,1);
                }
            }
            return null;
        },
        connect: function(userModel){
            var instance = new jegarn.client(this.config.host, this.config.port, this.config.reconnect);
            instance.setConnectListener(this.connectListener);
            instance.setDisconnectListener(this.disconnectListener);
            instance.setErrorListener(this.errorListener);
            instance.setPacketListener(this.packetListener);
            instance.setSendListener(this.sendListener);
            instance.setUser(userModel.account, userModel.token);
            this.instances[userModel.uid] = instance;
            instance.connect();
        },
        sendPacket: function(packet){
            var uid = packet.from;
            if(typeof this.instances[uid] != "undefined"){
                return this.instances[uid].sendPacket(packet);
            }else{
                return false;
            }
        },
        isUserConnected: function(uid){
            return typeof this.instances[uid] != "undefined" &&  this.instances[uid].authorized;
        },
        connectListener: function(s){
            console.log('connect',s);
        },
        disconnectListener: function(evt, s){
            minions.widget.chat.removeUser('uid', s.uid);
            console.log('disconnect',evt, s);
        },
        errorListener: function(evt, s){
            minions.widget.chat.removeUser('uid', s.uid);
            console.log('error',evt,s);
        },
        packetListener: function(packet, s){
            console.log('packet',packet, s);
            var chatBox = minions.widget.chatBox;
            var id = chatBox.parseIdFromPacket(packet, false);
            if(id != ''){
                id = chatBox.convertId(id, chatBox.CHATWINDOW);
                var target = minions.userManager.getUser(packet.from);
                var user = minions.userManager.getUser(packet.to);
                if(target && user){
                    minions.widget.chatWindow.addRecvPacket(id, packet, user, target);
                }
            }
        },
        sendListener: function(packet, s){
            console.log('send', packet, s);
        }
    };
    minions.widget.chatWindow = {
        initialed: false,
        init : function(){
            if(!this.initialed){
                this.initialed = true;
                $(document).on('click','.chat-window .close',function(){
                   $(this).parent().parent().css('display', 'none');
                    // add to conversation tab
                    var id = $(this).parents('.chat-window').attr('id');
                    minions.widget.chatBox.addConversationRoster(id);
                });
                $(document).on('click','.chat-window .send',function(){
                    minions.widget.chatWindow.sendCallback(this);
                });
                $(document).on('keydown','.chat-window .message-input',function(e){
                    if(e.keyCode == 13){
                        minions.preventDefault(e);
                        minions.widget.chatWindow.sendCallback(this);
                    }
                });
            }
        },
        sendCallback: function(target){
            var $chatWindow = $(target).parents('.chat-window');
            var $textInput = $chatWindow.find('.message-input');
            var text = $textInput.val();
            if(text && text != ""){
                var id = $chatWindow.attr('id');
                var chatBox = minions.widget.chatBox;
                var idInfo = chatBox.parseId(id);
                var packet;
                if(idInfo.chat){
                    packet = new jegarn.packet.TextChat();
                    packet.to = idInfo.target;
                }else if(idInfo.groupId){
                    packet = new jegarn.packet.TextGroupChat();
                    packet.setSendToAll();
                    packet.setGroupId(idInfo.groupId);
                }else if(idInfo.chatroomId){
                    packet = new jegarn.packet.TextChatroom();
                    packet.setSendToAll();
                    packet.setGroupId(idInfo.chatroomId);
                }else{
                    minions.log('chat window id parse failed',id,idInfo);
                    return false;
                }
                packet.from = idInfo.uid;
                packet.setText(text);
                var html = minions.widget.chatWindow.getDomBySendTextPacket(packet);
                var sendSuccess = minions.widget.chat.sendPacket(packet);
                if(sendSuccess){
                    $chatWindow.find('.chat-wp').append(html);
                    var $scrollBar = $chatWindow.find('.scroll-bar');
                    $scrollBar.scrollTop($scrollBar[0].scrollHeight);
                    $textInput.val('');
                }else{
                    minions.alert('Send Failed');
                }
            }
        },
        getDom: function(id,title){
            var html = '';
            html += '<div class="chat-window draggable" id="'+id+'">';
            html += '        <header>';
            html += '            <h2>'+title+'</h2>';
            html += '            <button class="close">Close</button>';
            html += '        </header>';
            html += '        <div class="chat-content scroll-bar">';
            html += '            <div class="chat-wp">';
            html += '            </div>';
            html += '        </div>';
            html += '        <footer>';
            html += '            <textarea class="message-input"></textarea>';
            html += '            <span class="send">Send</span>';
            html += '        </footer>';
            html += '    </div>';
            return html;
        },
        create: function(id, title, show){
            this.init();
            //noinspection JSJQueryEfficiency
            if($('#'+id).length > 0){
                if(show !== false) $('#'+id).show();
            }else{
                $('#chat').append(this.getDom(id, title));
            }
        },
        removeAll: function(uid){
            uid = '' + uid;
            $('.chat-window').each(function(){
                var id = $(this).attr('id');
                var curUid = id.substring(1,uid.length+1);
                if( curUid == uid){ // hack for parseId
                    $(this).remove();
                }
            });
        },
        latestSendTime: {},
        latestRecvTime: {},
        getLatestTimeDom: function(collection, uid){
            var date = new Date();
            var now = date.getTime();
            if(typeof collection[uid] != "undefined" && now - collection[uid] < 60000){
                return '';
            }
            collection[uid] = now;
            var timeStr = date.pattern('yyyy-MM-dd HH:mm');
            return '<div class="chat-time"><span>'+timeStr+'</span></div>';
        },
        getDomBySendTextPacket: function(packet){
            var user = minions.userManager.getUser(packet.from);
            if(!user){
                return '';
            }
            var html = '';
            html += this.getLatestTimeDom(this.latestSendTime, packet.from);
            html +='<div class="chat-mr">';
            html +='    <img class="avatar" src="'+user.avatar+'" />';
            html +='    <p class="name">'+user.nick+'</p>';
            html +='    <p class="content">'+minions.htmlspecialchars(packet.getText())+'</p>';
            html +='</div>';
            return html;
        },
        getDomByRecvTextPacket: function(packet){
            var user = minions.userManager.getUser(packet.from);
            if(!user){
                return '';
            }
            var html = '';
            html += this.getLatestTimeDom(this.latestSendTime, packet.from);
            html +='<div class="chat-ml">';
            html +='    <img class="avatar" src="'+user.avatar+'" />';
            html +='    <p class="name">'+user.nick+'</p>';
            html +='    <p class="content">'+minions.htmlspecialchars(packet.getText())+'</p>';
            html +='</div>';
            return html;
        },
        addRecvPacket: function(id, pkt, user, target){
            // parse packet type
            var packet = jegarn.packet;
            var type = pkt.type;
            var subType = pkt.getSubType();
            var parsedPkt = null;
            var groupId = null;
            switch (type){
                case packet.Chat.TYPE:
                    switch (subType){
                        case packet.TextChat.SUB_TYPE :
                            parsedPkt = (new packet.TextChat).getPacketFromPacket(pkt);
                            this.create(id, target.nick, pkt.from != 1); // hack for counter
                            var $chatWindow = $('#'+id);
                            $chatWindow.find('.chat-wp').append(this.getDomByRecvTextPacket(parsedPkt));
                            var $scrollBar = $chatWindow.find('.scroll-bar');
                            $scrollBar.scrollTop($scrollBar[0].scrollHeight);
                            break;
                    }
                    break;
                case packet.GroupChat.TYPE:
                    switch (subType){
                        case packet.TextGroupChat.SUB_TYPE :
                            parsedPkt = (new packet.TextGroupChat).getPacketFromPacket(pkt);
                            groupId = parsedPkt.getGroupId();
                            minions.group.info(user,groupId,function(data){
                               if(minions.response.isSuccess(data)){
                                    var group = minions.response.getResponseBody(data);
                                    minions.widget.chatWindow.create(id, group.name, pkt.from != 1); // hack for counter
                                    var $chatWindow = $('#'+id);
                                    $chatWindow.find('.chat-wp').append(minions.widget.chatWindow.getDomByRecvTextPacket(parsedPkt));
                                    var $scrollBar = $chatWindow.find('.scroll-bar');
                                    $scrollBar.scrollTop($scrollBar[0].scrollHeight);
                               }
                            });
                            break;
                    }
                    break;
                case packet.Chatroom.TYPE:
                    switch (subType){
                        case packet.TextChatroom.SUB_TYPE :
                            parsedPkt = (new packet.TextChatroom).getPacketFromPacket(pkt);
                            groupId = parsedPkt.getGroupId();
                            minions.group.info(user,groupId,function(data){
                                if(minions.response.isSuccess(data)){
                                    var group = minions.response.getResponseBody(data);
                                    minions.widget.chatWindow.create(id, group.name, pkt.from != 1); // hack for counter
                                    var $chatWindow = $('#'+id);
                                    $chatWindow.find('.chat-wp').append(minions.widget.chatWindow.getDomByRecvTextPacket(parsedPkt));
                                    var $scrollBar = $chatWindow.find('.scroll-bar');
                                    $scrollBar.scrollTop($scrollBar[0].scrollHeight);
                                }
                            });
                            break;
                    }
                    break;
                case packet.Notification.TYPE:
                    if (subType.substring(0,6) == 'friend') {
                        parsedPkt = (new packet.FriendNotification).getPacketFromPacket(pkt);
                        minions.widget.chatBox.addFriendNotification(parsedPkt);
                    }else if(subType.substring(0,5) == 'group'){
                        parsedPkt = (new packet.GroupNotification).getPacketFromPacket(pkt);
                        minions.widget.chatBox.addGroupNotification(parsedPkt);
                    }
                    break;
                default:{
                    minions.log('addRecvPacket type error',arguments);
                }
            }
            if(parsedPkt == null){
                minions.log('addRecvPacket subType error',arguments);
            }
        }
    };
    // code
    minions.response = {
        isSuccess : function(response){
            return response.code == 0;
        },
        getResponseBody : function(response){
            return response.response;
        },
        getMessage: function(response){
            var s = this;
            if(typeof s[response.code] == "undefined"){
                return "server not available currently";
            }else{
                return s[response.code];
            }
        },
        0 : 'success',
        4000 : 'server not available currently',
        4001: 'server not available currently',
        4002: 'server not available currently',
        4003: 'server not available currently',
        4004: 'server not available currently',
        4005: 'server not available currently',
        4006: 'server not available currently',
        4007: 'server not available currently',
        4008: 'server not available currently',
        4009: 'server not available currently',
        4010: 'server not available currently',
        5000: 'account required',
        5001: 'password required',
        5002: 'password or account lost',
        5003: 'login too frequently',
        5004: 'account already exists',
        5005: 'server not available currently',
        5006: 'user not exists',
        5007: 'password too short',
        5008: 'password invalid',
        5009: 'your ip is invalid',
        5010: 'register too frequently',
        5011: 'token expire',
        5012: 'upload file is empty',
        5013: 'upload file type error',
        5014: 'upload file size error',
        5015: 'login failed',
        5016: 'roster status error',
        5017: 'roster group not exists',
        5018: 'roster not exists',
        5019: 'move group failed',
        5020: 'you in black list',
        5021: 'unsubscribe failed',
        5022: 'message is empty',
        5023: 'object not found',
        5024: 'message not exists',
        5025: 'group name is empty',
        5026: 'group type error',
        5027: 'group not exists',
        5028: 'permission deny',
        5029: 'status wrong',
        5030: 'already request',
        5031: 'already a member',
        5032: 'you are be refused',
        5033: 'user not exists',
        5034: 'permission deny'
    };
    minions.demo = (function(){
        var demo = {};
        demo.user = {uid: 1000000, nick: 'yaoguai', motto: 'make jegarn better!', avatar: 'upload/avatar/default/b6.jpg'};
        demo.users = [
            {uid: 1000001, nick: 'Jack', motto: 'to be or not to be is a question!', avatar: 'upload/avatar/default/b1.jpg', 'present' : minions.user.ONLINE},
            {uid: 1000002, nick: 'lucy', motto: 'let\'s jump!', avatar: 'upload/avatar/default/g0.jpg', 'present' : minions.user.OFFLINE},
            {uid: 1000003, nick: 'fly100%', motto: 'yes, ppg!', avatar: 'upload/avatar/default/b2.jpg', 'present' : minions.user.ONLINE},
            {uid: 1000004, nick: 'rick', motto: 'how many walkers have you killed?', avatar: 'upload/avatar/default/b3.jpg', 'present' : minions.user.ONLINE}
        ];
        demo.groups = [
            {group_id: 2000000, name: 'default', rosters: [
                {uid: demo.user.uid, target_id: demo.users[0].uid, remark: null, user: demo.users[0]},
                {uid: demo.user.uid, target_id: demo.users[1].uid, remark: null, user: demo.users[1]}
            ]},
            {group_id: 2000001, name: 'friends', rosters: [
                {uid: demo.user.uid, target_id: demo.users[2].uid, remark: null, user: demo.users[2]},
                {uid: demo.user.uid, target_id: demo.users[3].uid, remark: null, user: demo.users[3]}
            ]}
        ];
        demo.init = function(){
            this.initChatBox();
            this.initChatWindow();
            this.initRecommend();
        };
        demo.destroied = false;
        demo.destroy = function(){
            if(demo.destroied){
                return false;
            }
            demo.destroied = true;
            var uid = demo.user.uid;
            minions.userManager.removeUser(uid);
            for(var i in demo.users){
                var userId = demo.users[i].uid;
                minions.userManager.removeUser(userId);
                minions.widget.recommend.removeUser(userId);
            }
            minions.userRosterGroupManager.removeUserRosterAll(uid);
            minions.widget.chatBox.destroy(uid);
        };
        demo.initChatBox = function(){
            var chatBox = minions.widget.chatBox;
            var user = demo.user;
            var list = demo.groups;
            chatBox.createForNewUser(user);
            // init roster
            minions.userRosterGroupManager.addUserRosterAll(user.uid, list);
            var $roster = $('#' + chatBox.ID_PRE + user.uid).find('.chat-people');
            for(var i in list){
                $roster.append(chatBox.getRosterGroupDom(list[i]));
            }
        };
        demo.initChatWindow = function(){
            var chatBox = minions.widget.chatBox;
            var user = demo.user;
            $('#' + chatBox.ID_PRE + user.uid).find('footer ul li').eq(0).click();
            var $roster = $('#' + chatBox.ID_PRE + user.uid).find('.chat-people');
            $roster.find('.list-group li').eq(1).click();
        };
        demo.initRecommend = function(){
            var $recommendUser = $('#recommend-user .recommend-user');
            for(var i in demo.users){
                $recommendUser.append(minions.widget.recommend.getUserDom(demo.users[i]));
            }
        };
        return demo;
    })();
})(window, jQuery);