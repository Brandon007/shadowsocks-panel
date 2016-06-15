<?php

use Phinx\Migration\AbstractMigration;

class UpdateVersion120 extends AbstractMigration
{
    public function change()
    {
        // 2016-04-26 add custom mail content.
        $option = [
            [
                'k'     =>  'buyFixedTransfer',
                'v'     =>  'www.baidu.com'
            ], [
                'k'     =>  'buySeniorMember',
                'v'     =>  'www.baidu.com'
            ],[
                'k'     =>  'buySuperMember',
                'v'     =>  'www.baidu.com'
            ]
        ];
        $this->execute("DELETE FROM `options` WHERE `k` LIKE 'buy%'");
        $this->insert('options', $option);
    }
}
