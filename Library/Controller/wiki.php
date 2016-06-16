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

    public function wiki-pc()
    {
        Template::setView('wiki/wiki-pc');
    }

    public function wiki-android()
    {
        Template::setView('wiki/wiki-android');
    }
    public function wiki-ipad()
    {
        Template::setView('wiki/wiki-ipad');
    }

    public function wiki-yueYu()
    {
        Template::setView('wiki/wiki-yueYu');
    }

}