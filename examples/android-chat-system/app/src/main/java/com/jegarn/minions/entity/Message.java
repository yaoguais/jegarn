package com.jegarn.minions.entity;

import com.orm.SugarRecord;

import java.util.List;

public class Message extends SugarRecord {
    private String fromUid;
    private String fromAvatar;
    private String toUid;
    private int groupId;
    private int type;
    private String contentType;
    private String content;
    private long dateline;

    public static final int TYPE_CHAT = 1;
    public static final int TYPE_GROUPCHAT = 2;
    public static final int TYPE_CHATROOM = 3;

    public Message() {
    }

    public Message(String from, String fromAvatar, String to, int groupId, int type, String contentType, String content, long dateline) {
        this.fromUid = from;
        this.fromAvatar = fromAvatar;
        this.toUid = to;
        this.groupId = groupId;
        this.type = type;
        this.contentType = contentType;
        this.content = content;
        this.dateline = dateline;
    }

    public String getFromUid() {
        return fromUid;
    }

    public String getFromAvatar() {
        return fromAvatar;
    }

    public String getToUid() {
        return toUid;
    }

    public int getGroupId() {
        return groupId;
    }

    public int getType() {
        return type;
    }

    public String getContentType() {
        return contentType;
    }

    public String getContent() {
        return content;
    }

    public long getDateline() {
        return dateline;
    }

    public void setFromAvatar(String fromAvatar) {
        this.fromAvatar = fromAvatar;
    }

    public static long saveNewMessage(Message message)
    {
        if(message.type == Message.TYPE_CHAT){
            message.groupId = 0;
        }
        message.dateline = System.currentTimeMillis();
        return message.save();
    }

    public static List<Message> listAllByType(int type, String from, String to, int groupId) {
        if (type == Message.TYPE_CHAT) {
            return Message.find(Message.class, "TYPE = ? and ((`FROM_UID` = ? and `TO_UID` = ?) or (`TO_UID` = ? and `FROM_UID` = ?))",
                    new String[]{"" + type, from, to, from, to}, null, "DATELINE asc", null);
        } else {
            return Message.find(Message.class, "TYPE = ? and GROUP_ID = ?", new String[]{"" + type, "" + groupId},
                    null, "DATELINE asc", null);
        }
    }
}
