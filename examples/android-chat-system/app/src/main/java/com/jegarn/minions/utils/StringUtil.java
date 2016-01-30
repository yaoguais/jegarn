package com.jegarn.minions.utils;

abstract public class StringUtil {

    public static boolean isEmptyString(String str) {
        return null == str || "".equals(str);
    }
}
