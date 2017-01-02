<?php

namespace Mushan\BaiduTongji;

class Login
{
    const LOGIN_URL='https://api.baidu.com/sem/common/HolmesLoginService';

    const PUBLIC_KEY='-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDHn/hfvTLRXViBXTmBhNYEIJeG
GGDkmrYBxCRelriLEYEcrwWrzp0au9nEISpjMlXeEW4+T82bCM22+JUXZpIga5qd
BrPkjU08Ktf5n7Nsd7n9ZeI0YoAKCub3ulVExcxGeS3RVxFai9ozERlavpoTOdUz
EH6YWHP4reFfpMpLzwIDAQAB
-----END PUBLIC KEY-----';

    private $public_key_resource;

    private $config;

    private $error_code=[
        2=>'INVALID_ENCODING: 请求数据的编码错误，非UTF-8',
        3=>'DAMAGED_DATA: 请求数据损坏',
        4=>'DATA_TOO_LARGE: 请求数据过大',
        6=>'INVALID_REQUEST: 请求数据不符合规范',
        7=>'FUNCTION_NOT_SUPPORTED: 未知的functionName',
        8=>'DAMAGED_RESPONSE : 响应数据损坏',
        9=>'INVALID_TOKEN: token无效',
        10=>'INVALID_USER: 用户无效',
        11=>'ERROR_PROCESSING: 登录请求处理异常',
        12=>'INVALID_ACCOUNTTYPE: 账户类型无效'
    ];

    public function __construct($config)
    {
        $this->config=$config;
        $this->public_key_resource=openssl_pkey_get_public(self::PUBLIC_KEY);
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
        $headers=$this->getHeader();

        $result=curl_post(self::LOGIN_URL,$post_data,$headers);
        $result=$this->responseHandle($result);

        if($result['code']!=0){
            $error_msg=array_key_exists($result['code'],$this->error_code)?$this->error_code[$result['code']]:'未知错误';
            throw new \Exception($error_msg);
        }
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

    public function getHeader()
    {
        $header=[
            'UUID:'.$this->uuid,
            'account_type:'.$this->account_type,
            'Content-Type:data/gzencode and rsa public encrypt;charset=UTF-8'
        ];

        return $header;
    }

    public function encry($data)
    {
        $post_data='';

        $data=gzencode(json_encode($data),9);

        $len=strlen($data);

        for($i=0;$i<$len;$i+=117){
            $ret=openssl_public_encrypt(substr($data,$i,117), $encrypted,$this->public_key_resource);
            if($ret){
                $post_data.=$encrypted;
            } else {
                throw new \Exception('秘钥错误');
            }
        }

        return $post_data;
    }

    public function responseHandle($data)
    {
        $result['data'] = '';
        $result['code'] = ord($data[0])*64 + ord($data[1]);

        if ($result['code'] === 0) {
            $result['data'] = substr($data, 8);

            $result['data']= gzdecode($result['data'], strlen($result['data']));
        }

        return $result;
    }
}