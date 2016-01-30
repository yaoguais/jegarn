package com.jegarn.jegarn.packet.base;

import com.jegarn.jegarn.packet.content.GroupContent;

abstract public class GroupChat extends Group{
    public static final String TYPE = "groupchat";
    protected GroupContent content;
    public GroupChat(){
        this.type = TYPE;
    }

    public GroupChat(String from, String to, String type, GroupContent content) {
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
