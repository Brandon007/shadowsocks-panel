<?php
/**
 * KK-Framework
 * Author: kookxiang <r18@ikk.me>
 */

namespace Core;

class DefaultRouter
{
    protected $foundController = false;
    protected $requestController = "";
    protected $requestMethod = "";
    protected $phpPath = "";
    public function handleRequest()
    {
        $requestPath = Request::getRequestPath();
        $requestPath = ltrim($requestPath, '/');
        if (!$requestPath) {
            $requestPath = 'Index';
        }
        Filter::preRoute($requestPath);
        $this->findController($requestPath);
        if (!$this->foundController) {
            throw new Error('The request URL is not exists' . 'requestPath->{' . $requestPath .'}' .'Controller->{' . $this->requestController .'}' . 'requestMethod->{' . $this->requestMethod.'}' .'phpPath->' . $this->phpPath, 404);
        }
    }

    protected function findController($requestPath, $subDir = '')
    {
        list($controller, $method) = explode('/', $requestPath, 2);
        $controller = ucfirst($controller);
        if (is_dir(LIBRARY_PATH . "Controller/{$subDir}{$controller}")) {
            if (!$method) {
                $method = 'Index';
            }
            $this->findController($method, $subDir . $controller . '/');
        } elseif (file_exists(LIBRARY_PATH . "Controller/{$subDir}{$controller}.php")) {
            if (!$method) {
                $method = 'index';
            } else {
                $method = lcfirst($method);
                $this->requestMethod = $method;
            }
            $className = str_replace('/', '\\', "Controller/{$subDir}{$controller}");
            $controller = new $className();
            if (method_exists($controller, $method)) {
                Filter::afterRoute($className, $method);
                $context = $controller->$method();
                if ($context) {
                    Template::setContext($context);
                }
                Filter::preRender();
                Template::render();
                Filter::afterRender();
                $this->foundController = true;
                $this->requestController = $controller;
                $this->requestMethod = $method;
            }
        }else{$this->phpPath = LIBRARY_PATH . "Controller/{$subDir}{$controller}.php"}
    }
}
