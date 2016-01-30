package com.jegarn.jegarn.packet.text;

import com.jegarn.jegarn.client.Convert;
import com.jegarn.jegarn.packet.base.ChatRoom;
import com.jegarn.jegarn.packet.content.TextGroupContent;

import java.io.IOException;

public class TextChatRoom extends ChatRoom{
    public static final String SUB_TYPE = "text";
    protected TextGroupContent content = new TextGroupContent();
    public TextChatRoom(){
        this.content.setType(SUB_TYPE);
    }

    public TextChatRoom(String from, String to, String type, TextGroupContent content) {
        super(from, to, type, content);
        this.content = content;
    }
    @Override
    public TextGroupContent getContent() {
        return content;
    }

    public void setContent(TextGroupContent content) {
        this.content = content;
    }

    @Override
    public void convertToBytes(Convert convert) throws IOException {
        // don't call parent, convert from/to/type itself for preventing extra data
        convert.packMapString("from", this.from);
        convert.packMapString("to", this.to);
        convert.packMapString("type", this.type);
        convert.packMapMap("content", 3);
        convert.packMapString("type", this.content.getType());
        convert.packMapInt("group_id", this.content.getGroupId());
        convert.packMapString("text", this.content.getText());
    }
}
