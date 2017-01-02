<?php
/**
 * Created by PhpStorm.
 * User: mushan
 * Date: 2016/11/26
 * Time: 12:50
 */

namespace Mushan\BaiduTongji;

use Mushan\BaiduTongji\Login;

class BaiduTongji
{
    const API_URL='https://api.baidu.com/json/tongji/v1/ReportService';

    private $config;

    private $login;

    public function __construct($config=array())
    {
        $this->config=$config;
        $this->login=new Login($config);
        $this->login->preLogin();
    }

    public function __get($name)
    {
        return isset($this->config[$name])?$this->config[$name]:false;
    }

    public function __set($name,$value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name]=$value;
        }
    }
}