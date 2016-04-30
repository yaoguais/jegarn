package com.jegarn.minions.manager;

import android.content.Context;

import com.google.gson.JsonSyntaxException;
import com.jegarn.minions.App;
import com.jegarn.minions.model.User;
import com.jegarn.minions.response.Response;
import com.jegarn.minions.utils.JsonUtil;
import com.jegarn.minions.utils.WidgetUtil;
import com.zhy.http.okhttp.OkHttpUtils;
import com.zhy.http.okhttp.callback.StringCallback;

import java.util.HashMap;
import java.util.Map;

import okhttp3.Call;

public class UserManager {
    private static Map<String, User> users = new HashMap<>();

    public static void loadUser(Context context, String uid, final UserLoaderListener listener) {
        User user = users.get(uid);
        if (user != null) {
            listener.loadSuccess(user);
        } else {
            OkHttpUtils.get().addParams("uid", App.user.uid).addParams("token", App.user.token)
                    .addParams("user_id", uid)
                    .url(App.getUrl(App.API_USER_INFO))
                    .build().execute(new UserInfoCallback(context) {
                @Override
                public void loadSuccess(User user) {
                    listener.loadSuccess(user);
                }
            });
        }
    }

    public static abstract class UserLoaderListener {
        public abstract void loadSuccess(User user);
    }

    public static abstract class UserInfoCallback extends StringCallback {
        private Context context;

        public UserInfoCallback(Context context) {
            this.context = context;
        }

        public abstract void loadSuccess(User user);

        private void toast(String message){
            if(context != null){
                WidgetUtil.toast(context.getApplicationContext(), message);
            }
        }

        @Override
        public void onError(Call call, Exception e) {
            this.toast(Response.getMessage(Response.FAIL_NETWORK));
            e.printStackTrace();
        }

        @Override
        public void onResponse(String str) {
            try {
                Response resp = JsonUtil.fromJson(str, Response.class);
                if (Response.isSuccess(resp.code)) {
                    if (resp.response == null) {
                        this.toast(Response.getMessage(Response.FAIL_SERVER_RESPONSE));
                    } else {
                        Map userMap = (Map) resp.response;
                        User user = new User();
                        user.uid = (String) userMap.get("uid");
                        user.account = (String) userMap.get("account");
                        user.nick = (String) userMap.get("nick");
                        user.avatar = (String) userMap.get("avatar");
                        user.motto = (String) userMap.get("motto");
                        users.put(user.uid, user);
                        loadSuccess(user);
                    }
                } else {
                    this.toast(Response.getMessage(resp.code));
                }
            } catch (JsonSyntaxException e) {
                this.toast(Response.getMessage(str));
            }
        }
    }
}
