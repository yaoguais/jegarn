package com.jegarn.minions.utils;

import com.jegarn.jegarn.client.Client;
import com.jegarn.jegarn.client.DefaultListener;
import com.jegarn.jegarn.client.JegarnException;
import com.jegarn.minions.App;

abstract public class JegarnUtil {
    public static Client client = new Client();
    private static boolean initialed = false;

    public static void init(String host, int port, int reconnectInterval, String account, String password, boolean enableSSl, DefaultListener listener) {
        client.init(host, port, reconnectInterval);
        client.setUser(account, password);
        client.setEnableSsl(enableSSl);
        client.setListener(listener);
    }

    public static void run(){
        if(!initialed){
            initialed = true;
            new Thread(new Runnable() {
                @Override
                public void run() {
                    System.out.println("---------- Jegarn Server Started ---------");
                    JegarnUtil.init(App.SERVER_HOST, App.SERVER_PORT, 5000, App.user.account, App.user.token, true, new DefaultListener());
                    try {
                        JegarnUtil.client.connect();
                        JegarnUtil.client.auth();
                    } catch (JegarnException e) {
                        JegarnUtil.client.close();
                        e.printStackTrace();
                    }
                }
            }).start();
        }
    }
}
