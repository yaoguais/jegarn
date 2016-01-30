package com.jegarn.jegarn.packet.content;

public class TextChatContent extends HasSubTypeContent {
    protected String text;

    public TextChatContent(){
        this.type = "text";
    }

    public TextChatContent(String text){
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
