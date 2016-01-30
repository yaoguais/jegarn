package com.jegarn.jegarn.client;

import com.jegarn.jegarn.packet.base.Packet;

public interface Listener {
    public void packetListener(Packet packet, Client client);
    public boolean sendListener(Packet packet, Client client);
    public void errorListener(Client.ErrorType type, Client client);
    public void connectListener(Client client);
    public void disconnectListener(Client client);
}