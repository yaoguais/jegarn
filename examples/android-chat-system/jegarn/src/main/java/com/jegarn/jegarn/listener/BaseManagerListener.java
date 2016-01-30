package com.jegarn.jegarn.listener;

public interface BaseManagerListener <T>{
    boolean processPacket(T packet);
}
