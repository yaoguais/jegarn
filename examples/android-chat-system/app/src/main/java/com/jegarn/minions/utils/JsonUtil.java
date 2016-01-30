package com.jegarn.minions.utils;

import com.google.gson.Gson;
import com.google.gson.JsonSyntaxException;
import com.jegarn.minions.App;

import java.lang.reflect.Type;

abstract public class JsonUtil {

    public static String toJson(Object src) {
        return new Gson().toJson(src);
    }

    public static <T> T fromJson(String json, Class<T> classOfT) throws JsonSyntaxException {
        if(App.DEBUG){
            System.out.println("fromJson: "+json);
        }
        return new Gson().fromJson(json, classOfT);
    }

    public static <T> T fromJson(String json, Type typeOfT) throws JsonSyntaxException {
        if(App.DEBUG){
            System.out.println("fromJson: "+json);
        }
        return new Gson().fromJson(json, typeOfT);
    }
}
