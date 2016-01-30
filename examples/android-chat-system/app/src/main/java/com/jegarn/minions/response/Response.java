package com.jegarn.minions.response;

import com.google.gson.JsonSyntaxException;
import com.jegarn.minions.utils.JsonUtil;

public class Response {

    public int code = -1;
    public Object response;

    public Response() {

    }

    public Response(int code, Object response) {
        this.code = code;
        this.response = response;
    }

    public static final int FAIL_NETWORK = 3000;
    public static final int FAIL_SERVER_RESPONSE = 3001;

    public static final int SUCCESS = 0;
    public static final int FAIL_INTERNAL_NO_RESPONSE = 4000;
    public static final int FAIL_INTERNAL_NO_CODE = 4001;
    public static final int FAIL_INTERNAL_EXCEPTION = 4002;
    public static final int FAIL_INTERNAL_MULTI_SEND = 4003;
    public static final int FAIL_INTERNAL_MULTI_RESPONSE = 4004;
    public static final int FAIL_ACTION_NOT_REACHABLE = 4005;
    public static final int FAIL_REQUEST_METHOD = 4006;
    public static final int FAIL_PARAMETER_MISSING = 4007;
    public static final int FAIL_PARAMETER_TYPE = 4008;
    public static final int FAIL_OBJECT_NO_THIS_PROPERTY = 4009;
    public static final int FAIL_USER_UID_OR_NAME_NOT_EXISTS = 4010;
    public static final int FAIL_EMPTY_ACCOUNT = 5000;
    public static final int FAIL_EMPTY_PASSWORD = 5001;
    public static final int FAIL_WRONG_ACCOUNT_OR_PASSWORD = 5002;
    public static final int FAIL_LOGIN_TOO_FREQUENTLY = 5003;
    public static final int FAIL_USER_NAME_ALREADY_EXISTS = 5004;
    public static final int FAIL_DATABASE_ERROR = 5005;
    public static final int FAIL_USER_NOT_EXISTS = 5006;
    public static final int FAIL_PASSWORD_LENGTH = 5007;
    public static final int FAIL_INVALID_PASSWORD = 5008;
    public static final int FAIL_INVALID_IP = 5009;
    public static final int FAIL_USER_CREATE_TOO_FREQUENTLY = 5010;
    public static final int FAIL_USER_TOKEN_EXPIRE = 5011;
    public static final int FAIL_UPLOAD_EMPTY_FILE = 5012;
    public static final int FAIL_UPLOAD_FILE_TYPE = 5013;
    public static final int FAIL_UPLOAD_FILE_SIZE = 5014;
    public static final int FAIL_LOGIN_FAILED = 5015;
    public static final int FAIL_ROSTER_STATUS = 5016;
    public static final int FAIL_GROUP_ROSTER_NOT_EXISTS = 5017;
    public static final int FAIL_ROSTER_NOT_EXISTS = 5018;
    public static final int FAIL_MOVE_GROUP = 5019;
    public static final int FAIL_ROSTER_BLACK = 5020;
    public static final int FAIL_ROSTER_UNSUBSCRIBE = 5021;
    public static final int FAIL_MESSAGE_EMPTY = 5022;
    public static final int FAIL_OBJECT_NOT_FOUND = 5023;
    public static final int FAIL_MESSAGE_NOT_EXISTS = 5024;
    public static final int FAIL_GROUP_NAME_EMPTY = 5025;
    public static final int FAIL_GROUP_TYPE = 5026;
    public static final int FAIL_GROUP_NOT_EXISTS = 5027;
    public static final int FAIL_GROUP_USER_PERMISSION = 5028;
    public static final int FAIL_GROUP_USER_STATUS = 5029;
    public static final int FAIL_GROUP_USER_ALREADY_REQUEST = 5030;
    public static final int FAIL_GROUP_USER_ALREADY_MEMBER = 5031;
    public static final int FAIL_GROUP_USER_ALREADY_REFUSED = 5032;
    public static final int FAIL_GROUP_USER_NOT_EXISTS = 5033;
    public static final int FAIL_PERMISSION_DENY = 5034;

    public static boolean isSuccess(int code){
        return code == SUCCESS;
    }

    public static boolean isSuccess(Response resp) {
        return resp.code == SUCCESS;
    }

