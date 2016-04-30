package com.jegarn.minions.activity;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.TextView;

import com.facebook.drawee.backends.pipeline.Fresco;
import com.facebook.drawee.view.SimpleDraweeView;
import com.google.gson.JsonSyntaxException;
import com.jegarn.jegarn.listener.ChatManagerListener;
import com.jegarn.jegarn.listener.ChatRoomManagerListener;
import com.jegarn.jegarn.listener.GroupChatManagerListener;
import com.jegarn.jegarn.manager.JegarnManager;
import com.jegarn.jegarn.packet.base.Chat;
import com.jegarn.jegarn.packet.base.ChatRoom;
import com.jegarn.jegarn.packet.base.GroupChat;
import com.jegarn.jegarn.packet.content.TextChatContent;
import com.jegarn.jegarn.packet.content.TextGroupContent;
import com.jegarn.jegarn.packet.text.TextChat;
import com.jegarn.jegarn.packet.text.TextChatRoom;
import com.jegarn.jegarn.packet.text.TextGroupChat;
import com.jegarn.minions.App;
import com.jegarn.minions.R;
import com.jegarn.minions.manager.UserManager;
import com.jegarn.minions.model.Group;
import com.jegarn.minions.model.Message;
import com.jegarn.minions.model.User;
import com.jegarn.minions.response.Response;
import com.jegarn.minions.utils.JsonUtil;
import com.jegarn.minions.utils.StringUtil;
import com.jegarn.minions.utils.WidgetUtil;
import com.zhy.http.okhttp.OkHttpUtils;
import com.zhy.http.okhttp.callback.StringCallback;

import java.lang.ref.WeakReference;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;

import okhttp3.Call;

public class GroupChatActivity extends Activity implements View.OnClickListener {

