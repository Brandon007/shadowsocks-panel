<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class AddCouponIntoUser extends AbstractMigration
{
    public function change()
    {

        $table = $this->table('member');
        $table->addColumn('coupon', 'string', ['after'=> 'invite', 'limit'=>32, 'null'=>true]);
        $table->update();

    }
}
