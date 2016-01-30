package com.jegarn.jegarn.client;

import com.jegarn.jegarn.packet.base.Auth;
import com.jegarn.jegarn.packet.base.Packet;
import com.jegarn.jegarn.packet.factory.HasSubTypeFactory;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.Map;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.ThreadFactory;

public class PacketReader {

    private Thread readerThread;
    private ExecutorService listenerExecutor;
    private Client client;
    private InputStream inputStream;
    private boolean done;
    private Convert convert;

    public PacketReader(Client client) {
        this.client = client;
        this.convert = new Convert();
        this.init();
    }

    public void init() {
        inputStream = client.inputStream;
        done = false;
        readerThread = new Thread() {
            public void run() {
                parsePackets(this);
            }
        };
        readerThread.setName("Jegarn Packet Reader (" + client.account + ")");
        readerThread.setDaemon(true);

        listenerExecutor = Executors.newSingleThreadExecutor(new ThreadFactory() {
            public Thread newThread(Runnable runnable) {
                Thread thread = new Thread(runnable, "Jegarn Listener Processor (" + client.account + ")");
                thread.setDaemon(true);
                return thread;
            }
        });
    }

    public void notifyConnectionError(Exception e) {
        done = true;
        client.close();
        e.printStackTrace();
        client.listener.errorListener(Client.ErrorType.NETWORK_ERROR, client);
    }

    private void parsePackets(Thread thread) {
        int bufferLength = 2048;
        int headLen = 4;
        ByteArrayOutputStream recvData = new ByteArrayOutputStream(bufferLength);
        byte[] tmpData = new byte[bufferLength];
        int len, recvDataLen, readStartPos, currentPacketLen;
        boolean needRenewRecvData, parsePacketError;
        while (!done && thread == readerThread) {
            try {
                System.out.println("recv data once again");
                len = inputStream.read(tmpData);
            } catch (IOException e) {
                e.printStackTrace();
                continue;
            }
            if (len > 0) {
                System.out.println("recv data:[" + len + "]" + new String(tmpData, 0, len));
                recvData.write(tmpData, 0, len);
                // parse recv data
                recvDataLen = recvData.size();
                readStartPos = 0;
                byte[] recvDataByte = recvData.toByteArray();
                needRenewRecvData = parsePacketError = false;
                for (; ; ) {
                    if (recvDataLen - readStartPos <= headLen) {
                        System.out.println("recv data header is not enough " + recvDataLen + "-" + readStartPos + "<=" + headLen);
                        break;
                    }
                    // calculate packet length
                    currentPacketLen = ByteArray.read32bit(recvDataByte, readStartPos);
                    System.out.println("recv packet length: "+currentPacketLen);
                    if (currentPacketLen <= 0) {
                        needRenewRecvData = parsePacketError = true;
                        break;
                    }
                    // current packet is enough
                    if (readStartPos + headLen + currentPacketLen <= recvDataLen) {
                        System.out.println("before parse one packet");
                        if (!parseOnePacketData(recvDataByte, readStartPos + headLen, currentPacketLen)) {
                            parsePacketError = true;
                        }
                        readStartPos += headLen + currentPacketLen;
                        //not parsed one packet, and recvData must be not changed, so do not need renew
                        needRenewRecvData = true;
                    } else {
                        break;
                    }
                }
                System.out.println("before need renew");
                // rest data, renew to cache this
                if (needRenewRecvData) {
                    System.out.println("before need renew success");
                    if(readStartPos < recvDataLen){
                        recvData = new ByteArrayOutputStream(bufferLength);
                        if (!parsePacketError) {
                            recvData.write(recvDataByte, readStartPos, recvDataLen - readStartPos);
                        }
                    }else{
                        recvData.reset();
                    }
                }
            }
        }
    }

    private boolean isEmptyString(String str) {
        return null == str || "".equals(str);
    }

    private boolean parseOnePacketData(byte[] recvDataByte, int offset, int len) {
        Map<String, Object> map = convert.unpackMap(recvDataByte, offset, len);
        if (map == null) {
            this.client.listener.errorListener(Client.ErrorType.RECV_PACKET_CRASHED, client);
            // when can not parse to map, maybe data has wrong
            return false;
        } else {
            String sessionId, from, to, type;
            Map<String, Object> content;
            try {
                sessionId = (String) map.get(Client.SESSION_KEY);
                from = "" + map.get("from");
                to = "" + map.get("to");
                type = (String) map.get("type");
                //noinspection unchecked
                content = (Map<String, Object>) map.get("content");
            } catch (Exception e) {
                e.printStackTrace();
                this.client.listener.errorListener(Client.ErrorType.RECV_PACKET_CRASHED, client);
                return true;
            }
            System.out.println("recv packet from: " + from + " to: " + to + " type: " + type);
            if (isEmptyString(from) || isEmptyString(to) || isEmptyString(type)) {
                System.out.println("packet from/to/type is empty");
                this.client.listener.errorListener(Client.ErrorType.RECV_PACKET_CRASHED, client);
                return true;
            }
            if (!client.isAuthorized()) {
                if (!Auth.TYPE.equals(type)) {
                    this.client.listener.errorListener(Client.ErrorType.RECV_PACKET_TYPE, client);
                } else {
                    String uid;
                    int status;
                    try {
                        uid = "" + content.get("uid");
                        status = (Integer) content.get("status");
                    } catch (Exception e) {
                        e.printStackTrace();
                        this.client.listener.errorListener(Client.ErrorType.RECV_PACKET_CRASHED, client);
                        return true;
                    }
                    System.out.println("recv auth packet uid: " + uid + " status: " + status);
                    switch (status) {
                        case Auth.STATUS_NEED_AUTH:
                            client.auth();
                            break;
                        case Auth.STATUS_AUTH_SUCCESS:
                            client.uid = uid;
                            client.sessionId = sessionId;
                            client.authorized = true;
                            this.client.listener.connectListener(client);
                            break;
                        case Auth.STATUS_AUTH_FAILED:
                            this.client.listener.errorListener(Client.ErrorType.AUTH_FAILED, client);
                            break;
                        default:
                            this.client.listener.errorListener(Client.ErrorType.RECV_PACKET_CRASHED, client);
                            break;
                    }
                }
            } else {
                Packet packet = HasSubTypeFactory.getInstance().getPacket(from, to, type, content);
                System.out.println("recv packet parsed instance: " + packet.toString());
                if (packet != null) {
                    processPacket(packet);
                } else {
                    this.client.listener.errorListener(Client.ErrorType.RECV_PACKET_TYPE, client);
                }
            }
            return true;
        }
    }

    private void processPacket(Packet packet) {
        if (packet == null) {
            return;
        }
        // Deliver the incoming packet to listeners.
        listenerExecutor.submit(new ListenerNotification(packet));
    }

    synchronized public void startup() throws JegarnException {
        readerThread.start();
    }

    public void shutdown() {
        // Notify connection listeners of the connection closing if done hasn't already been set.
        done = true;
        // Shut down the listener executor.
        listenerExecutor.shutdown();
    }

    private class ListenerNotification implements Runnable {

        private Packet packet;

        public ListenerNotification(Packet packet) {
            this.packet = packet;
        }

        public void run() {
            System.out.println("[ListenerNotification] dispatch once again before listener");
            client.listener.packetListener(packet, client);
        }
    }
}
