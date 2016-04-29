package com.jegarn.jegarn.packet.factory;

import com.jegarn.jegarn.packet.base.GroupChat;
import com.jegarn.jegarn.packet.content.TextGroupContent;
import com.jegarn.jegarn.packet.text.TextGroupChat;

import java.util.Map;

public class GroupChatFactory {
    private static GroupChatFactory instance;
    private GroupChatFactory(){

    }
    public static GroupChatFactory getInstance(){
        if(instance == null){
            synchronized (GroupChatFactory.class){
                instance = new GroupChatFactory();
            }
        }
        return instance;
    }
    public GroupChat getPacket(String from, String to, String type, Map<String, Object> content){
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
                return new TextGroupChat(from, to, type, new TextGroupContent(groupId, text));
            }catch (ClassCastException e){
                e.printStackTrace();
            }
        }
        return null;
    }
}
