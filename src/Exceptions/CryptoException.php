<?php

namespace Yangweijie\GmGui\Exceptions;

class CryptoException extends \Exception
{
    /**
     * 错误代码 - 输入验证错误
     */
    public const INPUT_VALIDATION_ERROR = 1001;

    /**
     * 错误代码 - 密钥错误
     */
    public const KEY_ERROR = 1002;

    /**
     * 错误代码 - 算法错误
     */
    public const ALGORITHM_ERROR = 1003;

    /**
     * 错误代码 - 文件操作错误
     */
    public const FILE_OPERATION_ERROR = 1004;

    /**
     * 错误代码 - 系统错误
     */
    public const SYSTEM_ERROR = 1005;

    /**
     * 错误类型
     *
     * @var string
     */
    protected string $errorType;

    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param int $code 错误代码
     * @param \Throwable|null $previous 前一个异常
     * @param string $errorType 错误类型
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        \Throwable $previous = null,
        string $errorType = "unknown"
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorType = $errorType;
    }

    /**
     * 获取错误类型
     *
     * @return string
     */
    public function getErrorType(): string
    {
        return $this->errorType;
    }

    /**
     * 创建输入验证错误
     *
     * @param string $message 错误消息
     * @return self
     */
    public static function inputValidationError(string $message): self
    {
        return new self($message, self::INPUT_VALIDATION_ERROR, null, "input_validation");
    }

    /**
     * 创建密钥错误
     *
     * @param string $message 错误消息
     * @return self
     */
    public static function keyError(string $message): self
    {
        return new self($message, self::KEY_ERROR, null, "key");
    }

    /**
     * 创建算法错误
     *
     * @param string $message 错误消息
     * @return self
     */
    public static function algorithmError(string $message): self
    {
        return new self($message, self::ALGORITHM_ERROR, null, "algorithm");
    }

    /**
     * 创建文件操作错误
     *
     * @param string $message 错误消息
     * @return self
     */
    public static function fileOperationError(string $message): self
    {
        return new self($message, self::FILE_OPERATION_ERROR, null, "file_operation");
    }

    /**
     * 创建系统错误
     *
     * @param string $message 错误消息
     * @return self
     */
    public static function systemError(string $message): self
    {
        return new self($message, self::SYSTEM_ERROR, null, "system");
    }
}