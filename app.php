<?php

/**
 * 国密客户端应用入口文件
 */

// 自动加载
require_once __DIR__ . '/vendor/autoload.php';

use Yangweijie\GmGui\Application\SmCryptoApp;
use Yangweijie\GmGui\Integration\AppIntegrationManager;

try {
    // 创建应用实例
    $app = new SmCryptoApp();
    
    // 创建集成管理器
    $integrationManager = new AppIntegrationManager($app);
    
    // 设置集成管理器
    $app->setIntegrationManager($integrationManager);
    
    // 集成所有组件
    $integrationManager->integrate();
    
    // 显示集成报告
    echo $integrationManager->generateIntegrationReport() . "\n";
    
    // 运行应用
    echo "启动GUI应用...\n";
    $integrationManager->run();
    
    echo "应用运行结束。\n";
} catch (\Exception $e) {
    echo "应用启动失败: " . $e->getMessage() . "\n";
    exit(1);
}

