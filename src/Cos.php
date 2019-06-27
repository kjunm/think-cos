<?php
/**
 * Created by PhpStorm.
 * User: wangjm
 * Date: 2019/6/25
 * Time: 18:41
 */
namespace kjunm\thinkcos;
use Qcloud\Cos\Client;
use sts;
class Cos
{
    /** @var $secretId 云api密钥 */
    private $secretId;
    /** @var $secretKey 云api密钥 */
    private $secretKey;
    /** @var $region 存储桶地域 */
    private $region ;
    /** @var $schema 协议头部,默认为http */
    private $schema;
    /** @var $bucket 存储桶名称*/
    private $bucket;
    /** @var Client $cos cos连接 */
    private $cos;
    /** @var self $_instance cos操作类 */
    private static $_instance;

    /**
     * Cos constructor.
     * @param array $config
     * @throws \Exception
     */
    private function __construct(array $config=[])
    {
        if(!isset($config['secretId'])){
            throw new \Exception('The "secretId" property must be set.');
        }
        if(!isset($config['secretKey'])){
            throw new \Exception('The "secretKey" property must be set.');
        }
        if(!isset($config['region'])){
            throw new \Exception('The "region" property must be set.');
        }
        if(!isset($config['bucket'])){
            throw new \Exception('The "region" property must be set.');
        }
        $this->secretId = $config['secretId'];
        $this->secretKey = $config['secretKey'];
        $this->region = $config['region'];
        $this->bucket = $config['bucket'];
        $this->schema = isset($config['schema']) ? $config['schema'] : 'http';
    }

    /**
     * set cos
     * @param Client $client
     */
    protected function setClient(Client $client)
    {
        $this->cos = $client;
    }

    /**
     * get cos
     * @return Client
     */
    protected function getClient()
    {
        if($this->cos === null){
            $this->setClient(
                new Client([
                    'region' => $this->region,
                    'schema' => $this->schema,
                    'credentials'=> [
                        'secretId'  => $this->secretId ,
                        'secretKey' => $this->secretKey
                    ]])
            );
        }
        return $this->cos;
    }


    /**
     * @param array $config
     * @return Cos
     * @throws Exception
     */
    public static function getInstance(array $config=[])
    {
        if(!self::$_instance instanceof self){
            if(empty($config)){
                throw new \Exception('cos config is empty!');
            }
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

    /**
     * 文件是否存在
     * @param $path upload/a.jpg
     * @return bool
     */
    public function has($path)
    {
        return $this->getClient()->doesObjectExist($this->bucket,$path);
    }

    /**
     * 上传对象
     * @param $path
     * @param $filePath
     * @return mixed
     */
    public function upload($path,$filePath)
    {
        $body = fopen($filePath,'rb');
        return $this->getClient()->Upload($this->bucket,$path,$body);
    }

    /**
     * 删除对象
     * @param $path
     * @return mixed
     */
    public function delete($path)
    {
        return $this->getClient()->deleteObject(['Bucket' => $this->bucket,'Key' => $path]);
    }

    /**
     * 删除多个对象
     * @param array $path_arr
     * @return mixed
     * @throws \Exception
     */
    public function deleteMulti(array $path_arr)
    {
        if(!is_array($path_arr)){
            throw new \Exception('The "path_arr" property must be a array.');
        }
        $objects = [];
        foreach ($path_arr as $value){
            $objects[]['Key'] = $value;
        }
        return $this->getClient()->deleteObjects(['Bucket' => $this->bucket,'Objects' => $objects]);
    }

    /**
     * 获取对象列表
     * @param array $options = [
     *      'Bucket' => 存储桶名称，格式：BucketName-APPID
     *      'Delimiter' => 默认为空，设置分隔符，比如设置/来模拟文件夹
     *      'EncodingType' => 默认不编码，规定返回值的编码方式，可选值：url
     *      'Marker' => 默认以 UTF-8 二进制顺序列出条目，标记返回 objects 的 list 的起点位置
     *      'Prefix' => 默认为空，对 object 的 key 进行筛选，匹配指定前缀（prefix）的 objects
     *      'MaxKeys' => 最多返回的 objects 数量，默认为最大的1000
     * ]
     * @return array
     */
    public function getAllObject($options = [])
    {
        $options = array_merge(['Bucket' => $this->bucket,'MaxKeys' => 100],$options);
        $list =  $this->getClient()->listObjects($options);
        $object_list = [];
        foreach ($list['Contents'] as $value){
            $object_list[] = $value['Key'];
        }
        return $object_list;
    }

    public function getTempKey($options = [])
    {
        $config = [
            'url' => 'https://sts.tencentcloudapi.com/',
            'domain' => 'sts.tencentcloudapi.com',
            'proxy' => '',
            'secretId' => $this->secretId, // 固定密钥
            'secretKey' => $this->secretKey, // 固定密钥
            'bucket' => $this->bucket, // 换成你的 bucket
            'region' => $this->region, // 换成 bucket 所在园区
            'durationSeconds' => 1800, // 密钥有效期
            'allowPrefix' => '*',
            'allowActions' => array (
                // 简单上传
                'name/cos:PutObject',
                'name/cos:PostObject',
                // 分片上传
                'name/cos:InitiateMultipartUpload',
                'name/cos:ListMultipartUploads',
                'name/cos:ListParts',
                'name/cos:UploadPart',
                'name/cos:CompleteMultipartUpload',
                //获取存储通列表
                "name/cos:GetService",
            )
        ];
        $config = array_merge($config,$options);
        $sts = new STS();
        return $sts->getTempKeys($config);
    }
}