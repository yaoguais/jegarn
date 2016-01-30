package com.jegarn.minions.activity;

import android.app.Activity;
import android.app.Fragment;
import android.app.FragmentManager;
import android.app.FragmentTransaction;
import android.os.Bundle;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import com.jegarn.minions.R;
import com.jegarn.minions.fragment.ChatroomFragment;
import com.jegarn.minions.fragment.ContactFragment;
import com.jegarn.minions.fragment.ConversationFragment;
import com.jegarn.minions.fragment.GroupchatFragment;

public class MainActivity extends Activity implements View.OnClickListener {

    private ImageView tabConversation, tabContact, tabGroupchat, tabChatroom;
    private int index = 1;
    private FragmentManager mFragmentManager;
    private ConversationFragment conversationFragment;
    private ContactFragment contactFragment;
    private GroupchatFragment groupchatFragment;
    private ChatroomFragment chatroomFragment;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        init();
    }

    private void init() {
        tabConversation = (ImageView) findViewById(R.id.tab_conversation);
        tabContact = (ImageView) findViewById(R.id.tab_contact);
        tabGroupchat = (ImageView) findViewById(R.id.tab_groupchat);
        tabChatroom = (ImageView) findViewById(R.id.tab_chatroom);
        tabConversation.setOnClickListener(this);
        tabContact.setOnClickListener(this);
        tabGroupchat.setOnClickListener(this);
        tabChatroom.setOnClickListener(this);
        tabConversation.setBackgroundResource(R.mipmap.tab_conversation_selected);
        tabContact.setBackgroundResource(R.mipmap.tab_contact);
        tabGroupchat.setBackgroundResource(R.mipmap.tab_groupchat);
        tabChatroom.setBackgroundResource(R.mipmap.tab_chatroom);
        int width = this.getWindowManager().getDefaultDisplay().getWidth() / 4;
        ViewGroup.LayoutParams params = tabConversation.getLayoutParams();
        params.width = width;
        tabConversation.setLayoutParams(params);
        tabContact.setLayoutParams(params);
        tabGroupchat.setLayoutParams(params);
        tabChatroom.setLayoutParams(params);
        mFragmentManager = getFragmentManager();
        setDefaultFragment();
    }

    private void setDefaultFragment() {
        FragmentTransaction transaction = mFragmentManager.beginTransaction();
        conversationFragment = new ConversationFragment();
        transaction.replace(R.id.content_layout, conversationFragment);
        transaction.commit();
    }

    private void replaceFragment(Fragment newFragment) {
        FragmentTransaction transaction = mFragmentManager.beginTransaction();
        if (!newFragment.isAdded()) {
            transaction.replace(R.id.content_layout, newFragment);
            transaction.commit();
        } else {
            transaction.show(newFragment);
        }
    }

    private void clearStatus() {
        if (index == 1) {
            tabConversation.setBackgroundResource(R.mipmap.tab_conversation);
        } else if (index == 2) {
            tabContact.setBackgroundResource(R.mipmap.tab_contact);
        } else if (index == 3) {
            tabGroupchat.setBackgroundResource(R.mipmap.tab_groupchat);
        } else if (index == 4) {
            tabChatroom.setBackgroundResource(R.mipmap.tab_chatroom);
        }
    }

    @Override
    public void onClick(View v) {
        clearStatus();
        switch (v.getId()) {
            case R.id.tab_conversation:
                if (index != 1 || conversationFragment == null) {
                    if (conversationFragment == null) {
                        conversationFragment = new ConversationFragment();
                    }
                    clearStatus();
                    replaceFragment(conversationFragment);
                    tabConversation.setBackgroundResource(R.mipmap.tab_conversation_selected);
                    index = 1;
                }
                break;
            case R.id.tab_contact:
                if (index != 2 || contactFragment == null) {
                    if (contactFragment == null) {
                        contactFragment = new ContactFragment();
                    }
                    clearStatus();
                    replaceFragment(contactFragment);
                    tabContact.setBackgroundResource(R.mipmap.tab_contact_selected);
                    index = 2;
                }

                break;
            case R.id.tab_groupchat:
                if (index != 3 || groupchatFragment == null) {
                    if (groupchatFragment == null) {
                        groupchatFragment = new GroupchatFragment();
                    }
                    clearStatus();
                    replaceFragment(groupchatFragment);
                    tabGroupchat.setBackgroundResource(R.mipmap.tab_groupchat_selected);
                    index = 3;
                }
                break;
            case R.id.tab_chatroom:
                if (index != 4 || chatroomFragment == null) {
                    if (chatroomFragment == null) {
                        chatroomFragment = new ChatroomFragment();
                    }
                    clearStatus();
                    replaceFragment(chatroomFragment);
                    tabChatroom.setBackgroundResource(R.mipmap.tab_chatroom_selected);
                    index = 4;
                }
                break;
        }
    }
}
