package com.jegarn.jegarn.client;

import com.jegarn.jegarn.packet.base.Auth;
import com.jegarn.jegarn.packet.base.Packet;

import java.io.IOException;
import java.io.OutputStream;
import java.util.concurrent.ArrayBlockingQueue;
import java.util.concurrent.BlockingQueue;

public class PacketWriter {

    private Thread writerThread;
    private Client client;
    private OutputStream outputStream;
    private final BlockingQueue<Packet> queue;
    private boolean done;
    private Convert convert;

    public PacketWriter(Client client) {
        this.queue = new ArrayBlockingQueue<>(500, true);
        this.client = client;
        this.convert = new Convert();
        init();
    }

    public void init() {
        outputStream = client.outputStream;
        done = false;
        writerThread = new Thread() {
            public void run() {
                writePackets(this);
            }
        };
        writerThread.setName("Jegarn Packet Writer (" + client.account + ")");
        writerThread.setDaemon(true);
    }

    private void writePackets(Thread thisThread) {
        try {
            byte[] lengthByte = new byte[4];
            while (!done && (writerThread == thisThread)) {
                Packet packet = nextPacket();
                if (packet != null) {
                    System.out.println("send packet from: " + packet.getFrom() + " to: " + packet.getTo() + " type: " + packet.getType());
                    try{
                        convert.packMapHeader(5);
                        if(null == client.sessionId){
                            convert.packMapNil(Client.SESSION_KEY);
                        }else{
                            convert.packMapString(Client.SESSION_KEY, client.sessionId);
                        }
                        packet.convertToBytes(convert);
                    }catch (IOException e){
                        convert.reset();
                        e.printStackTrace();
                        continue;
                    }
                    byte[] packetByte = convert.packMap();
                    if(packetByte != null){
                        ByteArray.write32bit(packetByte.length, lengthByte, 0);
                        synchronized (outputStream) {
                            // write error will throw IOException
                            outputStream.write(lengthByte);
                            outputStream.write(packetByte);
                            outputStream.flush();
                        }
                    }
                }
            }
            try {
                synchronized (outputStream) {
                    System.out.println("send packet before shutdown size: " + queue.size());
                    while (!queue.isEmpty()) {
                        Packet packet = queue.remove();
                        System.out.println("send packet before shutdown from: " + packet.getFrom() + " to: " + packet.getTo() + " type: " + packet.getType());
                        convert.packMapHeader(5);
                        convert.packMapString(Client.SESSION_KEY, client.sessionId);
                        packet.convertToBytes(convert);
                        byte[] packetByte = convert.packMap();
                        if(packetByte != null){
                            ByteArray.write32bit(packetByte.length, lengthByte, 0);
                            // write error will throw IOException
                            outputStream.write(lengthByte);
                            outputStream.write(packetByte);
                            outputStream.flush();
                        }
                    }
                    outputStream.flush();
                }
            } catch (Exception e) {
                e.printStackTrace();
            }
            queue.clear();
            try {
                outputStream.close();
            } catch (Exception e) {
                e.printStackTrace();
            }
        } catch (IOException ioe) {
            if (!(done || client.isClosed())) {
                done = true;
                if (client.packetReader != null) {
                    client.packetReader.notifyConnectionError(ioe);
                }
            }
        }
    }

    private Packet nextPacket() {
        Packet packet = null;
        // Wait until there's a packet or we're done.
        while (!done && (packet = queue.poll()) == null) {
            try {
                synchronized (queue) {
                    queue.wait();
                }
            } catch (InterruptedException ie) {
                // Do nothing
            }
        }
        return packet;
    }

    public boolean sendPacket(Packet packet) {
        if (!done) {
            // Invoke interceptors for the new packet that is about to be sent. Interceptors
            // may modify the content of the packet.
            System.out.println("before send packet from: " + packet.getFrom() + " to: " + packet.getTo() + " type: " + packet.getType());
            if (client.listener.sendListener(packet, client)) {
                try {
                    queue.put(packet);
                    synchronized (queue) {
                        queue.notifyAll();
                    }
                    return true;
                } catch (InterruptedException ie) {
                    ie.printStackTrace();
                }
            }
        }
        return false;
    }

    protected void sendAuthPacket() {
        if (!done) {
            Auth authPacket = new Auth();
            authPacket.getContent().setAccount(client.account);
            authPacket.getContent().setPassword(client.password);
            System.out.println("before send auth packet account: " + client.account + " password: " + client.password);
            try {
                queue.put(authPacket);
                synchronized (queue) {
                    queue.notifyAll();
                }
            } catch (InterruptedException ie) {
                ie.printStackTrace();
            }
        }
    }

    public void startup() {
        writerThread.start();
    }

    public void shutdown() {
        done = true;
        synchronized (queue) {
            queue.notifyAll();
        }
    }
}
