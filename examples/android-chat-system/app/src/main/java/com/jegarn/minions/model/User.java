package com.jegarn.minions.model;

public class User implements Cloneable{
    public static final int OFFLINE = 0;
    public static final int ONLINE = 1;
    public String uid;
    public String account;
    public String nick;
    public String motto;
    public String token;
    public String avatar;
    public int present;
}