    private TextView mUserNickTextView;
    private ListView mMessageListView;
    private MessageAdapter mMessageAdapter;
    private EditText mInputEditText;
    private ImageView mSendButton;
    private String fromUserUid, fromUserAvatar, toGroupName;
    private int toGroupId, toGroupType;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_chat);
        fromUserUid = App.user.uid;
        fromUserAvatar = App.user.avatar;
        mUserNickTextView = (TextView) findViewById(R.id.text_user_nick);
        Intent intent = getIntent();
        Bundle bundle = intent.getBundleExtra("groupMap");
        toGroupId = bundle.getInt("group_id");
        toGroupName = bundle.getString("name");
        toGroupType = bundle.getInt("type");
        System.out.println("group onCreate group_id: " + toGroupId + " type: " + toGroupType);
        mUserNickTextView.setText(toGroupName);
        mMessageListView = (ListView) findViewById(R.id.list_message);
        this.initMessageAdapter();
        mMessageListView.setAdapter(mMessageAdapter);
        mInputEditText = (EditText) findViewById(R.id.text_send_input);
        mSendButton = (ImageView) findViewById(R.id.image_send_button);
        mSendButton.setOnClickListener(this);
        this.initPacketListener();
    }

    private void initMessageAdapter() {
        int type  = toGroupType == Group.TYPE_GROUP ? com.jegarn.minions.entity.Message.TYPE_GROUPCHAT
                : com.jegarn.minions.entity.Message.TYPE_CHATROOM;
        List<com.jegarn.minions.entity.Message> dbMessages = com.jegarn.minions.entity.Message.listAllByType(
                type, fromUserUid, null, toGroupId
        );
        LinkedList<Message> messages = null;
        if (dbMessages != null) {
            messages = new LinkedList<>();
            for (com.jegarn.minions.entity.Message dbMessage : dbMessages) {
                Message message = new Message();
                message.from = dbMessage.getFromUid();
                message.fromAvatar = dbMessage.getFromAvatar();
                message.to = dbMessage.getToUid();
                message.type = Message.TYPE_TEXT;
                message.content = dbMessage.getContent();
                messages.add(message);
            }
        }
        mMessageAdapter = new MessageAdapter(this, messages);
    }

    @Override
    public void onClick(View v) {
        String text = mInputEditText.getText().toString();
        if (StringUtil.isEmptyString(text)) {
            WidgetUtil.toast(this, "message can not be empty");
        } else {
            TextGroupContent groupContent = new TextGroupContent(toGroupId, text);
            String type = toGroupType == Group.TYPE_GROUP ? GroupChat.TYPE : ChatRoom.TYPE;
            TextGroupChat packet = new TextGroupChat(fromUserUid, Group.TO_ALL, type, groupContent);
            packet.setSendToAll();
            if (JegarnManager.client.sendPacket(packet)) {
                Message message = new Message();
                message.from = fromUserUid;
                message.fromAvatar = fromUserAvatar;
                message.to = Group.TO_ALL;
                message.type = Message.TYPE_TEXT;
                message.content = text;
                mMessageAdapter.addMessage(message, true);
                mInputEditText.setText(null);

                int dbMessageType  = toGroupType == Group.TYPE_GROUP ? com.jegarn.minions.entity.Message.TYPE_GROUPCHAT
                        : com.jegarn.minions.entity.Message.TYPE_CHATROOM;
                com.jegarn.minions.entity.Message dbMessage = new com.jegarn.minions.entity.Message(
                        fromUserUid,
                        fromUserAvatar,
                        message.to,
                        toGroupId,
                        dbMessageType,
                        TextGroupChat.SUB_TYPE,
                        text,
                        0
                );
                com.jegarn.minions.entity.Message.saveNewMessage(dbMessage);
            } else {
                WidgetUtil.toast(this, "send message failed");
            }
        }
    }

    private void initPacketListener() {

        if (toGroupType == Group.TYPE_GROUP) {
            this.initGroupChatPacketListener();
        } else {
            this.initChatRoomPacketListener();
        }
    }

    private void initGroupChatPacketListener() {

        JegarnManager.client.getGroupChatManager().addListener(new GroupChatManagerListener() {
            @Override
            public boolean processPacket(GroupChat packet) {
                if (packet instanceof TextGroupChat) {
                    TextGroupChat textPacket = (TextGroupChat) packet;
                    Message message = new Message();
                    message.from = packet.getFrom();
                    message.fromAvatar = null;
                    message.to = packet.getTo();
                    message.type = Message.TYPE_TEXT;
                    message.content = textPacket.getContent().getText();
                    getFromUserInfoAndAppendMessage(message);
                } else {
                    System.out.println("[GroupChatActivity groupchat] recv packet subType " + packet.getContent().getType() + " but need text");
                }
                return true;
            }
        });
    }

    private void initChatRoomPacketListener() {

        JegarnManager.client.getChatRoomManager().addListener(new ChatRoomManagerListener() {
            @Override
            public boolean processPacket(ChatRoom packet) {
                if (packet instanceof TextChatRoom) {
                    TextChatRoom textPacket = (TextChatRoom) packet;
                    Message message = new Message();
                    message.from = packet.getFrom();
                    message.fromAvatar = null;
                    message.to = packet.getTo();
                    message.type = Message.TYPE_TEXT;
                    message.content = textPacket.getContent().getText();
                    getFromUserInfoAndAppendMessage(message);
                } else {
                    System.out.println("[GroupChatActivity chatroom] recv packet subType " + packet.getContent().getType() + " but need text");
                }
                return true;
            }
        });
    }

    public void getFromUserInfoAndAppendMessage(Message message)
    {
        UserManager.loadUser(this.getApplicationContext(), message.from, new GroupMessageLoadListener(message));
    }

    public class GroupMessageLoadListener extends UserManager.UserLoaderListener{
        Message message;
        public GroupMessageLoadListener(Message message) {
            this.message = message;
        }

        @Override
        public void loadSuccess(User user) {
            message.fromAvatar = user.avatar;
            android.os.Message msg = mHandler.obtainMessage();
            msg.what = MSG_RECV_CHAT_PACKET;
            msg.obj = message;
            msg.sendToTarget();

            WidgetUtil.vivrator(GroupChatActivity.this.getApplicationContext());

            int dbMessageType  = toGroupType == Group.TYPE_GROUP ? com.jegarn.minions.entity.Message.TYPE_GROUPCHAT
                    : com.jegarn.minions.entity.Message.TYPE_CHATROOM;
            com.jegarn.minions.entity.Message dbMessage = new com.jegarn.minions.entity.Message(
                    message.from,
                    message.fromAvatar,
                    message.to,
                    toGroupId,
                    dbMessageType,
                    TextGroupChat.SUB_TYPE,
                    message.content,
                    0
            );
            com.jegarn.minions.entity.Message.saveNewMessage(dbMessage);
        }
    }

    private static final int MSG_RECV_CHAT_PACKET = 1;

    private final ChatHandler mHandler = new ChatHandler(this);

    private static class ChatHandler extends Handler {
        private final WeakReference<GroupChatActivity> mActivity;

        public ChatHandler(GroupChatActivity activity) {
            mActivity = new WeakReference<>(activity);
        }

        @Override
        public void handleMessage(android.os.Message msg) {
            GroupChatActivity activity = mActivity.get();
            if (activity != null) {
                switch (msg.what) {
                    case MSG_RECV_CHAT_PACKET:
                        Message message = (Message) msg.obj;
                        activity.mMessageAdapter.addMessage(message, true);
                        break;
                }
            }
        }
    }

    public final class MessageAdapter extends BaseAdapter {

        private Context context;
        private LinkedList<Message> messages;
        private LayoutInflater layoutInflater;

        public final class ItemView {
            public SimpleDraweeView avatar;
            public TextView message;
        }

        public MessageAdapter(Context context, LinkedList<Message> messages) {
            this.context = context;
            this.messages = messages;
            layoutInflater = LayoutInflater.from(context);
        }

        public void addMessage(Message message, boolean refresh) {
            addMessage(message, refresh, true);
        }

        public void addMessage(Message message, boolean refresh, boolean append) {
            if (messages == null) {
                messages = new LinkedList<>();
            }
            if (append) {
                messages.add(message);
            } else {
                messages.addFirst(message);
            }
            if (refresh) {
                notifyDataSetChanged();
            }
        }

        @Override
        public int getCount() {
            return messages != null ? messages.size() : 0;
        }

        @Override
        public Object getItem(int position) {
            return messages.get(position);
        }

        @Override
        public long getItemId(int position) {
            return 0;
        }

        @Override
        public View getView(int position, View convertView, ViewGroup parent) {
            ItemView itemView;
            Message item = messages.get(position);
            System.out.println("[GroupChatActivity] fromUserUid: " + fromUserUid + " position: " + position +
                    " from: " + item.from + " to: " + item.to + " type: " + item.type +
                    " content: " + item.content.substring(0, item.content.length() < 10 ? item.content.length() : 10));
            boolean isOutMessage = item.from.equals(fromUserUid);
            //if (convertView == null) {
            itemView = new ItemView();
            convertView = layoutInflater.inflate(isOutMessage ? R.layout.list_out_message_item : R.layout.list_in_message_item, null);
            itemView.avatar = (SimpleDraweeView) convertView.findViewById(R.id.image_user_avatar);
            itemView.message = (TextView) convertView.findViewById(R.id.text_user_message);
            convertView.setTag(itemView);
            //} else {
            //    itemView = (ItemView) convertView.getTag();
            //}
            itemView.avatar.setImageURI(WidgetUtil.getImageUri(item.fromAvatar));
            itemView.message.setText(item.content);
            return convertView;
        }
    }
}
