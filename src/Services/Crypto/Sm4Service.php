<?php

namespace Yangweijie\GmGui\Services\Crypto;

use Exception;
use Yangweijie\GmGui\Interfaces\CryptoServiceInterface;
use Yangweijie\GmGui\Models\CryptoResult;
use Yangweijie\GmGui\Utils\Validator;
use Yangweijie\GmGui\Exceptions\CryptoException;
use Rtgm\sm\RtSm4;

class Sm4Service implements CryptoServiceInterface
{
    /**
     * SM4 加密算法实例
     *
     * @var RtSm4|null
     */
    protected ?RtSm4 $sm4 = null;

    /**
     * 加密密钥
     *
     * @var string
     */
    protected string $key = '';

    /**
     * 构造函数
     */
    public function __construct()
    {
        // SM4服务需要在设置密钥后才能使用
    }

    /**
     * 设置密钥
     *
     * @param string $key 加密密钥（二进制格式）
     * @return self
     * @throws CryptoException
     */
    public function setKey(string $key): self
    {
        // 验证密钥长度，SM4要求16字节（128位）密钥
        if (strlen($key) !== 16) {
            throw CryptoException::keyError("秘钥长度为16位");
        }
        
        // 将二进制密钥转换为十六进制格式存储
        $this->key = bin2hex($key);
        // 直接使用二进制密钥
        $this->sm4 = new RtSm4($key);
        return $this;
    }

    /**
     * 加密数据
     *
     * @param string $data 要加密的数据
     * @param array $options 加密选项
     * @return CryptoResult 加密结果
     */
    public function encrypt(string $data, array $options = []): CryptoResult
    {
        $startTime = microtime(true);
        $result = new CryptoResult();
        
        try {
            // 验证输入
            if (!Validator::validateNotEmpty($data)) {
                throw CryptoException::inputValidationError("数据不能为空");
            }
            
            // 检查密钥是否已设置
            if ($this->sm4 === null) {
                throw CryptoException::keyError("未设置加密密钥");
            }
            
            // 验证密钥长度
            if (!Validator::validateSm4KeyLength($this->key, 'hex')) {
                throw CryptoException::keyError("密钥长度不正确，应为16字节");
            }
            
            // 获取选项参数
            $mode = $options['mode'] ?? 'sm4-cbc';
            $iv = $options['iv'] ?? '';
            $outFormat = $options['outputFormat'] ?? 'hex';
            
            // 验证输出格式
            if (!in_array($outFormat, ['hex', 'base64', 'raw'])) {
                throw CryptoException::inputValidationError("不支持的输出格式: {$outFormat}");
            }
            
            // 验证需要IV的模式
            if (in_array($mode, ['sm4-cbc', 'sm4-ctr', 'sm4-ofb', 'sm4-cfb'])) {
                if (empty($iv)) {
                    // 自动生成IV
                    $iv = bin2hex(random_bytes(16));
                    $result->metadata['generatedIv'] = $iv;
                } elseif (!Validator::validateIvLength($iv, 'hex')) {
                    throw CryptoException::inputValidationError("IV长度不正确，应为16字节");
                }
            }
            
            // 执行加密
            $encrypted = $this->sm4->encrypt($data, $mode, hex2bin($iv), $outFormat);
            
            // 设置结果
            $result->success = true;
            $result->data = $encrypted;
            $result->format = $outFormat;
            $result->executionTime = microtime(true) - $startTime;
            
            // 如果使用了IV，将其添加到元数据中
            if (!empty($iv)) {
                $result->metadata['iv'] = $iv;
                $result->metadata['mode'] = $mode;
            }
            
        } catch (Exception $e) {
            $result->success = false;
            $result->error = $e->getMessage();
            $result->executionTime = microtime(true) - $startTime;
        }
        
        return $result;
    }

    /**
     * 解密数据
     *
     * @param string $data 要解密的数据
     * @param array $options 解密选项
     * @return CryptoResult 解密结果
     */
    public function decrypt(string $data, array $options = []): CryptoResult
    {
        $startTime = microtime(true);
        $result = new CryptoResult();
        
        try {
            // 验证输入
            if (!Validator::validateNotEmpty($data)) {
                throw CryptoException::inputValidationError("数据不能为空");
            }
            
            // 检查密钥是否已设置
            if ($this->sm4 === null) {
                throw CryptoException::keyError("未设置解密密钥");
            }
            
            // 验证密钥长度
            if (!Validator::validateSm4KeyLength($this->key, 'hex')) {
                throw CryptoException::keyError("密钥长度不正确，应为16字节");
            }
            
            // 获取选项参数
            $mode = $options['mode'] ?? 'sm4-cbc';
            $iv = $options['iv'] ?? '';
            $inputFormat = $options['inputFormat'] ?? 'hex';
            
            // 验证输入格式
            if (!in_array($inputFormat, ['hex', 'base64', 'raw'])) {
                throw CryptoException::inputValidationError("不支持的输入格式: {$inputFormat}");
            }
            
            // 验证需要IV的模式
            if (in_array($mode, ['sm4-cbc', 'sm4-ctr', 'sm4-ofb', 'sm4-cfb'])) {
                if (empty($iv)) {
                    throw CryptoException::inputValidationError("模式 {$mode} 需要提供IV");
                } elseif (!Validator::validateIvLength($iv, 'hex')) {
                    throw CryptoException::inputValidationError("IV长度不正确，应为16字节");
                }
            }
            
            // 执行解密
            $decrypted = $this->sm4->decrypt($data, $mode, hex2bin($iv), $inputFormat);
            
            // 设置结果
            $result->success = true;
            $result->data = $decrypted;
            $result->format = 'raw';
            $result->executionTime = microtime(true) - $startTime;
            
        } catch (Exception $e) {
            $result->success = false;
            $result->error = $e->getMessage();
            $result->executionTime = microtime(true) - $startTime;
        }
        
        return $result;
    }

    /**
     * 获取支持的格式列表
     *
     * @return array 支持的格式列表
     */
    public function getSupportedFormats(): array
    {
        return ['hex', 'base64', 'raw'];
    }

    /**
     * 验证输入数据
     *
     * @param string $data 要验证的数据
     * @param array $options 验证选项
     * @return bool 验证结果
     */
    public function validateInput(string $data, array $options = []): bool
    {
        $format = $options['format'] ?? 'hex';
        
        if ($format === 'hex') {
            return Validator::validateFormat($data, 'hex');
        } elseif ($format === 'base64') {
            return Validator::validateFormat($data, 'base64');
        }
        
        return true;
    }
}