<?php

namespace Yangweijie\GmGui\Application;

use Kingbes\Libui\SDK\LibuiWindow;

class UserFeedbackSystem
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 状态栏消息显示组件
     *
     * @var mixed
     */
    protected $statusBar;

    /**
     * 进度条组件
     *
     * @var mixed
     */
    protected $progressBar;

    /**
     * 进度标签组件
     *
     * @var mixed
     */
    protected $progressLabel;

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
     * 显示状态栏消息
     *
     * @param string $message 消息内容
     * @param string $type 消息类型 (info, warning, error, success)
     * @param int $duration 显示持续时间（毫秒），0表示不自动隐藏
     * @return void
     */
    public function showStatusMessage(string $message, string $type = 'info', int $duration = 5000): void
    {
        // 在实际的LibUI实现中，这里会更新状态栏组件
        // 目前只是记录到日志
        $timestamp = date('Y-m-d H:i:s');
        error_log("[{$timestamp}] [{$type}] {$message}");
        
        // 如果有状态栏组件，更新它
        if ($this->statusBar) {
            $this->statusBar->setText("{$type}: {$message}");
            
            // 如果设置了持续时间，安排隐藏消息
            if ($duration > 0) {
                // 在实际应用中，这里需要使用定时器
                // $this->scheduleHideMessage($duration);
            }
        }
    }

    /**
     * 显示成功提示
     *
     * @param string $message 消息内容
     * @param int $duration 显示持续时间（毫秒）
     * @return void
     */
    public function showSuccess(string $message, int $duration = 5000): void
    {
        $this->showStatusMessage($message, 'success', $duration);
        
        // 在实际应用中，这里可能会播放成功音效
        // $this->playSound('success');
    }

    /**
     * 显示警告提示
     *
     * @param string $message 消息内容
     * @param int $duration 显示持续时间（毫秒）
     * @return void
     */
    public function showWarning(string $message, int $duration = 5000): void
    {
        $this->showStatusMessage($message, 'warning', $duration);
        
        // 在实际应用中，这里可能会播放警告音效
        // $this->playSound('warning');
    }

    /**
     * 显示错误提示
     *
     * @param string $message 消息内容
     * @param int $duration 显示持续时间（毫秒）
     * @return void
     */
    public function showError(string $message, int $duration = 0): void
    {
        $this->showStatusMessage($message, 'error', $duration);
        
        // 在实际应用中，这里可能会播放错误音效
        // $this->playSound('error');
    }

    /**
     * 显示进度条
     *
     * @param string $title 进度条标题
     * @param int $max 最大值
     * @return void
     */
    public function showProgress(string $title, int $max = 100): void
    {
        // 在实际的LibUI实现中，这里会显示进度条组件
        $this->showStatusMessage("开始: {$title}");
        
        if ($this->progressLabel) {
            $this->progressLabel->setText($title);
        }
        
        if ($this->progressBar) {
            $this->progressBar->setMin(0);
            $this->progressBar->setMax($max);
            $this->progressBar->setValue(0);
        }
    }

    /**
     * 更新进度条
     *
     * @param int $value 当前值
     * @param string|null $message 进度消息
     * @return void
     */
    public function updateProgress(int $value, ?string $message = null): void
    {
        // 在实际的LibUI实现中，这里会更新进度条组件
        if ($message) {
            $this->showStatusMessage($message);
        }
        
        if ($this->progressBar) {
            $this->progressBar->setValue($value);
        }
    }

    /**
     * 隐藏进度条
     *
     * @param string|null $completionMessage 完成消息
     * @return void
     */
    public function hideProgress(?string $completionMessage = null): void
    {
        // 在实际的LibUI实现中，这里会隐藏进度条组件
        if ($completionMessage) {
            $this->showStatusMessage($completionMessage, 'success');
        } else {
            $this->showStatusMessage("完成");
        }
        
        if ($this->progressBar) {
            $this->progressBar->setValue(0);
        }
    }

    /**
     * 显示确认对话框
     *
     * @param string $title 对话框标题
     * @param string $message 对话框消息
     * @param callable|null $onConfirm 确认回调函数
     * @param callable|null $onCancel 取消回调函数
     * @return void
     */
    public function showConfirmDialog(
        string $title,
        string $message,
        ?callable $onConfirm = null,
        ?callable $onCancel = null
    ): void {
        try {
            // 创建确认对话框
            $result = $this->app->getUiApp()->createMessageDialog(
                null, // 父窗口
                $title,
                $message,
                \Kingbes\Libui\SDK\LibuiWindow::MSGBOX_YESNO
            );
            
            // 处理用户选择
            if ($result && $onConfirm) {
                $onConfirm();
            } elseif (!$result && $onCancel) {
                $onCancel();
            }
        } catch (\Exception $e) {
            $this->showError("显示确认对话框时出错: " . $e->getMessage());
        }
    }

    /**
     * 显示信息对话框
     *
     * @param string $title 对话框标题
     * @param string $message 对话框消息
     * @return void
     */
    public function showInfoDialog(string $title, string $message): void
    {
        try {
            // 创建信息对话框
            $this->app->getUiApp()->createMessageDialog(
                null, // 父窗口
                $title,
                $message,
                \Kingbes\Libui\SDK\LibuiWindow::MSGBOX_INFO
            );
        } catch (\Exception $e) {
            $this->showError("显示信息对话框时出错: " . $e->getMessage());
        }
    }

    /**
     * 显示输入对话框
     *
     * @param string $title 对话框标题
     * @param string $message 对话框消息
     * @param string $defaultValue 默认值
     * @param callable|null $onSubmit 提交回调函数
     * @param callable|null $onCancel 取消回调函数
     * @return void
     */
    public function showInputDialog(
        string $title,
        string $message,
        string $defaultValue = '',
        ?callable $onSubmit = null,
        ?callable $onCancel = null
    ): void {
        // 在实际的LibUI实现中，这里需要创建输入对话框
        // 目前只是显示一个简单的消息
        $this->showInfoDialog($title, $message . "\n\n默认值: " . $defaultValue);
        
        // 模拟用户输入
        if ($onSubmit) {
            $onSubmit($defaultValue);
        }
    }

    /**
     * 显示操作历史
     *
     * @return void
     */
    public function showOperationHistory(): void
    {
        // 在实际应用中，这里会显示操作历史记录
        $this->showInfoDialog("操作历史", "操作历史功能将在后续版本中实现");
    }

    /**
     * 清空操作历史
     *
     * @return void
     */
    public function clearOperationHistory(): void
    {
        // 在实际应用中，这里会清空操作历史记录
        $this->showInfoDialog("操作历史", "操作历史已清空");
    }

    /**
     * 设置状态栏组件
     *
     * @param mixed $statusBar 状态栏组件
     * @return void
     */
    public function setStatusBar($statusBar): void
    {
        $this->statusBar = $statusBar;
    }

    /**
     * 设置进度条组件
     *
     * @param mixed $progressBar 进度条组件
     * @return void
     */
    public function setProgressBar($progressBar): void
    {
        $this->progressBar = $progressBar;
    }

    /**
     * 设置进度标签组件
     *
     * @param mixed $progressLabel 进度标签组件
     * @return void
     */
    public function setProgressLabel($progressLabel): void
    {
        $this->progressLabel = $progressLabel;
    }

    /**
     * 播放音效
     *
     * @param string $soundType 音效类型
     * @return void
     */
    protected function playSound(string $soundType): void
    {
        // 在实际应用中，这里会播放相应的音效
        // 例如：成功音效、警告音效、错误音效等
        // 目前只是占位符
    }

    /**
     * 安排隐藏消息
     *
     * @param int $duration 持续时间（毫秒）
     * @return void
     */
    protected function scheduleHideMessage(int $duration): void
    {
        // 在实际应用中，这里会使用定时器来隐藏消息
        // 例如：setTimeout或类似的机制
        // 目前只是占位符
    }
}