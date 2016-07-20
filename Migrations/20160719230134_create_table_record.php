<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateTableRecord extends AbstractMigration
{
    public function change()
    {
        $this->table("record", array('comment' => '充值记录', 'primary_key' => ['id']))
            ->addColumn('id', 'integer', ['limit' => 10])
            ->addColumn('uid', 'integer', ['limit' => 10, 'default' => -1])
            ->addColumn('nickname', 'string', ['limit' => 64])
            ->addColumn('card', 'string', ['limit' => 60])
            ->addColumn('active_time', 'integer', ['null' => false])
            ->addColumn('type', 'string', ['limit'=> MysqlAdapter::INT_TINY, 'default'=> 0, 'comment'=> '类型 0-套餐卡 1-流量卡 2-测试卡'])
            ->addIndex(['type'], ['unique' => true])
            ->create();
    }
}
