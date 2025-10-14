<?php

use Yangweijie\GmGui\Models\KeyPair;

test('key pair can be created with default values', function () {
    $keyPair = new KeyPair();
    
    expect($keyPair)->toBeInstanceOf(KeyPair::class);
    expect($keyPair->publicKey)->toBeEmpty();
    expect($keyPair->privateKey)->toBeEmpty();
    expect($keyPair->format)->toBe('hex');
    expect($keyPair->algorithm)->toBe('sm2');
    expect($keyPair->metadata)->toBeArray();
    expect($keyPair->createdAt)->toBeInstanceOf(DateTime::class);
});

test('key pair can be created with custom values', function () {
    $publicKey = 'public_key_example';
    $privateKey = 'private_key_example';
    $format = 'pem';
    $algorithm = 'rsa';
    $metadata = ['custom' => 'data'];
    $createdAt = new DateTime('2023-01-01');
    
    $keyPair = new KeyPair(
        $publicKey,
        $privateKey,
        $format,
        $algorithm,
        $metadata,
        $createdAt
    );
    
    expect($keyPair->publicKey)->toBe($publicKey);
    expect($keyPair->privateKey)->toBe($privateKey);
    expect($keyPair->format)->toBe($format);
    expect($keyPair->algorithm)->toBe($algorithm);
    expect($keyPair->metadata)->toBe($metadata);
    expect($keyPair->createdAt)->toBe($createdAt);
});

test('key pair uses current time when no creation time provided', function () {
    $before = new DateTime();
    $keyPair = new KeyPair();
    $after = new DateTime();
    
    expect($keyPair->createdAt)->toBeInstanceOf(DateTime::class);
    expect($keyPair->createdAt)->toBeGreaterThanOrEqual($before);
    expect($keyPair->createdAt)->toBeLessThanOrEqual($after);
});