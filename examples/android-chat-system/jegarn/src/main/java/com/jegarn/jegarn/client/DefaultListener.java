package com.jegarn.jegarn.client;

import com.jegarn.jegarn.listener.ChatManagerListener;
import com.jegarn.jegarn.listener.ChatRoomManagerListener;
import com.jegarn.jegarn.listener.GroupChatManagerListener;
import com.jegarn.jegarn.listener.NotificationManagerListener;
import com.jegarn.jegarn.packet.base.Chat;
import com.jegarn.jegarn.packet.base.ChatRoom;
import com.jegarn.jegarn.packet.base.GroupChat;
import com.jegarn.jegarn.packet.base.Notification;
import com.jegarn.jegarn.packet.base.Packet;

public class DefaultListener implements Listener {

    @Override
    public void packetListener(Packet packet, Client client) {
        if (packet instanceof Chat) {
            for (ChatManagerListener listener : client.chatManager.getListeners()) {
                if (!listener.processPacket((Chat)packet)) {
                    return;
                }
            }
        } else if (packet instanceof Notification) {
            for (NotificationManagerListener listener : client.notificationManager.getListeners()) {
                if (!listener.processPacket((Notification)packet)) {
                    return;
                }
            }
        } else if (packet instanceof GroupChat) {
            for (GroupChatManagerListener listener : client.groupChatManager.getListeners()) {
                if (!listener.processPacket((GroupChat)packet)) {
                    return;
                }
            }
        } else if (packet instanceof ChatRoom) {
            for (ChatRoomManagerListener listener : client.chatRoomManager.getListeners()) {
                if (!listener.processPacket((ChatRoom)packet)) {
                    return;
                }
            }
        }
    }

    @Override
    public boolean sendListener(Packet packet, Client client) {
        return true;
    }

    @Override
    public void errorListener(Client.ErrorType type, Client client) {

    }

    @Override
    public void connectListener(Client client) {

    }

    @Override
    public void disconnectListener(Client client) {

    }
}
