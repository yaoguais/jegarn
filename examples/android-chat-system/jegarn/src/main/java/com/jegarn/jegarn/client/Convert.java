package com.jegarn.jegarn.client;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;

import org.msgpack.core.MessagePack;
import org.msgpack.core.MessagePacker;
import org.msgpack.jackson.dataformat.MessagePackFactory;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.util.Map;

public class Convert {

    protected MessagePacker packer;
    protected ObjectMapper objectMapper;
    protected ByteArrayOutputStream outputStream;
    protected TypeReference<Map<String, Object>> typeReference;

    public Convert() {
        outputStream = new ByteArrayOutputStream(2048);
        this.packer = MessagePack.newDefaultPacker(outputStream);
        this.objectMapper = new ObjectMapper(new MessagePackFactory());
    }

    public byte[] packMap() {
        byte[] out = null;
        try {
            packer.flush();
            out = outputStream.toByteArray();
        } catch (IOException e) {
            e.printStackTrace();
        }
        outputStream.reset();
        return out;
    }

    public Map<String, Object> unpackMap(byte[] bytes, int offset, int len) {
        if (typeReference == null) {
            typeReference = new TypeReference<Map<String, Object>>() {
            };
        }
        try {
            return objectMapper.readValue(bytes, offset, len, typeReference);
        } catch (IOException e) {
            e.printStackTrace();
            return null;
        }
    }

    public void reset(){
        outputStream.reset();
    }
    private MessagePacker packNil() throws IOException {
        packer.packNil();
        return packer;
    }

    public MessagePacker packMapNil(String key) throws IOException {
        packString(key);
        return packNil();
    }

    private MessagePacker packBoolean(boolean b) throws IOException {
        packer.packBoolean(b);
        return packer;
    }

    public MessagePacker packMapBoolean(String key, boolean b) throws IOException {
        packString(key);
        return packBoolean(b);
    }

    private MessagePacker packInt(int r) throws IOException {
        packer.packInt(r);
        return packer;
    }

    public MessagePacker packMapInt(String key, int r) throws IOException {
        packString(key);
        return packInt(r);
    }

    private MessagePacker packLong(long v) throws IOException {
        packer.packLong(v);
        return packer;
    }

    public MessagePacker packMapLong(String key, long v) throws IOException {
        packString(key);
        return packLong(v);
    }

    private MessagePacker packFloat(float v) throws IOException {
        packer.packFloat(v);
        return packer;
    }

    public MessagePacker packMapFloat(String key, float v) throws IOException {
        packString(key);
        return packFloat(v);
    }

    private MessagePacker packDouble(double v) throws IOException {
        packer.packDouble(v);
        return packer;
    }

    public MessagePacker packMapDouble(String key, double v) throws IOException {
        packString(key);
        return packDouble(v);
    }

    private MessagePacker packString(String s) throws IOException {
        packer.packString(s);
        return packer;
    }

    public MessagePacker packMapString(String key, String s) throws IOException {
        packString(key);
        return packString(s);
    }

    public MessagePacker packMapMap(String key, int mapSize) throws IOException {
        packString(key);
        return packMapHeader(mapSize);
    }

    private MessagePacker packArrayHeader(int arraySize) throws IOException {
        packer.packArrayHeader(arraySize);
        return packer;
    }

    public MessagePacker packMapHeader(int mapSize) throws IOException {
        packer.packMapHeader(mapSize);
        return packer;
    }
}
