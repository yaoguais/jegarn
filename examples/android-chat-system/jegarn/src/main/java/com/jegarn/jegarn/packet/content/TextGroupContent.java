package com.jegarn.jegarn.packet.content;

public class TextGroupContent extends GroupContent{
    protected String text;

    public TextGroupContent() {
        this.type = "text";
    }

    public TextGroupContent(int groupId, String text) {
        super(groupId);
        this.type = "text";
        this.text = text;
    }

    public String getText() {
        return text;
    }

    public void setText(String text) {
        this.text = text;
    }
}
