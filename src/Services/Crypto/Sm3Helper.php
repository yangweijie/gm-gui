<?php

namespace Yangweijie\GmGui\Services\Crypto;

use Yangweijie\GmGui\Utils\FormatConverter;

class Sm3Helper
{
    /**
     * 比较两个哈希值
     *
     * @param string $hash1 哈希值1
     * @param string $hash2 哈希值2
     * @param bool $caseSensitive 是否区分大小写
     * @return bool 是否相等
     */
    public static function compareHashes(string $hash1, string $hash2, bool $caseSensitive = false): bool
    {
        // 标准化哈希值（移除空格和换行符）
        $hash1 = preg_replace('/\s+/', '', $hash1);
        $hash2 = preg_replace('/\s+/', '', $hash2);
        
        if (!$caseSensitive) {
            $hash1 = strtolower($hash1);
            $hash2 = strtolower($hash2);
        }
        
        return $hash1 === $hash2;
    }

    /**
     * 格式化哈希值显示
     *
     * @param string $hash 哈希值
     * @param int $chunkLength 分组长度
     * @param string $separator 分隔符
     * @return string 格式化后的哈希值
     */
    public static function formatHash(string $hash, int $chunkLength = 4, string $separator = ' '): string
    {
        // 移除现有空格
        $hash = preg_replace('/\s+/', '', $hash);
        
        // 转换为大写
        $hash = strtoupper($hash);
        
        // 按指定长度分组
        $chunks = str_split($hash, $chunkLength);
        
        return implode($separator, $chunks);
    }

    /**
     * 验证哈希值格式
     *
     * @param string $hash 哈希值
     * @param string $format 格式类型
     * @return bool 是否有效
     */
    public static function validateHashFormat(string $hash, string $format = 'hex'): bool
    {
        // 移除空格和换行符
        $hash = preg_replace('/\s+/', '', $hash);
        
        switch ($format) {
            case 'hex':
                return ctype_xdigit($hash) && strlen($hash) === 64;
            case 'base64':
                return $hash === '' || (base64_decode($hash, true) !== false);
            default:
                return true;
        }
    }

    /**
     * 将哈希值复制到剪贴板（模拟实现）
     *
     * @param string $hash 哈希值
     * @return bool 是否成功
     */
    public static function copyToClipboard(string $hash): bool
    {
        // 在实际的桌面应用中，这里会调用系统API将哈希值复制到剪贴板
        // 目前只是模拟实现
        if (function_exists('clipboard_set')) {
            return clipboard_set($hash);
        }
        
        // 如果没有剪贴板函数，返回true表示在UI层处理
        return true;
    }
}