<?php

use Yangweijie\GmGui\Services\Crypto\Sm2Service;
use Yangweijie\GmGui\Models\CryptoResult;
use Yangweijie\GmGui\Exceptions\CryptoException;
use Yangweijie\GmGui\Services\KeyManagementService;

// 创建测试用的SM2服务实例
beforeEach(function () {
    $this->sm2Service = new Sm2Service();
});

test('sm2 service can be created', function () {
    expect($this->sm2Service)->toBeInstanceOf(Sm2Service::class);
});

test('sm2 service can encrypt data', function () {
    // 生成真实的密钥对用于测试
    $keyService = new KeyManagementService();
    $keyPair = $keyService->generateKeyPair();
    $publicKey = $keyPair->publicKey;
    $privateKey = $keyPair->privateKey;
    
    $data = 'Hello, World!';
    $options = [
        'publicKey' => $publicKey,
        'mode' => 'C1C3C2',
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm2Service->encrypt($data, $options);
    
    // 调试信息
    if (!$result->success) {
        echo "Encryption error: " . $result->error . "\n";
        echo "Public key: " . $publicKey . "\n";
        echo "Public key length: " . strlen($publicKey) . "\n";
    }
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect($result->format)->toBe('hex');
    expect($result->executionTime)->toBeGreaterThan(0);
});

test('sm2 service can decrypt data', function () {
    // 生成真实的密钥对用于测试
    $keyService = new KeyManagementService();
    $keyPair = $keyService->generateKeyPair();
    $publicKey = $keyPair->publicKey;
    $privateKey = $keyPair->privateKey;
    
    // 先加密数据
    $data = 'Hello, World!';
    $encryptOptions = [
        'publicKey' => $publicKey,
        'mode' => 'C1C3C2',  // 使用字符串而不是常量（与Sm2Service中一致）
        'outputFormat' => 'hex',
        'appendZeroFour' => false
    ];
    
    $encryptResult = $this->sm2Service->encrypt($data, $encryptOptions);
    
    // 检查加密是否成功
    if (!$encryptResult->success) {
        echo "Encryption error: " . $encryptResult->error . "\n";
        echo "Public key: " . $publicKey . "\n";
        echo "Public key length: " . strlen($publicKey) . "\n";
    }
    expect($encryptResult->success)->toBeTrue();
    
    // 然后解密数据
    $decryptOptions = [
        'privateKey' => $privateKey,
        'mode' => 'C1C3C2',  // 使用字符串而不是常量（与Sm2Service中一致）
        'inputFormat' => 'hex'
    ];
    
    $decryptResult = $this->sm2Service->decrypt($encryptResult->data, $decryptOptions);
    
    // 调试信息
    if (!$decryptResult->success) {
        echo "Decryption error: " . $decryptResult->error . "\n";
        echo "Private key: " . $privateKey . "\n";
        echo "Private key length: " . strlen($privateKey) . "\n";
        echo "Encrypted data: " . $encryptResult->data . "\n";
    }
    
    expect($decryptResult)->toBeInstanceOf(CryptoResult::class);
    expect($decryptResult->success)->toBeTrue();
    expect($decryptResult->data)->toBeString();
    expect($decryptResult->data)->toBe($data);
    expect($decryptResult->format)->toBe('raw');
    expect($decryptResult->executionTime)->toBeGreaterThan(0);
});

test('sm2 service can sign data', function () {
    // 使用固定的测试密钥对
    $publicKey = '046138c44feccd24f71def290344c1b4bcdcc2959d3585cc621af9089090bfe334d3d7e22da6477a5d48d25dc4eb94035ae614d2aac32761ba0501ed4b9f58c09';
    $privateKey = '21fbd478026e2d668e3570e514de0d312e443d1e294c1ca785dfbfb5f74de225';
    
    $data = 'Hello, World!';
    $options = [
        'privateKey' => $privateKey,
        'userId' => '1234567812345678',
        'outputFormat' => 'hex',
        'toRS' => false
    ];
    
    $result = $this->sm2Service->sign($data, $options);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect($result->format)->toBe('hex');
    expect($result->executionTime)->toBeGreaterThan(0);
});

test('sm2 service can verify signature', function () {
    // 生成真实的密钥对用于测试
    $keyService = new KeyManagementService();
    $keyPair = $keyService->generateKeyPair();
    $publicKey = $keyPair->publicKey;
    $privateKey = $keyPair->privateKey;
    
    // 先签名数据
    $data = 'Hello, World!';
    $signOptions = [
        'privateKey' => $privateKey,
        'userId' => '1234567812345678',
        'outputFormat' => 'hex',
        'toRS' => false
    ];
    
    $signResult = $this->sm2Service->sign($data, $signOptions);
    
    // 检查签名是否成功
    if (!$signResult->success) {
        echo "Sign error: " . $signResult->error . "\n";
        echo "Private key: " . $privateKey . "\n";
        echo "Private key length: " . strlen($privateKey) . "\n";
    }
    expect($signResult->success)->toBeTrue();
    
    // 然后验证签名
    $verifyOptions = [
        'publicKey' => $publicKey,
        'userId' => '1234567812345678',
        'signatureFormat' => 'hex'
    ];
    
    $verifyResult = $this->sm2Service->verify($data, $signResult->data, $verifyOptions);
    
    // 调试信息
    if (!$verifyResult->success) {
        echo "Verify error: " . $verifyResult->error . "\n";
        echo "Public key: " . $publicKey . "\n";
        echo "Public key length: " . strlen($publicKey) . "\n";
        echo "Signature: " . $signResult->data . "\n";
    }
    
    expect($verifyResult)->toBeInstanceOf(CryptoResult::class);
    expect($verifyResult->success)->toBeTrue();
    expect($verifyResult->data)->toBe('valid');
    expect($verifyResult->format)->toBe('verification');
    expect($verifyResult->executionTime)->toBeGreaterThan(0);
});

test('sm2 service throws exception for empty data encryption', function () {
    // 使用固定的测试密钥对
    $publicKey = '046138c44feccd24f71def290344c1b4bcdcc2959d3585cc621af9089090bfe334d3d7e22da6477a5d48d25dc4eb94035ae614d2aac32761ba0501ed4b9f58c09';
    
    $data = '';
    $options = [
        'publicKey' => $publicKey,
        'mode' => 'C1C3C2',
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm2Service->encrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('数据不能为空');
});

test('sm2 service throws exception for empty public key encryption', function () {
    $data = 'Hello, World!';
    $options = [
        'publicKey' => '',
        'mode' => 'C1C3C2',
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm2Service->encrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('公钥不能为空');
});

test('sm2 service throws exception for empty data decryption', function () {
    // 使用固定的测试私钥
    $privateKey = '21fbd478026e2d668e3570e514de0d312e443d1e294c1ca785dfbfb5f74de225';
    
    $data = '';
    $options = [
        'privateKey' => $privateKey,
        'mode' => 'C1C3C2',
        'inputFormat' => 'hex'
    ];
    
    $result = $this->sm2Service->decrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('数据不能为空');
});

test('sm2 service throws exception for empty private key decryption', function () {
    $data = 'test_encrypted_data';
    $options = [
        'privateKey' => '',
        'mode' => 'C1C3C2',
        'inputFormat' => 'hex'
    ];
    
    $result = $this->sm2Service->decrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('私钥不能为空');
});

test('sm2 service can get supported formats', function () {
    $formats = $this->sm2Service->getSupportedFormats();
    
    expect($formats)->toBeArray();
    expect($formats)->toContain('hex');
    expect($formats)->toContain('base64');
});

test('sm2 service can validate hex input', function () {
    $result = $this->sm2Service->validateInput('0123456789abcdef', ['format' => 'hex']);
    
    expect($result)->toBeTrue();
});

test('sm2 service can validate base64 input', function () {
    $result = $this->sm2Service->validateInput('SGVsbG8sIFdvcmxkIQ==', ['format' => 'base64']);
    
    expect($result)->toBeTrue();
});