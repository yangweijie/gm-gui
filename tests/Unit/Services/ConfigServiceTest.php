<?php

use Yangweijie\GmGui\Services\ConfigService;
use Yangweijie\GmGui\Application\Config\AppConfig;
use Yangweijie\GmGui\Exceptions\CryptoException;

// 创建测试用的临时配置目录
beforeEach(function () {
    $this->tempConfigDir = sys_get_temp_dir() . '/gm_gui_test_' . uniqid();
    mkdir($this->tempConfigDir, 0755, true);
    $this->configPath = $this->tempConfigDir . '/config.json';
});

// 测试结束后清理临时文件
afterEach(function () {
    if (file_exists($this->configPath)) {
        unlink($this->configPath);
    }
    if (is_dir($this->tempConfigDir)) {
        rmdir($this->tempConfigDir);
    }
});

test('config service can be created with default config', function () {
    $configService = new ConfigService($this->configPath);
    
    expect($configService)->toBeInstanceOf(ConfigService::class);
    
    $config = $configService->getConfig();
    expect($config)->toBeInstanceOf(AppConfig::class);
    expect($config->version)->toBe('1.0.0');
    expect($config->language)->toBe('zh-CN');
});

test('config service loads existing config file', function () {
    // 创建一个自定义配置文件，使用与 getDefaultConfig 相同的结构
    $customConfig = [
        'app' => [
            'version' => '2.0.0',
            'language' => 'en-US',
            'theme' => 'dark'
        ],
        'crypto' => [
            'outputFormat' => 'base64',
            'appendZeroFour' => true,
            'sm2Mode' => 'C1C2C3',
            'sm4Mode' => 'ecb',
            'signature' => [
                'defaultFormat' => 'rs',
                'defaultUserId' => 'testuser'
            ]
        ],
        'storage' => [
            'keyStorePath' => '/test/keys',
            'configPath' => '/test/config',
            'tempPath' => '/test/temp'
        ],
        'ui' => [
            'windowWidth' => 1024,
            'windowHeight' => 768,
            'rememberWindowState' => false
        ]
    ];
    
    file_put_contents($this->configPath, json_encode($customConfig, JSON_PRETTY_PRINT));
    
    $configService = new ConfigService($this->configPath);
    $config = $configService->getConfig();
    
    expect($config->version)->toBe('2.0.0');
    expect($config->language)->toBe('en-US');
    expect($config->theme)->toBe('dark');
    expect($config->windowWidth)->toBe(1024);
    expect($config->windowHeight)->toBe(768);
    expect($config->rememberWindowState)->toBeFalse();
    expect($config->crypto->outputFormat)->toBe('base64');
    expect($config->crypto->appendZeroFour)->toBeTrue();
    expect($config->crypto->sm2Mode)->toBe('C1C2C3');
    expect($config->crypto->sm4Mode)->toBe('ecb');
});

test('config service creates default config when file does not exist', function () {
    expect(file_exists($this->configPath))->toBeFalse();
    
    $configService = new ConfigService($this->configPath);
    
    expect(file_exists($this->configPath))->toBeTrue();
    
    $config = $configService->getConfig();
    expect($config->version)->toBe('1.0.0');
    expect($config->language)->toBe('zh-CN');
    expect($config->theme)->toBe('default');
});

test('config service handles corrupted config file', function () {
    // 创建一个损坏的配置文件
    file_put_contents($this->configPath, '{"invalid": json}');
    
    $configService = new ConfigService($this->configPath);
    
    // 应该加载默认配置
    $config = $configService->getConfig();
    expect($config->version)->toBe('1.0.0');
    expect($config->language)->toBe('zh-CN');
});

