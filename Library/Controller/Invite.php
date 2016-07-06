<?php
/**
 * SS-Panel
 * A simple Shadowsocks management system
 * Author: Sendya <18x@loacg.com>
 */

namespace Controller;

use \Core\Template;

use Helper\Utils;
use Model\User;
use \Model\Invite as InviteModel;


class Invite
{

    public function index()
    {
        $inviteList = InviteModel::getInviteArray(-1);
        Template::setView('home/invite');
        Template::putContext('inviteList', $inviteList);
    }

    /**
     * 生成邀请码，必要权限检查
     *
     * @JSON
     * @Authorization
     */
    public function create()
    {
        $user = User::getUserByUserId(User::getCurrent()->uid);
        $unUsedInvites = InviteModel::getInvitesByUid($user->uid,0);
        $unUsedCount = count($unUsedInvites);
        $result = array('error' => 1, 'message' => '创建邀请码失败，你还有{$unUsedCount}个邀请码没用完,请用完再申请!');

        if($unUsedCount>=5){
            return $result;
        }

        // if ($user->invite_num > 0) {
        //     $invite = InviteModel::addInvite($user->uid, 'A', false);
        //     $result = array(
        //         'error' => 0,
        //         'message' => '创建邀请码成功，刷新后可见',
        //         'invite_num' => $user->invite_num - 1,
        //         'invite' => $invite
        //     );
        //     $user->invite_num = $user->invite_num - 1;
        //     $user->save();
        // }
        $invite = InviteModel::addInvite($user->uid, 'A', false);
        $result = array(
            'error' => 0,
            'message' => '创建邀请码成功，刷新后可见',
            'invite' => $invite
        );        
        return $result;
    }

    /**
     * 购买邀请码，必要权限检查
     *
     * @JSON
     * @Authorization
     * @return array
     */
    public function buy()
    {
        $user = User::getUserByUserId(User::getCurrent()->uid);
        $result = array('error' => 1, 'message' => '购买失败，至少需要20GB流量才能购买邀请码。');
        $transfer = Utils::GB * 10;
        if ($user->transfer > ($transfer * 2)) {
            $user->transfer = $user->transfer - $transfer;
            $user->invite_num = $user->invite_num + 1;
            $user->save();
            $result = array('error' => 0, 'message' => '购买成功，扣除手续费10GB流量', 'invite_num' => $user->invite_num);
        }
        return $result;
    }

}
