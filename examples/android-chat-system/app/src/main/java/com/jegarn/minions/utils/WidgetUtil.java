package com.jegarn.minions.utils;

import android.app.Service;
import android.content.Context;
import android.net.Uri;
import android.os.Vibrator;
import android.widget.Toast;

import com.jegarn.minions.App;
import com.jegarn.minions.R;

abstract public class WidgetUtil {
    private static Vibrator vibrator;

    public static void vivrator(Context context){
        if(vibrator == null){
            vibrator=(Vibrator)context.getSystemService(Service.VIBRATOR_SERVICE);
        }
        vibrator.vibrate(600);
    }

    public static void toast(Context context, String message){
        Toast.makeText(context.getApplicationContext(), message, Toast.LENGTH_SHORT).show();
    }

    public static Uri getImageUri(String url) {
        url = App.SERVER_URL + url.replaceFirst("^/","");
        return Uri.parse(url);
    }

    public static int getImageId(String url){ // fresco will be later.
        String[] strs = url.split("/");
        if(strs.length >= 3){
            String filename = strs[strs.length - 1];
            String name = filename.substring(0,filename.length()-4);
            String type = strs[strs.length-3];
            if("avatar".equals(type)){
                if("g1".equals(name)){
                    return R.mipmap.avatar_g1;
                }else if("g7".equals(name)){
                    return R.mipmap.avatar_g7;
                }else if("g2".equals(name)){
                    return R.mipmap.avatar_g2;
                }else if("b1".equals(name)){
                    return R.mipmap.avatar_b1;
                }else if("b9".equals(name)){
                    return R.mipmap.avatar_b9;
                }else if("b6".equals(name)){
                    return R.mipmap.avatar_b6;
                }else if("b5".equals(name)){
                    return R.mipmap.avatar_b5;
                }else if("g8".equals(name)){
                    return R.mipmap.avatar_g8;
                }else if("b3".equals(name)){
                    return R.mipmap.avatar_b3;
                }else if("b4".equals(name)){
                    return R.mipmap.avatar_b4;
                }else if("b8".equals(name)){
                    return R.mipmap.avatar_b8;
                }else if("b7".equals(name)){
                    return R.mipmap.avatar_b7;
                }else if("g3".equals(name)){
                    return R.mipmap.avatar_g3;
                }else if("g4".equals(name)){
                    return R.mipmap.avatar_g4;
                }else if("g6".equals(name)){
                    return R.mipmap.avatar_g6;
                }else if("g9".equals(name)){
                    return R.mipmap.avatar_g9;
                }else if("b0".equals(name)){
                    return R.mipmap.avatar_b0;
                }else if("b2".equals(name)){
                    return R.mipmap.avatar_b2;
                }else if("g5".equals(name)){
                    return R.mipmap.avatar_g5;
                }else if("g0".equals(name)) {
                    return R.mipmap.avatar_g0;
                }
            }else if("group".equals(type)){
                if("counter".equals(name)){
                    return R.mipmap.group_counter;
                }
            }
        }
        return R.mipmap.avatar;
    }
}
