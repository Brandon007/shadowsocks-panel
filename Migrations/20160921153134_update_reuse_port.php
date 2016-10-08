<?php

use Phinx\Migration\AbstractMigration;

class UpdateReusePort extends AbstractMigration
{
    public function change()
    {
        // 2016-09-21 add reuseMonth content.
        $option = [
            [
                'k'     =>  'reuseMonth',
                'v'     =>  '1'
            ]
        ];
        $this->execute("DELETE FROM `options` WHERE `k` = 'reuseMonth'");
        $this->insert('options', $option);
    }
}
