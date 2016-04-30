package com.jegarn.minions.fragment;


import android.app.Activity;
import android.app.Fragment;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.TextView;

import com.facebook.drawee.backends.pipeline.Fresco;
import com.facebook.drawee.view.SimpleDraweeView;
import com.google.gson.JsonSyntaxException;
import com.jegarn.minions.App;
import com.jegarn.minions.R;
import com.jegarn.minions.activity.ChatActivity;
import com.jegarn.minions.response.Response;
import com.jegarn.minions.utils.JsonUtil;
import com.jegarn.minions.utils.WidgetUtil;
import com.zhy.http.okhttp.OkHttpUtils;
import com.zhy.http.okhttp.callback.StringCallback;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import okhttp3.Call;

/**
 * A simple {@link Fragment} subclass.
 */
public class ContactFragment extends Fragment implements AdapterView.OnItemClickListener{

    private View mView;
    private ListView mFriendListView;
    private Activity mActivity;
    private BaseAdapter mAdapter;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        mActivity = getActivity();
        mView = inflater.inflate(R.layout.fragment_contact, container, false);
        mFriendListView = (ListView) mView.findViewById(R.id.list_friend);
        mFriendListView.setOnItemClickListener(this);
        OkHttpUtils.get().addParams("uid", App.user.uid).addParams("token", App.user.token).url(App.getUrl(App.API_LIST_ALL_ROSTER))
                .build().execute(new ListRosterCallback());
        return mView;
    }


    @Override
    public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
        if(mAdapter != null){
            Map<String,Object> user = (Map)mAdapter.getItem(position);
            Bundle bundle =new Bundle();
            bundle.putString("uid", (String)user.get("uid"));
            bundle.putString("nick", (String)user.get("nick"));
            bundle.putString("avatar",(String)user.get("avatar"));
            Intent intent = new Intent(mActivity, ChatActivity.class);
            intent.putExtra("userMap", bundle);
            startActivity(intent);
        }

    }

    public final class ListRosterCallback extends StringCallback {
        @Override
        public void onError(Call call, Exception e) {
            WidgetUtil.toast(mActivity.getApplicationContext(), Response.getMessage(Response.FAIL_NETWORK));
            e.printStackTrace();
        }

        @Override
        public void onResponse(String str) {
            try {
                Response resp = JsonUtil.fromJson(str, Response.class);
                if (Response.isSuccess(resp.code)) {
                    if (resp.response == null) {
                        WidgetUtil.toast(mActivity.getApplicationContext(), Response.getMessage(Response.FAIL_SERVER_RESPONSE));
                    } else {
                        List<Map<String, Object>> friendItems = getFriendItemsFromResponse((ArrayList) resp.response);
                        mAdapter = new FriendListAdapter(mActivity, friendItems);
                        mFriendListView.setAdapter(mAdapter);
                    }
                } else {
                    WidgetUtil.toast(mActivity.getApplicationContext(), Response.getMessage(resp.code));
                }
            } catch (JsonSyntaxException e) {
                WidgetUtil.toast(mActivity.getApplicationContext(), Response.getMessage(str));
            }
        }
    }

    private List<Map<String, Object>> getFriendItemsFromResponse(ArrayList resp) {
        if (resp == null || resp.size() == 0) {
            return null;
        }
        List<Map<String, Object>> items = new ArrayList<>();
        for(Object groups : resp){
            ArrayList rosters = (ArrayList)((Map)groups).get("rosters");
            if(rosters != null && rosters.size() != 0){
                for(Object roster : rosters){
                    Map user = (Map)((Map)roster).get("user");
                    Map<String, Object> item = new HashMap<>();
                    item.put("uid",user.get("uid"));
                    item.put("avatar", user.get("avatar"));
                    item.put("nick", user.get("nick"));
                    item.put("motto",user.get("motto"));
                    items.add(item);
                }
            }
        }
        return items;
    }

    public final class FriendListAdapter extends BaseAdapter {

        private Context context;
        private List<Map<String, Object>> items;
        private LayoutInflater layoutInflater;

        public final class ItemView {
            public SimpleDraweeView avatar;
            public TextView nick;
            public TextView motto;
        }

        public FriendListAdapter(Context context, List<Map<String, Object>> items) {
            this.context = context;
            this.items = items;
            layoutInflater = LayoutInflater.from(context);
        }


        @Override
        public int getCount() {
            return items.size();
        }

        @Override
        public Object getItem(int position) {
            return items.get(position);
        }

        @Override
        public long getItemId(int position) {
            return 0;
        }

        @Override
        public View getView(int position, View convertView, ViewGroup parent) {
            ItemView itemView;
            if (convertView == null) {
                itemView = new ItemView();
                convertView = layoutInflater.inflate(R.layout.list_friend_item, null);
                itemView.avatar = (SimpleDraweeView) convertView.findViewById(R.id.image_avatar);
                itemView.nick = (TextView) convertView.findViewById(R.id.text_nick);
                itemView.motto = (TextView) convertView.findViewById(R.id.text_motto);
                convertView.setTag(itemView);
            } else {
                itemView = (ItemView) convertView.getTag();
            }
            Map<String, Object> item = items.get(position);
            itemView.avatar.setImageURI(WidgetUtil.getImageUri((String)item.get("avatar")));
            itemView.nick.setText((String) item.get("nick"));
            itemView.motto.setText((String) item.get("motto"));
            return convertView;
        }
    }
}
