<?php

use Yangweijie\GmGui\Utils\Validator;

test('validator can validate sm2 private key length in hex format', function () {
    // 有效的32字节SM2私钥（64个十六进制字符）
    $validPrivateKey = '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef';
    
    $result = Validator::validateSm2KeyLength($validPrivateKey, 'hex');
    
    expect($result)->toBeTrue();
});

test('validator can validate sm2 public key length in hex format with 04 prefix', function () {
    // 有效的65字节SM2公钥（130个十六进制字符），以'04'开头
    $validPublicKey = '04' . str_repeat('a', 128);
    
    $result = Validator::validateSm2KeyLength($validPublicKey, 'hex');
    
    expect($result)->toBeTrue();
});

test('validator can validate sm2 public key length in hex format without 04 prefix', function () {
    // 有效的64字节SM2公钥坐标（128个十六进制字符），不带'04'前缀
    $validPublicKey = str_repeat('a', 128);
    
    $result = Validator::validateSm2KeyLength($validPublicKey, 'hex');
    
    expect($result)->toBeTrue();
});

test('validator rejects invalid sm2 key length in hex format', function () {
    // 无效的密钥长度
    $invalidKey = '1234567890abcdef'; // 16个字符，8字节
    
    $result = Validator::validateSm2KeyLength($invalidKey, 'hex');
    
    expect($result)->toBeFalse();
});

test('validator rejects non-hex sm2 key', function () {
    // 包含非十六进制字符的密钥
    $invalidKey = '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdeg'; // 包含'g'
    
    $result = Validator::validateSm2KeyLength($invalidKey, 'hex');
    
    expect($result)->toBeFalse();
});

test('validator can validate sm2 key in base64 format', function () {
    // 有效的32字节Base64编码的私钥
    $validPrivateKey = base64_encode(random_bytes(32));
    
    $result = Validator::validateSm2KeyLength($validPrivateKey, 'base64');
    
    expect($result)->toBeTrue();
});

test('validator can validate sm2 public key in base64 format', function () {
    // 有效的65字节Base64编码的公钥
    $validPublicKey = base64_encode(random_bytes(65));
    
    $result = Validator::validateSm2KeyLength($validPublicKey, 'base64');
    
    expect($result)->toBeTrue();
});

test('validator rejects invalid base64 sm2 key', function () {
    // 无效的Base64字符串
    $invalidKey = 'invalid_base64!!!';
    
    $result = Validator::validateSm2KeyLength($invalidKey, 'base64');
    
    expect($result)->toBeFalse();
});

test('validator rejects unsupported format for sm2 key', function () {
    $key = '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef';
    
    $result = Validator::validateSm2KeyLength($key, 'unsupported');
    
    expect($result)->toBeFalse();
});

test('validator can validate sm4 key length in hex format', function () {
    // 有效的16字节SM4密钥（32个十六进制字符）
    $validKey = '1234567890abcdef1234567890abcdef';
    
    $result = Validator::validateSm4KeyLength($validKey, 'hex');
    
    expect($result)->toBeTrue();
});

test('validator rejects invalid sm4 key length in hex format', function () {
    // 无效的密钥长度
    $invalidKey = '1234567890abcdef'; // 16个字符，8字节
    
    $result = Validator::validateSm4KeyLength($invalidKey, 'hex');
    
    expect($result)->toBeFalse();
});

test('validator rejects non-hex sm4 key', function () {
    // 包含非十六进制字符的密钥
    $invalidKey = '1234567890abcdef1234567890abcdefg'; // 包含'g'
    
    $result = Validator::validateSm4KeyLength($invalidKey, 'hex');
    
    expect($result)->toBeFalse();
});

test('validator can validate sm4 key in base64 format', function () {
    // 有效的16字节Base64编码的密钥
    $validKey = base64_encode(random_bytes(16));
    
    $result = Validator::validateSm4KeyLength($validKey, 'base64');
    
    expect($result)->toBeTrue();
});

