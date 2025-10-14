<?php

namespace Yangweijie\GmGui\Application;

use Yangweijie\GmGui\Exceptions\CryptoException;

class GlobalErrorHandler
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 是否已注册错误处理
     *
     * @var bool
     */
    protected bool $registered = false;

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
     * 注册全局错误处理
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        // 注册错误处理函数
        set_error_handler([$this, 'handleError']);
        
        // 注册异常处理函数
        set_exception_handler([$this, 'handleException']);
        
        // 注册致命错误处理函数
        register_shutdown_function([$this, 'handleFatalError']);
        
        $this->registered = true;
    }

    /**
     * 处理PHP错误
     *
     * @param int $errno 错误级别
     * @param string $errstr 错误消息
     * @param string $errfile 错误文件
     * @param int $errline 错误行号
     * @return bool
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // 如果错误被@操作符抑制，不处理
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // 记录错误
        $this->logError($errno, $errstr, $errfile, $errline);

        // 根据错误级别处理
        switch ($errno) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $this->handleFatalErrorInternal($errno, $errstr, $errfile, $errline);
                break;
                
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $this->handleWarning($errno, $errstr, $errfile, $errline);
                break;
                
            case E_NOTICE:
            case E_USER_NOTICE:
                $this->handleNotice($errno, $errstr, $errfile, $errline);
                break;
                
            default:
                $this->handleUnknownError($errno, $errstr, $errfile, $errline);
                break;
        }

        return true;
    }

    /**
     * 处理未捕获的异常
     *
     * @param \Throwable $exception 异常对象
     * @return void
     */
    public function handleException(\Throwable $exception): void
    {
        // 记录异常
        $this->logException($exception);

        // 显示用户友好的错误消息
        $this->showUserFriendlyError($exception);

        // 如果是CryptoException，可能需要特殊处理
        if ($exception instanceof CryptoException) {
            $this->handleCryptoException($exception);
        }
    }

    /**
     * 处理致命错误
     *
     * @return void
     */
    public function handleFatalError(): void
    {
        $error = error_get_last();
        
        if ($error !== null) {
            $fatalErrorTypes = [
                E_ERROR,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
                E_USER_ERROR
            ];
            
            if (in_array($error['type'], $fatalErrorTypes)) {
                $this->handleFatalErrorInternal(
                    $error['type'],
                    $error['message'],
                    $error['file'],
                    $error['line']
                );
            }
        }
    }

    /**
     * 内部处理致命错误
     *
     * @param int $errno 错误级别
     * @param string $errstr 错误消息
     * @param string $errfile 错误文件
     * @param int $errline 错误行号
     * @return void
     */
    protected function handleFatalErrorInternal(int $errno, string $errstr, string $errfile, int $errline): void
    {
        // 构造错误消息
        $errorMessage = "致命错误: {$errstr}\n";
        $errorMessage .= "文件: {$errfile}\n";
        $errorMessage .= "行号: {$errline}\n";
        
        // 记录错误
        error_log($errorMessage, 3, './logs/fatal_error.log');
        
        // 显示错误消息
        if ($this->app) {
            $this->app->showError("发生致命错误，请查看日志文件了解详情。");
        }
        
        // 退出应用
        exit(1);
    }

    /**
     * 处理警告
     *
     * @param int $errno 错误级别
     * @param string $errstr 错误消息
     * @param string $errfile 错误文件
     * @param int $errline 错误行号
     * @return void
     */
    protected function handleWarning(int $errno, string $errstr, string $errfile, int $errline): void
    {
        // 对于某些警告，我们可能想要显示给用户
        $warningMessages = [
            'file_get_contents' => '文件读取警告',
            'file_put_contents' => '文件写入警告',
            'openssl' => '加密相关警告'
        ];
        
        $showToUser = false;
        foreach ($warningMessages as $pattern => $message) {
            if (strpos($errstr, $pattern) !== false) {
                $showToUser = true;
                break;
            }
        }
        
        if ($showToUser && $this->app) {
            $this->app->showError("警告: {$errstr}");
        }
    }

    /**
     * 处理通知
     *
     * @param int $errno 错误级别
     * @param string $errstr 错误消息
     * @param string $errfile 错误文件
     * @param int $errline 错误行号
     * @return void
     */
    protected function handleNotice(int $errno, string $errstr, string $errfile, int $errline): void
    {
        // 通知通常不需要显示给用户，但需要记录
        // 可以根据需要选择性地处理某些通知
    }

    /**
     * 处理未知错误
     *
     * @param int $errno 错误级别
     * @param string $errstr 错误消息
     * @param string $errfile 错误文件
     * @param int $errline 错误行号
     * @return void
     */
    protected function handleUnknownError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        // 记录未知错误
        $this->logError($errno, $errstr, $errfile, $errline);
    }

    /**
     * 处理CryptoException
     *
     * @param CryptoException $exception Crypto异常
     * @return void
     */
    protected function handleCryptoException(CryptoException $exception): void
    {
        // 根据错误类型提供更具体的处理
        switch ($exception->getCode()) {
            case CryptoException::INPUT_VALIDATION_ERROR:
                // 输入验证错误，可能需要提示用户
                if ($this->app) {
                    $this->app->showError("输入验证错误: " . $exception->getMessage());
                }
                break;
                
            case CryptoException::KEY_ERROR:
                // 密钥错误，可能需要提示用户检查密钥
                if ($this->app) {
                    $this->app->showError("密钥错误: " . $exception->getMessage());
                }
                break;
                
            case CryptoException::ALGORITHM_ERROR:
                // 算法错误，记录详细信息
                $this->logException($exception);
                break;
                
            case CryptoException::FILE_OPERATION_ERROR:
                // 文件操作错误，可能需要提示用户检查文件权限
                if ($this->app) {
                    $this->app->showError("文件操作错误: " . $exception->getMessage());
                }
                break;
                
            case CryptoException::SYSTEM_ERROR:
                // 系统错误，记录详细信息
                $this->logException($exception);
                break;
        }
    }

    /**
     * 显示用户友好的错误消息
     *
     * @param \Throwable $exception 异常对象
     * @return void
     */
    protected function showUserFriendlyError(\Throwable $exception): void
    {
        // 不向用户显示详细的错误信息，特别是包含路径的信息
        $userMessage = "发生错误，请稍后重试。";
        
        // 如果是CryptoException，可以提供更具体的错误类型
        if ($exception instanceof CryptoException) {
            switch ($exception->getErrorType()) {
                case 'input_validation':
                    $userMessage = "输入数据有误，请检查后重试。";
                    break;
                case 'key':
                    $userMessage = "密钥相关错误，请检查密钥是否正确。";
                    break;
                case 'algorithm':
                    $userMessage = "算法执行错误，请检查输入数据。";
                    break;
                case 'file_operation':
                    $userMessage = "文件操作错误，请检查文件权限。";
                    break;
                case 'system':
                    $userMessage = "系统错误，请联系技术支持。";
                    break;
            }
        }
        
        // 显示错误消息
        if ($this->app) {
            $this->app->showError($userMessage);
        }
    }

    /**
     * 记录错误
     *
     * @param int $errno 错误级别
     * @param string $errstr 错误消息
     * @param string $errfile 错误文件
     * @param int $errline 错误行号
     * @return void
     */
    protected function logError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        $errorType = $this->getErrorTypeString($errno);
        $message = "[{$errorType}] {$errstr} in {$errfile} on line {$errline}";
        
        // 确保日志目录存在
        $logDir = './logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        error_log($message . PHP_EOL, 3, $logDir . '/error.log');
    }

    /**
     * 记录异常
     *
     * @param \Throwable $exception 异常对象
     * @return void
     */
    protected function logException(\Throwable $exception): void
    {
        $message = "Exception: " . $exception->getMessage() . PHP_EOL;
        $message .= "File: " . $exception->getFile() . PHP_EOL;
        $message .= "Line: " . $exception->getLine() . PHP_EOL;
        $message .= "Trace: " . $exception->getTraceAsString() . PHP_EOL;
        
        // 确保日志目录存在
        $logDir = './logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        error_log($message . PHP_EOL, 3, $logDir . '/exception.log');
    }

    /**
     * 获取错误类型字符串
     *
     * @param int $errno 错误级别
     * @return string 错误类型字符串
     */
    protected function getErrorTypeString(int $errno): string
    {
        $errorTypes = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];
        
        return $errorTypes[$errno] ?? 'Unknown';
    }

    /**
     * 发送错误报告
     *
     * @param \Throwable $exception 异常对象
     * @return void
     */
    public function sendErrorReport(\Throwable $exception): void
    {
        // 收集错误信息
        $errorInfo = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'php_version' => phpversion(),
            'os' => php_uname(),
            'app_version' => defined('APP_VERSION') ? APP_VERSION : 'unknown'
        ];
        
        // 在实际应用中，这里会将错误报告发送到服务器或通过邮件发送
        // 目前只是记录到日志
        $this->logException($exception);
    }
}