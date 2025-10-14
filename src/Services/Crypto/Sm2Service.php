<?php

namespace Yangweijie\GmGui\Services\Crypto;

use Exception;
use Yangweijie\GmGui\Interfaces\CryptoServiceInterface;
use Yangweijie\GmGui\Models\CryptoResult;
use Yangweijie\GmGui\Utils\FormatConverter;
use Yangweijie\GmGui\Utils\Validator;
use Yangweijie\GmGui\Exceptions\CryptoException;
use yangweijie\Sm2;
use Rtgm\sm\RtSm2;

class Sm2Service implements CryptoServiceInterface
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 不再在构造函数中创建固定的sm2实例
    }

    /**
     * 获取适合指定格式的SM2实例
     *
     * @param string $format 签名格式 ('hex' 或 'base64')
     * @return Sm2
     */
    protected function getSm2Instance(string $format = 'hex'): Sm2
    {
        return new Sm2($format);
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
            
            // 获取选项参数
            $publicKey = $options['publicKey'] ?? '';
            $mode = $options['mode'] ?? 'C1C3C2';
            $outFormat = $options['outputFormat'] ?? 'hex';
            $appendZeroFour = $options['appendZeroFour'] ?? false;
            
            // 验证公钥
            if (!Validator::validateNotEmpty($publicKey)) {
                throw CryptoException::keyError("公钥不能为空");
            }
            
            // 验证输出格式
            if (!in_array($outFormat, ['hex', 'base64'])) {
                throw CryptoException::inputValidationError("不支持的输出格式: {$outFormat}");
            }
            
            // 获取SM2实例并执行加密
            $sm2 = $this->getSm2Instance($outFormat);
            $encrypted = $sm2->doEncrypt(
                $data,
                $publicKey,
                $mode === 'C1C3C2' ? C1C3C2 : C1C2C3,
                $outFormat,
                $appendZeroFour
            );
            
            // 设置结果
            $result->success = true;
            $result->data = $encrypted;
            $result->format = $outFormat;
            $result->executionTime = microtime(true) - $startTime;
            
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
            
            // 获取选项参数
            $privateKey = $options['privateKey'] ?? '';
            $mode = $options['mode'] ?? 'C1C3C2';
            $inputFormat = $options['inputFormat'] ?? 'hex';
            
            // 验证私钥
            if (!Validator::validateNotEmpty($privateKey)) {
                throw CryptoException::keyError("私钥不能为空");
            }
            
            // 验证输入格式
            if (!in_array($inputFormat, ['hex', 'base64'])) {
                throw CryptoException::inputValidationError("不支持的输入格式: {$inputFormat}");
            }
            
            // 格式转换
            if ($inputFormat === 'base64') {
                $data = FormatConverter::binToHex(FormatConverter::base64ToBin($data));
            }
            
            // 获取SM2实例并执行解密
            $sm2 = $this->getSm2Instance('hex'); // 解密时使用hex格式
            $decrypted = $sm2->doDecrypt($data, $privateKey, true, $mode === 'C1C3C2' ? C1C3C2 : C1C2C3);
            
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
     * 签名数据
     *
     * @param string $data 原始数据
     * @param array $options 签名选项
     * @return CryptoResult 签名结果
     */
    public function sign(string $data, array $options = []): CryptoResult
    {
        $startTime = microtime(true);
        $result = new CryptoResult();
        
        try {
            // 验证输入
            if (!Validator::validateNotEmpty($data)) {
                throw CryptoException::inputValidationError("数据不能为空");
            }
            
            // 获取选项参数
            $privateKey = $options['privateKey'] ?? '';
            $userId = $options['userId'] ?? '';
            $outFormat = $options['outputFormat'] ?? 'hex';
            $toRS = $options['toRS'] ?? false;
            
            // 验证私钥
            if (!Validator::validateNotEmpty($privateKey)) {
                throw CryptoException::keyError("私钥不能为空");
            }
            
            // 验证输出格式
            if (!in_array($outFormat, ['hex', 'base64'])) {
                throw CryptoException::inputValidationError("不支持的输出格式: {$outFormat}");
            }
            
            // 获取SM2实例并执行签名
            $sm2 = $this->getSm2Instance($outFormat);
            $signature = $sm2->doSign(
                $data,
                $privateKey,
                $userId,
                $outFormat,
                $toRS
            );
            
            // 设置结果
            $result->success = true;
            $result->data = $signature;
            $result->format = $outFormat;
            $result->executionTime = microtime(true) - $startTime;
            
        } catch (Exception $e) {
            $result->success = false;
            $result->error = $e->getMessage();
            $result->executionTime = microtime(true) - $startTime;
        }
        
        return $result;
    }

    /**
     * 验证签名
     *
     * @param string $data 原始数据
     * @param string $signature 签名
     * @param array $options 验证选项
     * @return CryptoResult 验证结果
     */
    public function verify(string $data, string $signature, array $options = []): CryptoResult
    {
        $startTime = microtime(true);
        $result = new CryptoResult();
        
        try {
            // 验证输入
            if (!Validator::validateNotEmpty($data)) {
                throw CryptoException::inputValidationError("数据不能为空");
            }
            
            if (!Validator::validateNotEmpty($signature)) {
                throw CryptoException::inputValidationError("签名不能为空");
            }
            
            // 获取选项参数
            $publicKey = $options['publicKey'] ?? '';
            $userId = $options['userId'] ?? '';
            $inputFormat = $options['inputFormat'] ?? 'hex';
            $signatureFormat = $options['signatureFormat'] ?? 'asn1';
            
            // 验证公钥
            if (!Validator::validateNotEmpty($publicKey)) {
                throw CryptoException::keyError("公钥不能为空");
            }
            
            // 验证输入格式
            if (!in_array($inputFormat, ['hex', 'base64'])) {
                throw CryptoException::inputValidationError("不支持的输入格式: {$inputFormat}");
            }
            
            // 验证签名格式
            if (!in_array($signatureFormat, ['asn1', 'plain', 'hex', 'base64'])) {
                throw CryptoException::inputValidationError("不支持的签名格式: {$signatureFormat}");
            }
            
            // 格式转换 - 将签名转换为十六进制格式进行处理
            $processedSignature = $signature;
            
            // 如果签名是base64格式，先转换为hex格式
            if ($inputFormat === 'base64' || $signatureFormat === 'base64') {
                $processedSignature = FormatConverter::binToHex(FormatConverter::base64ToBin($signature));
            }
            
            // 如果签名是Plain格式(R|S格式)，需要转换为ASN.1格式进行验签
            if ($signatureFormat === 'plain') {
                try {
                    // 确保签名是hex格式
                    if (!ctype_xdigit($processedSignature)) {
                        // 如果不是hex格式，尝试转换
                        $processedSignature = FormatConverter::binToHex(FormatConverter::base64ToBin($processedSignature));
                    }
                    
                    // 检查签名长度是否正确（R|S格式应该是128个十六进制字符，即64字节）
                    if (strlen($processedSignature) != 128) {
                        throw new Exception("无效的R|S格式签名长度: " . strlen($processedSignature));
                    }
                    
                    // 使用SmSignFormatRS将R|S格式转换为ASN.1格式
                    // 先将hex格式的签名转换为base64格式供转换函数使用
                    $signatureBase64 = base64_encode(hex2bin($processedSignature));
                    $asn1Signature = \Rtgm\util\SmSignFormatRS::rs_to_asn1($signatureBase64, 'base64');
                    // 转换后的签名是base64格式，需要再转换为hex格式
                    $processedSignature = FormatConverter::binToHex(FormatConverter::base64ToBin($asn1Signature));
                } catch (Exception $e) {
                    throw new Exception("签名格式转换失败: " . $e->getMessage());
                }
            }
            
            // 创建适合指定格式的SM2实例
            $sm2 = $this->getSm2Instance('hex');
            
            // 执行验签
            $verified = $sm2->verifySign(
                $data,
                $processedSignature,
                $publicKey,
                $userId
            );
            
            // 设置结果
            $result->success = true;
            $result->data = $verified ? 'valid' : 'invalid';
            $result->format = 'verification';
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
        return ['hex', 'base64'];
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