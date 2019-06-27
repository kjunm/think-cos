<?php
/**
 * Created by PhpStorm.
 * User: wangjm
 * Date: 2019/6/26
 * Time: 18:36
 */
require_once __DIR__.'/vendor/autoload.php';
use kjunm\thinkcos\Cos;

$cos = Cos::getInstance([
    'secretId' => '******************************',
    'secretKey' => '******************************',
    'region' => 'ap-chongqing',
    'bucket' => '******',
    'schema' => 'https',
]);
print_r($cos->delete('upload/test/04cf7444f462f0d16b1e60ad83460798.jpg'));exit();
print_r($cos->getAllObject(['MaxKeys' => 5,'Prefix' => 'upload/','EncodingType' => 'url']));

