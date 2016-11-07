<?php
/**
 * Created by PhpStorm.
 * User: Brandon
 * Date: 2016/3/27
 * Time: 12:02
 */

namespace Controller\Admin;

use Core\Template;
use Model\WxOrder;

/**
 * Class WxOrder
 * @Admin
 * @Authorization
 * @package Controller\Admin
 */
class Wxorder
{

    public function index()
    {
        $data['orderList'] = WxOrder::queryAll();
        Template::setContext($data);
        Template::setView('admin/wxorder');
    }

    /**
     * 导出记录
     */
    public function export()
    {
        $orders = WxOrder::queryAll();
        $file_name = '微信充值记录列表_' . time() . '.csv';
        $data = 'openid,金额,套餐类型,支付时间,备注'. "\n";
        foreach ($orders as $order) {
            $data .= $order->openid . ',' . $order->money . ',' . $order->type==0?'周卡':$order->type==1?'月卡':$order->type==2?'季卡':'年卡' . ',' . date('Y-m-d H:i:s', $order->pay_time) . ',' . $order->remark . "\n";
        }
        header("Content-type:text/csv;charset=utf-8");
        header("Content-Disposition:attachment;filename=".$file_name);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $data = iconv('utf-8','gb2312',$data);
        echo $data;
        exit();
    }    

}