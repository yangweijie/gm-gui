<?php
namespace Yangweijie\GmGui\Utils;

use Exception;
use RuntimeException;

/**
 * 纯 PHP 解析 PKCS#8 PEM 提取 SM2 私钥 d 值
 * 不依赖 OpenSSL 命令行，只需要 PHP 的数学扩展
 */
class Sm2PrivateKeyParserUtils
{
    /**
     * 从 PKCS#8 PEM 中提取 SM2 私钥 d 值（16进制）
     */
    public static function extractPrivateKeyFromPkcs8(string $pem): string
    {
        // 1. 解码 PEM 格式
        $der = self::pemToDer($pem);

        // 2. 解析 ASN.1 DER 结构
        $data = self::parseAsn1Der($der);

        // 3. 提取私钥数据
        return self::extractSm2PrivateKey($data);
    }

    /**
     * PEM 转换为 DER
     */
    private static function pemToDer(string $pem): string
    {
        // 移除 PEM 头尾和空白字符
        $pem = preg_replace('/-----(BEGIN|END)[\w\s]+-----/', '', $pem);
        $pem = preg_replace('/\s+/', '', $pem);

        // Base64 解码
        $der = base64_decode($pem);
        if ($der === false) {
            throw new RuntimeException('Base64 解码失败');
        }

        return $der;
    }

    /**
     * 简单的 ASN.1 DER 解析器
     */
    private static function parseAsn1Der(string $der): array
    {
        $offset = 0;
        return self::parseAsn1Value($der, $offset);
    }

    /**
     * 解析单个 ASN.1 值
     */
    private static function parseAsn1Value(string $der, int &$offset)
    {
        if ($offset >= strlen($der)) {
            throw new RuntimeException('ASN.1 解析越界');
        }

        $tag = ord($der[$offset++]);
        $length = self::parseAsn1Length($der, $offset);

        $value = substr($der, $offset, $length);
        $offset += $length;

        // 检查是否是序列
        if (($tag & 0x1F) === 0x10) {
            return self::parseAsn1Sequence($value);
        }

        // 检查是否是八位组串
        if (($tag & 0x1F) === 0x04) {
            return $value;
        }

        // 检查是否是整数
        if (($tag & 0x1F) === 0x02) {
            return $value;
        }

        return $value;
    }

    /**
     * 解析 ASN.1 长度字段
     */
    private static function parseAsn1Length(string $der, int &$offset): int
    {
        $length = ord($der[$offset++]);

        if ($length & 0x80) {
            $numBytes = $length & 0x7F;
            $length = 0;
            for ($i = 0; $i < $numBytes; $i++) {
                $length = ($length << 8) | ord($der[$offset++]);
            }
        }

        return $length;
    }

    /**
     * 解析 ASN.1 序列
     */
    private static function parseAsn1Sequence(string $data): array
    {
        $result = [];
        $offset = 0;

        while ($offset < strlen($data)) {
            try {
                $result[] = self::parseAsn1Value($data, $offset);
            } catch (Exception $e) {
                break;
            }
        }

        return $result;
    }

    /**
     * 从解析的数据中提取 SM2 私钥
     */
    private static function extractSm2PrivateKey(array $data): string
    {
        // PKCS#8 结构通常是:
        // Sequence[
        //   Integer(version = 0),
        //   Sequence[OID, params],
        //   OctetString(privateKey)
        // ]

        if (count($data) < 3) {
            throw new RuntimeException('无效的 PKCS#8 结构');
        }

        // 私钥在第三个元素（八位组串）中
        $privateKeyOctet = $data[2];

        if (!is_string($privateKeyOctet)) {
            throw new RuntimeException('私钥数据不是字符串');
        }

        // EC 私钥的结构通常是:
        // Sequence[
        //   Integer(version = 1),
        //   OctetString(privateKey d)
        //   [optional parameters]
        // ]
        $privateKeyData = self::parseAsn1Der($privateKeyOctet);

        if (count($privateKeyData) < 2) {
            throw new RuntimeException('无效的 EC 私钥结构');
        }

        // 私钥 d 值在第二个元素中
        $dValue = $privateKeyData[1];

        if (!is_string($dValue)) {
            throw new RuntimeException('私钥 d 值不是字符串');
        }

        // 转换为16进制
        $hex = bin2hex($dValue);

        // 清理前导零并填充到64字符
        $hex = ltrim($hex, '0');
        $hex = str_pad($hex, 64, '0', STR_PAD_LEFT);

        return substr($hex, -64);
    }
}