<?php

use Yangweijie\GmGui\Models\CryptoResult;

test('crypto result can be created with default values', function () {
    $result = new CryptoResult();
    
    expect($result)->toBeInstanceOf(CryptoResult::class);
    expect($result->success)->toBeFalse();
    expect($result->data)->toBeEmpty();
    expect($result->format)->toBe('hex');
    expect($result->error)->toBeNull();
    expect($result->metadata)->toBeArray();
    expect($result->executionTime)->toBe(0.0);
});

test('crypto result can be created with custom values', function () {
    $success = true;
    $data = 'encrypted_data';
    $format = 'base64';
    $error = 'error_message';
    $metadata = ['key' => 'value'];
    $executionTime = 1.23;
    
    $result = new CryptoResult(
        $success,
        $data,
        $format,
        $error,
        $metadata,
        $executionTime
    );
    
    expect($result->success)->toBe($success);
    expect($result->data)->toBe($data);
    expect($result->format)->toBe($format);
    expect($result->error)->toBe($error);
    expect($result->metadata)->toBe($metadata);
    expect($result->executionTime)->toBe($executionTime);
});

test('crypto result default values are correct', function () {
    $result = new CryptoResult();
    
    expect($result->success)->toBeFalse();
    expect($result->data)->toBe('');
    expect($result->format)->toBe('hex');
    expect($result->error)->toBeNull();
    expect($result->metadata)->toBe([]);
    expect($result->executionTime)->toBe(0.0);
});