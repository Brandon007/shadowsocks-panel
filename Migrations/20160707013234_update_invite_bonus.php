<?php

use Phinx\Migration\AbstractMigration;

class UpdateInviteBonus extends AbstractMigration
{
    public function change()
    {
        // 2016-07-07 add invite bonus.
        $option = [
            [
                'k'     =>  'inviteBonus',
                'v'     =>  '5'
            ],[
                'k'     =>  'inviteBonusContent',
                'v'     =>  'Dear {nickname}:<br/>恭喜你成功邀请 {beInvited} ,获得奖励流量 {inviteBonus}G以及延长使用时间 {inviteBonusDay}天<br/><br/>Yours, The {SITE_NAME} Team'
            ],[
                'k'     =>  'inviteBonusDay',
                'v'     =>  '7'
            ]               
        ];
        $this->execute("DELETE FROM `options` WHERE `k` LIKE 'inviteBonus%'");
        $this->insert('options', $option);
    }
}
