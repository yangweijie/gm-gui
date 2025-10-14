<?php

namespace Yangweijie\GmGui\Optimization;

class LargeFileProcessor
{
    /**
     * 默认块大小（字节）
     */
    public const DEFAULT_CHUNK_SIZE = 8192; // 8KB

    /**
     * 大文件阈值（字节）
     */
    public const LARGE_FILE_THRESHOLD = 1048576; // 1MB

    /**
     * 最小块大小（字节）
     */
    public const MIN_CHUNK_SIZE = 4096; // 4KB

    /**
     * 最大块大小（字节）
     */
    public const MAX_CHUNK_SIZE = 1048576; // 1MB

    /**
     * 应用实例
     *
     * @var mixed
     */
    protected $app;

    /**
     * 块大小
     *
     * @var int
     */
    protected int $chunkSize;

    /**
     * 是否启用内存优化
     *
     * @var bool
     */
    protected bool $memoryOptimization = true;

    /**
     * 是否启用动态块大小调整
     *
     * @var bool
     */
    protected bool $dynamicChunkSizing = true;

    /**
     * 进度回调函数
     *
     * @var callable|null
     */
    protected $progressCallback = null;

    /**
     * 构造函数
     *
     * @param mixed $app 应用实例
     * @param int $chunkSize 块大小
     */
    public function __construct($app, int $chunkSize = self::DEFAULT_CHUNK_SIZE)
    {
        $this->app = $app;
        $this->chunkSize = $chunkSize;
        
        // 根据可用内存调整块大小
        if ($this->dynamicChunkSizing) {
            $this->chunkSize = $this->calculateOptimalChunkSize();
        }
    }

    /**
     * 计算最优块大小
     *
     * @return int 最优块大小
     */
    protected function calculateOptimalChunkSize(): int
    {
        // 获取可用内存
        $availableMemory = $this->getAvailableMemory();
        
        // 根据可用内存计算块大小
        if ($availableMemory > 512 * 1024 * 1024) { // 512MB
            return min(self::MAX_CHUNK_SIZE, $this->chunkSize * 4);
        } elseif ($availableMemory > 256 * 1024 * 1024) { // 256MB
            return min(self::MAX_CHUNK_SIZE, $this->chunkSize * 2);
        } elseif ($availableMemory < 64 * 1024 * 1024) { // 64MB
            return max(self::MIN_CHUNK_SIZE, $this->chunkSize / 4);
        }
        
        return $this->chunkSize;
    }

    /**
     * 获取可用内存
     *
     * @return int 可用内存（字节）
     */
    protected function getAvailableMemory(): int
    {
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit === '-1') {
            // 无内存限制
            return PHP_INT_MAX;
        }
        
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        $usedMemory = memory_get_usage(true);
        
