package com.jegarn.jegarn.packet.base;

import com.jegarn.jegarn.packet.content.HasSubTypeContent;

abstract public class Chat extends HasSubTypePacket{
    public static final String TYPE = "chat";
    public Chat(){
        this.type = TYPE;
    }

    public Chat(String from, String to, String type, HasSubTypeContent content) {
        super(from, to, type, content);
    }
}
