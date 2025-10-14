<?php

namespace Yangweijie\GmGui\Utils;

class FormatConverter
{
    /**
     * 将十六进制字符串转换为二进制字符串
     *
     * @param string $hex 十六进制字符串
     * @return string 二进制字符串
     */
    public static function hexToBin(string $hex): string
    {
        return hex2bin($hex);
    }

    /**
     * 将二进制字符串转换为十六进制字符串
     *
     * @param string $bin 二进制字符串
     * @return string 十六进制字符串
     */
    public static function binToHex(string $bin): string
    {
        return bin2hex($bin);
    }

    /**
     * 将Base64字符串转换为二进制字符串
     *
     * @param string $base64 Base64字符串
     * @return string 二进制字符串
     */
    public static function base64ToBin(string $base64): string
    {
        return base64_decode($base64);
    }

    /**
     * 将二进制字符串转换为Base64字符串
     *
     * @param string $bin 二进制字符串
     * @return string Base64字符串
     */
    public static function binToBase64(string $bin): string
    {
        return base64_encode($bin);
    }

    /**
     * 检查字符串是否为有效的十六进制格式
     *
     * @param string $hex 待检查的字符串
     * @return bool 是否为有效的十六进制格式
     */
    public static function isValidHex(string $hex): bool
    {
        return ctype_xdigit($hex) && strlen($hex) % 2 === 0;
    }

    /**
     * 检查字符串是否为有效的Base64格式
     *
     * @param string $base64 待检查的字符串
     * @return bool 是否为有效的Base64格式
     */
    public static function isValidBase64(string $base64): bool
    {
        if ($base64 === '') {
            return true;
        }
        
        $decoded = base64_decode($base64, true);
        return $decoded !== false && base64_encode($decoded) === $base64;
    }
}