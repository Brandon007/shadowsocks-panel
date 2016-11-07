<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateTableWxOrder extends AbstractMigration
{
    public function change()
    {
        $this->table("wxorder")
            ->addColumn('out_trade_no', 'string', ['limit' => 28,'null' => false])
            ->addColumn('openid', 'integer', ['limit' => 32,'null' => false])
            ->addColumn('money', 'integer', ['limit' => 5, 'comment'=> '单位:分'])
            ->addColumn('pay_time', 'integer', ['null' => false])
            ->addColumn('type', 'integer', ['limit'=> MysqlAdapter::INT_TINY, 'default'=> 0, 'comment'=> '类型 0-周卡 1-月卡 2-季卡 3-年卡'])
            ->addColumn('status', 'integer', ['limit'=> MysqlAdapter::INT_TINY, 'default'=> 0, 'comment'=> '状态 0-未支付 1-已支付','null' => false])
            ->addColumn('remark', 'string', ['limit' => 255])
            ->create();
    }
}
