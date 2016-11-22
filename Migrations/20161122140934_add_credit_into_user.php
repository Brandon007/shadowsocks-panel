<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class AddCreditIntoUser extends AbstractMigration
{
    public function change()
    {

        $table = $this->table('member');
        $table->addColumn('credit', 'integer', ['after'=> 'coupon', 'limit'=>5, 'default' => 0]);
        $table->update();

    }
}
