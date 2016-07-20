<?php
/**
 * Project: shadowsocks-panel
 * Author: Brandon <wukongss.com>
 * Time: 2016/7/19 20:37
 */


namespace Model;

use Core\Database as DB;
use Core\Model;

class Record extends Model
{

    public $id; // 主键
    public $uid; // 充值用户
    public $nickname; // 充值用户昵称
    public $card; // 卡号 不重复
    public $active_time;
    public $type; // 类型 0-套餐卡 1-流量卡 2-测试卡
    /**
     * 1. 为套餐卡时，此字段为套餐类型（单位A/B/C/D/VIP）
     * 2. 为流量卡时，此字段为流量大小（单位GB）
     * 3. 为测试卡时，此字段为测试时长（单位天）
     */
    public $info;
    public $money; // 金额

    public static function queryAll()
    {
        $sql = 'SELECT * FROM record ';
        $sql .= ' ORDER BY active_time desc';
        $st = DB::sql($sql);
        $st->execute();
        return $st->fetchAll(DB::FETCH_CLASS, __CLASS__);
    }

}