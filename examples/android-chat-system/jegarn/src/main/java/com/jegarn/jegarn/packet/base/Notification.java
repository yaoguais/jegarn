package com.jegarn.jegarn.packet.base;

public class Notification extends HasSubTypePacket{
    public static final String TYPE = "notification";
    public Notification(){
        this.type = TYPE;
    }
}
