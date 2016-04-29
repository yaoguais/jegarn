package com.jegarn.jegarn.packet.base;

import com.jegarn.jegarn.packet.content.HasSubTypeContent;

abstract public class Group extends HasSubTypePacket{
    public Group() {
    }
    public Group(String from, String to, String type, HasSubTypeContent content) {
        super(from, to, type, content);
    }
    public boolean isSendToAll(){
        return "all".equals(this.to);
    }
    public void setSendToAll(){
        this.to = "all";
    }
}
