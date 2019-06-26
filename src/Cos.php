<?php
/**
 * Created by PhpStorm.
 * User: wangjm
 * Date: 2019/6/25
 * Time: 18:41
 */
namespace kjunm\thinkcos;
use Qcloud\Cos\Client;
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
     * @throws Exception
     */
    private function __construct(array $config=[])
    {
        if(!isset($config['secretId'])){
            throw new Exception('The "secretId" property must be set.');
        }
        if(!isset($config['secretKey'])){
            throw new Exception('The "secretKey" property must be set.');
        }
        if(!isset($config['region'])){
            throw new Exception('The "region" property must be set.');
        }
        if(!isset($config['bucket'])){
            throw new Exception('The "region" property must be set.');
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
                throw new Exception('cos config is empty!');
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
     * @param $path
     * @param $filePath
     * @return mixed
     */
    public function upload($path,$filePath)
    {
        $body = fopen($filePath,'rb');
        return $this->getClient()->Upload($this->bucket,$path,$body);
    }
}