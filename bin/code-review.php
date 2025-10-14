#!/usr/bin/env php
<?php

/**
 * Multi-Agent Code Review System
 * 
 * This script performs comprehensive code review covering security, performance,
 * quality, and architecture aspects using specialized agents.
 */

// 定义项目根目录
define('PROJECT_ROOT', dirname(__DIR__));

// 自动加载
require_once PROJECT_ROOT . '/vendor/autoload.php';

use Yangweijie\GmGui\Services\ConfigService;

class MultiAgentCodeReview
{
    private array $config;
    private array $results = [];
    
    public function __construct()
    {
        $this->loadConfig();
    }
    
    private function loadConfig(): void
    {
        $configPath = PROJECT_ROOT . '/config/code-review-config.json';
        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
        } else {
            $this->config = $this->getDefaultConfig();
        }
    }
    
    private function getDefaultConfig(): array
    {
        return [
            'security' => [
                'rules' => [
                    [
                        'id' => 'SECURITY_CRYPTO_KEY_EXPOSURE',
                        'name' => '密钥暴露检测',
                        'description' => '检测代码中是否直接暴露密钥',
                        'severity' => 'high',
                        'patterns' => [
                            'privateKey.*=.*[\'\"][a-f0-9]{64}[\'\"]',
                            'publicKey.*=.*[\'\"][a-f0-9]{128,}[\'\"]'
                        ]
                    ]
                ]
            ],
            'performance' => [
                'rules' => [
                    [
                        'id' => 'PERFORMANCE_LARGE_FILE_PROCESSING',
                        'name' => '大文件处理优化',
                        'description' => '检测大文件处理优化问题',
                        'severity' => 'medium',
                        'patterns' => [
                            'file_get_contents\\([^)]*\\)[^;]*;.*filesize\\(.*\\).*>(.*)1048576'
                        ]
                    ]
                ]
            ],
            'quality' => [
                'rules' => [
                    [
                        'id' => 'QUALITY_CODE_COMPLEXITY',
                        'name' => '代码复杂度检查',
                        'description' => '检测高复杂度代码',
                        'severity' => 'medium',
                        'patterns' => [
                            'if.*elseif.*elseif.*elseif',
                            'for.*for.*for'
                        ]
                    ]
                ]
            ],
            'architecture' => [
                'rules' => [
                    [
                        'id' => 'ARCHITECTURE_SERVICE_DEPENDENCY',
                        'name' => '服务依赖检查',
                        'description' => '检测服务依赖关系问题',
                        'severity' => 'medium',
                        'patterns' => [
                            'new.*Service.*\\(.*new.*Service'
                        ]
                    ]
                ]
            ]
        ];
    }
    
    public function runReview(string $target = 'src'): array
    {
        echo "开始执行多代理代码审查...\n";
        echo "审查目标: {$target}\n\n";
        
        // 运行各类审查
        $this->runSecurityReview($target);
        $this->runPerformanceReview($target);
        $this->runQualityReview($target);
        $this->runArchitectureReview($target);
        
        // 生成报告
        $this->generateReport();
        
        return $this->results;
    }
    
    private function runSecurityReview(string $target): void
    {
        echo "执行安全审查...\n";
        $this->results['security'] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'issues' => []
        ];
        
        if (isset($this->config['security']['rules'])) {
            foreach ($this->config['security']['rules'] as $rule) {
                $issues = $this->findPatternInFiles($target, $rule['patterns']);
                if (!empty($issues)) {
                    $this->results['security']['issues'] = array_merge(
                        $this->results['security']['issues'],
                        $issues
                    );
                }
            }
        }
        
        echo "安全审查完成，发现 " . count($this->results['security']['issues']) . " 个问题\n\n";
    }
    
    private function runPerformanceReview(string $target): void
    {
        echo "执行性能审查...\n";
        $this->results['performance'] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'issues' => []
        ];
        
        if (isset($this->config['performance']['rules'])) {
            foreach ($this->config['performance']['rules'] as $rule) {
                $issues = $this->findPatternInFiles($target, $rule['patterns']);
                if (!empty($issues)) {
                    $this->results['performance']['issues'] = array_merge(
                        $this->results['performance']['issues'],
                        $issues
                    );
                }
            }
        }
        
        echo "性能审查完成，发现 " . count($this->results['performance']['issues']) . " 个问题\n\n";
    }
    
    private function runQualityReview(string $target): void
    {
        echo "执行代码质量审查...\n";
        $this->results['quality'] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'issues' => []
        ];
        
        if (isset($this->config['quality']['rules'])) {
            foreach ($this->config['quality']['rules'] as $rule) {
                $issues = $this->findPatternInFiles($target, $rule['patterns']);
                if (!empty($issues)) {
                    $this->results['quality']['issues'] = array_merge(
                        $this->results['quality']['issues'],
                        $issues
                    );
                }
            }
        }
        
        echo "代码质量审查完成，发现 " . count($this->results['quality']['issues']) . " 个问题\n\n";
    }
    
    private function runArchitectureReview(string $target): void
    {
        echo "执行架构审查...\n";
        $this->results['architecture'] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'issues' => []
        ];
        
        if (isset($this->config['architecture']['rules'])) {
            foreach ($this->config['architecture']['rules'] as $rule) {
                $issues = $this->findPatternInFiles($target, $rule['patterns']);
                if (!empty($issues)) {
                    $this->results['architecture']['issues'] = array_merge(
                        $this->results['architecture']['issues'],
                        $issues
                    );
                }
            }
        }
        
        echo "架构审查完成，发现 " . count($this->results['architecture']['issues']) . " 个问题\n\n";
    }
    
    private function findPatternInFiles(string $directory, array $patterns): array
    {
        $issues = [];
        
        // 获取所有PHP文件
        $files = $this->getPhpFiles($directory);
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $lines = explode("\n", $content);
            
            foreach ($patterns as $pattern) {
                // 使用正则表达式匹配
                if (preg_match_all("/{$pattern}/i", $content, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        // 计算行号
                        $lineNumber = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                        
                        $issues[] = [
                            'file' => $file,
                            'line' => $lineNumber,
                            'pattern' => $pattern,
                            'match' => $match[0],
                            'severity' => 'medium' // 默认严重性
                        ];
                    }
                }
            }
        }
        
        return $issues;
    }
    
    private function getPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    private function generateReport(): void
    {
        $reportFile = PROJECT_ROOT . '/logs/code-review-report-' . date('Y-m-d-H-i-s') . '.json';
        
        // 确保日志目录存在
        if (!is_dir(PROJECT_ROOT . '/logs')) {
            mkdir(PROJECT_ROOT . '/logs', 0755, true);
        }
        
        // 保存报告
        file_put_contents($reportFile, json_encode($this->results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "代码审查报告已生成: {$reportFile}\n\n";
        
        // 输出摘要
        $this->printSummary();
    }
    
    private function printSummary(): void
    {
        echo "=== 代码审查摘要 ===\n";
        
        $totalIssues = 0;
        foreach (['security', 'performance', 'quality', 'architecture'] as $category) {
            if (isset($this->results[$category]['issues'])) {
                $count = count($this->results[$category]['issues']);
                $totalIssues += $count;
                echo ucfirst($category) . " 问题: {$count}\n";
            }
        }
        
        echo "总问题数: {$totalIssues}\n";
        
        if ($totalIssues > 0) {
            echo "\n请查看详细报告以获取更多信息。\n";
        } else {
            echo "\n未发现问题，代码质量良好！\n";
        }
    }
}

// 主程序入口
if (php_sapi_name() === 'cli') {
    $review = new MultiAgentCodeReview();
    
    // 获取命令行参数
    $target = $argv[1] ?? 'src';
    
    // 运行审查
    $results = $review->runReview($target);
    
    // 如果有严重问题，返回非零退出码
    $hasCriticalIssues = false;
    foreach (['security', 'architecture'] as $category) {
        if (isset($results[$category]['issues']) && count($results[$category]['issues']) > 0) {
            $hasCriticalIssues = true;
            break;
        }
    }
    
    exit($hasCriticalIssues ? 1 : 0);
}