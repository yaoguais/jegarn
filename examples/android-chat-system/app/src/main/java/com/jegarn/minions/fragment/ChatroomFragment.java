package com.jegarn.minions.fragment;


import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.BaseAdapter;
import android.widget.ListView;
import android.widget.TextView;

import com.facebook.drawee.backends.pipeline.Fresco;
import com.facebook.drawee.view.SimpleDraweeView;
import com.google.gson.JsonSyntaxException;
import com.jegarn.minions.App;
import com.jegarn.minions.R;
import com.jegarn.minions.activity.GroupChatActivity;
import com.jegarn.minions.model.Group;
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
public class ChatroomFragment extends Fragment implements AdapterView.OnItemClickListener{

    private View mView;
    private ListView mGroupListView;
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
        mView = inflater.inflate(R.layout.fragment_groupchat, container, false);
        mGroupListView = (ListView) mView.findViewById(R.id.list_group);
        mGroupListView.setOnItemClickListener(this);
        OkHttpUtils.get().addParams("uid", App.user.uid).addParams("token", App.user.token)
                .addParams("type", ""+ Group.TYPE_CHATROOM).addParams("status", ""+Group.STATUS_AGREE)
                .url(App.getUrl(App.API_LIST_ALL_GROUP))
                .build().execute(new ListGroupCallback());
        return mView;
    }


    @Override
    public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
        if(mAdapter != null){
            Map<String,Object> user = (Map)mAdapter.getItem(position);
            Bundle bundle =new Bundle();
            bundle.putInt("group_id", Integer.parseInt((String)user.get("group_id")));
            bundle.putString("name", (String)user.get("name"));
            bundle.putInt("type", Group.TYPE_CHATROOM);
            Intent intent = new Intent(mActivity, GroupChatActivity.class);
            intent.putExtra("groupMap", bundle);
            startActivity(intent);
        }

    }

    public final class ListGroupCallback extends StringCallback {
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
                        List<Map<String, Object>> groupItems = getGroupItemsFromResponse((ArrayList) resp.response);
                        mAdapter = new GroupListAdapter(mActivity, groupItems);
                        mGroupListView.setAdapter(mAdapter);
                    }
                } else {
                    WidgetUtil.toast(mActivity.getApplicationContext(), Response.getMessage(resp.code));
                }
            } catch (JsonSyntaxException e) {
                WidgetUtil.toast(mActivity.getApplicationContext(), Response.getMessage(str));
            }
        }
    }

    private List<Map<String, Object>> getGroupItemsFromResponse(ArrayList resp) {
        if (resp == null || resp.size() == 0) {
            return null;
        }
        List<Map<String, Object>> items = new ArrayList<>();
        for(Object group : resp){
            Map groupMap = (Map)group;
            Map<String, Object> item = new HashMap<>();
            item.put("group_id",groupMap.get("group_id"));
            item.put("name",groupMap.get("name"));
            item.put("icon",groupMap.get("icon"));
            item.put("description",groupMap.get("description"));
            items.add(item);
        }
        return items;
    }

    public final class GroupListAdapter extends BaseAdapter {

        private Context context;
        private List<Map<String, Object>> items;
        private LayoutInflater layoutInflater;

        public final class ItemView {
            public SimpleDraweeView icon;
            public TextView name;
            public TextView description;
        }

        public GroupListAdapter(Context context, List<Map<String, Object>> items) {
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
                convertView = layoutInflater.inflate(R.layout.list_group_item, null);
                itemView.icon = (SimpleDraweeView) convertView.findViewById(R.id.image_icon);
                itemView.name = (TextView) convertView.findViewById(R.id.text_name);
                itemView.description = (TextView) convertView.findViewById(R.id.text_description);
                convertView.setTag(itemView);
            } else {
                itemView = (ItemView) convertView.getTag();
            }
            Map<String, Object> item = items.get(position);
            itemView.icon.setImageURI(WidgetUtil.getImageUri((String)item.get("icon")));
            itemView.name.setText((String) item.get("name"));
            itemView.description.setText((String) item.get("description"));
            return convertView;
        }
    }

}
