package com.jegarn.minions.im;

import android.content.Context;

import com.jegarn.jegarn.client.Client;
import com.jegarn.jegarn.client.DefaultListener;
import com.jegarn.jegarn.listener.ChatManagerListener;
import com.jegarn.jegarn.listener.ChatRoomManagerListener;
import com.jegarn.jegarn.listener.GroupChatManagerListener;
import com.jegarn.jegarn.listener.NotificationManagerListener;
import com.jegarn.jegarn.packet.base.Chat;
import com.jegarn.jegarn.packet.base.ChatRoom;
import com.jegarn.jegarn.packet.base.GroupChat;
import com.jegarn.jegarn.packet.base.Notification;
import com.jegarn.jegarn.packet.base.Packet;
import com.jegarn.jegarn.packet.text.TextChat;
import com.jegarn.jegarn.packet.text.TextChatRoom;
import com.jegarn.jegarn.packet.text.TextGroupChat;
import com.jegarn.minions.entity.Message;
import com.jegarn.minions.manager.UserManager;
import com.jegarn.minions.model.User;
import com.jegarn.minions.utils.WidgetUtil;

import java.util.Collection;

public class DbRecordListener extends DefaultListener {

    private Context context;

    public DbRecordListener(Context context)
    {
        this.context = context;
    }
    @Override
    public void packetListener(Packet packet, Client client) {
        if (packet instanceof Chat) {
            Collection<ChatManagerListener> listeners = client.getChatManager().getListeners();
            if (listeners.size() > 0) {
                for (ChatManagerListener listener : listeners) {
                    if (!listener.processPacket((Chat) packet)) {
                        return;
                    }
                }
            } else {
                if (packet instanceof TextChat) {
                    TextChat pkt = (TextChat) packet;
                    Message message = new Message(
                            pkt.getFrom(),
                            null,
                            pkt.getTo(),
                            0,
                            Message.TYPE_CHAT,
                            pkt.getContent().getType(),
                            pkt.getContent().getText(),
                            System.currentTimeMillis()
                    );
                    UserManager.loadUser(null, pkt.getFrom(), new RecoredMessageUserLoaderListener(message));
                }
            }
        } else if (packet instanceof Notification) {
            for (NotificationManagerListener listener : client.getNotificationManager().getListeners()) {
                if (!listener.processPacket((Notification) packet)) {
                    return;
                }
            }
        } else if (packet instanceof GroupChat) {
            Collection<GroupChatManagerListener> listeners = client.getGroupChatManager().getListeners();
            if(listeners.size() > 0){
                for (GroupChatManagerListener listener : listeners) {
                    if (!listener.processPacket((GroupChat) packet)) {
                        return;
                    }
                }
            }else {
                if (packet instanceof TextGroupChat) {
                    TextGroupChat pkt = (TextGroupChat) packet;
                    Message message = new Message(
                            pkt.getFrom(),
                            null,
                            pkt.getTo(),
                            pkt.getContent().getGroupId(),
                            Message.TYPE_GROUPCHAT,
                            pkt.getContent().getType(),
                            pkt.getContent().getText(),
                            System.currentTimeMillis()
                    );
                    UserManager.loadUser(null, pkt.getFrom(), new RecoredMessageUserLoaderListener(message));
                    WidgetUtil.vivrator(context);
                }
            }
        } else if (packet instanceof ChatRoom) {
            Collection<ChatRoomManagerListener> listeners = client.getChatRoomManager().getListeners();
            if(listeners.size() > 0){
                for (ChatRoomManagerListener listener : listeners) {
                    if (!listener.processPacket((ChatRoom) packet)) {
                        return;
                    }
                }
            }else {
                if (packet instanceof TextChatRoom) {
                    TextChatRoom pkt = (TextChatRoom) packet;
                    Message message = new Message(
                            pkt.getFrom(),
                            null,
                            pkt.getTo(),
                            pkt.getContent().getGroupId(),
                            Message.TYPE_CHATROOM,
                            pkt.getContent().getType(),
                            pkt.getContent().getText(),
                            0
                    );
                    UserManager.loadUser(null, pkt.getFrom(), new RecoredMessageUserLoaderListener(message));
                }
            }
        }
    }

    public static class RecoredMessageUserLoaderListener extends UserManager.UserLoaderListener {

        private Message message;

        public RecoredMessageUserLoaderListener(Message message) {
            this.message = message;
        }

        @Override
        public void loadSuccess(User user) {
            this.message.setFromAvatar(user.avatar);
            Message.saveNewMessage(message);
        }
    }
}
