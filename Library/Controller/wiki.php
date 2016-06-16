<?php
/**
 * SS-Panel
 * A simple Shadowsocks management system
 * Author: Brandon
 */
namespace Controller;

use Core\Template;

/**
 * Class Wiki
 * @package Controller
 */
class Wiki
{
    public function index()
    {
        Template::setView('wiki/index');
    }

    public function pc()
    {
        Template::setView('wiki/pc');
    }

    public function android()
    {
        Template::setView('wiki/android');
    }
    public function ipad()
    {
        Template::setView('wiki/ipad');
    }

    public function yueYu()
    {
        Template::setView('wiki/yueYu');
    }

}
?>