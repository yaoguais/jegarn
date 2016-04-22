package com.jegarn.jegarn.manager;

import com.jegarn.jegarn.client.Client;
import com.jegarn.jegarn.client.DefaultListener;
import com.jegarn.jegarn.client.JegarnException;

public class JegarnManager {

    public static Client client = new Client();
    private static JegarnManager instance = null;
    private static boolean runed = false;
    private JegarnManager(){}

    public static JegarnManager getInstance(){
        if(instance == null){
            synchronized (JegarnManager.class){
                if(instance == null){
                    instance = new JegarnManager();
                }
            }
        }
        return instance;
    }

    public void init(String host, int port, int reconnectInterval, String account, String password, boolean enableSSl, DefaultListener listener) {
        client.init(host, port, reconnectInterval);
        client.setUser(account, password);
        client.setEnableSsl(enableSSl);
        client.setListener(listener);
    }

    private void runOnce(){
        new Thread(new Runnable() {
            @Override
            public void run() {
                System.out.println("---------- Jegarn Server Started ---------");
                try {
                    JegarnManager.client.connect();
                    JegarnManager.client.auth();
                } catch (JegarnException e) {
                    JegarnManager.client.close();
                    e.printStackTrace();
                }
            }
        }).start();
    }

    public void run(){
        if(!runed){
            synchronized (JegarnManager.class){
                if(!runed){
                    runed = true;
                    runOnce();
                }
            }
        }
    }
}
