<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class AddSubscribeIntoUser extends AbstractMigration
{
    public function change()
    {

        $table = $this->table('member');
        $table->addColumn('subscribe', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 0]);
        $table->update();

    }
}
