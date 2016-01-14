<?php

use jegarn\packet\FriendAgreeNotification;
use jegarn\packet\FriendRequestNotification;
use minions\manager\RosterManager;
use minions\model\RosterGroup;
use minions\model\Roster;
use minions\util\ConvertUtil;

class RosterTest extends AppTestBase {

    /**
     * 1. create two user
     * 2. a user ask for b to make friend
     * 3. b user agree this ask
     * 4. get friends for a, check whether there is b;get friends for b, check whether there is a
     * 5. a create group named 'a new group', then move b to this group, and update group name, then delete this group
     * 6. delete two user and there relations
     */
    public function testRoster(){
        // 1
        $userTest = new UserTest();
        $userOne = $userTest->createUser();
        $userTwo = $userTest->createUser();
        // 2
        $rosterA = $this->requestForFriend($userOne, $userTwo);
        //3
        $this->receiveRequest($userTwo, $userOne);
        // 4
        $resp = $this->request('/api/roster/list',[
            'uid' => $userOne->id,
            'token' => $userOne->token,
            'status' => Roster::STATUS_AGREE
        ]);
        $this->assertResponseNotEmptyList($resp);
        $rosters = $this->getResponseBody($resp);
        $found = false;
        foreach($rosters as $r){
            if($r['target_id'] == $userTwo->id){
                $found = true;
                break;
            }
        }
        self::assertTrue($found);
        // 5
        $resp = $this->request('/api/roster/create_group',[
            'uid' => $userOne->id,
            'token' => $userOne->token,
            'name' => 'a new group',
            'rank' => 1
        ],true);
        $this->assertRequestSuccess($resp);
        $groupRoster = ConvertUtil::arrayToObject($this->getResponseBody($resp), new RosterGroup(), ['group_id' => 'id', 'name', 'uid', 'rank']);
        RosterManager::getInstance()->getRoster($rosterA);
        $resp = $this->request('/api/roster/update',[
            'uid' => $userOne->id,
            'token' => $userOne->token,
            'target_id' => $userTwo->id,
            'status' => $rosterA->status,
            'remark' => 'updated remark',
            'group_id' => $groupRoster->id,
            'rank' => 0
        ], true);
        $this->assertRequestSuccess($resp);
        $resp = $this->request('/api/roster/update_group',[
            'uid' => $userOne->id,
            'token' => $userOne->token,
            'group_id' => $groupRoster->id,
            'name' => 'a updated name',
            'rank' => 2
        ], true);
        $this->assertRequestSuccess($resp);
        $this->assertRequestSuccess($this->request('/api/roster/delete_group',[
            'uid' => $userOne->id,
            'token' => $userOne->token,
            'group_id' => $groupRoster->id
        ], true));
        // 6
        $userTest->deleteUser($userOne);
        $userTest->deleteUser($userTwo);
        RosterManager::getInstance()->deleteRoster($rosterA);
    }

    public function requestForFriend($userOne, $userTwo){

         $resp = $this->request('/api/roster/create',[
             'uid' => $userOne->id,
             'token' => $userOne->token,
             'target_id' => $userTwo->id,
             'remark' => 'lucy',
             'group_id' => 0,
             'rank' => 0
         ],true);
        $this->assertRequestSuccess($resp);
        $roster = $this->getResponseBody($resp);
        // check cache, when user request, target user will get a offline notify message, check and remove it
        $this->assertNotificationPacket($userTwo->id, new FriendRequestNotification());
        return ConvertUtil::arrayToObject($roster, new Roster(), ['roster_id' => 'id', 'target_id' =>'targetId', 'status']);
    }

    public function receiveRequest($userTwo, $userOne){

        $resp = $this->request('/api/roster/update',[
            'uid' => $userTwo->id,
            'token' => $userTwo->token,
            'target_id' => $userOne->id,
            'status' => Roster::STATUS_AGREE,
            'remark' => 'jack',
            'group_id' => 0,
            'rank' => 1
        ], true);
        $this->assertRequestSuccess($resp);
        $roster = $this->getResponseBody($resp);
        // check cache, when user agree the request, both user will receive a agree message
        $this->assertNotificationPacket($userTwo->id, new FriendAgreeNotification());
        $this->assertNotificationPacket($userOne->id, new FriendAgreeNotification());

        return ConvertUtil::arrayToObject($roster, new Roster(), ['roster_id' => 'id', 'target_id' => 'targetId', 'status']);
    }
}