<?php
namespace Controller;

use Core\Template;

class Wiki {
    public function index() {
        // TODO: Do something here
        Template::setView('home/index');
    }
    public function pc() {
        Template::setView('wiki/pc');
    }

    public function android() {
        Template::setView('wiki/android');
    }

    public function ipad() {
        Template::setView('wiki/ipad');
    }

    public function yueYu() {
        Template::setView('wiki/yueYu');
    }    
}
