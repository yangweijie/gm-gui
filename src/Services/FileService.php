<?php

namespace Yangweijie\GmGui\Services;

use Yangweijie\GmGui\Interfaces\FileServiceInterface;
use Yangweijie\GmGui\Utils\FileHelper;
use Yangweijie\GmGui\Exceptions\CryptoException;

class FileService implements FileServiceInterface
{
    /**
     * 读取文件内容
     *
     * @param string $path 文件路径
     * @return string 文件内容
     * @throws CryptoException
     */
    public function readFile(string $path): string
    {
        try {
            return FileHelper::readFile($path);
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("读取文件失败: " . $e->getMessage());
        }
    }

    /**
     * 写入文件内容
     *
     * @param string $path 文件路径
     * @param string $content 文件内容
     * @return bool 写入结果
     * @throws CryptoException
     */
    public function writeFile(string $path, string $content): bool
    {
        try {
            return FileHelper::writeFile($path, $content);
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("写入文件失败: " . $e->getMessage());
        }
    }

    /**
     * 选择文件（模拟实现）
     *
     * @param array $filters 文件过滤器
     * @return string|null 选择的文件路径
     */
    public function selectFile(array $filters = []): ?string
    {
        // 在实际的GUI应用中，这里会调用文件选择对话框
        // 目前只是模拟实现，返回null表示需要在UI层处理
        return null;
    }

    /**
     * 保存文件（模拟实现）
     *
     * @param string $defaultName 默认文件名
     * @param array $filters 文件过滤器
     * @return string|null 保存的文件路径
     */
    public function saveFile(string $defaultName, array $filters = []): ?string
    {
        // 在实际的GUI应用中，这里会调用文件保存对话框
        // 目前只是模拟实现，返回null表示需要在UI层处理
        return null;
    }

    /**
     * 获取文件信息
     *
     * @param string $path 文件路径
     * @return array 文件信息
     */
    public function getFileInfo(string $path): array
    {
        return FileHelper::getFileInfo($path);
    }

    /**
     * 复制文件
     *
     * @param string $source 源文件路径
     * @param string $destination 目标文件路径
     * @return bool 是否复制成功
     * @throws CryptoException
     */
    public function copyFile(string $source, string $destination): bool
    {
        try {
            if (!file_exists($source)) {
                throw new \Exception("源文件不存在: {$source}");
            }
            
            if (!is_readable($source)) {
                throw new \Exception("源文件不可读: {$source}");
            }
            
            $content = file_get_contents($source);
            if ($content === false) {
                throw new \Exception("读取源文件失败: {$source}");
            }
            
            $directory = dirname($destination);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    throw new \Exception("创建目录失败: {$directory}");
                }
            }
            
            if (file_put_contents($destination, $content) === false) {
                throw new \Exception("写入目标文件失败: {$destination}");
            }
            
            return true;
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("复制文件失败: " . $e->getMessage());
        }
    }

    /**
     * 移动文件
     *
     * @param string $source 源文件路径
     * @param string $destination 目标文件路径
     * @return bool 是否移动成功
     * @throws CryptoException
     */
    public function moveFile(string $source, string $destination): bool
    {
        try {
            if (!file_exists($source)) {
                throw new \Exception("源文件不存在: {$source}");
            }
            
            $directory = dirname($destination);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    throw new \Exception("创建目录失败: {$directory}");
                }
            }
            
            return rename($source, $destination);
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("移动文件失败: " . $e->getMessage());
        }
    }

    /**
     * 删除文件
     *
     * @param string $path 文件路径
     * @return bool 是否删除成功
     * @throws CryptoException
     */
    public function deleteFile(string $path): bool
    {
        try {
            if (!file_exists($path)) {
                return true; // 文件不存在，认为删除成功
            }
            
            return FileHelper::secureDelete($path);
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("删除文件失败: " . $e->getMessage());
        }
    }

    /**
     * 获取文件大小
     *
     * @param string $path 文件路径
     * @return int 文件大小（字节）
     * @throws CryptoException
     */
    public function getFileSize(string $path): int
    {
        try {
            if (!file_exists($path)) {
                throw new \Exception("文件不存在: {$path}");
            }
            
            return filesize($path);
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("获取文件大小失败: " . $e->getMessage());
        }
    }

    /**
     * 检查文件是否存在
     *
     * @param string $path 文件路径
     * @return bool 是否存在
     */
    public function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * 检查文件是否可读
     *
     * @param string $path 文件路径
     * @return bool 是否可读
     */
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    /**
     * 检查文件是否可写
     *
     * @param string $path 文件路径
     * @return bool 是否可写
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * 创建临时文件
     *
     * @param string $content 文件内容
     * @param string $prefix 文件名前缀
     * @return string 临时文件路径
     * @throws CryptoException
     */
    public function createTempFile(string $content = '', string $prefix = 'temp_'): string
    {
        try {
            $tempDir = sys_get_temp_dir();
            $tempFile = tempnam($tempDir, $prefix);
            
            if ($tempFile === false) {
                throw new \Exception("创建临时文件失败");
            }
            
            if (!empty($content)) {
                if (file_put_contents($tempFile, $content) === false) {
                    unlink($tempFile);
                    throw new \Exception("写入临时文件失败");
                }
            }
            
            return $tempFile;
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("创建临时文件失败: " . $e->getMessage());
        }
    }

    /**
     * 分块读取大文件
     *
     * @param string $path 文件路径
     * @param callable $callback 处理每个块的回调函数
     * @param int $chunkSize 块大小（字节）
     * @return void
     * @throws CryptoException
     */
    public function readChunked(string $path, callable $callback, int $chunkSize = 8192): void
    {
        try {
            if (!file_exists($path)) {
                throw new \Exception("文件不存在: {$path}");
            }
            
            $handle = fopen($path, 'rb');
            if ($handle === false) {
                throw new \Exception("打开文件失败: {$path}");
            }
            
            $chunkIndex = 0;
            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk !== false) {
                    $callback($chunk, $chunkIndex);
                    $chunkIndex++;
                }
            }
            
            fclose($handle);
        } catch (\Exception $e) {
            throw CryptoException::fileOperationError("分块读取文件失败: " . $e->getMessage());
        }
    }
}