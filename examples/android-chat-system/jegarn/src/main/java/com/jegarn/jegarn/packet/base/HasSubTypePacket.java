package com.jegarn.jegarn.packet.base;

import com.jegarn.jegarn.packet.content.HasSubTypeContent;

abstract public class HasSubTypePacket extends Packet{

    public static final String SUB_TYPE = null;
    protected HasSubTypeContent content;

    public HasSubTypePacket(){

    }

    public HasSubTypePacket(String from, String to, String type, HasSubTypeContent content) {
        this.from = from;
        this.to = to;
        this.type = type;
        this.content = content;
    }

    @Override
    public HasSubTypeContent getContent() {
        return content;
    }

    public void setContent(HasSubTypeContent content) {
        this.content = content;
    }
}
