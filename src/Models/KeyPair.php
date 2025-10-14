<?php

namespace Yangweijie\GmGui\Models;

use DateTime;

class KeyPair
{
    /**
     * 公钥
     *
     * @var string
     */
    public string $publicKey;

    /**
     * 私钥
     *
     * @var string
     */
    public string $privateKey;

    /**
     * 密钥格式
     *
     * @var string
     */
    public string $format;

    /**
     * 算法类型
     *
     * @var string
     */
    public string $algorithm;

    /**
     * 元数据
     *
     * @var array
     */
    public array $metadata;

    /**
     * 创建时间
     *
     * @var DateTime
     */
    public DateTime $createdAt;

    /**
     * 构造函数
     *
     * @param string $publicKey 公钥
     * @param string $privateKey 私钥
     * @param string $format 密钥格式
     * @param string $algorithm 算法类型
     * @param array $metadata 元数据
     * @param DateTime|null $createdAt 创建时间
     */
    public function __construct(
        string $publicKey = '',
        string $privateKey = '',
        string $format = 'hex',
        string $algorithm = 'sm2',
        array $metadata = [],
        ?DateTime $createdAt = null
    ) {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->format = $format;
        $this->algorithm = $algorithm;
        $this->metadata = $metadata;
        $this->createdAt = $createdAt ?? new DateTime();
    }
}