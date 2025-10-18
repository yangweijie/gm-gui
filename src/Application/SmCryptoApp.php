<?php

namespace Yangweijie\GmGui\Application;

use Kingbes\Libui\App;
use Kingbes\Libui\SDK\LibuiApplication as BaseLibuiApplication;
use Yangweijie\GmGui\Integration\AppIntegrationManager;
use Yangweijie\GmGui\Services\Crypto\Sm2Service;
use Yangweijie\GmGui\Services\Crypto\Sm3Service;
use Yangweijie\GmGui\Services\Crypto\Sm4Service;
use Yangweijie\GmGui\Services\KeyManagementService;
use Yangweijie\GmGui\Services\FileService;
use Yangweijie\GmGui\Services\FileDragDropService;
use Yangweijie\GmGui\Services\ConfigService;
use Yangweijie\GmGui\Services\CodeGenerationService;
use Yangweijie\GmGui\UI\MainWindow;

/**
 * 国密客户端应用主类
 */
class SmCryptoApp
{
    private bool $initialized = false;
    private BaseLibuiApplication $uiApp;
    private ErrorHandler $errorHandler;
    
    // 服务组件
    private Sm2Service $sm2Service;
    private Sm3Service $sm3Service;
    private Sm4Service $sm4Service;
    private KeyManagementService $keyManagementService;
    private FileService $fileService;
    private FileDragDropService $fileDragDropService;
    private ConfigService $configService;
    private CodeGenerationService $codeGenerationService;

    // 集成管理器
    private AppIntegrationManager $integrationManager;
    
    // 主窗口引用
    private ?MainWindow $mainWindow = null;

    public function __construct()
    {
        $this->uiApp = BaseLibuiApplication::getInstance();
    }

    /**
     * 初始化应用
     *
     * @return void
     */
    public function init(): void
    {
        if ($this->initialized) {
            return;
        }

        // 初始化 Libui 应用
        $this->uiApp->init();
        
        // 初始化错误处理器
        $this->errorHandler = new ErrorHandler();
        
        // 初始化服务组件
        $this->initializeServices();
        
        $this->initialized = true;
    }

    /**
     * 初始化服务组件
     *
     * @return void
     */
    private function initializeServices(): void
    {
        $this->configService = new ConfigService();
        $this->sm2Service = new Sm2Service();
        $this->sm3Service = new Sm3Service();
        $this->sm4Service = new Sm4Service();
        $this->keyManagementService = new KeyManagementService();
        $this->fileService = new FileService();
        $this->fileDragDropService = new FileDragDropService();
        $this->codeGenerationService = new CodeGenerationService();
    }

    /**
     * 获取 Libui 应用实例
     *
     * @return BaseLibuiApplication
     */
    public function getUiApp(): BaseLibuiApplication
    {
        return $this->uiApp;
    }

    /**
     * 获取 SM2 服务
     *
     * @return Sm2Service
     */
    public function getSm2Service(): Sm2Service
    {
        return $this->sm2Service;
    }

    /**
     * 获取 SM3 服务
     *
     * @return Sm3Service
     */
    public function getSm3Service(): Sm3Service
    {
        return $this->sm3Service;
    }

    /**
     * 获取 SM4 服务
     *
     * @return Sm4Service
     */
    public function getSm4Service(): Sm4Service
    {
        return $this->sm4Service;
    }

    /**
     * 获取密钥管理服务
     *
     * @return KeyManagementService
     */
    public function getKeyManagementService(): KeyManagementService
    {
        return $this->keyManagementService;
    }

    /**
     * 获取文件服务
     *
     * @return FileService
     */
    public function getFileService(): FileService
    {
        return $this->fileService;
    }

    /**
     * 获取文件拖拽服务
     *
     * @return FileDragDropService
     */
    public function getFileDragDropService(): FileDragDropService
    {
        return $this->fileDragDropService;
    }

    /**
     * 获取配置服务
     *
     * @return ConfigService
     */
    public function getConfigService(): ConfigService
    {
        return $this->configService;
    }

