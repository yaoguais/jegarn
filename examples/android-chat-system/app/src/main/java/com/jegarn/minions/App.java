package com.jegarn.minions;

import android.app.Application;

import com.facebook.drawee.backends.pipeline.Fresco;
import com.jegarn.minions.entity.Message;
import com.jegarn.minions.model.User;
import com.orm.SugarApp;
import com.orm.SugarContext;
import com.zhy.http.okhttp.OkHttpUtils;

public class App extends Application{
    public static boolean DEBUG = true;

    public static String SERVER_HOST = "jegarn.com";
    public static int SERVER_PORT = 9501;
    public static String SERVER_URL = "https://jegarn.com/";
    public static String FAKE_USER_UID = "12";
    public static String FAKE_USER_ACCOUNT = "yaoguai";
    public static String FAKE_USER_NICK = "Yao Guai";
    public static String FAKE_USER_TOKEN    = "a03231805fb703d159dbc71633c72b86";

//    public static String SERVER_HOST = "192.168.199.243";
//    public static String SERVER_URL = "https://192.168.199.243/";
//    public static String FAKE_USER_UID = "2";
//    public static String FAKE_USER_ACCOUNT = "mytester";
//    public static String FAKE_USER_NICK = "MyTester";
//    public static String FAKE_USER_TOKEN    = "994dd2c6fdecbb826905070d44578025";

    public static final String API_LOGIN = "api/user/login";
    public static final String API_USER_INFO = "api/user/info";
    public static final String API_LIST_ALL_ROSTER = "api/roster/list_all";
    public static final String API_LIST_ALL_GROUP = "api/group/list";
    public static String getUrl(String host, String pathInfo){
        return host + pathInfo;
    }
    public static String getUrl(String pathInfo){
        return SERVER_URL + pathInfo;
    }

    public static User user = new User();
    public static void setUser(User u){
        user.uid = u.uid;
        user.account = u.account;
        user.nick = u.nick;
        user.motto = u.motto;
        user.token = u.token;
        user.avatar = u.avatar;
        user.present = u.present;
    }

    public static void init(){
        if(DEBUG){
            OkHttpUtils.getInstance().debug(null);
        }
    }

    @Override
    public void onCreate() {
        super.onCreate();
        Fresco.initialize(this.getApplicationContext());
        SugarContext.init(this.getApplicationContext());
    }

    @Override
    public void onTerminate() {
        super.onTerminate();
        SugarContext.terminate();
    }
}
