package com.jegarn.minions.activity;

import android.app.Activity;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.google.gson.JsonSyntaxException;
import com.jegarn.jegarn.client.ByteArray;
import com.jegarn.jegarn.client.Convert;
import com.jegarn.jegarn.client.DefaultListener;
import com.jegarn.jegarn.client.DefaultX509TrustManager;
import com.jegarn.jegarn.manager.JegarnManager;
import com.jegarn.jegarn.packet.base.Packet;
import com.jegarn.jegarn.packet.content.TextChatContent;
import com.jegarn.jegarn.packet.content.TextGroupContent;
import com.jegarn.jegarn.packet.text.TextChat;
import com.jegarn.jegarn.packet.text.TextChatRoom;
import com.jegarn.jegarn.packet.text.TextGroupChat;
import com.jegarn.minions.App;
import com.jegarn.minions.R;
import com.jegarn.minions.im.DbRecordListener;
import com.jegarn.minions.model.User;
import com.jegarn.minions.response.Response;
import com.jegarn.minions.response.UserResponse;
import com.jegarn.minions.utils.JsonUtil;
import com.jegarn.minions.utils.SdUtil;
import com.jegarn.minions.utils.StringUtil;
import com.jegarn.minions.utils.WidgetUtil;
import com.zhy.http.okhttp.OkHttpUtils;
import com.zhy.http.okhttp.callback.StringCallback;

import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.Socket;
import java.net.UnknownHostException;
import java.security.KeyManagementException;
import java.security.NoSuchAlgorithmException;
import java.util.HashMap;
import java.util.Map;

import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSocketFactory;
import javax.net.ssl.X509TrustManager;

import okhttp3.Call;

public class LoginActivity extends Activity implements View.OnClickListener {

    EditText accountEditText, passwordEditText;
    Button submitButton, fakeSubmitButton;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        this.accountEditText = (EditText) findViewById(R.id.accountEditText);
        this.passwordEditText = (EditText) findViewById(R.id.passwordEditText);
        this.submitButton = (Button) findViewById(R.id.submitButton);
        this.fakeSubmitButton = (Button) findViewById(R.id.fakeSubmitButton);
        this.fakeSubmitButton.setBackgroundColor(Color.TRANSPARENT);

