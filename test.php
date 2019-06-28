<?php
/**
 * Created by PhpStorm.
 * User: wangjm
 * Date: 2019/6/26
 * Time: 18:36
 */
require_once __DIR__.'/vendor/autoload.php';
use kjunm\thinkcos\Cos;



//curl访问
function curl_https($url, $data = array(), $header = '', $withHeader = false){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);// 设置URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//是否将参数返回到输出到页面中（0表示是1表示否）
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    if($data){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    if($header){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//跳过证书检测,否则会导致请求失败,返回false
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $res = curl_exec($ch);
    $err = curl_errno($ch);
    if($err){
        write_log('curl访问 '.$url.' 出错，错误码：'.$err.' -- '.curl_error($ch), 'curl');
        return false;
    }
    if($withHeader){
        $httpInfo = curl_getinfo($ch);//注意：curl_close后无法获取数据
        $res = array('body'=>$res, 'header'=>$httpInfo);
    }
    curl_close($ch);// 关闭url
    return $res;
}

$cos = Cos::getInstance([
    'secretId' => '',
    'secretKey' => '',
    'region' => '',
    'bucket' => '',
    'schema' => '',
]);
$url = 'http://wx.qlogo.cn/mmopen/vi_32/1vZvI39NWFQ9XM4LtQpFrQJ1xlgZxx3w7bQxKARol6503Iuswjjn6nIGBiaycAjAtpujxyzYsrztuuICqIM5ibXQ/0';
$data = curl_https($url,'','',true);
print_r($cos->uploadString('upload/test/1.jpg',$data['body']));exit();
print_r($cos->getTempKey());exit();
print_r($cos->delete('upload/test/04cf7444f462f0d16b1e60ad83460798.jpg'));exit();
print_r($cos->getAllObject(['MaxKeys' => 5,'Prefix' => 'upload/','EncodingType' => 'url']));

