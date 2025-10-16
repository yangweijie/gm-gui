<?php

namespace Yangweijie\GmGui\Utils;

class Validator
{
    /**
     * 验证SM2密钥长度
     *
     * @param string $key 密钥
     * @param string $format 密钥格式
     * @return bool 是否有效
     */
    public static function validateSm2KeyLength(string $key, string $format = 'hex'): bool
    {
        switch ($format) {
            case 'hex':
                // SM2私钥为32字节（64个十六进制字符）
                // SM2公钥为65字节（130个十六进制字符），以'04'开头
                if (strlen($key) === 64 && ctype_xdigit($key)) {
                    // 可能是私钥
                    return true;
                } elseif (strlen($key) === 130 && substr($key, 0, 2) === '04' && ctype_xdigit($key)) {
                    // 可能是公钥
                    return true;
                } elseif (strlen($key) === 128 && ctype_xdigit($key)) {
                    // 也可能是不带'04'前缀的公钥坐标
                    return true;
                }
                return false;
            case 'base64':
                // Base64编码的数据
                $decoded = base64_decode($key, true);
                if ($decoded === false) {
                    return false;
                }
                // Base64解码后的私钥为32字节，公钥为65字节
                return (strlen($decoded) === 32 || strlen($decoded) === 65);
            default:
                return false;
        }
    }

    /**
     * 验证SM4密钥长度
     *
     * @param string $key 密钥
     * @param string $format 密钥格式
     * @return bool 是否有效
     */
    public static function validateSm4KeyLength(string $key, string $format = 'hex'): bool
    {
        switch ($format) {
            case 'hex':
                // SM4密钥为16字节（32个十六进制字符）
                return strlen($key) === 32 && ctype_xdigit($key);
            case 'base64':
                // Base64编码的16字节数据
                $decoded = base64_decode($key, true);
                return $decoded !== false && strlen($decoded) === 16;
            default:
                return false;
        }
    }

    /**
     * 验证初始化向量长度
     *
     * @param string $iv 初始化向量
     * @param string $format IV格式
     * @param int $length 期望的长度（字节）
     * @return bool 是否有效
     */
    public static function validateIvLength(string $iv, string $format = 'hex', int $length = 16): bool
    {
        switch ($format) {
            case 'hex':
                // IV为指定字节长度（十六进制字符数为字节长度的2倍）
                return strlen($iv) === ($length * 2) && ctype_xdigit($iv);
            case 'base64':
                // Base64编码的数据
                $decoded = base64_decode($iv, true);
                return $decoded !== false && strlen($decoded) === $length;
            default:
                return false;
        }
    }

    /**
     * 验证数据是否为空
     *
     * @param string $data 数据
     * @return bool 是否为空
     */
    public static function validateNotEmpty(string $data): bool
    {
        return !empty(trim($data));
    }

    /**
     * 验证数据格式
     *
     * @param string $data 数据
     * @param string $format 格式
     * @return bool 是否有效
     */
    public static function validateFormat(string $data, string $format): bool
    {
        switch ($format) {
            case 'hex':
                return ctype_xdigit($data);
            case 'base64':
                return $data === '' || (base64_decode($data, true) !== false);
            default:
                return true;
        }
    }
}