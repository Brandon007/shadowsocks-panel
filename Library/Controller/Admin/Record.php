<?php
/**
 * Created by PhpStorm.
 * User: Brandon
 * Date: 2016/3/27
 * Time: 12:02
 */

namespace Controller\Admin;

use Core\Template;
use Model\Record;

/**
 * Class Record
 * @Admin
 * @Authorization
 * @package Controller\Admin
 */
class Record
{

    public function index()
    {
        $data['recordList'] = Record::queryAll();
        Template::setContext($data);
        Template::setView('admin/record');
    }

    /**
     * 导出记录
     */
    public function export()
    {
        $records = Record::queryAll();
        $file_name = '充值记录列表_' . time() . '.csv';
        $data = 'uid,邀请码,邀请码等级,金额,充值时间,充值人'. "\n";
        foreach ($records as $record) {
            $data .= $record->uid . ',' . $record->card . ',' . Utils::planAutoShow($record->info) . ',' . $record->money . ',' . date('Y-m-d H:i:s', $record->active_time) . ',' . $record->nickname . "\n";
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