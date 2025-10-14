<?php

namespace Yangweijie\GmGui\Services;

use Yangweijie\GmGui\Interfaces\KeyManagementInterface;
use Yangweijie\GmGui\Models\KeyPair;
use Yangweijie\GmGui\Utils\FileHelper;
use Yangweijie\GmGui\Utils\FormatConverter;
use Yangweijie\GmGui\Utils\Validator;
use Yangweijie\GmGui\Exceptions\CryptoException;
use yangweijie\Sm2;

class KeyManagementService implements KeyManagementInterface
{
    /**
     * 密钥存储路径
     *
     * @var string
     */
    protected string $keyStorePath;

    /**
     * SM2 实例
     *
     * @var Sm2
     */
    protected Sm2 $sm2;

    /**
     * 构造函数
     *
     * @param string $keyStorePath 密钥存储路径
     */
    public function __construct(string $keyStorePath = './keys')
    {
        $this->keyStorePath = $keyStorePath;
        $this->sm2 = new Sm2();
        
        // 确保密钥存储目录存在
        if (!is_dir($this->keyStorePath)) {
            mkdir($this->keyStorePath, 0755, true);
        }
    }

    /**
     * 生成密钥对
     *
     * @return KeyPair 生成的密钥对
     */
    public function generateKeyPair(): KeyPair
    {
        try {
            // 生成密钥对
            $keyPair = $this->sm2->generatekey();
            
            // 创建 KeyPair 对象
            $result = new KeyPair(
                $keyPair[1],  // publicKey
                $keyPair[0],  // privateKey
                'hex',
                'sm2'
            );
            
            return $result;
        } catch (\Exception $e) {
            throw CryptoException::keyError("生成密钥对失败: " . $e->getMessage());
        }
    }

    /**
     * 导入密钥
     *
     * @param string $keyData 密钥数据
     * @param string $format 密钥格式
     * @return KeyPair 导入的密钥对
     */
    public function importKey(string $keyData, string $format = 'hex'): KeyPair
    {
        try {
            // 验证输入
            if (!Validator::validateNotEmpty($keyData)) {
                throw CryptoException::inputValidationError("密钥数据不能为空");
            }
            
            $publicKey = '';
            $privateKey = '';
            
            switch ($format) {
                case 'hex':
                    // 假设是十六进制格式的密钥对，用冒号分隔
                    $parts = explode(':', $keyData);
                    if (count($parts) >= 2) {
                        $publicKey = $parts[0];
                        $privateKey = $parts[1];
                    } else {
                        // 可能只有公钥或私钥
                        if (strlen($keyData) === 130 && substr($keyData, 0, 2) === '04' && ctype_xdigit($keyData)) {
                            // 可能是公钥（带04前缀）
                            $publicKey = $keyData;
                        } elseif (strlen($keyData) === 128 && ctype_xdigit($keyData)) {
                            // 可能是不带04前缀的公钥坐标
                            $publicKey = $keyData;
                        } elseif (strlen($keyData) === 64 && ctype_xdigit($keyData)) {
                            // 可能是私钥
                            $privateKey = $keyData;
                        } else {
                            // 如果不匹配任何已知格式，抛出异常
                            throw CryptoException::inputValidationError("无效的十六进制密钥格式");
                        }
                    }
                    break;
                    
                case 'pem':
                    // 解析PEM格式
                    $publicKey = $this->extractPublicKeyFromPem($keyData);
                    $privateKey = $this->extractPrivateKeyFromPem($keyData);
                    break;
                    
                case 'asn1':
                    // 解析ASN.1格式
                    $publicKey = $this->sm2->asn1ToHexPublic($keyData);
                    $privateKey = $this->sm2->asn1ToHexPrivate($keyData);
                    break;
                    
                default:
                    throw CryptoException::inputValidationError("不支持的密钥格式: {$format}");
            }
            
            // 创建 KeyPair 对象
            $keyPair = new KeyPair(
                $publicKey,
                $privateKey,
                $format,
                'sm2'
            );
            
            // 验证密钥
            if (!empty($publicKey) && !Validator::validateSm2KeyLength($publicKey, 'hex')) {
                throw CryptoException::keyError("公钥格式不正确");
            }
            
            if (!empty($privateKey) && !Validator::validateSm2KeyLength($privateKey, 'hex')) {
                throw CryptoException::keyError("私钥格式不正确");
            }
            
            return $keyPair;
        } catch (CryptoException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw CryptoException::keyError("导入密钥失败: " . $e->getMessage());
        }
    }

