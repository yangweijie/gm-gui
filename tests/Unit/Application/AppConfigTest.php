<?php

use Yangweijie\GmGui\Application\Config\AppConfig;
use Yangweijie\GmGui\Models\CryptoConfig;

test('app config can be created with default values', function () {
    $config = new AppConfig();
    
    expect($config)->toBeInstanceOf(AppConfig::class);
    expect($config->version)->toBe('1.0.0');
    expect($config->language)->toBe('zh-CN');
    expect($config->theme)->toBe('default');
    expect($config->windowWidth)->toBe(1200);
    expect($config->windowHeight)->toBe(800);
    expect($config->rememberWindowState)->toBeTrue();
    expect($config->crypto)->toBeInstanceOf(CryptoConfig::class);
});

test('app config can be created with custom values', function () {
    $customConfig = [
        'app' => [
            'version' => '2.0.0',
            'language' => 'en-US',
            'theme' => 'dark'
        ],
        'ui' => [
            'windowWidth' => 1024,
            'windowHeight' => 768,
            'rememberWindowState' => false
        ],
        'storage' => [
            'keyStorePath' => '/custom/keys',
            'configPath' => '/custom/config',
            'tempPath' => '/custom/temp'
        ]
    ];
    
    $config = new AppConfig($customConfig);
    
    expect($config->version)->toBe('2.0.0');
    expect($config->language)->toBe('en-US');
    expect($config->theme)->toBe('dark');
    expect($config->windowWidth)->toBe(1024);
    expect($config->windowHeight)->toBe(768);
    expect($config->rememberWindowState)->toBeFalse();
    expect($config->storage['keyStorePath'])->toBe('/custom/keys');
    expect($config->storage['configPath'])->toBe('/custom/config');
    expect($config->storage['tempPath'])->toBe('/custom/temp');
});

test('app config crypto settings can be customized', function () {
    $customConfig = [
        'crypto' => [
            'outputFormat' => 'base64',
            'appendZeroFour' => true,
            'sm2Mode' => 'C1C2C3',
            'sm4Mode' => 'ecb',
            'signatureFormat' => 'rs'
        ]
    ];
    
    $config = new AppConfig($customConfig);
    
    expect($config->crypto->outputFormat)->toBe('base64');
    expect($config->crypto->appendZeroFour)->toBeTrue();
    expect($config->crypto->sm2Mode)->toBe('C1C2C3');
    expect($config->crypto->sm4Mode)->toBe('ecb');
    expect($config->crypto->signatureFormat)->toBe('rs');
});