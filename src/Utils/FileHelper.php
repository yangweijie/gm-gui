<?php

namespace Yangweijie\GmGui\Utils;

use Exception;

class FileHelper
{
    /**
     * 读取文件内容
     *
     * @param string $path 文件路径
     * @return string 文件内容
     * @throws Exception 文件读取失败时抛出异常
     */
    public static function readFile(string $path): string
    {
        if (!file_exists($path)) {
            throw new Exception("文件不存在: {$path}");
        }
        
        if (!is_readable($path)) {
            throw new Exception("文件不可读: {$path}");
        }
        
        $content = file_get_contents($path);
        if ($content === false) {
            throw new Exception("读取文件失败: {$path}");
        }
        
        return $content;
    }

    /**
     * 写入文件内容
     *
     * @param string $path 文件路径
     * @param string $content 文件内容
     * @return bool 是否写入成功
     * @throws Exception 文件写入失败时抛出异常
     */
    public static function writeFile(string $path, string $content): bool
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new Exception("创建目录失败: {$directory}");
            }
        }
        
        if (file_put_contents($path, $content) === false) {
            throw new Exception("写入文件失败: {$path}");
        }
        
        return true;
    }

    /**
     * 获取文件信息
     *
     * @param string $path 文件路径
     * @return array 文件信息
     */
    public static function getFileInfo(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }
        
        return [
            'size' => filesize($path),
            'modified' => filemtime($path),
            'permissions' => fileperms($path),
            'is_readable' => is_readable($path),
            'is_writable' => is_writable($path),
        ];
    }

    /**
     * 安全删除文件
     *
     * @param string $path 文件路径
     * @return bool 是否删除成功
     */
    public static function secureDelete(string $path): bool
    {
        if (!file_exists($path)) {
            return true;
        }
        
        // 简单的安全删除：覆盖文件内容后再删除
        $size = filesize($path);
        if ($size > 0) {
            $fp = fopen($path, 'r+');
            if ($fp !== false) {
                // 用随机数据覆盖文件内容
                fwrite($fp, str_repeat("\0", $size));
                fclose($fp);
            }
        }
        
        return unlink($path);
    }
}