    public static boolean isSuccess(String str) {
        int firstNumber;
        for (int i = 0, l = str.length(); i < l; ++i) {
            firstNumber = str.charAt(i);
            if (firstNumber >= 48 && firstNumber <= 57) {
                if (firstNumber == 48) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    public static String getMessage(int code) {
        switch (code) {
            case FAIL_NETWORK:
                return "network error";
            case FAIL_SERVER_RESPONSE:
                return "server response parse failed";
            case SUCCESS:
                return "success";
            case FAIL_INTERNAL_NO_RESPONSE:
                return "server not available currently";
            case FAIL_INTERNAL_NO_CODE:
                return "server not available currently";
            case FAIL_INTERNAL_EXCEPTION:
                return "server not available currently";
            case FAIL_INTERNAL_MULTI_SEND:
                return "server not available currently";
            case FAIL_INTERNAL_MULTI_RESPONSE:
                return "server not available currently";
            case FAIL_ACTION_NOT_REACHABLE:
                return "server not available currently";
            case FAIL_REQUEST_METHOD:
                return "server not available currently";
            case FAIL_PARAMETER_MISSING:
                return "server not available currently";
            case FAIL_PARAMETER_TYPE:
                return "server not available currently";
            case FAIL_OBJECT_NO_THIS_PROPERTY:
                return "server not available currently";
            case FAIL_USER_UID_OR_NAME_NOT_EXISTS:
                return "server not available currently";
            case FAIL_EMPTY_ACCOUNT:
                return "account required";
            case FAIL_EMPTY_PASSWORD:
                return "password required";
            case FAIL_WRONG_ACCOUNT_OR_PASSWORD:
                return "password or account lost";
            case FAIL_LOGIN_TOO_FREQUENTLY:
                return "login too frequently";
            case FAIL_USER_NAME_ALREADY_EXISTS:
                return "account already exists";
            case FAIL_DATABASE_ERROR:
                return "server not available currently";
            case FAIL_USER_NOT_EXISTS:
                return "user not exists";
            case FAIL_PASSWORD_LENGTH:
                return "password too short";
            case FAIL_INVALID_PASSWORD:
                return "password invalid";
            case FAIL_INVALID_IP:
                return "your ip is invalid";
            case FAIL_USER_CREATE_TOO_FREQUENTLY:
                return "register too frequently";
            case FAIL_USER_TOKEN_EXPIRE:
                return "token expire";
            case FAIL_UPLOAD_EMPTY_FILE:
                return "upload file is empty";
            case FAIL_UPLOAD_FILE_TYPE:
                return "upload file type error";
            case FAIL_UPLOAD_FILE_SIZE:
                return "upload file size error";
            case FAIL_LOGIN_FAILED:
                return "login failed";
            case FAIL_ROSTER_STATUS:
                return "roster status error";
            case FAIL_GROUP_ROSTER_NOT_EXISTS:
                return "roster group not exists";
            case FAIL_ROSTER_NOT_EXISTS:
                return "roster not exists";
            case FAIL_MOVE_GROUP:
                return "move group failed";
            case FAIL_ROSTER_BLACK:
                return "you in black list";
            case FAIL_ROSTER_UNSUBSCRIBE:
                return "unsubscribe failed";
            case FAIL_MESSAGE_EMPTY:
                return "message is empty";
            case FAIL_OBJECT_NOT_FOUND:
                return "object not found";
            case FAIL_MESSAGE_NOT_EXISTS:
                return "message not exists";
            case FAIL_GROUP_NAME_EMPTY:
                return "group name is empty";
            case FAIL_GROUP_TYPE:
                return "group type error";
            case FAIL_GROUP_NOT_EXISTS:
                return "group not exists";
            case FAIL_GROUP_USER_PERMISSION:
                return "permission deny";
            case FAIL_GROUP_USER_STATUS:
                return "status wrong";
            case FAIL_GROUP_USER_ALREADY_REQUEST:
                return "already request";
            case FAIL_GROUP_USER_ALREADY_MEMBER:
                return "already a member";
            case FAIL_GROUP_USER_ALREADY_REFUSED:
                return "you are be refused";
            case FAIL_GROUP_USER_NOT_EXISTS:
                return "user not exists";
            case FAIL_PERMISSION_DENY:
                return "permission deny";
        }
        return "server not available currently";
    }

    public static String getMessage(Response resp) {
        return Response.getMessage(resp.code);
    }

    public static String getMessage(String str){
        Response resp;
        try{
            resp = JsonUtil.fromJson(str, Response.class);
            return getMessage(resp);
        }catch(JsonSyntaxException e){
            return getMessage(FAIL_INTERNAL_NO_CODE);
        }

    }
}
