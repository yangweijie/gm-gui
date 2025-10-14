<?php

namespace Yangweijie\GmGui\Services;

use Yangweijie\GmGui\Exceptions\CryptoException;

class FileDragDropService
{
    /**
     * 处理拖拽的文件
     *
     * @param array $filePaths 文件路径数组
     * @param string $operation 操作类型（encrypt, decrypt, hash等）
     * @param array $options 操作选项
     * @return array 处理结果
     */
    public function handleDroppedFiles(array $filePaths, string $operation, array $options = []): array
    {
        $results = [];
        
        foreach ($filePaths as $filePath) {
            try {
                // 检查文件是否存在
                if (!file_exists($filePath)) {
                    $results[] = [
                        'file' => $filePath,
                        'success' => false,
                        'error' => '文件不存在'
                    ];
                    continue;
                }
                
                // 检查文件是否可读
                if (!is_readable($filePath)) {
                    $results[] = [
                        'file' => $filePath,
                        'success' => false,
                        'error' => '文件不可读'
                    ];
                    continue;
                }
                
                // 根据操作类型处理文件
                $result = $this->processFile($filePath, $operation, $options);
                $results[] = array_merge(['file' => $filePath], $result);
                
            } catch (\Exception $e) {
                $results[] = [
                    'file' => $filePath,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * 处理单个文件
     *
     * @param string $filePath 文件路径
     * @param string $operation 操作类型
     * @param array $options 操作选项
     * @return array 处理结果
     */
    protected function processFile(string $filePath, string $operation, array $options): array
    {
        // 这个方法需要在具体的加密服务中实现
        // 这里只是一个框架示例
        switch ($operation) {
            case 'encrypt':
                return $this->encryptFile($filePath, $options);
            case 'decrypt':
                return $this->decryptFile($filePath, $options);
            case 'hash':
                return $this->hashFile($filePath, $options);
            default:
                throw new \Exception("不支持的操作类型: {$operation}");
        }
    }

    /**
     * 加密文件（框架示例）
     *
     * @param string $filePath 文件路径
     * @param array $options 加密选项
     * @return array 加密结果
     */
    protected function encryptFile(string $filePath, array $options): array
    {
        // 在实际实现中，这里会调用具体的加密服务
        // 这里只是一个框架示例
        return [
            'success' => true,
            'outputFile' => $filePath . '.encrypted',
            'message' => '文件加密成功'
        ];
    }

    /**
     * 解密文件（框架示例）
     *
     * @param string $filePath 文件路径
     * @param array $options 解密选项
     * @return array 解密结果
     */
    protected function decryptFile(string $filePath, array $options): array
    {
        // 在实际实现中，这里会调用具体的解密服务
        // 这里只是一个框架示例
        return [
            'success' => true,
            'outputFile' => preg_replace('/\.encrypted$/', '', $filePath),
            'message' => '文件解密成功'
        ];
    }

    /**
     * 计算文件哈希（框架示例）
     *
     * @param string $filePath 文件路径
     * @param array $options 哈希选项
     * @return array 哈希结果
     */
    protected function hashFile(string $filePath, array $options): array
    {
        // 在实际实现中，这里会调用具体的哈希服务
        // 这里只是一个框架示例
        return [
            'success' => true,
            'hash' => str_repeat('0', 64), // 示例哈希值
            'message' => '哈希计算成功'
        ];
    }

    /**
     * 批量处理文件
     *
     * @param array $filePaths 文件路径数组
     * @param string $operation 操作类型
     * @param array $options 操作选项
     * @param callable|null $progressCallback 进度回调函数
     * @return array 处理结果
     */
    public function batchProcessFiles(
        array $filePaths, 
        string $operation, 
        array $options = [], 
        ?callable $progressCallback = null
    ): array {
        $results = [];
        $totalFiles = count($filePaths);
        $processedFiles = 0;
        
        foreach ($filePaths as $filePath) {
            try {
                // 检查文件是否存在
                if (!file_exists($filePath)) {
                    $results[] = [
                        'file' => $filePath,
                        'success' => false,
                        'error' => '文件不存在'
                    ];
                    $processedFiles++;
                    continue;
                }
                
                // 检查文件是否可读
                if (!is_readable($filePath)) {
                    $results[] = [
                        'file' => $filePath,
                        'success' => false,
                        'error' => '文件不可读'
                    ];
                    $processedFiles++;
                    continue;
                }
                
                // 根据操作类型处理文件
                $result = $this->processFile($filePath, $operation, $options);
                $results[] = array_merge(['file' => $filePath], $result);
                
            } catch (\Exception $e) {
                $results[] = [
                    'file' => $filePath,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            } finally {
                $processedFiles++;
                
                // 调用进度回调函数
                if ($progressCallback) {
                    $progressCallback($processedFiles, $totalFiles, $filePath);
                }
            }
        }
        
        return $results;
    }

    /**
     * 验证文件类型
     *
     * @param string $filePath 文件路径
     * @param array $allowedTypes 允许的文件类型
     * @return bool 是否允许
     */
    public function validateFileType(string $filePath, array $allowedTypes): bool
    {
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($fileExtension, $allowedTypes);
    }

    /**
     * 获取文件类型信息
     *
     * @param string $filePath 文件路径
     * @return array 文件类型信息
     */
    public function getFileType(string $filePath): array
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        
        return [
            'mimeType' => $mimeType,
            'extension' => $extension,
            'size' => filesize($filePath)
        ];
    }
}