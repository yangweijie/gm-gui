<?php
namespace Yangweijie\GmGui\Utils;

use Exception;
use RuntimeException;
use Rtgm\util\MyAsn1;

/**
 * 纯 PHP 解析 PKCS#8 PEM 提取 SM2 私钥 d 值
 * 不依赖 OpenSSL 命令行，只需要 PHP 的数学扩展
 */
class Sm2PublicKeyParserUtil
{
    /**
     * 从 Base64 公钥中提取十六进制公钥
     * 使用 MyAsn1 类简化解析
     *
     * @param string $publicKey Base64 编码的公钥
     * @return string 十六进制公钥（不带 04 前缀）
     * @throws Exception
     */
    private function extractPublicKeyHex(string $publicKey): string
    {
        $der = base64_decode($publicKey);

        // 使用 MyAsn1 类解析
        $parsed = MyAsn1::decode($der, 'bin');

        // 解析结果: [0] => [算法标识], [1] => 公钥点（十六进制字符串，带 04 前缀）
        if (isset($parsed[1]) && is_string($parsed[1])) {
            $publicKeyHexWith04 = $parsed[1];

            // 去掉 04 前缀
            if (substr($publicKeyHexWith04, 0, 2) == '04') {
                $publicKeyHexWith04 = substr($publicKeyHexWith04, 2);
            }

            return $publicKeyHexWith04;
        }

        throw new Exception('无法解析公钥');
    }
}