test('config service can get config values using dot notation', function () {
    // 先创建一个带有特定值的配置
    $customConfig = [
        'app' => [
            'version' => '2.0.0',
            'language' => 'en-US',
            'theme' => 'dark'
        ],
        'crypto' => [
            'outputFormat' => 'base64',
            'appendZeroFour' => true,
            'sm2Mode' => 'C1C2C3',
            'sm4Mode' => 'ecb',
            'signature' => [
                'defaultFormat' => 'rs',
                'defaultUserId' => 'testuser'
            ]
        ],
        'storage' => [
            'keyStorePath' => '/test/keys',
            'configPath' => '/test/config',
            'tempPath' => '/test/temp'
        ],
        'ui' => [
            'windowWidth' => 1024,
            'windowHeight' => 768,
            'rememberWindowState' => false
        ]
    ];
    
    file_put_contents($this->configPath, json_encode($customConfig, JSON_PRETTY_PRINT));
    
    $configService = new ConfigService($this->configPath);
    
    // 测试获取存在的值
    expect($configService->get('version'))->toBe('2.0.0');
    expect($configService->get('language'))->toBe('en-US');
    expect($configService->get('crypto.outputFormat'))->toBe('base64');
    
    // 测试获取不存在的值，应该返回默认值
    expect($configService->get('nonexistent', 'default'))->toBe('default');
    expect($configService->get('nonexistent'))->toBeNull();
});

test('config service can update config', function () {
    $configService = new ConfigService($this->configPath);
    
    $newConfig = [
        'app' => [
            'version' => '2.0.0',
            'language' => 'en-US',
            'theme' => 'light'
        ],
        'crypto' => [
            'outputFormat' => 'base64',
            'appendZeroFour' => true,
            'sm2Mode' => 'C1C2C3',
            'sm4Mode' => 'ctr',
            'signature' => [
                'defaultFormat' => 'rs',
                'defaultUserId' => 'updateduser'
            ]
        ],
        'storage' => [
            'keyStorePath' => '/updated/keys',
            'configPath' => '/updated/config',
            'tempPath' => '/updated/temp'
        ],
        'ui' => [
            'windowWidth' => 1920,
            'windowHeight' => 1080,
            'rememberWindowState' => false
        ]
    ];
    
    $result = $configService->updateConfig($newConfig);
    
    expect($result)->toBeTrue();
    
    $config = $configService->getConfig();
    expect($config->version)->toBe('2.0.0');
    expect($config->language)->toBe('en-US');
    expect($config->theme)->toBe('light');
    expect($config->windowWidth)->toBe(1920);
    expect($config->windowHeight)->toBe(1080);
    expect($config->crypto->outputFormat)->toBe('base64');
});

test('config service throws exception for invalid config update', function () {
    $configService = new ConfigService($this->configPath);
    
    // 缺少必需的配置项
    $invalidConfig = [
        'app' => ['version' => '1.0.0']
        // 缺少 crypto, storage, ui
    ];
    
    $configService->updateConfig($invalidConfig);
})->throws(CryptoException::class);

test('config service can reset to default config', function () {
    $configService = new ConfigService($this->configPath);
    
    // 先更新配置为自定义值
    $customConfig = [
        'app' => [
            'version' => '2.0.0',
            'language' => 'en-US',
            'theme' => 'dark'
        ],
        'crypto' => [
            'outputFormat' => 'base64',
            'appendZeroFour' => true,
            'sm2Mode' => 'C1C2C3',
            'sm4Mode' => 'ecb',
            'signature' => [
                'defaultFormat' => 'rs',
                'defaultUserId' => 'testuser'
            ]
        ],
        'storage' => [
            'keyStorePath' => '/test/keys',
            'configPath' => '/test/config',
            'tempPath' => '/test/temp'
        ],
        'ui' => [
            'windowWidth' => 1024,
            'windowHeight' => 768,
            'rememberWindowState' => false
        ]
    ];
    
    $configService->updateConfig($customConfig);
    
    // 验证配置已更新
    $config = $configService->getConfig();
    expect($config->version)->toBe('2.0.0');
    expect($config->language)->toBe('en-US');
    
    // 重置为默认配置
    $result = $configService->resetToDefault();
    
    expect($result)->toBeTrue();
    
    // 验证配置已重置
    $config = $configService->getConfig();
    expect($config->version)->toBe('1.0.0');
    expect($config->language)->toBe('zh-CN');
    expect($config->theme)->toBe('default');
});