package com.jegarn.jegarn.client;

import com.jegarn.jegarn.manager.ChatManager;
import com.jegarn.jegarn.manager.ChatRoomManager;
import com.jegarn.jegarn.manager.GroupChatManager;
import com.jegarn.jegarn.manager.NotificationManager;
import com.jegarn.jegarn.packet.base.Packet;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.Socket;
import java.net.UnknownHostException;
import java.security.KeyManagementException;
import java.security.NoSuchAlgorithmException;

import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSocketFactory;
import javax.net.ssl.X509TrustManager;

public class Client {
    protected String uid;
    protected String account;
    protected String password;
    protected String host;
    protected int port;
    protected Socket socket;
    protected boolean running;
    protected String sessionId;
    protected boolean authorized;
    protected int reconnectInterval;
    protected boolean enableSsl;
    protected DefaultListener listener;
    protected OutputStream outputStream;
    protected InputStream inputStream;
    protected PacketWriter packetWriter;
    protected PacketReader packetReader;
    protected ChatManager chatManager;
    protected GroupChatManager groupChatManager;
    protected ChatRoomManager chatRoomManager;
    protected NotificationManager notificationManager;
    public static final String SESSION_KEY = "session_id";
    public enum ErrorType {NETWORK_ERROR, RECV_PACKET_CRASHED, RECV_PACKET_TYPE, AUTH_FAILED, SEND_PACKET_VALID}

    public Client() {
        chatManager = new ChatManager();
        groupChatManager = new GroupChatManager();
        chatRoomManager = new ChatRoomManager();
        notificationManager = new NotificationManager();
    }

    public void init(String host, int port, int reconnectInterval){
        this.host = host;
        this.port = port;
        this.reconnectInterval = reconnectInterval;
    }

    public void setUser(String account, String password) {
        this.account = account;
        this.password = password;
    }

    public void setListener(DefaultListener listener) {
        this.listener = listener;
    }

    public boolean isAuthorized() {
        return this.authorized;
    }

    public void setEnableSsl(boolean enable) {
        this.enableSsl = enable;
    }

    public void connect() throws JegarnException {
        this.checkConfig();
        if (running) {
            throw new JegarnException("server is already running");
        }
        running = true;
        try {
            this.initSocket();
            this.initReaderAndWriter();
            packetWriter = new PacketWriter(this);
            packetReader = new PacketReader(this);
            packetWriter.startup();
            packetReader.startup();
        } catch (JegarnException e) {
            this.close();
            throw e;
        }
    }

    public void close() {
        this.running = false;
        this.authorized = false;
        if (packetWriter != null) {
            try {
                packetWriter.shutdown();
            } catch (Throwable ignore) { /* ignore */ }
            packetWriter = null;
        }
        if (packetReader != null) {
            try {
                packetReader.shutdown();
            } catch (Throwable ignore) { /* ignore */ }
            packetReader = null;
        }
        if (inputStream != null) {
            try {
                inputStream.close();
            } catch (Throwable ignore) { /* ignore */ }
            inputStream = null;
        }
        if (outputStream != null) {
            try {
                outputStream.close();
            } catch (Throwable ignore) {  /* ignore */}
            outputStream = null;
        }
        if (socket != null) {
            try {
                socket.close();
            } catch (Exception e) { /* ignore */ }
            socket = null;
        }
    }

    public void reconnect() {
        this.close();
        try {
            this.connect();
        } catch (JegarnException e) {
            e.printStackTrace();
        }
    }

    public boolean sendPacket(Packet packet) {
        if (this.packetWriter != null) {
            return this.packetWriter.sendPacket(packet);
        }
        return false;
    }

    public boolean isClosed() {
        return !(running && authorized);
    }

    public void auth() {
        this.packetWriter.sendAuthPacket();
    }

    public ChatManager getChatManager() { return chatManager; }

    public GroupChatManager getGroupChatManager() {
        return groupChatManager;
    }

    public ChatRoomManager getChatRoomManager() {
        return chatRoomManager;
    }

    public NotificationManager getNotificationManager() {
        return notificationManager;
    }

    protected void checkConfig() throws JegarnException {
        if (isEmptyString(host)) {
            throw new JegarnException("host is not set");
        } else if (port <= 0) {
            throw new JegarnException("port is not set");
        } else if (isEmptyString(account)) {
            throw new JegarnException("account is not set");
        } else if (isEmptyString(password)) {
            throw new JegarnException("password is not set");
        } else if (null == listener) {
            throw new JegarnException("listener is not set");
        }
    }

    private boolean isEmptyString(String str) {
        return str == null || "".equals(str);
    }

    protected void initSocket() throws JegarnException {
        if (!this.enableSsl) {
            try {
                socket = new Socket(this.host, this.port);
            } catch (UnknownHostException e) {
                e.printStackTrace();
                throw new JegarnException("host is unknown");
            } catch (IOException e) {
                e.printStackTrace();
                throw new JegarnException("create socket failed");
            }
        } else {
            SSLContext ctx;
            try {
                ctx = SSLContext.getInstance("SSL");
            } catch (NoSuchAlgorithmException e) {
                e.printStackTrace();
                throw new JegarnException("[SSL] no such algorithm");
            }
            try {
                ctx.init(null, new X509TrustManager[]{new DefaultX509TrustManager()}, null);
            } catch (KeyManagementException e) {
                e.printStackTrace();
                throw new JegarnException("[SSL] key management init failed");
            }
            SSLSocketFactory factory = ctx.getSocketFactory();
            try {
                socket = factory.createSocket(host, port);
            } catch (UnknownHostException e) {
                e.printStackTrace();
                throw new JegarnException("[SSL] host is unknown");
            } catch (IOException e) {
                e.printStackTrace();
                throw new JegarnException("[SSL] create socket failed");
            }
        }
    }

    protected void initReaderAndWriter() throws JegarnException {
        try {
            inputStream = socket.getInputStream();
        } catch (IOException e) {
            e.printStackTrace();
            throw new JegarnException("socket create input stream failed");
        }
        try {
            outputStream = socket.getOutputStream();
        } catch (IOException e) {
            e.printStackTrace();
            throw new JegarnException("socket create output stream failed");
        }
    }
}
