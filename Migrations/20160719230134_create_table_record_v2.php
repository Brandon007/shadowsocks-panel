<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateTableRecordV2 extends AbstractMigration
{
    public function change()
    {
        $this->table("record")
            ->addColumn('uid', 'integer', ['limit' => 10, 'default' => -1])
            ->addColumn('nickname', 'string', ['limit' => 128])
            ->addColumn('card', 'string', ['limit' => 60])
            ->addColumn('active_time', 'integer', ['null' => false])
            ->addColumn('type', 'integer', ['limit'=> MysqlAdapter::INT_TINY, 'default'=> 0, 'comment'=> '类型 0-套餐卡 1-流量卡 2-测试卡'])
            ->addColumn('info', 'string', ['limit' => 60])
            ->addColumn('money', 'integer', ['limit' => 3])
            ->create();
    }
}
