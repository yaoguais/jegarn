package com.jegarn.jegarn.packet.text;

import com.jegarn.jegarn.client.Convert;
import com.jegarn.jegarn.packet.base.Chat;
import com.jegarn.jegarn.packet.content.TextChatContent;

import java.io.IOException;

public class TextChat extends Chat {

    public static final String SUB_TYPE = "text";
    protected TextChatContent content = new TextChatContent();

    public TextChat() {
        this.content.setType(SUB_TYPE);
    }

    public TextChat(String from, String to, String type, TextChatContent content) {
        super(from, to, type, content);
        this.content = content;
    }

    @Override
    public TextChatContent getContent() {
        return content;
    }

    public void setContent(TextChatContent content) {
        this.content = content;
    }

    @Override
    public void convertToBytes(Convert convert) throws IOException {
        // don't call parent, convert from/to/type itself for preventing extra data
        convert.packMapString("from", this.from);
        convert.packMapString("to", this.to);
        convert.packMapString("type", this.type);
        convert.packMapMap("content", 2);
        convert.packMapString("type", this.content.getType());
        convert.packMapString("text", this.content.getText());
    }
}
