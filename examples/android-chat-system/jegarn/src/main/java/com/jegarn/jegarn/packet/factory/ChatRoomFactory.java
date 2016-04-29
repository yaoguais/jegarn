package com.jegarn.jegarn.packet.factory;

import com.jegarn.jegarn.packet.base.ChatRoom;
import com.jegarn.jegarn.packet.content.TextGroupContent;
import com.jegarn.jegarn.packet.text.TextChatRoom;
import com.jegarn.jegarn.packet.text.TextGroupChat;

import java.util.Map;

public class ChatRoomFactory {
    private static ChatRoomFactory instance;
    private ChatRoomFactory(){

    }
    public static ChatRoomFactory getInstance(){
        if(instance == null){
            synchronized (ChatRoomFactory.class){
                instance = new ChatRoomFactory();
            }
        }
        return instance;
    }
    public ChatRoom getPacket(String from, String to, String type, Map<String, Object> content){
        if(content == null){
            return null;
        }
        String subType = "" + content.get("type");
        if("".equals(subType)){
            return null;
        }else if(TextGroupChat.SUB_TYPE.equals(subType)){
            try{
                int groupId = Integer.parseInt((String) content.get("group_id"));
                String text = "" + content.get("text");
                return new TextChatRoom(from, to, type, new TextGroupContent(groupId, text));
            }catch (ClassCastException e){
                e.printStackTrace();
            }
        }
        return null;
    }
}
