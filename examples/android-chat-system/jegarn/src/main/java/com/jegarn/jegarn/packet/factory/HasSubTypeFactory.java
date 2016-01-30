package com.jegarn.jegarn.packet.factory;

import com.jegarn.jegarn.packet.base.Chat;
import com.jegarn.jegarn.packet.base.ChatRoom;
import com.jegarn.jegarn.packet.base.GroupChat;
import com.jegarn.jegarn.packet.base.HasSubTypePacket;

import java.util.Map;

public class HasSubTypeFactory {
    private static HasSubTypeFactory instance;

    private HasSubTypeFactory() {

    }

    public static HasSubTypeFactory getInstance() {
        if (instance == null) {
            synchronized (HasSubTypeFactory.class) {
                instance = new HasSubTypeFactory();
            }
        }
        return instance;
    }

    public HasSubTypePacket getPacket(String from, String to, String type, Map<String, Object> content) {
        if (null == type || "".equals(type)) {
            return null;
        } else if (Chat.TYPE.equals(type)) {
            return ChatFactory.getInstance().getPacket(from, to, type, content);
        } else if (GroupChat.TYPE.equals(type)) {
            return GroupChatFactory.getInstance().getPacket(from, to, type, content);
        } else if (ChatRoom.TYPE.equals(type)) {
            return ChatRoomFactory.getInstance().getPacket(from, to, type, content);
        }
        return null;
    }
}
