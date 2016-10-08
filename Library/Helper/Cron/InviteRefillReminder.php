<?php
/**
 * shadowsocks-panel
 * Add: 2016/03/27 03:50
 * Author: Sendya <18x@loacg.com>
 */

namespace Helper\Cron;

use Contactable\ICron;
use Helper\Logger;
use Helper\Mailer;
use Model\Mail;
use Model\User;
use Model\Invite as InviteModel;

/**
 * 发邮局提现补充邀请码 - InviteRefillReminder
 * 当邀请码小于20个时候,自动发送
 *
 * @package Helper\Cron
 */
class InviteRefillReminder implements ICron
{
    const STEP = 1800; // 30分钟执行一次

    public function run()
    {
        $inviteList = InviteModel::getInviteArray(-1);
        $leftNum = count($inviteList);
        if ($leftNum<20) {
            $mailer = Mailer::getInstance();
            $mailer->toQueue(true);
            Logger::getInstance()->info('发送邀请码不足20个通知');
            $mail = new Mail();
            $user = User::getUserByUserId(1);
            $mail->to = $user->email;
            $mail->subject = '[' . SITE_NAME . '] ' . "邀请码剩余量告急";
            $mailContent = "邀请码还剩{$leftNum}个,记得生成更多的邀请码哟 :)";
            $mailContent .= "<p style=\"padding: 1.5em 1em 0; color: #999; font-size: 12px;\">—— 本邮件由 ". SITE_NAME ." (<a href=\"".BASE_URL."\">".BASE_URL."</a>) 账户管控系统发送</p>";
            $mail->content = $mailContent;
            $mailer->send($mail);
        }
    }

    public function getStep()
    {
        return time() + self::STEP;
    }
}