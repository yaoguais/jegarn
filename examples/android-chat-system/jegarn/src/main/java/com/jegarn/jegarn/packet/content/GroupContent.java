package com.jegarn.jegarn.packet.content;

abstract public class GroupContent extends HasSubTypeContent{

    protected int groupId;

    public GroupContent(){

    }

    public GroupContent(int groupId){
        this.groupId = groupId;
    }

    public int getGroupId() {
        return groupId;
    }

    public void setGroupId(int groupId) {
        this.groupId = groupId;
    }
}
