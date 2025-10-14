<?php

namespace Yangweijie\GmGui\Models;

class CryptoConfig
{
    /**
     * 默认输出格式
     *
     * @var string
     */
    public string $outputFormat = 'hex';

    /**
     * SM2 是否补 04 前缀
     *
     * @var bool
     */
    public bool $appendZeroFour = false;

    /**
     * SM2 加密模式
     *
     * @var string
     */
    public string $sm2Mode = 'C1C3C2';

    /**
     * SM4 默认模式
     *
     * @var string
     */
    public string $sm4Mode = 'cbc';

    /**
     * 签名格式
     *
     * @var string
     */
    public string $signatureFormat = 'asn1';

    /**
     * 密钥存储路径
     *
     * @var string
     */
    public string $keyStorePath = './keys';

    /**
     * 构造函数
     *
     * @param array $config 配置数组
     */
    public function __construct(array $config = [])
    {
        if (isset($config['outputFormat'])) {
            $this->outputFormat = $config['outputFormat'];
        }
        
        if (isset($config['appendZeroFour'])) {
            $this->appendZeroFour = $config['appendZeroFour'];
        }
        
        if (isset($config['sm2Mode'])) {
            $this->sm2Mode = $config['sm2Mode'];
        }
        
        if (isset($config['sm4Mode'])) {
            $this->sm4Mode = $config['sm4Mode'];
        }
        
        if (isset($config['signatureFormat'])) {
            $this->signatureFormat = $config['signatureFormat'];
        }
        
        if (isset($config['keyStorePath'])) {
            $this->keyStorePath = $config['keyStorePath'];
        }
    }
}