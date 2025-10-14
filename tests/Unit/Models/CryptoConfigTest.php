<?php

use Yangweijie\GmGui\Models\CryptoConfig;

test('crypto config can be created with default values', function () {
    $config = new CryptoConfig();
    
    expect($config)->toBeInstanceOf(CryptoConfig::class);
    expect($config->outputFormat)->toBe('hex');
    expect($config->appendZeroFour)->toBeFalse();
    expect($config->sm2Mode)->toBe('C1C3C2');
    expect($config->sm4Mode)->toBe('cbc');
    expect($config->signatureFormat)->toBe('asn1');
    expect($config->keyStorePath)->toBe('./keys');
});

test('crypto config can be created with custom values', function () {
    $customConfig = [
        'outputFormat' => 'base64',
        'appendZeroFour' => true,
        'sm2Mode' => 'C1C2C3',
        'sm4Mode' => 'ecb',
        'signatureFormat' => 'rs',
        'keyStorePath' => '/custom/keys'
    ];
    
    $config = new CryptoConfig($customConfig);
    
    expect($config->outputFormat)->toBe('base64');
    expect($config->appendZeroFour)->toBeTrue();
    expect($config->sm2Mode)->toBe('C1C2C3');
    expect($config->sm4Mode)->toBe('ecb');
    expect($config->signatureFormat)->toBe('rs');
    expect($config->keyStorePath)->toBe('/custom/keys');
});