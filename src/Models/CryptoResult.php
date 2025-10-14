<?php

namespace Yangweijie\GmGui\Models;

class CryptoResult
{
    /**
     * 操作是否成功
     *
     * @var bool
     */
    public bool $success;

    /**
     * 处理后的数据
     *
     * @var string
     */
    public string $data;

    /**
     * 数据格式
     *
     * @var string
     */
    public string $format;

    /**
     * 错误信息
     *
     * @var string|null
     */
    public ?string $error;

    /**
     * 元数据
     *
     * @var array
     */
    public array $metadata;

    /**
     * 执行时间（秒）
     *
     * @var float
     */
    public float $executionTime;

    /**
     * 构造函数
     *
     * @param bool $success 操作是否成功
     * @param string $data 处理后的数据
     * @param string $format 数据格式
     * @param string|null $error 错误信息
     * @param array $metadata 元数据
     * @param float $executionTime 执行时间
     */
    public function __construct(
        bool $success = false,
        string $data = '',
        string $format = 'hex',
        ?string $error = null,
        array $metadata = [],
        float $executionTime = 0.0
    ) {
        $this->success = $success;
        $this->data = $data;
        $this->format = $format;
        $this->error = $error;
        $this->metadata = $metadata;
        $this->executionTime = $executionTime;
    }
}