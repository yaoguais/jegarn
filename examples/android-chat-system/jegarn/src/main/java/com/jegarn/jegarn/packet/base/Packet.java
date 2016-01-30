package com.jegarn.jegarn.packet.base;

import com.jegarn.jegarn.client.Convert;

import java.io.IOException;

public class Packet {
    public static final String TYPE = null;
    protected String from;
    protected String to;
    protected String type;
    protected Object content;

    public Packet(){

    }

    public Packet(String from, String to, String type, Object content) {
        this.from = from;
        this.to = to;
        this.type = type;
        this.content = content;
    }

    public boolean isFromSystemUser() {
        return "system".equals(this.from);
    }

    public void setToSystemUser() {
        this.to = "system";
    }

    public void convertToBytes(Convert convert) throws IOException {
        convert.packMapString("from",this.from);
        convert.packMapString("to", this.to);
        convert.packMapString("type",this.type);
    }

    public String getFrom() {
        return from;
    }

    public void setFrom(String from) {
        this.from = from;
    }

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public String getTo() {
        return to;
    }

    public void setTo(String to) {
        this.to = to;
    }

    public Object getContent() {
        return content;
    }

    public void setContent(Object content) {
        this.content = content;
    }
}
