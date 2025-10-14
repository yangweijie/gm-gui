<?php

use Yangweijie\GmGui\Services\Crypto\Sm3Service;
use Yangweijie\GmGui\Models\CryptoResult;
use Yangweijie\GmGui\Exceptions\CryptoException;

// 创建测试用的SM3服务实例
beforeEach(function () {
    $this->sm3Service = new Sm3Service();
});

test('sm3 service can be created', function () {
    expect($this->sm3Service)->toBeInstanceOf(Sm3Service::class);
});

test('sm3 service can hash data', function () {
    $data = 'Hello, World!';
    $options = [
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm3Service->hash($data, $options);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect(strlen($result->data))->toBe(64); // SM3哈希值长度为64个十六进制字符
    expect($result->format)->toBe('hex');
    expect($result->executionTime)->toBeGreaterThan(0);
});

test('sm3 service can hash data with base64 output', function () {
    $data = 'Hello, World!';
    $options = [
        'outputFormat' => 'base64'
    ];
    
    $result = $this->sm3Service->hash($data, $options);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect($result->format)->toBe('base64');
    expect($result->executionTime)->toBeGreaterThan(0);
});

test('sm3 service can encrypt data (alias for hash)', function () {
    $data = 'Hello, World!';
    $options = [
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm3Service->encrypt($data, $options);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect(strlen($result->data))->toBe(64);
    expect($result->format)->toBe('hex');
});

test('sm3 service throws exception for empty data', function () {
    $data = '';
    $options = [
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm3Service->hash($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('数据不能为空');
});

test('sm3 service throws exception for invalid output format', function () {
    $data = 'Hello, World!';
    $options = [
        'outputFormat' => 'invalid'
    ];
    
    $result = $this->sm3Service->hash($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('不支持的输出格式');
});

test('sm3 service decrypt returns error', function () {
    $data = 'test_hash_data';
    $options = [
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm3Service->decrypt($data, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('SM3是哈希算法，不支持解密操作');
});

test('sm3 service can compare hashes', function () {
    $hash1 = 'a' . str_repeat('0', 63); // 64个字符的哈希值
    $hash2 = 'A' . str_repeat('0', 63); // 相同的哈希值，但大写
    
    $result = $this->sm3Service->compareHashes($hash1, $hash2);
    
    expect($result['equal'])->toBeTrue();
    expect($result['hash1'])->toBe(strtolower($hash1));
    expect($result['hash2'])->toBe(strtolower($hash2));
    expect($result['length1'])->toBe(64);
    expect($result['length2'])->toBe(64);
});

test('sm3 service can compare different hashes', function () {
    $hash1 = 'a' . str_repeat('0', 63); // 64个字符的哈希值
    $hash2 = 'b' . str_repeat('0', 63); // 不同的哈希值
    
    $result = $this->sm3Service->compareHashes($hash1, $hash2);
    
    expect($result['equal'])->toBeFalse();
});

test('sm3 service can compare hashes with whitespace', function () {
    $hash1 = 'a' . str_repeat('0', 63); // 64个字符的哈希值
    $hash2 = 'A' . str_repeat('0', 31) . ' ' . str_repeat('0', 32); // 包含空格的哈希值，确保总长度相同
    
    $result = $this->sm3Service->compareHashes($hash1, $hash2);
    
    expect($result['equal'])->toBeTrue();
});

test('sm3 service can get supported formats', function () {
    $formats = $this->sm3Service->getSupportedFormats();
    
    expect($formats)->toBeArray();
    expect($formats)->toContain('hex');
    expect($formats)->toContain('base64');
});

test('sm3 service can validate hex input', function () {
    $result = $this->sm3Service->validateInput('0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef', ['format' => 'hex']);
    
    expect($result)->toBeTrue();
});

test('sm3 service can validate base64 input', function () {
    $result = $this->sm3Service->validateInput('SGVsbG8sIFdvcmxkIQ==', ['format' => 'base64']);
    
    expect($result)->toBeTrue();
});

test('sm3 service can copy to clipboard', function () {
    $hash = str_repeat('a', 64);
    $result = $this->sm3Service->copyToClipboard($hash);
    
    // 由于测试环境中可能没有剪贴板工具，我们只验证方法不会抛出异常
    expect($result)->toBeBool();
});

test('sm3 service can hash file', function () {
    // 创建临时测试文件
    $tempFile = tempnam(sys_get_temp_dir(), 'sm3_test_');
    $testData = 'Hello, World!';
    file_put_contents($tempFile, $testData);
    
    $options = [
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm3Service->hashFile($tempFile, $options);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect(strlen($result->data))->toBe(64);
    expect($result->format)->toBe('hex');
    expect($result->executionTime)->toBeGreaterThan(0);
    expect($result->metadata)->toHaveKey('filePath');
    expect($result->metadata)->toHaveKey('fileSize');
    
    // 清理临时文件
    unlink($tempFile);
});

test('sm3 service can hash large file with progress callback', function () {
    // 创建临时大文件（超过默认块大小）
    $tempFile = tempnam(sys_get_temp_dir(), 'sm3_large_test_');
    $testData = str_repeat('Hello, World!', 1000); // 创建较大的测试数据
    file_put_contents($tempFile, $testData);
    
    $progressUpdates = [];
    $progressCallback = function($progress, $processedBytes, $totalBytes) use (&$progressUpdates) {
        $progressUpdates[] = [
            'progress' => $progress,
            'processed' => $processedBytes,
            'total' => $totalBytes
        ];
    };
    
    $options = [
        'outputFormat' => 'hex',
        'chunkSize' => 100 // 小块大小以确保触发分块处理
    ];
    
    $result = $this->sm3Service->hashFile($tempFile, $options, $progressCallback);
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeTrue();
    expect($result->data)->toBeString();
    expect(strlen($result->data))->toBe(64);
    expect($result->format)->toBe('hex');
    
    // 验证进度回调被调用
    expect(count($progressUpdates))->toBeGreaterThan(0);
    $lastUpdate = end($progressUpdates);
    expect($lastUpdate['progress'])->toBe(100);
    expect($lastUpdate['processed'])->toBe($lastUpdate['total']);
    
    // 清理临时文件
    unlink($tempFile);
});

test('sm3 service throws exception for non-existent file', function () {
    $nonExistentFile = '/tmp/non_existent_file_' . uniqid();
    $options = [
        'outputFormat' => 'hex'
    ];
    
    $result = $this->sm3Service->hashFile($nonExistentFile, $options);
    
    expect($result->success)->toBeFalse();
    expect($result->error)->toContain('文件不存在');
});