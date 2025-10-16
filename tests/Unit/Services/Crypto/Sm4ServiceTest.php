<?php

use Yangweijie\GmGui\Services\Crypto\Sm4Service;
use Yangweijie\GmGui\Models\CryptoResult;
use Yangweijie\GmGui\Exceptions\CryptoException;

// 创建测试用的SM4服务实例
beforeEach(function () {
    $this->sm4Service = new Sm4Service();
});

test('sm4 service can be created', function () {
    expect($this->sm4Service)->toBeInstanceOf(Sm4Service::class);
});

test('sm4 service can encrypt data with cbc mode', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    $options = [
        'mode' => 'sm4-cbc',
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm4Service->encrypt($data, $options);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect($result->format)->toBe('hex');
    expect($result->executionTime)->toBeGreaterThan(0);
    expect($result->metadata)->toHaveKey('generatedIv');
    expect(strlen($result->metadata['generatedIv']))->toBe(32); // 16字节的十六进制表示
});

test('sm4 service can decrypt data with cbc mode', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    $iv = 'fedcba9876543210fedcba9876543210'; // 16字节的IV
    
    // 先加密数据
    $encryptOptions = [
        'mode' => 'sm4-cbc',
        'iv' => $iv,
        'outputFormat' => 'hex'
    ];
    
    $encryptResult = $this->sm4Service->encrypt($data, $encryptOptions);
    
    expect($encryptResult->success)->toBeTrue();
    
    // 然后解密数据
    $decryptOptions = [
        'mode' => 'sm4-cbc',
        'iv' => $iv,
        'inputFormat' => 'hex'
    ];
    
    $decryptResult = $this->sm4Service->decrypt($encryptResult->data, $decryptOptions);
    
    expect($decryptResult)->toBeInstanceOf(CryptoResult::class);
    expect($decryptResult->success)->toBeTrue();
    expect($decryptResult->data)->toBeString();
    expect($decryptResult->data)->toBe($data);
    expect($decryptResult->format)->toBe('raw');
    expect($decryptResult->executionTime)->toBeGreaterThan(0);
});

test('sm4 service can encrypt data with ecb mode', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    $options = [
        'mode' => 'sm4-ecb',
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm4Service->encrypt($data, $options);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect($result->format)->toBe('hex');
    expect($result->executionTime)->toBeGreaterThan(0);
});

test('sm4 service can decrypt data with ecb mode', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    
    // 先加密数据
    $encryptOptions = [
        'mode' => 'sm4-ecb',
        'outputFormat' => 'hex'
    ];
    
    $encryptResult = $this->sm4Service->encrypt($data, $encryptOptions);
    
    expect($encryptResult->success)->toBeTrue();
    
    // 然后解密数据
    $decryptOptions = [
        'mode' => 'sm4-ecb',
        'inputFormat' => 'hex'
    ];
    
    $decryptResult = $this->sm4Service->decrypt($encryptResult->data, $decryptOptions);
    
    expect($decryptResult)->toBeInstanceOf(CryptoResult::class);
    expect($decryptResult->success)->toBeTrue();
    expect($decryptResult->data)->toBeString();
    expect($decryptResult->data)->toBe($data);
    expect($decryptResult->format)->toBe('raw');
    expect($decryptResult->executionTime)->toBeGreaterThan(0);
});

test('sm4 service throws exception for empty data encryption', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = '';
    $options = [
        'mode' => 'sm4-cbc',
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm4Service->encrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('数据不能为空');
});

test('sm4 service throws exception when key not set', function () {
    $data = 'Hello, World!';
    $options = [
        'mode' => 'sm4-cbc',
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm4Service->encrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('未设置加密密钥');
});

test('sm4 service throws exception for invalid key length', function () {
    // 测试在setKey时捕获无效长度的密钥
    $key = hex2bin('0123456789abcdef'); // 8字节的二进制密钥
    
    // 期望在设置密钥时就抛出异常
    expect(fn() => $this->sm4Service->setKey($key))->toThrow(\Exception::class, '秘钥长度为16位');
});

test('sm4 service throws exception for empty data decryption', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = '';
    $options = [
        'mode' => 'sm4-cbc',
        'iv' => 'fedcba9876543210fedcba9876543210',
        'inputFormat' => 'hex'
    ];
    
    $result = $this->sm4Service->decrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('数据不能为空');
});

test('sm4 service throws exception when key not set for decryption', function () {
    $data = 'test_encrypted_data';
    $options = [
        'mode' => 'sm4-cbc',
        'iv' => 'fedcba9876543210fedcba9876543210',
        'inputFormat' => 'hex'
    ];
    
    $result = $this->sm4Service->decrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('未设置解密密钥');
});

test('sm4 service can get supported formats', function () {
    $formats = $this->sm4Service->getSupportedFormats();
    
    expect($formats)->toBeArray();
    expect($formats)->toContain('hex');
    expect($formats)->toContain('base64');
    expect($formats)->toContain('raw');
});

test('sm4 service can validate hex input', function () {
    $result = $this->sm4Service->validateInput('0123456789abcdef', ['format' => 'hex']);
    
    expect($result)->toBeTrue();
});