test('validator rejects invalid base64 sm4 key', function () {
    // 无效的Base64字符串
    $invalidKey = 'invalid_base64!!!';
    
    $result = Validator::validateSm4KeyLength($invalidKey, 'base64');
    
    expect($result)->toBeFalse();
});

test('validator rejects unsupported format for sm4 key', function () {
    $key = '1234567890abcdef1234567890abcdef';
    
    $result = Validator::validateSm4KeyLength($key, 'unsupported');
    
    expect($result)->toBeFalse();
});

test('validator can validate iv length in hex format', function () {
    // 有效的16字节IV（32个十六进制字符）
    $validIv = '1234567890abcdef1234567890abcdef';
    
    $result = Validator::validateIvLength($validIv, 'hex');
    
    expect($result)->toBeTrue();
});

test('validator rejects invalid iv length in hex format', function () {
    // 无效的IV长度
    $invalidIv = '1234567890abcdef'; // 16个字符，8字节
    
    $result = Validator::validateIvLength($invalidIv, 'hex');
    
    expect($result)->toBeFalse();
});

test('validator rejects non-hex iv', function () {
    // 包含非十六进制字符的IV
    $invalidIv = '1234567890abcdef1234567890abcdefg'; // 包含'g'
    
    $result = Validator::validateIvLength($invalidIv, 'hex');
    
    expect($result)->toBeFalse();
});

test('validator can validate iv in base64 format', function () {
    // 有效的16字节Base64编码的IV
    $validIv = base64_encode(random_bytes(16));
    
    $result = Validator::validateIvLength($validIv, 'base64');
    
    expect($result)->toBeTrue();
});

test('validator rejects invalid base64 iv', function () {
    // 无效的Base64字符串
    $invalidIv = 'invalid_base64!!!';
    
    $result = Validator::validateIvLength($invalidIv, 'base64');
    
    expect($result)->toBeFalse();
});

test('validator rejects unsupported format for iv', function () {
    $iv = '1234567890abcdef1234567890abcdef';
    
    $result = Validator::validateIvLength($iv, 'unsupported');
    
    expect($result)->toBeFalse();
});

test('validator can validate non-empty data', function () {
    $data = 'Hello, World!';
    
    $result = Validator::validateNotEmpty($data);
    
    expect($result)->toBeTrue();
});

test('validator can validate empty data', function () {
    $data = '';
    
    $result = Validator::validateNotEmpty($data);
    
    expect($result)->toBeFalse();
});

test('validator can validate whitespace-only data', function () {
    $data = '   ';
    
    $result = Validator::validateNotEmpty($data);
    
    expect($result)->toBeFalse();
});

test('validator can validate hex format', function () {
    $data = '1234567890abcdefABCDEF';
    
    $result = Validator::validateFormat($data, 'hex');
    
    expect($result)->toBeTrue();
});

test('validator rejects non-hex format', function () {
    $data = '1234567890abcdefG'; // 包含'G'
    
    $result = Validator::validateFormat($data, 'hex');
    
    expect($result)->toBeFalse();
});

test('validator can validate base64 format', function () {
    $data = base64_encode('Hello, World!');
    
    $result = Validator::validateFormat($data, 'base64');
    
    expect($result)->toBeTrue();
});

test('validator can validate empty base64 format', function () {
    $data = '';
    
    $result = Validator::validateFormat($data, 'base64');
    
    expect($result)->toBeTrue();
});

test('validator rejects invalid base64 format', function () {
    $data = 'invalid_base64!!!';
    
    $result = Validator::validateFormat($data, 'base64');
    
    expect($result)->toBeFalse();
});

test('validator accepts unsupported format', function () {
    $data = 'any_string';
    
    $result = Validator::validateFormat($data, 'unsupported');
    
    expect($result)->toBeTrue();
});