    /**
     * 获取代码生成服务
     *
     * @return CodeGenerationService
     */
    public function getCodeGenerationService(): CodeGenerationService
    {
        return $this->codeGenerationService;
    }

    /**
     * 获取错误处理器
     *
     * @return ErrorHandler
     */
    public function getErrorHandler(): ErrorHandler
    {
        return $this->errorHandler;
    }
    
    /**
     * 设置集成管理器
     *
     * @param AppIntegrationManager $integrationManager 集成管理器
     * @return void
     */
    public function setIntegrationManager(AppIntegrationManager $integrationManager): void
    {
        $this->integrationManager = $integrationManager;
    }
    
    /**
     * 获取集成管理器
     *
     * @return AppIntegrationManager 集成管理器
     */
    public function getIntegrationManager(): AppIntegrationManager
    {
        return $this->integrationManager;
    }
    
    /**
     * 设置主窗口
     *
     * @param \Yangweijie\GmGui\UI\MainWindow $mainWindow 主窗口
     * @return void
     */
    public function setMainWindow(\Yangweijie\GmGui\UI\MainWindow $mainWindow): void
    {
        $this->mainWindow = $mainWindow;
    }
    
    /**
     * 获取主窗口
     *
     * @return \Yangweijie\GmGui\UI\MainWindow|null 主窗口
     */
    public function getMainWindow(): ?\Yangweijie\GmGui\UI\MainWindow
    {
        return $this->mainWindow;
    }

    /**
     * 运行应用
     *
     * @return void
     */
    public function run(): void
    {
        if (!$this->initialized) {
            $this->init();
        }
        
        // 设置保持活动机制
        $this->setupKeepAlive();
        
        // 运行 Libui 应用
        $this->uiApp->run();
    }

    /**
     * 设置保持活动机制
     *
     * @return void
     */
    protected function setupKeepAlive(): void
    {
        // 每30秒执行一次保持活动操作
        App::timer(30000, function() {
            // 保持应用程序活跃
            // 可以在这里添加一些轻量级的操作
            // 例如：检查窗口状态、更新UI等
        });
    }

    /**
     * 退出应用
     *
     * @return void
     */
    public function exit(): void
    {
        // 保存配置
        // $this->configService->saveConfig();
        
        // 退出 Libui 应用
        $this->uiApp->quit();
    }

    /**
     * 显示错误消息
     *
     * @param string $message 错误消息
     * @return void
     */
    public function showError(string $message): void
    {
        $this->errorHandler->showUserFriendlyError($message);
        
        // 更新状态栏显示错误消息
        if ($this->mainWindow !== null) {
            $this->mainWindow->showStatusMessage($message, 'error');
        }
    }

    /**
     * 记录日志
     *
     * @param string $message 日志消息
     * @param string $level 日志级别
     * @return void
     */
    public function log(string $message, string $level = 'info'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        // 确保日志目录存在
        $logDir = './logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // 写入日志文件
        file_put_contents($logDir . '/app.log', $logMessage, FILE_APPEND | LOCK_EX);
        
        // 更新状态栏显示日志消息（仅在info级别时，且不包含调试信息）
        if ($this->mainWindow !== null && $level === 'info' && !$this->isDebugMessage($message)) {
            $this->mainWindow->showStatusMessage($message, 'info');
        }
    }
    
    /**
     * 统一按钮点击事件处理
     * 在所有按钮点击时清空状态栏消息
     *
     * @return void
     */
    public function onButtonClick(): void
    {
        // 清空状态栏消息
        if ($this->mainWindow !== null) {
            $this->mainWindow->clearStatusMessage();
        }
    }
    
    /**
     * 判断是否为调试消息
     *
     * @param string $message 消息内容
     * @return bool 是否为调试消息
     */
    private function isDebugMessage(string $message): bool
    {
        // 检查是否包含调试信息的特征
        $debugIndicators = [
            '__set_state',
            'array(',
            'executionTime',
            'metadata',
            'CryptoResult'
        ];
        
        foreach ($debugIndicators as $indicator) {
            if (strpos($message, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
}