test('sm4 service can validate base64 input', function () {
    $result = $this->sm4Service->validateInput('SGVsbG8sIFdvcmxkIQ==', ['format' => 'base64']);
    
    expect($result)->toBeTrue();
});

test('sm4 service can encrypt with base64 output', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    $iv = 'fedcba9876543210fedcba9876543210'; // 16字节的IV
    
    $options = [
        'mode' => 'sm4-cbc',
        'iv' => $iv,
        'outputFormat' => 'base64'
    ];
    
    $result = $this->sm4Service->encrypt($data, $options);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect($result->format)->toBe('base64');
    expect($result->executionTime)->toBeGreaterThan(0);
});

test('sm4 service can decrypt base64 input', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    $iv = 'fedcba9876543210fedcba9876543210'; // 16字节的IV
    
    // 先加密数据为base64格式
    $encryptOptions = [
        'mode' => 'sm4-cbc',
        'iv' => $iv,
        'outputFormat' => 'base64'
    ];
    
    $encryptResult = $this->sm4Service->encrypt($data, $encryptOptions);
    
    expect($encryptResult->success)->toBeTrue();
    
    // 然后解密base64格式的数据
    $decryptOptions = [
        'mode' => 'sm4-cbc',
        'iv' => $iv,
        'inputFormat' => 'base64'
    ];
    
    $decryptResult = $this->sm4Service->decrypt($encryptResult->data, $decryptOptions);
    
    expect($decryptResult)->toBeInstanceOf(CryptoResult::class);
    expect($decryptResult->success)->toBeTrue();
    expect($decryptResult->data)->toBeString();
    expect($decryptResult->data)->toBe($data);
    expect($decryptResult->format)->toBe('raw');
});

test('sm4 service can encrypt data with gcm mode', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    $iv = '0123456789abcdef01234567'; // 12字节的IV (24个十六进制字符)
    
    $options = [
        'mode' => 'sm4-gcm',
        'iv' => $iv,
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm4Service->encrypt($data, $options);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect($result->format)->toBe('hex');
    expect($result->executionTime)->toBeGreaterThan(0);
    expect($result->metadata)->toHaveKey('iv');
    expect(strlen($result->metadata['iv']))->toBe(24); // 12字节的十六进制表示
    expect($result->metadata)->toHaveKey('mode');
    expect($result->metadata['mode'])->toBe('sm4-gcm');
    expect($result->metadata)->toHaveKey('tagLength');
    expect($result->metadata['tagLength'])->toBe(16); // 认证标签长度为16字节
});

test('sm4 service can decrypt data with gcm mode', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    $iv = '0123456789abcdef01234567'; // 12字节的IV (24个十六进制字符)
    
    // 先加密数据
    $encryptOptions = [
        'mode' => 'sm4-gcm',
        'iv' => $iv,
        'outputFormat' => 'hex'
    ];
    
    $encryptResult = $this->sm4Service->encrypt($data, $encryptOptions);
    
    expect($encryptResult->success)->toBeTrue();
    
    // 然后解密数据
    $decryptOptions = [
        'mode' => 'sm4-gcm',
        'iv' => $iv,
        'inputFormat' => 'hex'
    ];
    
    $decryptResult = $this->sm4Service->decrypt($encryptResult->data, $decryptOptions);
    
    expect($decryptResult)->toBeInstanceOf(CryptoResult::class);
    expect($decryptResult->success)->toBeTrue();
    expect($decryptResult->data)->toBeString();
    expect($decryptResult->data)->toBe($data);
    expect($decryptResult->format)->toBe('raw');
    expect($decryptResult->executionTime)->toBeGreaterThan(0);
});

test('sm4 service can encrypt and decrypt with gcm mode using base64', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    $iv = '0123456789abcdef01234567'; // 12字节的IV (24个十六进制字符)
    
    // 先加密数据为base64格式
    $encryptOptions = [
        'mode' => 'sm4-gcm',
        'iv' => $iv,
        'outputFormat' => 'base64'
    ];
    
    $encryptResult = $this->sm4Service->encrypt($data, $encryptOptions);
    
    expect($encryptResult->success)->toBeTrue();
    
    // 然后解密base64格式的数据
    $decryptOptions = [
        'mode' => 'sm4-gcm',
        'iv' => $iv,
        'inputFormat' => 'base64'
    ];
    
    $decryptResult = $this->sm4Service->decrypt($encryptResult->data, $decryptOptions);
    
    expect($decryptResult)->toBeInstanceOf(CryptoResult::class);
    expect($decryptResult->success)->toBeTrue();
    expect($decryptResult->data)->toBeString();
    expect($decryptResult->data)->toBe($data);
    expect($decryptResult->format)->toBe('raw');
});

test('sm4 service throws exception for invalid iv length in gcm mode', function () {
    // 设置16字节的密钥（32个十六进制字符）
    $key = hex2bin('0123456789abcdef0123456789abcdef');
    $this->sm4Service->setKey($key);
    
    $data = 'Hello, World!';
    $iv = 'fedcba9876543210fedcba9876543210'; // 16字节的IV (应该为12字节)
    
    $options = [
        'mode' => 'sm4-gcm',
        'iv' => $iv,
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm4Service->encrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('GCM模式IV长度不正确，应为12字节');
});