package com.jegarn.jegarn.packet.factory;

import com.jegarn.jegarn.packet.base.Chat;
import com.jegarn.jegarn.packet.content.TextChatContent;
import com.jegarn.jegarn.packet.text.TextChat;

import java.util.Map;

public class ChatFactory {
    private static ChatFactory instance;
    private ChatFactory(){

    }
    public static ChatFactory getInstance(){
        if(instance == null){
            synchronized (ChatFactory.class){
                instance = new ChatFactory();
            }
        }
        return instance;
    }
    public Chat getPacket(String from, String to, String type, Map<String, Object> content){
        if(content == null){
            return null;
        }
        String subType = "" + content.get("type");
        if("".equals(subType)){
            return null;
        }else if(TextChat.SUB_TYPE.equals(subType)){
            return new TextChat(from, to, type, new TextChatContent("" + content.get("text")));
        }
        return null;
    }
}
