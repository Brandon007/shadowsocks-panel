<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class UpdateWxOpenid extends AbstractMigration
{
    public function change()
    {

        $table = $this->table('member');
        $table->addColumn('openid', 'string', ['after'=> 'uid', 'limit'=>32, 'null'=>true]);
        $table->update();

    }
}
