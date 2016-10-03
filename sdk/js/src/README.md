# Jegarn JS SDK

[This project](https://github.com/Yaoguais/jegarn/tree/master/examples/web-chat-system)
is a demo of using the Jegarn JS SDK.




## Install

    1. download the "jegarn.js"
    2. put the "<script src='jegarn.js'></script>" into your html file

[jegarn.js download](https://github.com/Yaoguais/jegarn/blob/master/examples/web-chat-system/app/public/js/jegarn.js)




## Example

For more details, please see the demo project.
And these are some base usages.

#### connect

    var client = new jegarn.client("jegarn.com", 9503, 5000);
    client.setConnectListener(connectListener);
    client.setDisconnectListener(disconnectListener);
    client.setErrorListener(errorListener);
    client.setPacketListener(packetListener);
    client.setSendListener(sendListener);
    client.setUser("account", "password");
    client.connect();

    function connectListener(s) {
        console.log('connect', s);
    }
    function disconnectListener(evt, s) {
        console.log('disconnect', evt, s);
    }
    function errorListener(evt, s) {
        console.log('error', evt, s);
    }
    // new message listener
    function packetListener(packet, s) {
        console.log('packet', packet, s);
    }
    function sendListener(packet, s) {
        console.log('send', packet, s);
    }

#### send new message

    var packet = new jegarn.packet.TextChat();
    packet.from = "my_uid";
    packet.to = "friend_uid";
    packet.setText("hello");
    client.sendPacket(packet);




## License

Apache License Version 2.0