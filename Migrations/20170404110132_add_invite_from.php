<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class addInviteFrom extends AbstractMigration
{
    public function change()
    {

        $table = $this->table('member');
        $table->addColumn('inviteFrom','string', ['limit' => 64,'null'=>true]);
        $table->update();

    }
}
