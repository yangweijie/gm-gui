<?php

/**
 * 从 Base64 公钥中提取十六进制公钥
 *
 * @param string $publicKey Base64 编码的公钥
 * @return string 十六进制公钥（不带 04 前缀）
 */
function extractPublicKeyHex($publicKey)
{
    $der = base64_decode($publicKey);
    $offset = 0;

    // 跳过 SEQUENCE
    $offset++; // 0x30
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }

    // 跳过 SEQUENCE (算法标识)
    $offset++; // 0x30
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }
    $offset += $len; // 算法标识内容

    // 跳过 BIT STRING
    $offset++; // 0x03
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }
    $offset++; // 未使用位数

    // 跳过 04 前缀
    $offset++;

    // 提取公钥点 (64 bytes)
    $publicKeyHex = bin2hex(substr($der, $offset, 64));

    return $publicKeyHex;
}

/**
 * 从 Base64 私钥中提取十六进制私钥
 *
 * @param string $privateKey Base64 编码的私钥
 * @return string 十六进制私钥
 */
function extractPrivateKeyHex($privateKey)
{
    $der = base64_decode($privateKey);
    $offset = 0;

    // 跳过 SEQUENCE
    $offset++; // 0x30
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }

    // 跳过 INTEGER(0)
    $offset++; // 0x02
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }
    $offset += $len; // 值 (00)

    // 跳过 SEQUENCE (算法标识)
    $offset++; // 0x30
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }
    $offset += $len; // 算法标识内容

    // 跳过 OCTET STRING
    $offset++; // 0x04
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }

    // 跳过 SEQUENCE
    $offset++; // 0x30
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }

    // 跳过 INTEGER(1) - version
    $offset++; // 0x02
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }
    $offset += $len; // 值 (01)

    // 读取 OCTET STRING - 私钥
    $offset++; // 0x04
    $len = ord($der[$offset]);
    $offset++; // 长度
    if ($len & 0x80) {
        $n = $len & 0x7f;
        $offset += $n;
    }

    $privateKeyHex = bin2hex(substr($der, $offset, $len));

    // 去掉 00 前缀
    if (strlen($privateKeyHex) == 66 && substr($privateKeyHex, 0, 2) == '00') {
        $privateKeyHex = substr($privateKeyHex, 2);
    }

    return $privateKeyHex;
}

/**
 * 将十六进制公钥转换为 PEM 格式（用于生成密钥时）
 *
 * @param string $hexPublicKey
 * @return string DER 编码的公钥
 */
function hexToPemPublic($hexPublicKey)
{
    // 添加 04 前缀
    if (substr($hexPublicKey, 0, 2) != '04') {
        $hexPublicKey = '04' . $hexPublicKey;
    }

    $publicKeyBin = hex2bin($hexPublicKey);

    // 构造 DER 编码
    // OID for EC public key: 1.2.840.10045.2.1
    // OID for SM2 curve: 1.2.156.10197.1.301
    $oidEc = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01";
    $oidSm2 = "\x06\x08\x2a\x81\x1c\xcf\x55\x01\x82\x2d";

    $algorithmIdentifier = "\x30" . chr(strlen($oidEc) + strlen($oidSm2) + 4) .
        "\x02\x01\x01" . // version
        $oidEc . $oidSm2;

    $bitString = "\x03" . chr(strlen($publicKeyBin) + 1) . "\x00" . $publicKeyBin;

    $der = "\x30" . chr(strlen($algorithmIdentifier) + strlen($bitString)) .
        $algorithmIdentifier . $bitString;

    return $der;
}

/**
 * 将十六进制私钥转换为 PEM 格式（用于生成密钥时）
 *
 * @param string $hexPrivateKey
 * @param string $hexPublicKey
 * @return string DER 编码的私钥
 */
function hexToPemPrivate($hexPrivateKey, $hexPublicKey)
{
    // 添加 04 前缀到公钥
    if (substr($hexPublicKey, 0, 2) != '04') {
        $hexPublicKey = '04' . $hexPublicKey;
    }

    $privateKeyBin = hex2bin($hexPrivateKey);
    $publicKeyBin = hex2bin($hexPublicKey);

    // 构造 DER 编码
    $oidEc = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01";
    $oidSm2 = "\x06\x08\x2a\x81\x1c\xcf\x55\x01\x82\x2d";

    $algorithmIdentifier = "\x30" . chr(strlen($oidEc) + strlen($oidSm2) + 4) .
        "\x02\x01\x01" .
        $oidEc . $oidSm2;

    $octetString = "\x04" . chr(strlen($publicKeyBin)) . $publicKeyBin;

    // 私钥可能需要添加 00 前缀
    if (strlen($privateKeyBin) == 32) {
        $privateKeyBin = "\x00" . $privateKeyBin;
    }

    $privateKeyInteger = "\x02" . chr(strlen($privateKeyBin)) . $privateKeyBin;

    $version = "\x02\x01\x00";

    $der = "\x30" . chr(strlen($version) + strlen($privateKeyInteger) +
            strlen($algorithmIdentifier) + strlen($octetString)) .
        $version .
        $privateKeyInteger .
        $algorithmIdentifier .
        $octetString;

    return $der;
}