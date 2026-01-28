<?php

namespace Yangweijie\GmGui\Optimization;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yangweijie\GmGui\Application\SmCryptoApp;

class AppStartupOptimizer
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 是否启用延迟加载
     *
     * @var bool
     */
    protected bool $lazyLoading = true;

    /**
     * 是否启用预加载
     *
     * @var bool
     */
    protected bool $preloading = false;

    /**
     * 预加载的类列表
     *
     * @var array
     */
    protected array $preloadClasses = [];

    /**
     * 启动时间记录
     *
     * @var array
     */
    protected array $timing = [];

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
     * 优化应用启动
     *
     * @return void
     */
    public function optimizeStartup(): void
    {
        // 记录启动开始时间
        $this->timing['start'] = microtime(true);

        // 优化自动加载
        $this->optimizeAutoloader();

        // 延迟加载服务
        if ($this->lazyLoading) {
            $this->setupLazyLoading();
        }

        // 预加载关键类
        if ($this->preloading) {
            $this->preloadCriticalClasses();
        }

        // 优化内存使用
        $this->optimizeMemoryUsage();

        // 记录启动结束时间
        $this->timing['end'] = microtime(true);
        $this->timing['duration'] = $this->timing['end'] - $this->timing['start'];

        // 记录启动时间
        $this->logStartupTime();
    }

    /**
     * 优化自动加载
     *
     * @return void
     */
    protected function optimizeAutoloader(): void
    {
        // 生成类映射以提高自动加载性能
        $classMap = $this->generateClassMap();
        
        // 缓存自动加载信息
        $this->cacheAutoloaderInfo($classMap);
        
        // 优化PSR-4加载规则
        $this->optimizePsr4Rules();
        
        $this->logOptimizationStep('优化自动加载');
    }

    /**
     * 生成类映射
     *
     * @return array 类映射
     */
    protected function generateClassMap(): array
    {
        $classMap = [];
        $appRoot = dirname(__DIR__, 2);
        
        // 定义要扫描的目录
        $scanDirs = [
            $appRoot . '/Application',
            $appRoot . '/Services',
            $appRoot . '/UI',
            $appRoot . '/Models',
            $appRoot . '/Utils',
            $appRoot . '/Exceptions',
            $appRoot . '/Optimization',
            $appRoot . '/Integration'
        ];
        
        foreach ($scanDirs as $dir) {
            if (is_dir($dir)) {
                $this->scanDirForClasses($dir, $classMap, $appRoot);
            }
        }
        
        return $classMap;
    }

    /**
     * 扫描目录中的类文件
     *
     * @param string $dir 目录路径
     * @param array $classMap 类映射数组
     * @param string $appRoot 应用根目录
     * @return void
     */
    protected function scanDirForClasses(string $dir, array &$classMap, string $appRoot): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        $phpFiles = new \RegexIterator($iterator, '/\.php$/', \RegexIterator::GET_MATCH);
        
        foreach ($phpFiles as $file) {
            $filePath = $file[0];
            $content = file_get_contents($filePath);
            
            // 提取类名
            if (preg_match('/^\s*class\s+(\w+)/m', $content, $matches)) {
                $className = $matches[1];
                
                // 根据文件路径确定命名空间
                $relativePath = str_replace($appRoot . '/', '', $filePath);
                $namespaceParts = explode('/', dirname($relativePath));
                $namespace = 'Yangweijie\\GmGui\\' . implode('\\', $namespaceParts);
                
                // 处理特殊情况
                if ($namespace === 'Yangweijie\\GmGui\\Application\\Config') {
                    $fullClassName = $namespace . '\\' . $className;
                } else if (in_array('Services', $namespaceParts) && in_array('Crypto', $namespaceParts)) {
                    $fullClassName = 'Yangweijie\\GmGui\\Services\\Crypto\\' . $className;
                } else if (in_array('UI', $namespaceParts) && in_array('Components', $namespaceParts)) {
                    $fullClassName = 'Yangweijie\\GmGui\\UI\\Components\\' . $className;
                } else if (in_array('UI', $namespaceParts) && in_array('Dialogs', $namespaceParts)) {
                    $fullClassName = 'Yangweijie\\GmGui\\UI\\Dialogs\\' . $className;
                } else {
                    $fullClassName = $namespace . '\\' . $className;
                }
                
                $classMap[$fullClassName] = $filePath;
            }
        }
    }

    /**
     * 缓存自动加载信息
     *
     * @param array $classMap 类映射
     * @return void
     */
    protected function cacheAutoloaderInfo(array $classMap): void
    {
        $cacheFile = sys_get_temp_dir() . '/gm_gui_classmap_cache.php';
        $exportedClassMap = var_export($classMap, true);
        
        $cacheContent = "<?php\n\n// 自动生成的类映射缓存\nreturn {$exportedClassMap};\n";
        
        file_put_contents($cacheFile, $cacheContent);
        
        // 如果使用Composer，可以更新composer.json中的classmap
        $composerFile = dirname(__DIR__, 2) . '/composer.json';
        if (file_exists($composerFile)) {
            $composerData = json_decode(file_get_contents($composerFile), true);
            if (!isset($composerData['autoload']['classmap'])) {
                $composerData['autoload']['classmap'] = [];
            }
            
            // 添加缓存文件到classmap
            if (!in_array('cache/gm_gui_classmap_cache.php', $composerData['autoload']['classmap'])) {
                $composerData['autoload']['classmap'][] = 'cache/gm_gui_classmap_cache.php';
                
                // 注意：在实际应用中，我们不会自动修改composer.json
                // 这里只是记录建议
                $this->logOptimizationStep('建议将类映射缓存添加到composer.json的classmap中');
            }
        }
    }

    /**
     * 优化PSR-4加载规则
     *
     * @return void
     */
    protected function optimizePsr4Rules(): void
    {
        // 在实际应用中，这里可能会：
        // 1. 分析当前的PSR-4规则使用情况
        // 2. 优化规则顺序以提高匹配效率
        // 3. 合并相似的规则
        
        // 目前只是记录日志
        $this->logOptimizationStep('优化PSR-4加载规则');
    }

    /**
     * 设置延迟加载
     *
     * @return void
     */
    protected function setupLazyLoading(): void
    {
        // 修改服务容器以支持延迟加载
        // 在实际应用中，这里会配置依赖注入容器
        
        // 目前只是记录日志
        $this->logOptimizationStep('设置延迟加载');
    }

    /**
     * 预加载关键类
     *
     * @return void
     */
    protected function preloadCriticalClasses(): void
    {
        // 预加载关键类以减少运行时加载时间
        $criticalClasses = [
            \Yangweijie\GmGui\Application\Config\AppConfig::class,
            \Yangweijie\GmGui\Services\ConfigService::class,
            \Yangweijie\GmGui\Services\KeyManagementService::class,
            \Yangweijie\GmGui\Services\Crypto\Sm2Service::class,
            \Yangweijie\GmGui\Services\Crypto\Sm3Service::class,
            \Yangweijie\GmGui\Services\Crypto\Sm4Service::class,
            \Kingbes\Libui\SDK\LibuiApplication::class,
            \Kingbes\Libui\SDK\LibuiWindow::class
        ];

        // 预加载缓存
        $preloadCacheFile = sys_get_temp_dir() . '/gm_gui_preload_cache.json';
        $cachedClasses = [];

        // 尝试从缓存加载
        if (file_exists($preloadCacheFile)) {
            $cachedClasses = json_decode(file_get_contents($preloadCacheFile), true);
        }

        $preloadedClasses = [];
        $failedClasses = [];

        foreach ($criticalClasses as $class) {
            // 检查是否已在缓存中
            if (in_array($class, $cachedClasses)) {
                // 类已在缓存中，直接标记为已加载
                $preloadedClasses[] = $class;
                $this->logOptimizationStep("从缓存预加载类: {$class}");
                continue;
            }

            // 尝试加载类
            if (class_exists($class)) {
                // 实际预加载类文件
                $reflection = new \ReflectionClass($class);
                // 访问类的一些属性以确保完全加载
                $reflection->getDefaultProperties();
                
                $preloadedClasses[] = $class;
                $this->logOptimizationStep("预加载类: {$class}");
            } else {
                $failedClasses[] = $class;
                $this->logOptimizationStep("预加载类失败: {$class}");
            }
        }

        // 更新缓存
        file_put_contents($preloadCacheFile, json_encode($preloadedClasses));

        // 记录失败的类
        if (!empty($failedClasses)) {
            error_log("预加载失败的类: " . implode(', ', $failedClasses));
        }
    }

    /**
     * 优化内存使用
     *
     * @return void
     */
    protected function optimizeMemoryUsage(): void
    {
        // 设置内存限制
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit !== false) {
            // 如果当前内存限制较小，适当增加
            $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
            if ($memoryLimitBytes < 256 * 1024 * 1024) { // 256MB
                ini_set('memory_limit', '256M');
            }
        }

        // 启用和优化OPcache（如果可用）
        $this->optimizeOPcache();

        // 清理垃圾回收
        gc_collect_cycles();
        $this->logOptimizationStep('优化内存使用');
    }

    /**
     * 优化OPcache设置
     *
     * @return void
     */
    protected function optimizeOPcache(): void
    {
        if (function_exists('opcache_enable')) {
            // 启用OPcache
            opcache_enable();
            
            // 优化OPcache设置
            $opcacheOptions = [
                'opcache.enable' => 1,
                'opcache.memory_consumption' => 128,
                'opcache.interned_strings_buffer' => 8,
                'opcache.max_accelerated_files' => 4000,
                'opcache.revalidate_freq' => 60,
                'opcache.fast_shutdown' => 1
            ];
            
            foreach ($opcacheOptions as $option => $value) {
                if (function_exists('opcache_get_configuration')) {
                    $config = opcache_get_configuration();
                    if (isset($config['directives'][$option]) && 
                        $config['directives'][$option] != $value) {
                        // 注意：在运行时无法更改某些OPcache设置
                        // 这些设置应在php.ini中配置
                        $this->logOptimizationStep("建议在php.ini中设置 {$option} = {$value}");
                    }
                }
            }
            
            // 预编译关键文件（如果支持）
            if (function_exists('opcache_compile_file')) {
                $this->precompileCriticalFiles();
            }
            
            $this->logOptimizationStep('启用并优化OPcache');
        } else {
            $this->logOptimizationStep('OPcache不可用');
        }
    }

    /**
     * 预编译关键文件
     *
     * @return void
     */
    protected function precompileCriticalFiles(): void
    {
        // 获取应用根目录
        $appRoot = dirname(__DIR__, 2);
        
        // 关键文件列表
        $criticalFiles = [
            $appRoot . '/Application/SmCryptoApp.php',
            $appRoot . '/Application/Config/AppConfig.php',
            $appRoot . '/Services/ConfigService.php',
            $appRoot . '/Services/KeyManagementService.php',
            $appRoot . '/Services/Crypto/Sm2Service.php',
            $appRoot . '/Services/Crypto/Sm3Service.php',
            $appRoot . '/Services/Crypto/Sm4Service.php',
            $appRoot . '/UI/MainWindow.php'
        ];
        
        foreach ($criticalFiles as $file) {
            if (file_exists($file) && is_readable($file)) {
                try {
                    opcache_compile_file($file);
                    $this->logOptimizationStep("预编译文件: {$file}");
                } catch (\Exception $e) {
                    $this->logOptimizationStep("预编译文件失败: {$file} - " . $e->getMessage());
                }
            }
        }
    }

    /**
     * 解析内存限制
     *
     * @param string $memoryLimit 内存限制字符串
     * @return int 内存限制字节数
     */
    protected function parseMemoryLimit(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $memoryLimit = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $memoryLimit *= 1024;
                // no break
            case 'm':
                $memoryLimit *= 1024;
                // no break
            case 'k':
                $memoryLimit *= 1024;
        }

        return $memoryLimit;
    }

    /**
     * 显示启动画面
     *
     * @return void
     */
    public function showSplashScreen(): void
    {
        // 在实际的LibUI应用中，这里会创建启动画面窗口
        // 目前只是记录日志
        $this->logOptimizationStep('显示启动画面');
    }

    /**
     * 隐藏启动画面
     *
     * @return void
     */
    public function hideSplashScreen(): void
    {
        // 在实际的LibUI应用中，这里会关闭启动画面窗口
        // 目前只是记录日志
        $this->logOptimizationStep('隐藏启动画面');
    }

    /**
     * 并行初始化服务
     *
     * @param array $services 服务列表
     * @return void
     */
    public function initializeServicesParallel(array $services): void
    {
        // 在实际应用中，这里可能会使用多线程或异步初始化服务
        // 目前只是顺序初始化
        foreach ($services as $serviceName => $serviceClass) {
            try {
                // 初始化服务
                $service = new $serviceClass($this->app);
                
                // 注册到应用容器
                // $this->app->registerService($serviceName, $service);
                
                $this->logOptimizationStep("初始化服务: {$serviceName}");
            } catch (\Exception $e) {
                $this->app->showError("初始化服务 {$serviceName} 时出错: " . $e->getMessage());
            }
        }
    }

    /**
     * 延迟初始化服务
     *
     * @param string $serviceName 服务名称
     * @param string $serviceClass 服务类名
     * @return callable 返回创建服务的闭包
     */
    public function deferServiceInitialization(string $serviceName, string $serviceClass): callable
    {
        return function() use ($serviceName, $serviceClass) {
            static $service = null;
            
            if ($service === null) {
                try {
                    $service = new $serviceClass($this->app);
                    $this->logOptimizationStep("延迟初始化服务: {$serviceName}");
                } catch (\Exception $e) {
                    $this->app->showError("初始化服务 {$serviceName} 时出错: " . $e->getMessage());
                }
            }
            
            return $service;
        };
    }

    /**
     * 预热应用
     *
     * @return void
     */
    public function warmupApplication(): void
    {
        // 预热关键组件
        $this->warmupCryptoServices();
        $this->warmupFileServices();
        $this->warmupUIComponents();
    }

    /**
     * 预热加密服务
     *
     * @return void
     */
    protected function warmupCryptoServices(): void
    {
        try {
            // 初始化SM2服务
            $sm2Service = $this->app->getSm2Service();
            
            // 初始化SM3服务
            $sm3Service = $this->app->getSm3Service();
            
            // 初始化SM4服务
            $sm4Service = $this->app->getSm4Service();
            
            $this->logOptimizationStep('预热加密服务');
        } catch (\Exception $e) {
            $this->app->showError("预热加密服务时出错: " . $e->getMessage());
        }
    }

    /**
     * 预热文件服务
     *
     * @return void
     */
    protected function warmupFileServices(): void
    {
        try {
            // 初始化文件服务
            $fileService = $this->app->getFileService();
            
            // 初始化密钥管理服务
            $keyService = $this->app->getKeyManagementService();
            
            $this->logOptimizationStep('预热文件服务');
        } catch (\Exception $e) {
            $this->app->showError("预热文件服务时出错: " . $e->getMessage());
        }
    }

    /**
     * 预热UI组件
     *
     * @return void
     */
    protected function warmupUIComponents(): void
    {
        // 在实际应用中，这里可能会预加载常用的UI组件
        $this->logOptimizationStep('预热UI组件');
    }

    /**
     * 记录优化步骤
     *
     * @param string $step 步骤描述
     * @return void
     */
    protected function logOptimizationStep(string $step): void
    {
        // 记录到日志
        $timestamp = date('Y-m-d H:i:s');
        error_log("[{$timestamp}] [优化] {$step}");
    }

    /**
     * 记录启动时间
     *
     * @return void
     */
    protected function logStartupTime(): void
    {
        if (isset($this->timing['duration'])) {
            $duration = round($this->timing['duration'] * 1000, 2); // 转换为毫秒
            error_log("应用启动时间: {$duration} 毫秒");
        }
    }

    /**
     * 获取启动统计信息
     *
     * @return array 统计信息
     */
    public function getStartupStats(): array
    {
        $stats = [
            'startup_time_ms' => isset($this->timing['duration']) ? round($this->timing['duration'] * 1000, 2) : 0,
            'memory_usage_mb' => round(memory_get_usage() / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'lazy_loading_enabled' => $this->lazyLoading,
            'preloading_enabled' => $this->preloading
        ];
        
        // 添加OPcache统计信息（如果可用）
        if (function_exists('opcache_get_status')) {
            $opcacheStatus = opcache_get_status(false);
            if ($opcacheStatus) {
                $stats['opcache_enabled'] = $opcacheStatus['opcache_enabled'] ?? false;
                $stats['opcache_hit_rate'] = round($opcacheStatus['opcache_statistics']['opcache_hit_rate'] ?? 0, 2);
                $stats['opcache_memory_usage_mb'] = round(($opcacheStatus['memory_usage']['used_memory'] ?? 0) / 1024 / 1024, 2);
            }
        }
        
        return $stats;
    }

    /**
     * 启用或禁用延迟加载
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setLazyLoading(bool $enabled): void
    {
        $this->lazyLoading = $enabled;
    }

    /**
     * 启用或禁用预加载
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setPreloading(bool $enabled): void
    {
        $this->preloading = $enabled;
    }

    /**
     * 添加预加载类
     *
     * @param string $class 类名
     * @return void
     */
    public function addPreloadClass(string $class): void
    {
        if (!in_array($class, $this->preloadClasses)) {
            $this->preloadClasses[] = $class;
        }
    }

    /**
     * 生成优化报告
     *
     * @return string 优化报告
     */
    public function generateOptimizationReport(): string
    {
        $stats = $this->getStartupStats();
        $report = "=== 应用启动优化报告 ===\n";
        $report .= "生成时间: " . date('Y-m-d H:i:s') . "\n\n";
        
        $report .= "性能指标:\n";
        $report .= "- 启动时间: {$stats['startup_time_ms']} 毫秒\n";
        $report .= "- 当前内存使用: {$stats['memory_usage_mb']} MB\n";
        $report .= "- 峰值内存使用: {$stats['memory_peak_mb']} MB\n";
        $report .= "- 延迟加载: " . ($stats['lazy_loading_enabled'] ? '启用' : '禁用') . "\n";
        $report .= "- 预加载: " . ($stats['preloading_enabled'] ? '启用' : '禁用') . "\n";
        
        if (isset($stats['opcache_enabled'])) {
            $report .= "- OPcache: " . ($stats['opcache_enabled'] ? '启用' : '禁用') . "\n";
            if ($stats['opcache_enabled']) {
                $report .= "- OPcache命中率: {$stats['opcache_hit_rate']}%\n";
                $report .= "- OPcache内存使用: {$stats['opcache_memory_usage_mb']} MB\n";
            }
        }
        
        $report .= "\n优化建议:\n";
        
        // 根据统计信息提供优化建议
        if ($stats['startup_time_ms'] > 1000) {
            $report .= "- 启动时间较长，建议检查预加载类列表\n";
        }
        
        if ($stats['memory_peak_mb'] > 256) {
            $report .= "- 峰值内存使用较高，建议优化延迟加载策略\n";
        }
        
        if (!isset($stats['opcache_enabled']) || !$stats['opcache_enabled']) {
            $report .= "- 建议启用OPcache以提高性能\n";
        } elseif (isset($stats['opcache_hit_rate']) && $stats['opcache_hit_rate'] < 90) {
            $report .= "- OPcache命中率较低，建议优化缓存策略\n";
        }
        
        return $report;
    }
    
    /**
     * 配置优化器
     *
     * @return void
     */
    public function configure(): void
    {
        // 这里可以添加配置逻辑
        $this->logOptimizationStep('配置优化器');
    }
}