package com.jegarn.jegarn.client;

public class JegarnException extends Exception{
    public JegarnException() {
        super();
    }

    public JegarnException(String detailMessage) {
        super(detailMessage);
    }

    public JegarnException(String detailMessage, Throwable throwable) {
        super(detailMessage, throwable);
    }

    public JegarnException(Throwable throwable) {
        super(throwable);
    }
}
