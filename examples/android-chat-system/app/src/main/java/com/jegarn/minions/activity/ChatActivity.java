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

import com.facebook.drawee.view.SimpleDraweeView;
import com.jegarn.jegarn.listener.ChatManagerListener;
import com.jegarn.jegarn.manager.JegarnManager;
import com.jegarn.jegarn.packet.base.Chat;
import com.jegarn.jegarn.packet.content.TextChatContent;
import com.jegarn.jegarn.packet.text.TextChat;
import com.jegarn.minions.App;
import com.jegarn.minions.R;
import com.jegarn.minions.model.Message;
import com.jegarn.minions.utils.StringUtil;
import com.jegarn.minions.utils.WidgetUtil;

import java.lang.ref.WeakReference;
import java.util.LinkedList;
import java.util.List;

public class ChatActivity extends Activity implements View.OnClickListener {

    private TextView mUserNickTextView;
    private ListView mMessageListView;
    private MessageAdapter mMessageAdapter;
    private EditText mInputEditText;
    private ImageView mSendButton;
    private String fromUserUid, fromUserAvatar, toUserUid, toUserNick, toUserAvatar;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_chat);
        fromUserUid = App.user.uid;
        fromUserAvatar = App.user.avatar;
        mUserNickTextView = (TextView) findViewById(R.id.text_user_nick);
        Intent intent = getIntent();
        Bundle bundle = intent.getBundleExtra("userMap");
        toUserUid = bundle.getString("uid");
        toUserNick = bundle.getString("nick");
        toUserAvatar = bundle.getString("avatar");
        mUserNickTextView.setText(toUserNick);
        mMessageListView = (ListView) findViewById(R.id.list_message);
        this.initMessageAdapter();
        mMessageListView.setAdapter(mMessageAdapter);
        mInputEditText = (EditText) findViewById(R.id.text_send_input);
        mSendButton = (ImageView) findViewById(R.id.image_send_button);
        mSendButton.setOnClickListener(this);
        this.initPacketListener();
    }

    private void initMessageAdapter() {
        List<com.jegarn.minions.entity.Message> dbMessages = com.jegarn.minions.entity.Message.listAllByType(
                com.jegarn.minions.entity.Message.TYPE_CHAT, fromUserUid, toUserUid, 0
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
            TextChat packet = new TextChat(fromUserUid, toUserUid, TextChat.TYPE, new TextChatContent(text));
            if (JegarnManager.client.sendPacket(packet)) {
                Message message = new Message();
                message.from = fromUserUid;
                message.to = toUserUid;
                message.type = Message.TYPE_TEXT;
                message.content = text;
                mMessageAdapter.addMessage(message, true);
                mInputEditText.setText(null);

                com.jegarn.minions.entity.Message dbMessage = new com.jegarn.minions.entity.Message(
                        fromUserUid,
                        fromUserAvatar,
                        toUserUid,
                        0,
                        com.jegarn.minions.entity.Message.TYPE_CHAT,
                        TextChat.SUB_TYPE,
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
        JegarnManager.client.getChatManager().addListener(new ChatManagerListener() {
            @Override
            public boolean processPacket(Chat packet) {
                System.out.println("[ChatActivity] recv packet");
                if (toUserUid.equals(packet.getFrom())) {
                    if (packet instanceof TextChat) {
                        TextChat pkt = (TextChat) packet;
                        Message message = new Message();
                        message.from = packet.getFrom();
                        message.to = packet.getTo();
                        message.type = Message.TYPE_TEXT;
                        message.content = pkt.getContent().getText();
                        android.os.Message msg = mHandler.obtainMessage();
                        msg.what = MSG_RECV_CHAT_PACKET;
                        msg.obj = message;
                        msg.sendToTarget();

                        com.jegarn.minions.entity.Message dbMessage = new com.jegarn.minions.entity.Message(
                                pkt.getFrom(),
                                toUserAvatar,
                                pkt.getTo(),
                                0,
                                com.jegarn.minions.entity.Message.TYPE_CHAT,
                                pkt.getContent().getType(),
                                pkt.getContent().getText(),
                                0
                        );
                        com.jegarn.minions.entity.Message.saveNewMessage(dbMessage);
                    } else {
                        System.out.println("[ChatActivity] recv packet subType " + packet.getContent().getType() + " but need text");
                    }
                } else {
                    // deal chat message from others
                    System.out.println("[ChatActivity] recv packet from " + packet.getFrom() + " but need " + toUserUid);
                }
                return true;
            }
        });
    }

    private static final int MSG_RECV_CHAT_PACKET = 1;

    private final ChatHandler mHandler = new ChatHandler(this);

    private static class ChatHandler extends Handler {
        private final WeakReference<ChatActivity> mActivity;

        public ChatHandler(ChatActivity activity) {
            mActivity = new WeakReference<>(activity);
        }

        @Override
        public void handleMessage(android.os.Message msg) {
            ChatActivity activity = mActivity.get();
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
            System.out.println("[ChatActivity] fromUserUid: " + fromUserUid + " position: " + position +
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
            itemView.avatar.setImageURI(WidgetUtil.getImageUri(isOutMessage ? fromUserAvatar : toUserAvatar));
            itemView.message.setText(item.content);
            return convertView;
        }
    }
}