    /**
     * 从文件导入密钥
     *
     * @param string $filePath 文件路径
     * @return bool 是否导入成功
     */
    public function importKeyFromFile(string $filePath): bool
    {
        try {
            // 检查文件是否存在
            if (!file_exists($filePath)) {
                throw CryptoException::fileOperationError("密钥文件不存在: {$filePath}");
            }
            
            // 获取文件扩展名以确定格式
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $format = 'hex'; // 默认格式
            
            if ($extension === 'pem') {
                $format = 'pem';
            } elseif ($extension === 'der' || $extension === 'asn1') {
                $format = 'asn1';
            }
            
            // 读取文件内容
            $keyData = FileHelper::readFile($filePath);
            
            // 导入密钥
            $keyPair = $this->importKey($keyData, $format);
            
            // 生成保存文件名
            $filename = basename($filePath);
            
            // 保存密钥对
            return $this->saveKeyPair($keyPair, $filename, $format);
        } catch (\Exception $e) {
            throw CryptoException::keyError("从文件导入密钥失败: " . $e->getMessage());
        }
    }

    /**
     * 导出密钥
     *
     * @param KeyPair $keyPair 密钥对
     * @param string $format 导出格式
     * @return string 导出的密钥数据
     */
    public function exportKey(KeyPair $keyPair, string $format): string
    {
        try {
            switch ($format) {
                case 'hex':
                    // 导出为十六进制格式
                    if (!empty($keyPair->publicKey) && !empty($keyPair->privateKey)) {
                        return $keyPair->publicKey . ':' . $keyPair->privateKey;
                    } elseif (!empty($keyPair->publicKey)) {
                        return $keyPair->publicKey;
                    } elseif (!empty($keyPair->privateKey)) {
                        return $keyPair->privateKey;
                    }
                    break;
                    
                case 'pem':
                    // 导出为PEM格式
                    return $this->convertToPemFormat($keyPair);
                    
                case 'asn1':
                    // ASN.1格式需要特殊处理
                    throw CryptoException::inputValidationError("ASN.1格式暂不支持导出");
                    
                default:
                    throw CryptoException::inputValidationError("不支持的导出格式: {$format}");
            }
            
            throw CryptoException::keyError("密钥数据为空");
        } catch (CryptoException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw CryptoException::keyError("导出密钥失败: " . $e->getMessage());
        }
    }

