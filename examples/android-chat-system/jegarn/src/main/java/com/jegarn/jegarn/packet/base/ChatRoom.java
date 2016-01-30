package com.jegarn.jegarn.packet.base;

import com.jegarn.jegarn.packet.content.GroupContent;
import com.jegarn.jegarn.packet.content.HasSubTypeContent;

public class ChatRoom extends Group{
    public static final String TYPE = "chatroom";
    protected GroupContent content;
    public ChatRoom(){
        this.type = TYPE;
    }

    public ChatRoom(String from, String to, String type, HasSubTypeContent content) {
        super(from, to, type, content);
    }

    @Override
    public GroupContent getContent() {
        return content;
    }

    public void setContent(GroupContent content) {
        this.content = content;
    }
}
