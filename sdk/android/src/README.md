# Jegarn Android SDK

[This project](https://github.com/Yaoguais/android-on-the-way/tree/master/android-chat-system)
is a demo of using the Jegarn Android SDK.




## Install


First, copy the sdk library into you project.

    copy "/android-chat-system/jegarn" into your project

Then, update your build.gradle file.

    dependencies {
        compile project(':jegarn')
    }




## Example

For more details, please see the demo project.
And these are some base usages.

#### connect

    String host = "jegarn.com";
    int port = 9501;
    int reconnectInterval = 5000;
    String account = "test";
    String password = "test";
    // is connect to ssl server ?
    boolean enableSsl = true;
    DefaultListener listener = new DefaultListener();
    JegarnManager.getInstance().init(host,port, reconnectInterval, account, password, enableSsl,listener);
    JegarnManager.getInstance().run();

#### and new message listener of chat packet

    JegarnManager.client.getChatManager().addListener(new ChatManagerListener() {
        @Override
        public boolean processPacket(Chat packet) {
            if (toUserUid.equals(packet.getFrom())) {
                if (packet instanceof TextChat) {
                    TextChat pkt = (TextChat) packet;
                }
            }
            return true;
        }
    });

#### send new message

    TextChat packet = new TextChat("my_uid", "friend_uid", TextChat.TYPE, new TextChatContent("hello"));
    JegarnManager.client.sendPacket(packet);




## Run Project

Three steps:

    git clone git@github.com:Yaoguais/android-on-the-way.git
    cd android-chat-system
    gradle build

Open your Android Studio for adding the project of root directory "android-chat-system", then just run it.




## License

Apache License Version 2.0