    /**
     * 验证密钥
     *
     * @param string $keyData 密钥数据
     * @param string $format 密钥格式
     * @return bool 验证结果
     */
    public function validateKey(string $keyData, string $format): bool
    {
        try {
            $keyPair = $this->importKey($keyData, $format);
            return !empty($keyPair->publicKey) || !empty($keyPair->privateKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 转换密钥格式
     *
     * @param string $keyData 密钥数据
     * @param string $fromFormat 源格式
     * @param string $toFormat 目标格式
     * @return string 转换后的密钥数据
     */
    public function convertKeyFormat(string $keyData, string $fromFormat, string $toFormat): string
    {
        try {
            // 导入源格式密钥
            $keyPair = $this->importKey($keyData, $fromFormat);
            
            // 导出为目标格式
            return $this->exportKey($keyPair, $toFormat);
        } catch (\Exception $e) {
            throw CryptoException::keyError("转换密钥格式失败: " . $e->getMessage());
        }
    }

    /**
     * 保存密钥对到文件
     *
     * @param KeyPair $keyPair 密钥对
     * @param string $filename 文件名或完整路径
     * @param string $format 存储格式
     * @return bool 是否保存成功
     */
    public function saveKeyPair(KeyPair $keyPair, string $filename, string $format = 'hex'): bool
    {
        try {
            // 检查filename是否已经是完整路径（包含目录分隔符）
            if (strpos($filename, DIRECTORY_SEPARATOR) !== false) {
                // 使用完整路径
                $filePath = $filename;
            } else {
                // 确保文件名有正确的扩展名
                if ($format === 'pem' && !str_ends_with($filename, '.pem')) {
                    $filename .= '.pem';
                } elseif ($format === 'hex' && !str_ends_with($filename, '.key')) {
                    $filename .= '.key';
                }
                
                // 构建完整路径
                $filePath = $this->keyStorePath . DIRECTORY_SEPARATOR . $filename;
            }
            
            // 导出密钥
            $keyData = $this->exportKey($keyPair, $format);
            
            // 保存到文件
            return FileHelper::writeFile($filePath, $keyData);
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("保存密钥失败: " . $e->getMessage());
        }
    }

    /**
     * 从文件加载密钥对
     *
     * @param string $filename 文件名
     * @param string $format 文件格式
     * @return KeyPair 密钥对
     */
    public function loadKeyPair(string $filename, string $format = 'hex'): KeyPair
    {
        try {
            // 构建完整路径
            $filePath = $this->keyStorePath . DIRECTORY_SEPARATOR . $filename;
            
            // 读取文件内容
            $keyData = FileHelper::readFile($filePath);
            
            // 导入密钥
            return $this->importKey($keyData, $format);
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("加载密钥失败: " . $e->getMessage());
        }
    }

    /**
     * 列出所有密钥文件
     *
     * @return array 密钥文件列表
     */
    public function listKeys(): array
    {
        $keys = [];
        
        if (is_dir($this->keyStorePath)) {
            $files = scandir($this->keyStorePath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $keys[] = $file;
                }
            }
        }
        
        return $keys;
    }

    /**
     * 删除密钥文件
     *
     * @param string $filename 文件名
     * @return bool 是否删除成功
     */
    public function deleteKey(string $filename): bool
    {
        try {
            $filePath = $this->keyStorePath . DIRECTORY_SEPARATOR . $filename;
            return FileHelper::secureDelete($filePath);
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("删除密钥失败: " . $e->getMessage());
        }
    }

    /**
     * 从PEM格式提取公钥
     *
     * @param string $pemData PEM数据
     * @return string 公钥
     */
    protected function extractPublicKeyFromPem(string $pemData): string
    {
        // 简化的PEM解析
        if (preg_match('/-----BEGIN PUBLIC KEY-----(.*?)-----END PUBLIC KEY-----/s', $pemData, $matches)) {
            $pemContent = $matches[1];
            $binary = base64_decode($pemContent);
            // 这里需要更复杂的ASN.1解析，简化处理
            return bin2hex($binary);
        }
        
        return '';
    }

    /**
     * 从PEM格式提取私钥
     *
     * @param string $pemData PEM数据
     * @return string 私钥
     */
    protected function extractPrivateKeyFromPem(string $pemData): string
    {
        // 简化的PEM解析
        if (preg_match('/-----BEGIN PRIVATE KEY-----(.*?)-----END PRIVATE KEY-----/s', $pemData, $matches)) {
            $pemContent = $matches[1];
            $binary = base64_decode($pemContent);
            // 这里需要更复杂的ASN.1解析，简化处理
            return bin2hex($binary);
        }
        
        return '';
    }

    /**
     * 转换为PEM格式
     *
     * @param KeyPair $keyPair 密钥对
     * @return string PEM格式数据
     */
    protected function convertToPemFormat(KeyPair $keyPair): string
    {
        $pem = '';
        
        if (!empty($keyPair->publicKey)) {
            $binary = hex2bin($keyPair->publicKey);
            $base64 = base64_encode($binary);
            $pem .= "-----BEGIN PUBLIC KEY-----\n";
            $pem .= chunk_split($base64, 64, "\n");
            $pem .= "-----END PUBLIC KEY-----\n";
        }
        
        if (!empty($keyPair->privateKey)) {
            $binary = hex2bin($keyPair->privateKey);
            $base64 = base64_encode($binary);
            $pem .= "-----BEGIN PRIVATE KEY-----\n";
            $pem .= chunk_split($base64, 64, "\n");
            $pem .= "-----END PRIVATE KEY-----\n";
        }
        
        return $pem;
    }
}
