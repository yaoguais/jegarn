package com.jegarn.minions.activity;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;

import com.jegarn.minions.App;
import com.jegarn.minions.R;

public class SplashActivity extends Activity {

    private Handler handler = new Handler();
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);

        this.handler.postDelayed(new Runnable() {
            @Override
            public void run() {
                finish();
            }
        }, 3000);
    }

    public void finish(){
        App.init();
        Intent intent = new Intent(SplashActivity.this, LoginActivity.class);
        startActivity(intent);
        super.finish();
    }
}
