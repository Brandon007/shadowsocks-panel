<?php
/**
 * Project: shadowsocks-panel
 * Author: Brandon <wukongss.com>
 * Time: 2016/11/7 14:26
 */


namespace Model;

use Core\Database as DB;
use Core\Model;

class WxOrder extends Model
{

    public $id; // 主键
    public $out_trade_no; //自定义订单号
    public $openid; // 充值用户openid
    public $money; // 金额(单位:分)
    public $pay_time;
    public $type; // 类型 0-周卡 1-月卡 2-季卡 3-年卡
    public $status;//0-未支付 1-已支付
    public $remark;

    public function queryAll()
    {
        $sql = 'SELECT * FROM wxorder ';
        $sql .= ' ORDER BY pay_time desc';
        $st = DB::sql($sql);
        $st->execute();
        return $st->fetchAll(DB::FETCH_CLASS, __CLASS__);
    }

    public function queryByTradeNum($num)
    {
        $st = DB::sql("SELECT * FROM wxorder WHERE out_trade_no=:num");
        $st->bindValue(":num", $num, DB::PARAM_STR);
        $st->execute();
        return $st->fetchObject(__CLASS__);
    }
}