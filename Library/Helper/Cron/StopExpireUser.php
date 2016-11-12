<?php
/**
 * shadowsocks-panel
 * Add: 2016/03/27 03:50
 * Author: Sendya <18x@loacg.com>
 */

namespace Helper\Cron;
include_once  '/home/wwwroot/shadowsocks-panel/Package/autoload.php'; // 引入 composer 入口文件
use Contactable\ICron;

use Helper\Logger;
use Helper\Mailer;
use Helper\Option;
use Helper\Utils;
use Model\Mail;
use Model\User;

/**
 * 计划任务 - StopExpireUser
 * 自动停止 超流量/使用时间到期 用户
 *
 * @package Helper\Cron
 */
class StopExpireUser implements ICron
{
    const STEP = 300; // 5分钟执行一次
    private $options = [
        'debug'  => true,
        'app_id' => 'wx15bfb1691cf3c4b8',
        'secret' => '6257d7590838302979be2acc9c653c65',
        'token'  => 'wukongss',
        // 'aes_key' => null, // 可选
        'log' => [
            'level' => 'debug',
            'file'  => '/home/wwwlogs/wechat.log' // XXX: 绝对路径！！！！
        ]
        //...
    ];

    public function run()
    {
        $users = User::getUserArrayByExpire();
        $wechatUsers = User::getWechatUserArrayByExpire();
        $notificationMail = Option::get('mail_stop_expire_notification');
        $mailContentTemplate = Option::get('custom_mail_stop_expire_content');
        $app = new Application($this->options);
        if (!$notificationMail) {
            Option::set('mail_stop_expire_notification', 0); // 设置邮件提醒的系统参数
        }

        // $mailer = Mailer::getInstance();
        // $mailer->toQueue(true);

        // foreach ($users as $user) {
        //     $user->stop();
        //     Logger::getInstance()->info('user ['.$user->email.'] 未续费或流量超用已被暂停服务');
        //     if ($notificationMail) {
        //         $mail = new Mail();
        //         $mail->to = $user->email;
        //         $mail->subject = '[' . SITE_NAME . '] ' . "用户 {$user->nickname}，您的账户由于到期或流量超用已被暂停服务";
        //         $params = [
        //             'nickname' => $user->nickname,
        //             'email' => $user->email,
        //             'useTraffic' => Utils::flowAutoShow($user->flow_up + $user->flow_down),
        //             'transfer' => Utils::flowAutoShow($user->transfer),
        //             'expireTime' => date('Y-m-d H:i:s', $user->expireTime)
        //         ];
        //         $mailContent = Utils::placeholderReplace($mailContentTemplate, $params);
        //         $mailContent .= "<p style=\"padding: 1.5em 1em 0; color: #999; font-size: 12px;\">—— 本邮件由 ". SITE_NAME ." (<a href=\"".BASE_URL."\">".BASE_URL."</a>) 账户管控系统发送</p>";
        //         $mail->content = $mailContent;
        //         $mailer->send($mail);
        //     }
        // }
        // 避免频繁更新 Option 单例对象，循环结束后再执行
        if ($notificationMail) {
            Option::set('mail_queue', 1);
        }
        // 2016-04-26 15:00 - by @Sendya Fixed issue #62
        // User::enableUsersByExpireTime(); // 启用已续费且流量未超过的用户


        foreach ($wechatUsers as $wechatUser) {
            sendTemplateMsg($app,$wechatUser->openid);
            $wechatUser->stop();
        }
    }

    public function getStep()
    {
        return time() + self::STEP;
    }

    public function sendTemplateMsg($app,$openid){
        $userId = $openid;
        $templateId = 'OtmnvVvHqBH9eUCC5-KkXV-QPVNDWkgpArvOlUbco04';
        $url = 'http://wx.wukongss.com/bind.php';
        $data = array(
            "first"    => array("加速服务到期！", '#555555'),
            "keyword1" => array("高级套餐", "#336699"),
            "keyword2" => array("2014-10-10 ", "#FF0000"),
            "remark"   => array("欢迎再次购买！", "#5599FF"),
        );
        $result = $app->notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
        var_dump($result);        
    }
}