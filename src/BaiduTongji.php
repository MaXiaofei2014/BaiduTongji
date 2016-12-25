<?php
/**
 * Created by PhpStorm.
 * User: mushan
 * Date: 2016/11/26
 * Time: 12:50
 */

namespace Mushan\BaiduTongji;

use App\Exceptions\Handler;
use Mockery\Exception;

class BaiduTongji
{
    const LOGIN_URL='https://api.baidu.com/sem/common/HolmesLoginService';

    const API_URL='https://api.baidu.com/json/tongji/v1/ReportService';

    const PUBLIC_KEY='-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDHn/hfvTLRXViBXTmBhNYEIJeG
GGDkmrYBxCRelriLEYEcrwWrzp0au9nEISpjMlXeEW4+T82bCM22+JUXZpIga5qd
BrPkjU08Ktf5n7Nsd7n9ZeI0YoAKCub3ulVExcxGeS3RVxFai9ozERlavpoTOdUz
EH6YWHP4reFfpMpLzwIDAQAB
-----END PUBLIC KEY-----';

    private $config;

    private $public_key;

    private $userid;

    public function __construct()
    {
        $this->config=config('baidu_tongji');
        $this->public_key=openssl_pkey_get_public(self::PUBLIC_KEY);
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

    public function preLogin()
    {
        $header=$this->header();
        $post_data=[
            'username'=>$this->username,
            'token'=>$this->token,
            'functionName'=>'preLogin',
            'uuid'=>$this->uuid,
            'request'=>[
                'osVersion' => 'windows',
                'deviceType' => 'pc',
                'clientVersion' => '1.0',
            ]
        ];
        $post_data=$this->encry($post_data);
        $result=$this->loginResponseHandle($this->post(self::LOGIN_URL,$post_data,$header));
        if($result['code']===0){
            $retData = gzdecode($result['data'], strlen($result['data']));
            $retArray = json_decode($retData, true);
        }
    }

    public function encry($data)
    {
        $post_data='';

        $data=gzencode(json_encode($data),9);

        $len=strlen($data);

        for($i=0;$i<$len;$i+=117){
            $ret=openssl_public_encrypt(substr($data,$i,117), $encrypted,$this->public_key);
            if($ret){
                $post_data.=$encrypted;
            } else {
                throw new Exception('秘钥错误');
            }
        }

        return $post_data;
    }

    public function header($type='login')
    {
        if ($type=='login') {
            $header=[
                'UUID:'.$this->uuid,
                'account_type:'.$this->account_type,
                'Content-Type:data/gzencode and rsa public encrypt;charset=UTF-8'
            ];
        } else {
            $header=[
                'UUID:'.$this->uuid,
                'USERID:'.$this->userid,
                'Content-Type:data/json;charset=UTF-8'
            ];
        }

        return $header;
    }

    public function loginResponseHandle($data)
    {
        $result['data'] = '';
        $result['code'] = ord($data[0])*64 + ord($data[1]);

        if ($result['code'] === 0) {
            $result['data'] = substr($data, 8);
        }

        return $result;
    }

    public function post($url,$data,$header=array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmpInfo = curl_exec($curl);

        if (curl_errno($curl)) {
            echo '[error] CURL ERROR: ' . curl_error($curl). PHP_EOL;
        }
        curl_close($curl);

        return $tmpInfo;
    }
}