<?php

namespace Yangweijie\GmGui\Interfaces;

use Yangweijie\GmGui\Models\CryptoResult;

interface CryptoServiceInterface
{
    /**
     * 加密数据
     *
     * @param string $data 要加密的数据
     * @param array $options 加密选项
     * @return CryptoResult 加密结果
     */
    public function encrypt(string $data, array $options = []): CryptoResult;

    /**
     * 解密数据
     *
     * @param string $data 要解密的数据
     * @param array $options 解密选项
     * @return CryptoResult 解密结果
     */
    public function decrypt(string $data, array $options = []): CryptoResult;

    /**
     * 获取支持的格式列表
     *
     * @return array 支持的格式列表
     */
    public function getSupportedFormats(): array;

    /**
     * 验证输入数据
     *
     * @param string $data 要验证的数据
     * @param array $options 验证选项
     * @return bool 验证结果
     */
    public function validateInput(string $data, array $options = []): bool;
}