<?php

namespace Yangweijie\GmGui\Services\Crypto;

use Yangweijie\GmGui\Interfaces\CryptoServiceInterface;
use Yangweijie\GmGui\Models\CryptoResult;
use Yangweijie\GmGui\Utils\FormatConverter;
use Yangweijie\GmGui\Utils\Validator;
use Yangweijie\GmGui\Exceptions\CryptoException;
use Rtgm\sm\RtSm3;

class Sm3Service implements CryptoServiceInterface
{
    /**
     * SM3 哈希算法实例
     *
     * @var RtSm3
     */
    protected RtSm3 $sm3;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->sm3 = new RtSm3();
    }

    /**
     * 计算哈希值
     *
     * @param string $data 要计算哈希的数据
     * @param array $options 选项
     * @return CryptoResult 哈希结果
     */
    public function encrypt(string $data, array $options = []): CryptoResult
    {
        return $this->hash($data, $options);
    }

    /**
     * 计算哈希值（别名）
     *
     * @param string $data 要计算哈希的数据
     * @param array $options 选项
     * @return CryptoResult 哈希结果
     */
    public function hash(string $data, array $options = []): CryptoResult
    {
        $startTime = microtime(true);
        $result = new CryptoResult();
        
        try {
            // 验证输入
            if (!Validator::validateNotEmpty($data)) {
                throw CryptoException::inputValidationError("数据不能为空");
            }
            
            // 获取选项参数
            $outFormat = $options['outputFormat'] ?? 'hex';
            
            // 验证输出格式
            if (!in_array($outFormat, ['hex', 'base64'])) {
                throw CryptoException::inputValidationError("不支持的输出格式: {$outFormat}");
            }
            
            // 执行哈希计算
            $hash = $this->sm3->digest($data, 1); // 1表示返回十六进制格式
            
            // 格式转换
            if ($outFormat === 'base64') {
                $hash = FormatConverter::binToBase64(FormatConverter::hexToBin($hash));
            }
            
            // 设置结果
            $result->success = true;
            $result->data = $hash;
            $result->format = $outFormat;
            $result->executionTime = microtime(true) - $startTime;
            
        } catch (\Exception $e) {
            $result->success = false;
            $result->error = $e->getMessage();
            $result->executionTime = microtime(true) - $startTime;
        }
        
        return $result;
    }

    /**
     * 计算文件哈希值
     *
     * @param string $filePath 文件路径
     * @param array $options 选项
     * @param callable|null $progressCallback 进度回调函数
     * @return CryptoResult 哈希结果
     */
    public function hashFile(string $filePath, array $options = [], ?callable $progressCallback = null): CryptoResult
    {
        $startTime = microtime(true);
        $result = new CryptoResult();
        
        try {
            // 检查文件是否存在
            if (!file_exists($filePath)) {
                throw CryptoException::fileOperationError("文件不存在: {$filePath}");
            }
            
            // 检查文件是否可读
            if (!is_readable($filePath)) {
                throw CryptoException::fileOperationError("文件不可读: {$filePath}");
            }
            
            // 获取选项参数
            $outFormat = $options['outputFormat'] ?? 'hex';
            $chunkSize = $options['chunkSize'] ?? 8192; // 默认8KB块大小
            
            // 验证输出格式
            if (!in_array($outFormat, ['hex', 'base64'])) {
                throw CryptoException::inputValidationError("不支持的输出格式: {$outFormat}");
            }
            
            // 获取文件大小
            $fileSize = filesize($filePath);
            $result->metadata['filePath'] = $filePath;
            $result->metadata['fileSize'] = $fileSize;
            
            // 对于大文件，分块读取并计算哈希
            if ($fileSize > $chunkSize) {
                $hash = $this->hashLargeFile($filePath, $chunkSize, $progressCallback);
            } else {
                // 小文件直接读取
                $data = file_get_contents($filePath);
                if ($data === false) {
                    throw CryptoException::fileOperationError("读取文件失败: {$filePath}");
                }
                
                // 执行哈希计算
                $hash = $this->sm3->digest($data, 1); // 1表示返回十六进制格式
                
                // 调用进度回调（如果提供）
                if ($progressCallback !== null) {
                    $progressCallback(100, $fileSize, $fileSize);
                }
            }
            
            // 格式转换
            if ($outFormat === 'base64') {
                $hash = FormatConverter::binToBase64(FormatConverter::hexToBin($hash));
            }
            
            // 设置结果
            $result->success = true;
            $result->data = $hash;
            $result->format = $outFormat;
            $result->executionTime = microtime(true) - $startTime;
            
        } catch (\Exception $e) {
            $result->success = false;
            $result->error = $e->getMessage();
            $result->executionTime = microtime(true) - $startTime;
        }
        
        return $result;
    }

    /**
     * 计算大文件哈希值（分块处理）
     *
     * @param string $filePath 文件路径
     * @param int $chunkSize 块大小
     * @param callable|null $progressCallback 进度回调函数
     * @return string 哈希值
     */
    protected function hashLargeFile(string $filePath, int $chunkSize, ?callable $progressCallback = null): string
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw CryptoException::fileOperationError("无法打开文件: {$filePath}");
        }
        
        // 对于大文件，我们仍然使用全文件读取方式，因为RtSm3库不支持增量更新
        // 这里保留分块读取是为了显示进度
        $fileSize = filesize($filePath);
        $processedBytes = 0;
        $data = '';
        
        // 分块读取文件以显示进度
        while (!feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            if ($chunk === false) {
                fclose($handle);
                throw CryptoException::fileOperationError("读取文件失败: {$filePath}");
            }
            
            $data .= $chunk;
            $processedBytes += strlen($chunk);
            
            // 调用进度回调（如果提供）
            if ($progressCallback !== null) {
                $progress = intval(($processedBytes / $fileSize) * 100);
                $progressCallback($progress, $processedBytes, $fileSize);
            }
        }
        
        fclose($handle);
        
        // 使用RtSm3计算哈希
        $hash = $this->sm3->digest($data, 1); // 1表示返回十六进制格式
        
        // 再次调用进度回调表示完成
        if ($progressCallback !== null) {
            $progressCallback(100, $fileSize, $fileSize);
        }
        
        return $hash;
    }

    /**
     * 解密数据（不适用于哈希算法）
     *
     * @param string $data 要解密的数据
     * @param array $options 解密选项
     * @return CryptoResult 解密结果
     */
    public function decrypt(string $data, array $options = []): CryptoResult
    {
        $result = new CryptoResult();
        $result->success = false;
        $result->error = "SM3是哈希算法，不支持解密操作";
        return $result;
    }

    /**
     * 比较两个哈希值
     *
     * @param string $hash1 哈希值1
     * @param string $hash2 哈希值2
     * @return array 比较结果和详细信息
     */
    public function compareHashes(string $hash1, string $hash2): array
    {
        // 清理哈希值（移除空格和换行符）
        $cleanHash1 = preg_replace('/\s+/', '', strtolower($hash1));
        $cleanHash2 = preg_replace('/\s+/', '', strtolower($hash2));
        
        // 转换为小写进行比较
        $isEqual = $cleanHash1 === $cleanHash2;
        
        // 返回详细比较结果
        return [
            'equal' => $isEqual,
            'hash1' => $cleanHash1,
            'hash2' => $cleanHash2,
            'length1' => strlen($cleanHash1),
            'length2' => strlen($cleanHash2)
        ];
    }

    /**
     * 复制哈希值到剪贴板
     *
     * @param string $hash 哈希值
     * @return bool 是否成功
     */
    public function copyToClipboard(string $hash): bool
    {
        try {
            // 在不同操作系统上使用不同的剪贴板命令
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows系统
                $process = proc_open('clip', [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ], $pipes);
                
                if (is_resource($process)) {
                    fwrite($pipes[0], $hash);
                    fclose($pipes[0]);
                    proc_close($process);
                    return true;
                }
            } elseif (PHP_OS_FAMILY === 'Darwin') {
                // macOS系统
                $process = proc_open('pbcopy', [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ], $pipes);
                
                if (is_resource($process)) {
                    fwrite($pipes[0], $hash);
                    fclose($pipes[0]);
                    proc_close($process);
                    return true;
                }
            } elseif (PHP_OS_FAMILY === 'Linux') {
                // Linux系统
                $process = proc_open('xclip -selection clipboard', [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ], $pipes);
                
                if (is_resource($process)) {
                    fwrite($pipes[0], $hash);
                    fclose($pipes[0]);
                    proc_close($process);
                    return true;
                }
            }
            
            // 如果系统命令不可用，返回false
            return false;
        } catch (\Exception $e) {
            // 记录错误但不抛出异常
            error_log("复制到剪贴板失败: " . $e->getMessage());
            return false;
        }
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