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
    const API_URL='https://api.baidu.com/json/tongji/v1/ProductService/api';

    private $config;

    private $login;

    private $header,$post_header;

    public function __construct($config=array())
    {
        $this->config=$config;
        $this->login=new Login($config);
        $this->login->preLogin();
        $this->login->doLogin();

        $this->header=[
            'UUID:'.$this->uuid,
            'USERID:'.$this->login->ucid,
            'Content-Type:data/json;charset=UTF-8'
        ];

        $this->post_header=[
            'username'=>$this->username,
            'password'=>$this->login->st,
            'token'=>$this->token,
            'account_type'=>$this->account_type
        ];
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

    public function getSiteList()
    {
        $result=$this->request([
            'serviceName'=>'profile',
            'methodName'=>'getsites',
        ]);
        if(empty($result['list'])){
            throw new \Exception('没有站点');
        }

        $list=$result['list'];

        return $list;

    }

    public function getData()
    {
        $result=$this->request([
            'serviceName'=>'report',
            'methodName'=>'query',
            'QueryParameterType'=>[
                'reportid'=>1,
                'siteid' => '9890037',
                'start_time' => '20161230',
                'end_time' => '20161231',
                'dimensions'=>'pageid',
                'metrics'=>['pageviews','visitors','ips','entrances','outwards','exits','stayTime','exitRate'],
                'filter'=>[],
                'sort'=>[],
                'start_index'=>0,
                'max_results'=>10000
            ]
        ]);

        return $result;

    }

    private function request($post_data)
    {
        $post_data=[
            'header'=>$this->post_header,
            'body'=>$post_data
        ];

        $result=curl_post(self::API_URL,json_encode($post_data),$this->header);
        echo $result;exit;
        $result=json_decode($result,true);

        if($result['header']['status']==3){
            $failure=$result['header']['failures'][0];
            $message='level:'.$result['header']['desc'].';code:'.$failure['code'].';message:'.$failure['message'];
            throw new \Exception($message);
        }

        return $result['body'];
    }
}