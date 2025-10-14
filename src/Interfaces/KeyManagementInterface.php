<?php

namespace Yangweijie\GmGui\Interfaces;

use Yangweijie\GmGui\Models\KeyPair;

interface KeyManagementInterface
{
    /**
     * 生成密钥对
     *
     * @return KeyPair 生成的密钥对
     */
    public function generateKeyPair(): KeyPair;

    /**
     * 导入密钥
     *
     * @param string $keyData 密钥数据
     * @param string $format 密钥格式
     * @return KeyPair 导入的密钥对
     */
    public function importKey(string $keyData, string $format): KeyPair;

    /**
     * 导出密钥
     *
     * @param KeyPair $keyPair 密钥对
     * @param string $format 导出格式
     * @return string 导出的密钥数据
     */
    public function exportKey(KeyPair $keyPair, string $format): string;

    /**
     * 验证密钥
     *
     * @param string $keyData 密钥数据
     * @param string $format 密钥格式
     * @return bool 验证结果
     */
    public function validateKey(string $keyData, string $format): bool;

    /**
     * 转换密钥格式
     *
     * @param string $keyData 密钥数据
     * @param string $fromFormat 源格式
     * @param string $toFormat 目标格式
     * @return string 转换后的密钥数据
     */
    public function convertKeyFormat(string $keyData, string $fromFormat, string $toFormat): string;
}