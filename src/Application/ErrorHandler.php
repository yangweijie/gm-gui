<?php

namespace Yangweijie\GmGui\Application;

use Exception;
use Yangweijie\GmGui\Exceptions\CryptoException;
use Yangweijie\GmGui\Models\CryptoResult;

class ErrorHandler
{
    /**
     * 处理加密错误
     *
     * @param Exception $e 异常
     * @return CryptoResult 加密结果
     */
    public function handleCryptoError(Exception $e): CryptoResult
    {
        $result = new CryptoResult();
        $result->success = false;
        $result->error = $e->getMessage();
        $result->executionTime = 0.0;
        
        // 如果是 CryptoException，设置更多详细信息
        if ($e instanceof CryptoException) {
            $result->metadata['errorType'] = $e->getErrorType();
            $result->metadata['errorCode'] = $e->getCode();
        }
        
        return $result;
    }

    /**
     * 处理文件错误
     *
     * @param Exception $e 异常
     * @throws CryptoException
     */
    public function handleFileError(Exception $e): void
    {
        throw CryptoException::fileOperationError($e->getMessage());
    }

    /**
     * 处理验证错误
     *
     * @param string $field 字段名
     * @param string $message 错误消息
     * @throws CryptoException
     */
    public function handleValidationError(string $field, string $message): void
    {
        throw CryptoException::inputValidationError("字段 '{$field}': {$message}");
    }

    /**
     * 显示用户友好的错误
     *
     * @param string $message 错误消息
     * @return void
     */
    public function showUserFriendlyError(string $message): void
    {
        // 在实际应用中，这里会显示一个用户友好的错误对话框
        error_log("应用错误: " . $message);
    }
}