        return max(0, $memoryLimitBytes - $usedMemory);
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
     * 分块处理大文件
     *
     * @param string $inputFile 输入文件路径
     * @param string $outputFile 输出文件路径
     * @param callable $processCallback 处理回调函数
     * @param array $options 处理选项
     * @return bool 是否处理成功
     */
    public function processLargeFile(
        string $inputFile,
        string $outputFile,
        callable $processCallback,
        array $options = []
    ): bool {
        try {
            // 检查文件是否存在
            if (!file_exists($inputFile)) {
                throw new \Exception("输入文件不存在: {$inputFile}");
            }

            // 检查文件是否可读
            if (!is_readable($inputFile)) {
                throw new \Exception("输入文件不可读: {$inputFile}");
            }

            // 获取文件大小
            $fileSize = filesize($inputFile);
            if ($fileSize === false) {
                throw new \Exception("无法获取文件大小: {$inputFile}");
            }

            // 如果文件较小，直接处理
            if ($fileSize <= $this->chunkSize) {
                return $this->processSmallFile($inputFile, $outputFile, $processCallback, $options);
            }

            // 分块处理大文件
            return $this->processFileInChunks($inputFile, $outputFile, $processCallback, $options);
        } catch (\Exception $e) {
            if ($this->app) {
                $this->app->showError("处理文件时出错: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * 处理小文件
     *
     * @param string $inputFile 输入文件路径
     * @param string $outputFile 输出文件路径
     * @param callable $processCallback 处理回调函数
     * @param array $options 处理选项
     * @return bool 是否处理成功
     */
    protected function processSmallFile(
        string $inputFile,
        string $outputFile,
        callable $processCallback,
        array $options = []
    ): bool {
        try {
            // 读取整个文件
            $data = file_get_contents($inputFile);
            if ($data === false) {
                throw new \Exception("读取文件失败: {$inputFile}");
            }

            // 处理数据
            $processedData = $processCallback($data, $options);

            // 写入输出文件
            $result = file_put_contents($outputFile, $processedData);
            if ($result === false) {
                throw new \Exception("写入文件失败: {$outputFile}");
            }

            return true;
        } catch (\Exception $e) {
            if ($this->app) {
                $this->app->showError("处理小文件时出错: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * 分块处理文件
     *
     * @param string $inputFile 输入文件路径
     * @param string $outputFile 输出文件路径
     * @param callable $processCallback 处理回调函数
     * @param array $options 处理选项
     * @return bool 是否处理成功
     */
    protected function processFileInChunks(
        string $inputFile,
        string $outputFile,
        callable $processCallback,
        array $options = []
    ): bool {
        try {
            // 打开输入文件
            $inputHandle = fopen($inputFile, 'rb');
            if ($inputHandle === false) {
                throw new \Exception("无法打开输入文件: {$inputFile}");
            }

            // 打开输出文件
            $outputHandle = fopen($outputFile, 'wb');
            if ($outputHandle === false) {
                fclose($inputHandle);
                throw new \Exception("无法打开输出文件: {$outputFile}");
            }

            // 获取文件大小
            $fileSize = filesize($inputFile);
            if ($fileSize === false) {
                fclose($inputHandle);
                fclose($outputHandle);
                throw new \Exception("无法获取文件大小: {$inputFile}");
            }

            // 初始化进度
            $processedBytes = 0;
            $chunkIndex = 0;

            // 获取初始内存使用情况
            $initialMemory = memory_get_usage(true);
            
            // 动态块大小调整
            $currentChunkSize = $this->chunkSize;

            // 分块处理
            while (!feof($inputHandle)) {
                // 检查内存使用情况，必要时调整块大小
                if ($this->dynamicChunkSizing && $chunkIndex % 10 === 0) {
                    $currentMemory = memory_get_usage(true);
                    $memoryUsed = $currentMemory - $initialMemory;
                    
                    // 如果内存使用超过100MB，减小块大小
                    if ($memoryUsed > 100 * 1024 * 1024) {
                        $currentChunkSize = max(self::MIN_CHUNK_SIZE, $currentChunkSize / 2);
                    }
                    // 如果内存使用低于50MB且当前块大小小于默认大小，增加块大小
                    elseif ($memoryUsed < 50 * 1024 * 1024 && $currentChunkSize < $this->chunkSize) {
                        $currentChunkSize = min($this->chunkSize, $currentChunkSize * 2);
                    }
                }

                // 读取数据块
                $chunk = fread($inputHandle, $currentChunkSize);
                if ($chunk === false) {
                    fclose($inputHandle);
                    fclose($outputHandle);
                    throw new \Exception("读取文件块失败");
                }

                // 处理数据块
                $processedChunk = $processCallback($chunk, $options);

                // 写入处理后的数据块
                $writeResult = fwrite($outputHandle, $processedChunk);
                if ($writeResult === false) {
                    fclose($inputHandle);
                    fclose($outputHandle);
                    throw new \Exception("写入文件块失败");
                }

                // 更新进度
                $processedBytes += strlen($chunk);
                $chunkIndex++;

                // 调用进度回调
                if ($this->progressCallback) {
                    $progress = $fileSize > 0 ? ($processedBytes / $fileSize) * 100 : 0;
                    call_user_func($this->progressCallback, $progress, $processedBytes, $fileSize, $chunkIndex);
                }

                // 如果启用了内存优化，定期清理内存
                if ($this->memoryOptimization && $chunkIndex % 50 === 0) {
                    gc_collect_cycles();
                    
                    // 更激进的内存优化：在内存使用较高时清理更多
                    $currentMemory = memory_get_usage(true);
                    if (($currentMemory - $initialMemory) > 200 * 1024 * 1024) { // 200MB
                        gc_collect_cycles(); // 第二次清理
                    }
                }
                
                // 释放处理后的数据块以节省内存
                unset($processedChunk);
            }

            // 关闭文件句柄
            fclose($inputHandle);
            fclose($outputHandle);

            // 最终内存清理
            if ($this->memoryOptimization) {
                gc_collect_cycles();
            }

            return true;
        } catch (\Exception $e) {
            if ($this->app) {
                $this->app->showError("分块处理文件时出错: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * 并行处理多个文件
     *
     * @param array $filePairs 文件对数组 ['input' => 'output', ...]
     * @param callable $processCallback 处理回调函数
     * @param array $options 处理选项
     * @param int $maxParallel 最大并行数
     * @return array 处理结果
     */
    public function processFilesParallel(
        array $filePairs,
        callable $processCallback,
        array $options = [],
        int $maxParallel = 4
    ): array {
        $results = [];
        
        // 检查是否支持多进程处理
        $useMultiprocessing = function_exists('pcntl_fork') && function_exists('pcntl_waitpid');
        
        if ($useMultiprocessing) {
            // 使用多进程并行处理
            $results = $this->processFilesWithMultiprocessing($filePairs, $processCallback, $options, $maxParallel);
        } else {
            // 使用原有的分块处理方式
            $results = $this->processFilesInChunks($filePairs, $processCallback, $options, $maxParallel);
        }

        return $results;
    }

    /**
     * 使用多进程处理多个文件
     *
     * @param array $filePairs 文件对数组 ['input' => 'output', ...]
     * @param callable $processCallback 处理回调函数
     * @param array $options 处理选项
     * @param int $maxParallel 最大并行数
     * @return array 处理结果
     */
    protected function processFilesWithMultiprocessing(
        array $filePairs,
        callable $processCallback,
        array $options = [],
        int $maxParallel = 4
    ): array {
        $results = [];
        $chunks = array_chunk($filePairs, $maxParallel, true);
        $children = [];

        foreach ($chunks as $chunk) {
            // 启动子进程处理当前块中的文件
            foreach ($chunk as $inputFile => $outputFile) {
                $pid = pcntl_fork();
                
                if ($pid == -1) {
                    // Fork失败，回退到单进程处理
                    try {
                        $result = $this->processLargeFile($inputFile, $outputFile, $processCallback, $options);
                        $results[$inputFile] = [
                            'success' => $result,
                            'outputFile' => $outputFile,
                            'error' => $result ? null : '处理失败'
                        ];
                    } catch (\Exception $e) {
                        $results[$inputFile] = [
                            'success' => false,
                            'outputFile' => $outputFile,
                            'error' => $e->getMessage()
                        ];
                    }
                } elseif ($pid == 0) {
                    // 子进程
                    try {
                        $result = $this->processLargeFile($inputFile, $outputFile, $processCallback, $options);
                        // 将结果写入临时文件或通过IPC传递
                        $tempFile = sys_get_temp_dir() . '/gm_gui_result_' . getmypid() . '.json';
                        $resultData = [
                            'inputFile' => $inputFile,
                            'success' => $result,
                            'outputFile' => $outputFile,
                            'error' => $result ? null : '处理失败'
                        ];
                        file_put_contents($tempFile, json_encode($resultData));
                        exit(0);
                    } catch (\Exception $e) {
                        $tempFile = sys_get_temp_dir() . '/gm_gui_result_' . getmypid() . '.json';
                        $resultData = [
                            'inputFile' => $inputFile,
                            'success' => false,
                            'outputFile' => $outputFile,
                            'error' => $e->getMessage()
                        ];
                        file_put_contents($tempFile, json_encode($resultData));
                        exit(1);
                    }
                } else {
                    // 父进程
                    $children[] = [
                        'pid' => $pid,
                        'inputFile' => $inputFile
                    ];
                }
            }

            // 等待所有子进程完成
            foreach ($children as $child) {
                $pid = $child['pid'];
                $inputFile = $child['inputFile'];
                
                pcntl_waitpid($pid, $status);
                
                // 读取子进程的结果
                $tempFile = sys_get_temp_dir() . '/gm_gui_result_' . $pid . '.json';
                if (file_exists($tempFile)) {
                    $resultData = json_decode(file_get_contents($tempFile), true);
                    $results[$inputFile] = $resultData;
                    unlink($tempFile); // 清理临时文件
                } else {
                    $results[$inputFile] = [
                        'success' => false,
                        'outputFile' => $filePairs[$inputFile],
                        'error' => '子进程处理失败'
                    ];
                }
            }

            // 清理子进程数组
            $children = [];

            // 如果启用了内存优化，在处理完每个块后清理内存
            if ($this->memoryOptimization) {
                gc_collect_cycles();
            }
        }

        return $results;
    }

    /**
     * 使用分块方式处理多个文件（原有方法）
     *
     * @param array $filePairs 文件对数组 ['input' => 'output', ...]
     * @param callable $processCallback 处理回调函数
     * @param array $options 处理选项
     * @param int $maxParallel 最大并行数
     * @return array 处理结果
     */
    protected function processFilesInChunks(
        array $filePairs,
        callable $processCallback,
        array $options = [],
        int $maxParallel = 4
    ): array {
        $results = [];
        $chunks = array_chunk($filePairs, $maxParallel, true);

        foreach ($chunks as $chunk) {
            $chunkResults = [];

            // 并行处理当前块中的文件
            foreach ($chunk as $inputFile => $outputFile) {
                try {
                    $result = $this->processLargeFile($inputFile, $outputFile, $processCallback, $options);
                    $chunkResults[$inputFile] = [
                        'success' => $result,
                        'outputFile' => $outputFile,
                        'error' => $result ? null : '处理失败'
                    ];
                } catch (\Exception $e) {
                    $chunkResults[$inputFile] = [
                        'success' => false,
                        'outputFile' => $outputFile,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // 合并结果
            $results = array_merge($results, $chunkResults);

            // 如果启用了内存优化，在处理完每个块后清理内存
            if ($this->memoryOptimization) {
                gc_collect_cycles();
            }
        }

        return $results;
    }

    /**
     * 流式处理文件（适用于需要保持状态的处理）
     *
     * @param string $inputFile 输入文件路径
     * @param string $outputFile 输出文件路径
     * @param callable $initCallback 初始化回调函数
     * @param callable $processCallback 处理回调函数
     * @param callable $finishCallback 完成回调函数
     * @param array $options 处理选项
     * @return bool 是否处理成功
     */
    public function processFileStreaming(
        string $inputFile,
        string $outputFile,
        callable $initCallback,
        callable $processCallback,
        callable $finishCallback,
        array $options = []
    ): bool {
        try {
            // 检查文件是否存在
            if (!file_exists($inputFile)) {
                throw new \Exception("输入文件不存在: {$inputFile}");
            }

            // 打开文件
            $inputHandle = fopen($inputFile, 'rb');
            if ($inputHandle === false) {
                throw new \Exception("无法打开输入文件: {$inputFile}");
            }

            $outputHandle = fopen($outputFile, 'wb');
            if ($outputHandle === false) {
                fclose($inputHandle);
                throw new \Exception("无法打开输出文件: {$outputFile}");
            }

            // 设置缓冲区大小
            $bufferSize = $this->chunkSize * 2;
            stream_set_write_buffer($outputHandle, $bufferSize);

            // 初始化处理状态
            $state = $initCallback($options);

            // 获取文件大小
            $fileSize = filesize($inputFile);
            if ($fileSize === false) {
                fclose($inputHandle);
                fclose($outputHandle);
                throw new \Exception("无法获取文件大小: {$inputFile}");
            }

            // 初始化进度
            $processedBytes = 0;
            $chunkIndex = 0;

            // 流式处理
            while (!feof($inputHandle)) {
                // 读取数据块
                $chunk = fread($inputHandle, $this->chunkSize);
                if ($chunk === false) {
                    fclose($inputHandle);
                    fclose($outputHandle);
                    throw new \Exception("读取文件块失败");
                }

                // 处理数据块并更新状态
                $result = $processCallback($chunk, $state, $options);

                // 写入处理后的数据
                if (isset($result['data'])) {
                    $writeResult = fwrite($outputHandle, $result['data']);
                    if ($writeResult === false) {
                        fclose($inputHandle);
                        fclose($outputHandle);
                        throw new \Exception("写入文件块失败");
                    }
                    
                    // 定期刷新缓冲区以确保数据写入
                    if ($chunkIndex % 10 === 0) {
                        fflush($outputHandle);
                    }
                }

                // 更新进度
                $processedBytes += strlen($chunk);
                $chunkIndex++;

                // 调用进度回调
                if ($this->progressCallback) {
                    $progress = $fileSize > 0 ? ($processedBytes / $fileSize) * 100 : 0;
                    call_user_func($this->progressCallback, $progress, $processedBytes, $fileSize, $chunkIndex);
                }

                // 如果启用了内存优化，定期清理内存
                if ($this->memoryOptimization && $chunkIndex % 50 === 0) {
                    gc_collect_cycles();
                }
                
                // 释放处理结果以节省内存
                unset($result);
            }

            // 完成处理
            $finalResult = $finishCallback($state, $options);

            // 如果有最终数据需要写入
            if (isset($finalResult['data'])) {
                fwrite($outputHandle, $finalResult['data']);
            }

            // 强制刷新缓冲区并关闭文件句柄
            fflush($outputHandle);
            fclose($inputHandle);
            fclose($outputHandle);

            // 最终内存清理
            if ($this->memoryOptimization) {
                gc_collect_cycles();
            }

            return true;
        } catch (\Exception $e) {
            if ($this->app) {
                $this->app->showError("流式处理文件时出错: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * 设置块大小
     *
     * @param int $chunkSize 块大小（字节）
     * @return void
     */
    public function setChunkSize(int $chunkSize): void
    {
        $this->chunkSize = max(self::MIN_CHUNK_SIZE, min(self::MAX_CHUNK_SIZE, $chunkSize));
    }

    /**
     * 启用或禁用动态块大小调整
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setDynamicChunkSizing(bool $enabled): void
    {
        $this->dynamicChunkSizing = $enabled;
    }

    /**
     * 获取块大小
     *
     * @return int 块大小（字节）
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * 启用或禁用内存优化
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setMemoryOptimization(bool $enabled): void
    {
        $this->memoryOptimization = $enabled;
    }

    /**
     * 设置进度回调函数
     *
     * @param callable|null $callback 回调函数
     * @return void
     */
    public function setProgressCallback(?callable $callback): void
    {
        $this->progressCallback = $callback;
    }

    /**
     * 获取文件处理统计信息
     *
     * @param string $filePath 文件路径
     * @return array 统计信息
     */
    public function getFileStats(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['error' => '文件不存在'];
        }

        $stats = [
            'size' => filesize($filePath),
            'is_large' => filesize($filePath) > self::LARGE_FILE_THRESHOLD,
            'chunk_size' => $this->chunkSize,
            'estimated_chunks' => ceil(filesize($filePath) / $this->chunkSize),
            'readable' => is_readable($filePath),
            'writable' => is_writable($filePath),
            'modified' => filemtime($filePath)
        ];

        return $stats;
    }

    /**
     * 预估处理时间
     *
     * @param string $filePath 文件路径
     * @param float $bytesPerSecond 处理速度（字节/秒）
     * @return array 预估信息
     */
    public function estimateProcessingTime(string $filePath, float $bytesPerSecond): array
    {
        if (!file_exists($filePath)) {
            return ['error' => '文件不存在'];
        }

        $fileSize = filesize($filePath);
        if ($fileSize === false) {
            return ['error' => '无法获取文件大小'];
        }

        $estimatedTime = $fileSize / $bytesPerSecond;
        $minutes = floor($estimatedTime / 60);
        $seconds = $estimatedTime % 60;

        return [
            'file_size' => $fileSize,
            'estimated_time_seconds' => $estimatedTime,
            'estimated_time_formatted' => "{$minutes}分{$seconds}秒",
            'bytes_per_second' => $bytesPerSecond
        ];
    }

    /**
     * 快速文件复制（利用系统优化）
     *
     * @param string $source 源文件路径
     * @param string $destination 目标文件路径
     * @return bool 是否复制成功
     */
    public function quickFileCopy(string $source, string $destination): bool
    {
        // 检查源文件是否存在
        if (!file_exists($source)) {
            if ($this->app) {
                $this->app->showError("源文件不存在: {$source}");
            }
            return false;
        }

        // 检查源文件是否可读
        if (!is_readable($source)) {
            if ($this->app) {
                $this->app->showError("源文件不可读: {$source}");
            }
            return false;
        }

        // 尝试使用系统命令进行快速复制（在Unix-like系统上）
        if (PHP_OS_FAMILY !== 'Windows') {
            $command = "cp " . escapeshellarg($source) . " " . escapeshellarg($destination);
            $result = null;
            $output = [];
            exec($command, $output, $result);
            
            if ($result === 0 && file_exists($destination)) {
                return true;
            }
        }

        // 如果系统命令失败或在Windows上，使用PHP内置函数
        return copy($source, $destination);
    }

    /**
     * 获取文件处理性能指标
     *
     * @param string $filePath 文件路径
     * @return array 性能指标
     */
    public function getFilePerformanceMetrics(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['error' => '文件不存在'];
        }

        // 获取文件系统信息
        $diskStats = disk_total_space(dirname($filePath));
        $diskFree = disk_free_space(dirname($filePath));
        
        // 获取文件统计信息
        $fileStats = $this->getFileStats($filePath);
        
        // 获取I/O性能指标
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // 读取文件的一部分来测试读取性能
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            $chunk = fread($handle, min($this->chunkSize, $fileStats['size']));
            fclose($handle);
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage();
            
            $readTime = $endTime - $startTime;
            $memoryUsed = $endMemory - $startMemory;
        } else {
            $readTime = 0;
            $memoryUsed = 0;
        }

        return [
            'disk_total_space' => $diskStats,
            'disk_free_space' => $diskFree,
            'disk_usage_percent' => round((($diskStats - $diskFree) / $diskStats) * 100, 2),
            'file_stats' => $fileStats,
            'read_performance' => [
                'time_seconds' => $readTime,
                'memory_used' => $memoryUsed,
                'chunk_size' => min($this->chunkSize, $fileStats['size'])
            ]
        ];
    }
}