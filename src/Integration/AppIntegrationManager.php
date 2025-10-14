<?php

namespace Yangweijie\GmGui\Integration;

use Exception;
use Yangweijie\GmGui\Application\SmCryptoApp;
use Yangweijie\GmGui\UI\MainWindow;
use Yangweijie\GmGui\UI\AppMenuBar;
use Yangweijie\GmGui\Optimization\AppStartupOptimizer;
use Yangweijie\GmGui\Optimization\LargeFileProcessor;
use Yangweijie\GmGui\Application\GlobalErrorHandler;
use Yangweijie\GmGui\Application\UserFeedbackSystem;

class AppIntegrationManager
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 主窗口
     *
     * @var MainWindow
     */
    protected MainWindow $mainWindow;

    /**
     * 菜单栏
     *
     * @var AppMenuBar
     */
    protected AppMenuBar $menuBar;

    /**
     * 启动优化器
     *
     * @var AppStartupOptimizer
     */
    protected AppStartupOptimizer $startupOptimizer;

    /**
     * 大文件处理器
     *
     * @var LargeFileProcessor
     */
    protected LargeFileProcessor $largeFileProcessor;

    /**
     * 全局错误处理器
     *
     * @var GlobalErrorHandler
     */
    protected GlobalErrorHandler $errorHandler;

    /**
     * 用户反馈系统
     *
     * @var UserFeedbackSystem
     */
    protected UserFeedbackSystem $feedbackSystem;

    /**
     * 是否已集成
     *
     * @var bool
     */
    protected bool $integrated = false;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     */
    public function __construct(SmCryptoApp $app)
    {
        $this->app = $app;
    }

    /**
     * 集成所有组件
     *
     * @return void
     */
    public function integrate(): void
    {
        if ($this->integrated) {
            return;
        }

        try {
            // 记录集成开始
            $this->logIntegration('开始应用集成');

            // 初始化优化器
            $this->initializeOptimizers();

            // 显示启动画面
            $this->showStartupScreen();

            // 优化启动过程
            $this->optimizeStartup();

            // 初始化核心组件
            $this->initializeCoreComponents();

            // 创建菜单栏（在应用初始化之前）
            $this->createMenuBar();

            // 初始化应用核心组件
            $this->app->init();

            // 初始化UI组件（在应用初始化之后）
            $this->initializeUIComponents();

            // 初始化错误处理和反馈系统
            $this->initializeErrorAndFeedbackSystems();

            // 配置优化器（在应用初始化后）
            $this->configureOptimizers();

            // 预热应用
            $this->warmupApplication();

            // 隐藏启动画面
            $this->hideStartupScreen();

            // 标记为已集成
            $this->integrated = true;

            // 记录集成完成
            $this->logIntegration('应用集成完成');
        } catch (Exception $e) {
            $this->handleIntegrationError($e);
        }
    }

    /**
     * 初始化优化器
     *
     * @return void
     */
    protected function initializeOptimizers(): void
    {
        $this->startupOptimizer = new AppStartupOptimizer($this->app);
        $this->largeFileProcessor = new LargeFileProcessor($this->app);

        $this->logIntegration('初始化优化器');
    }

    /**
     * 配置优化器（在应用初始化后）
     *
     * @return void
     */
    protected function configureOptimizers(): void
    {
        // 调用启动优化器的configure方法
        $this->startupOptimizer->configure();
        $this->logIntegration('配置优化器');
    }

    /**
     * 创建菜单栏
     *
     * @return void
     */
    protected function createMenuBar(): void
    {
        $this->menuBar = new AppMenuBar($this->app, $this->app->getUiApp());
        $this->logIntegration('创建菜单栏');
    }

    /**
     * 显示启动画面
     *
     * @return void
     */
    protected function showStartupScreen(): void
    {
        $this->startupOptimizer->showSplashScreen();
        $this->logIntegration('显示启动画面');
    }

    /**
     * 优化启动过程
     *
     * @return void
     */
    protected function optimizeStartup(): void
    {
        $this->startupOptimizer->optimizeStartup();
        $this->logIntegration('优化启动过程');
    }

    /**
     * 初始化核心组件
     *
     * @return void
     */
    protected function initializeCoreComponents(): void
    {
        // 核心组件已经在应用初始化时创建
        // 这里可以进行额外的配置
        $this->logIntegration('初始化核心组件');
    }

    /**
     * 初始化UI组件
     *
     * @return void
     * @throws Exception
     */
    protected function initializeUIComponents(): void
    {
        try {
            // 创建主窗口（使用固定尺寸 800x600）
            $width = 800;
            $height = 600;
            
            $this->mainWindow = new MainWindow(
                $this->app,
                "国密客户端",
                $width,
                $height
            );

            // 设置主窗口引用
            $this->app->setMainWindow($this->mainWindow);

            // 初始化UI组件（不包括菜单）
            $this->mainWindow->initUI();

            // 显示主窗口
            error_log("AppIntegrationManager: About to show main window");
            $this->mainWindow->show();
            error_log("AppIntegrationManager: Main window shown");

            $this->logIntegration('初始化UI组件');
        } catch (Exception $e) {
            throw new Exception("初始化UI组件失败: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 初始化错误处理和反馈系统
     *
     * @return void
     */
    protected function initializeErrorAndFeedbackSystems(): void
    {
        // 初始化全局错误处理器
        $this->errorHandler = new GlobalErrorHandler($this->app);
        $this->errorHandler->register();

        // 初始化用户反馈系统
        $this->feedbackSystem = new UserFeedbackSystem($this->app);

        $this->logIntegration('初始化错误处理和反馈系统');
    }

    /**
     * 预热应用
     *
     * @return void
     */
    protected function warmupApplication(): void
    {
        $this->startupOptimizer->warmupApplication();
        $this->logIntegration('预热应用');
    }

    /**
     * 隐藏启动画面
     *
     * @return void
     */
    protected function hideStartupScreen(): void
    {
        $this->startupOptimizer->hideSplashScreen();
        $this->logIntegration('隐藏启动画面');
    }

    /**
     * 处理集成错误
     *
     * @param Exception $exception 异常
     * @return void
     */
    protected function handleIntegrationError(Exception $exception): void
    {
        // 记录错误
        error_log("应用集成失败: " . $exception->getMessage());
        error_log($exception->getTraceAsString());

        // 显示用户友好的错误消息
        if ($this->app) {
            $this->app->showError("应用启动失败，请查看日志了解详情。");
        }

        // 如果有错误处理器，使用它处理异常
        if ($this->errorHandler) {
            $this->errorHandler->handleException($exception);
        }

        // 退出应用
        exit(1);
    }

    /**
     * 运行应用
     *
     * @return void
     */
    public function run(): void
    {
        if (!$this->integrated) {
            $this->integrate();
        }

        try {
            // 添加定时器以保持应用程序响应
            $this->setupKeepAliveTimer();
            
            // 运行应用主循环
            $this->app->run();
        } catch (Exception $e) {
            $this->handleIntegrationError($e);
        }
    }

    /**
     * 设置保持活动定时器
     *
     * @return void
     */
    protected function setupKeepAliveTimer(): void
    {
        // 每30秒触发一次，保持应用程序活跃
        $this->app->getUiApp()->queueMain(function() {
            // 这里可以添加一些轻量级的操作来保持应用程序活跃
            // 例如检查窗口焦点状态或执行一些后台任务
        });
        
        // 使用LibUI的定时器功能，每30秒执行一次
        \Kingbes\Libui\App::timer(30000, function() {
            // 定时器回调，保持应用程序活跃
            // 这里可以执行一些轻量级的操作
        });
    }

    /**
     * 获取主窗口
     *
     * @return MainWindow 主窗口
     */
    public function getMainWindow(): MainWindow
    {
        return $this->mainWindow;
    }

    /**
     * 获取菜单栏
     *
     * @return AppMenuBar 菜单栏
     */
    public function getMenuBar(): AppMenuBar
    {
        return $this->menuBar;
    }

    /**
     * 获取大文件处理器
     *
     * @return LargeFileProcessor 大文件处理器
     */
    public function getLargeFileProcessor(): LargeFileProcessor
    {
        return $this->largeFileProcessor;
    }

    /**
     * 获取用户反馈系统
     *
     * @return UserFeedbackSystem 用户反馈系统
     */
    public function getFeedbackSystem(): UserFeedbackSystem
    {
        return $this->feedbackSystem;
    }

    /**
     * 记录集成过程
     *
     * @param string $message 消息
     * @return void
     */
    protected function logIntegration(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        error_log("[{$timestamp}] [集成] {$message}");
    }

    /**
     * 获取集成统计信息
     *
     * @return array 统计信息
     */
    public function getIntegrationStats(): array
    {
        $stats = [
            'integrated' => $this->integrated,
            'components' => [
                'main_window' => isset($this->mainWindow),
                'menu_bar' => isset($this->menuBar),
                'error_handler' => isset($this->errorHandler),
                'feedback_system' => isset($this->feedbackSystem),
                'startup_optimizer' => isset($this->startupOptimizer),
                'large_file_processor' => isset($this->largeFileProcessor)
            ]
        ];

        // 添加启动优化器统计
        if ($this->startupOptimizer) {
            $stats['startup_stats'] = $this->startupOptimizer->getStartupStats();
        }

        // 添加大文件处理器统计
        if ($this->largeFileProcessor) {
            // 获取一个测试文件的性能指标（如果存在）
            $testFile = dirname(__DIR__, 2) . '/composer.json'; // 使用composer.json作为测试文件
            if (file_exists($testFile)) {
                $stats['file_processing_stats'] = $this->largeFileProcessor->getFilePerformanceMetrics($testFile);
            }
        }

        return $stats;
    }

    /**
     * 执行健康检查
     *
     * @return bool 是否健康
     */
    public function healthCheck(): bool
    {
        $checks = [
            'app_initialized' => $this->app !== null,
            'main_window_created' => $this->mainWindow !== null,
            'menu_bar_created' => $this->menuBar !== null,
            'error_handler_registered' => $this->errorHandler !== null,
            'feedback_system_initialized' => $this->feedbackSystem !== null,
            'startup_optimizer_initialized' => $this->startupOptimizer !== null,
            'large_file_processor_initialized' => $this->largeFileProcessor !== null
        ];

        foreach ($checks as $check => $result) {
            if (!$result) {
                error_log("健康检查失败: {$check}");
                return false;
            }
        }

        // 检查优化器是否正常工作
        if ($this->startupOptimizer) {
            $stats = $this->startupOptimizer->getStartupStats();
            // 检查启动时间是否合理（小于5秒）
            if (isset($stats['startup_time_ms']) && $stats['startup_time_ms'] > 5000) {
                error_log("健康检查警告: 启动时间过长 {$stats['startup_time_ms']} 毫秒");
            }
        }

        return true;
    }

    /**
     * 重新集成组件
     *
     * @return void
     */
    public function reinitialize(): void
    {
        $this->integrated = false;
        $this->integrate();
    }

    /**
     * 生成集成和优化报告
     *
     * @return string 报告内容
     */
    public function generateIntegrationReport(): string
    {
        $stats = $this->getIntegrationStats();
        $report = "=== 应用集成和优化报告 ===\n";
        $report .= "生成时间: " . date('Y-m-d H:i:s') . "\n\n";
        
        $report .= "集成状态:\n";
        $report .= "- 集成完成: " . ($stats['integrated'] ? '是' : '否') . "\n";
        
        $report .= "\n组件状态:\n";
        foreach ($stats['components'] as $component => $initialized) {
            $report .= "- {$component}: " . ($initialized ? '已初始化' : '未初始化') . "\n";
        }
        
        if (isset($stats['startup_stats'])) {
            $report .= "\n启动优化统计:\n";
            $startupStats = $stats['startup_stats'];
            $report .= "- 启动时间: {$startupStats['startup_time_ms']} 毫秒\n";
            $report .= "- 当前内存使用: {$startupStats['memory_usage_mb']} MB\n";
            $report .= "- 峰值内存使用: {$startupStats['memory_peak_mb']} MB\n";
            
            if (isset($startupStats['opcache_enabled'])) {
                $report .= "- OPcache: " . ($startupStats['opcache_enabled'] ? '启用' : '禁用') . "\n";
                if ($startupStats['opcache_enabled']) {
                    $report .= "- OPcache命中率: {$startupStats['opcache_hit_rate']}%\n";
                }
            }
        }
        
        if (isset($stats['file_processing_stats'])) {
            $report .= "\n文件处理性能:\n";
            $fileStats = $stats['file_processing_stats'];
            if (isset($fileStats['read_performance'])) {
                $readPerf = $fileStats['read_performance'];
                $report .= "- 读取时间: {$readPerf['time_seconds']} 秒\n";
                $report .= "- 读取内存使用: {$readPerf['memory_used']} 字节\n";
                $report .= "- 块大小: {$readPerf['chunk_size']} 字节\n";
            }
        }
        
        $report .= "\n健康检查:\n";
        $healthStatus = $this->healthCheck();
        $report .= "- 应用健康: " . ($healthStatus ? '健康' : '不健康') . "\n";
        
        if ($this->startupOptimizer) {
            $report .= "\n优化建议:\n";
            $optimizationReport = $this->startupOptimizer->generateOptimizationReport();
            // 提取优化建议部分
            $lines = explode("\n", $optimizationReport);
            $suggestionsStarted = false;
            foreach ($lines as $line) {
                if ($suggestionsStarted) {
                    $report .= $line . "\n";
                }
                if (strpos($line, '优化建议:') !== false) {
                    $suggestionsStarted = true;
                }
            }
        }
        
        return $report;
    }
}