        this.submitButton.setOnClickListener(this);
        this.fakeSubmitButton.setOnClickListener(this);
//        this.testSendMsgpack();
//        this.testRecvMsgpack();
//        this.testConnectSslServer();
//        this.testCreatePacket();
    }

    @Override
    public void onClick(View v) {
        final int id = v.getId();

        if (R.id.fakeSubmitButton == id) {
            if (fakeLogin()) {
                Toast.makeText(getApplicationContext(), "fake login", Toast.LENGTH_SHORT).show();
            }
        } else if (R.id.submitButton == id) {
            String account = this.accountEditText.getText().toString();
            if (StringUtil.isEmptyString(account)) {
                Toast.makeText(getApplicationContext(), "account is empty", Toast.LENGTH_SHORT).show();
                return;
            }
            if (App.FAKE_USER_ACCOUNT.equals(account)) {
                Toast.makeText(getApplicationContext(), "account " + App.FAKE_USER_ACCOUNT + " is not allowed login", Toast.LENGTH_SHORT).show();
                return;
            }
            String password = this.passwordEditText.getText().toString();
            if (StringUtil.isEmptyString(password)) {
                Toast.makeText(getApplicationContext(), "password is empty", Toast.LENGTH_SHORT).show();
                return;
            }
            Map<String, String> params = new HashMap<>();
            params.put("account", account);
            params.put("password", password);
            OkHttpUtils.post().url(App.getUrl(App.API_LOGIN)).params(params).build().execute(new LoginCallback());
        }
    }

    private boolean fakeLogin() {
        App.user.uid = App.FAKE_USER_UID;
        App.user.account = App.FAKE_USER_ACCOUNT;
        App.user.avatar = "/upload/avatar/default/b7.jpg";
        App.user.motto = "make jegarn better !";
        App.user.nick = App.FAKE_USER_NICK;
        App.user.token = App.FAKE_USER_TOKEN;
        App.user.present = User.ONLINE;
        JegarnManager.getInstance().init(App.SERVER_HOST, App.SERVER_PORT,
                5000, App.user.account, App.user.token, true, new DbRecordListener(this.getApplicationContext()));
        JegarnManager.getInstance().run();
        finish();
        return true;
    }

    public final class LoginCallback extends StringCallback {
        @Override
        public void onError(Call call, Exception e) {
            WidgetUtil.toast(getApplicationContext(), Response.getMessage(Response.FAIL_NETWORK));
        }

        @Override
        public void onResponse(String str) {
            try {
                UserResponse resp = JsonUtil.fromJson(str, UserResponse.class);
                if (Response.isSuccess(resp.code)) {
                    if (resp.response == null) {
                        WidgetUtil.toast(getApplicationContext(), Response.getMessage(Response.FAIL_SERVER_RESPONSE));
                    } else {
                        App.setUser(resp.response);
                        User user = resp.response;
                        JegarnManager.getInstance().init(App.SERVER_HOST, App.SERVER_PORT,
                                5000, user.account, user.token, true, new DbRecordListener(LoginActivity.this.getApplicationContext()));
                        JegarnManager.getInstance().run();
                        finish();
                    }
                } else {
                    WidgetUtil.toast(getApplicationContext(), Response.getMessage(resp.code));
                }
            } catch (JsonSyntaxException e) {
                WidgetUtil.toast(getApplicationContext(), Response.getMessage(str));
            }
        }
    }

    @Override
    public void finish() {
        Intent intent = new Intent(LoginActivity.this, MainActivity.class);
        startActivity(intent);
        super.finish();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
    }

    private byte[] getAuthPacketBytes() {
        Convert convert = new Convert();
        try{
            {
                convert.packMapHeader(5);
                convert.packMapNil("session_id");
                convert.packMapString("from","0");
                convert.packMapString("to", "to");
                convert.packMapString("type","auth");
                {
                    convert.packMapMap("content",4);
                    convert.packMapNil("uid");
                    convert.packMapString("account",App.FAKE_USER_ACCOUNT);
                    convert.packMapString("password",App.FAKE_USER_TOKEN);
                    convert.packMapInt("status",0);
                }
            }
            return convert.packMap();
        }catch (IOException e){
            return null;
        }
    }

    private void testSendMsgpack() {
        byte[] authBytes = this.getAuthPacketBytes();
        if (authBytes == null) {
            System.out.println("msgpack failed");
        } else {
            byte[] dataBytes = new byte[4 + authBytes.length];
            ByteArray.write32bit(authBytes.length, dataBytes,0);
            System.arraycopy(authBytes,0,dataBytes,4,authBytes.length);
            ByteArrayInputStream stream = new ByteArrayInputStream(dataBytes);
            new SdUtil().write2SDFromInput("minions/", "msgpack_java.txt", stream);
            System.out.println("sd success[" + new String(authBytes) + "]");
        }
    }

    private void testRecvMsgpack() {
        ByteArrayOutputStream out = new SdUtil().readSdFile("minions/", "msgpack_php.txt");
        if (null == out) {
            System.out.println("msgpack_php.txt not found");
        } else {
            byte[] all = out.toByteArray();
            Map<String, Object> map = new Convert().unpackMap(all,4,all.length - 4);
            if(map == null){
                System.out.println("msgpack java unpack failed");
            }else{
                System.out.println("msgpack data length: " + ByteArray.read32bit(all, 0));
                System.out.println("msgpack java unpack success");
                System.out.println("msgpack java unpack to:" + map.get("to"));
                try{
                    Map<String, Object> content = (Map<String, Object>)map.get("content");
                    System.out.println("msgpack java unpack content-account:" + content.get("account"));
                }catch (Exception e){
                    e.printStackTrace();
                    System.out.println("msgpack java unpack failed");
                }
            }
        }
    }

    private void testConnectSslServer() {
        new Thread(new Runnable() {
            @Override
            public void run() {
                testConnectSslServerInternal();
            }
        }).start();
    }

    private void testConnectSslServerInternal() {
        SSLContext ctx;
        try {
            ctx = SSLContext.getInstance("SSL");
        } catch (NoSuchAlgorithmException e) {
            e.printStackTrace();
            return;
        }
        try {
            ctx.init(null, new X509TrustManager[]{new DefaultX509TrustManager()}, null);
        } catch (KeyManagementException e) {
            e.printStackTrace();
            return;
        }
        SSLSocketFactory factory = ctx.getSocketFactory();
        Socket socket;
        try {
            socket = factory.createSocket("jegarn.com", 9501);
        } catch (UnknownHostException e) {
            e.printStackTrace();
            return;
        } catch (IOException e) {
            e.printStackTrace();
            return;
        }
        InputStream inputStream;
        OutputStream outputStream;
        try {
            inputStream = socket.getInputStream();
            outputStream = socket.getOutputStream();
        } catch (IOException e) {
            e.printStackTrace();
            return;
        }
        byte[] authPacketBytes = this.getAuthPacketBytes();
        if (authPacketBytes == null) {
            System.out.println("msgpack failed");
            return;
        }
        try {
            byte[] lengthByte = new byte[4];
            ByteArray.write32bit(authPacketBytes.length, lengthByte, 0);
            outputStream.write(lengthByte);
            outputStream.write(authPacketBytes);
            outputStream.flush();
            System.out.println("send auth packet");
        } catch (IOException e) {
            e.printStackTrace();
            return;
        }
        int len;
        byte[] tmpData = new byte[2048];
        boolean running = true;
        while (running) {
            try {
                System.out.println("before read data");
                len = inputStream.read(tmpData);
            } catch (IOException e) {
                e.printStackTrace();
                continue;
            }
            if (len > 0) {
                System.out.println("recv data:[" + len + "/" + tmpData.length + "]" + new String(tmpData, 0, len));
                System.out.println("msgpack data length: " + ByteArray.read32bit(tmpData, 0));
                Map<String, Object> map = new Convert().unpackMap(tmpData,4,len - 4);
                if(map == null){
                    System.out.println("msgpack java unpack failed");
                }else{
                    System.out.println("msgpack java unpack success");
                    System.out.println("msgpack java unpack to:" + map.get("to"));
                    try{
                        Map<String, Object> content = (Map<String, Object>)map.get("content");
                        System.out.println("msgpack java unpack content-account:" + content.get("account"));
                    }catch (Exception e){
                        e.printStackTrace();
                        System.out.println("msgpack java unpack failed");
                    }
                }
            }
//            running = false;
        }
        try {
            inputStream.close();
            outputStream.close();
            socket.close();
        } catch (IOException e) {
            e.printStackTrace();
        }
        System.out.println("shutdown client");
    }

    private void testCreatePacket(){

        Packet packet = new TextChat("a", "b", "chat", new TextChatContent("this is text"));
        TextChat textChat = (TextChat)packet;
        System.out.println("TextChat-" + textChat.getContent().getType()+ "-" + textChat.getContent().getText());

        packet = new TextGroupChat("a","b","groupchat", new TextGroupContent(10, "hello everybody"));
        TextGroupChat textGroupChat = (TextGroupChat)packet;
        System.out.println("TextGroupChat-" + textGroupChat.getContent().getType()+ "-" +
                textGroupChat.getContent().getGroupId() + "-" + textGroupChat.getContent().getText());

        packet = new TextChatRoom("a","b","groupchat", new TextGroupContent(10, "hello everybody"));
        TextChatRoom textChatRoom = (TextChatRoom)packet;
        System.out.println("TextChatRoom-" + textChatRoom.getContent().getType()+ "-" +
                textChatRoom.getContent().getGroupId() + "-" + textChatRoom.getContent().getText());
        System.out.println("create test over");
    }
}
