package com.jegarn.jegarn.packet.base;

import com.jegarn.jegarn.client.Convert;

import java.io.IOException;

public class Auth extends Packet {
    public static final String TYPE = "auth";
    public static final int STATUS_NEED_AUTH = 1;
    public static final int STATUS_AUTH_SUCCESS = 2;
    public static final int STATUS_AUTH_FAILED  = 3;
    protected   AuthContent content;
    public Auth(){
        this.from = "0";
        this.setToSystemUser();
        this.type = TYPE;
        this.content = new AuthContent();
    }

    @Override
    public AuthContent getContent() {
        return content;
    }

    public void setContent(AuthContent content) {
        this.content = content;
    }

    @Override
    public void convertToBytes(Convert convert) throws IOException{
        super.convertToBytes(convert);
        convert.packMapMap("content",4);
        convert.packMapNil("uid");
        convert.packMapString("account", this.content.account);
        convert.packMapString("password",this.content.password);
        convert.packMapNil("status");
    }

    public class AuthContent{
        protected String uid;
        protected String account;
        protected String password;
        protected int status;

        public String getUid() {
            return uid;
        }

        public void setUid(String uid) {
            this.uid = uid;
        }

        public String getAccount() {
            return account;
        }

        public void setAccount(String account) {
            this.account = account;
        }

        public String getPassword() {
            return password;
        }

        public void setPassword(String password) {
            this.password = password;
        }

        public int getStatus() {
            return status;
        }

        public void setStatus(int status) {
            this.status = status;
        }
    }
}
