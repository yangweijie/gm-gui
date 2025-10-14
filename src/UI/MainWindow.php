<?php

namespace Yangweijie\GmGui\UI;

use Kingbes\Libui\SDK\LibuiLabel;
use Kingbes\Libui\SDK\LibuiWindow;
use Kingbes\Libui\SDK\LibuiTab;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiVBox;
use FFI\CData;
use Kingbes\Libui\Window;
use Yangweijie\GmGui\Application\SmCryptoApp;
use Yangweijie\GmGui\UI\Components\CryptoTabPanel;

class MainWindow extends LibuiWindow
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 选项卡控件
     *
     * @var LibuiTab
     */
    protected LibuiTab $tabControl;

    /**
     * 状态栏标签
     *
     * @var LibuiLabel
     */
    protected LibuiLabel $statusLabel;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     * @param string $title 窗口标题
     * @param int $width 窗口宽度
     * @param int $height 窗口高度
     */
    public function __construct(SmCryptoApp $app, string $title = "国密客户端", int $width = 1200, int $height = 800)
    {
        $this->app = $app;
        
        // 调用父类构造函数创建窗口，不带菜单栏
        parent::__construct($title, $width, $height, false);
    }

    /**
     * 初始化UI组件（公共方法，供外部调用）
     *
     * @return void
     */
    public function initUI(): void
    {
        $this->initUIComponents();
    }

    /**
     * 初始化UI组件（私有方法）
     *
     * @return void
     */
    private function initUIComponents(): void
    {
        // 创建选项卡控件
        $this->createTabControl();
        
        // 创建状态栏标签
        $this->statusLabel = new LibuiLabel("  就绪");
        
        // 创建垂直布局容器
        $mainVBox = new LibuiVBox();
        $mainVBox->setPadded(false);
        
        // 添加选项卡控件到主布局
        $mainVBox->append($this->tabControl, true); // 拉伸填充
        
        // 添加状态栏到主布局
        $mainVBox->append($this->statusLabel, false); // 不拉伸
        
        // 设置窗口内容
        $this->setChild($mainVBox);

        $this->center();
        
        // 绑定窗口事件
        $this->bindEvents();
        
        // 添加调试信息
        error_log("MainWindow: initUIComponents completed");
    }

    /**
     * 创建选项卡控件
     *
     * @return void
     */
    protected function createTabControl(): void
    {
        // 使用CryptoTabPanel来管理所有加密相关的选项卡
        $this->tabControl = new CryptoTabPanel($this->app);
    }

    /**
     * 绑定窗口事件
     *
     * @return void
     */
    protected function bindEvents(): void
    {
        // 窗口关闭事件
        Window::onClosing($this->handle, function() {
            $this->onWindowClosing();
            // 返回false表示不关闭窗口，让确认对话框来决定是否关闭
            return false;
        });
        
        // 窗口焦点改变事件
        Window::onFocusChanged($this->handle, function() {
            // 窗口焦点改变时的处理
            error_log("MainWindow: Window focus changed");
        });
    }

    /**
     * 窗口关闭事件处理
     *
     * @return void
     */
    protected function onWindowClosing(): void
    {
        error_log("MainWindow: Window closing event triggered");
        
        // 创建确认窗口
        $confirmWindow = $this->app->getUiApp()->createConfirmWindow(
            "确认退出", 
            "确定要退出国密客户端吗？",
            function($window) {  // 确认回调 - 退出应用
                error_log("MainWindow: User confirmed exit");
                // 真正关闭窗口并退出应用
                $this->app->exit();
            },
            function($window) {  // 取消回调 - 不做任何事情
                error_log("MainWindow: User cancelled exit");
                // 用户取消退出操作，不需要做任何事情
            }
        );
        
        // 显示确认窗口
        $confirmWindow->show();
    }

    /**
     * 显示状态消息
     *
     * @param string $message 消息内容
     * @param string $type 消息类型 (info, warning, error)
     * @return void
     */
    public function showStatusMessage(string $message, string $type = 'info'): void
    {
        // 根据消息类型添加前缀
        $prefixedMessage = match($type) {
            'error' => "  ❌ 错误: {$message}",
            'warning' => "  ⚠️ 警告: {$message}",
            'info' => "  ℹ️ {$message}",
            default => $message
        };
        
        $this->statusLabel->setText($prefixedMessage);
        error_log("状态消息 [{$type}]: {$message}");
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
        // 实现进度条显示
        // 这里将在后续实现中完成
        $this->showStatusMessage("开始: {$title}");
    }

    /**
     * 更新进度条
     *
     * @param int $value 当前值
     * @return void
     */
    public function updateProgress(int $value): void
    {
        // 实现进度条更新
        // 这里将在后续实现中完成
        $this->showStatusMessage("进度: {$value}");
    }

    /**
     * 隐藏进度条
     *
     * @return void
     */
    public function hideProgress(): void
    {
        // 实现进度条隐藏
        // 这里将在后续实现中完成
        $this->showStatusMessage("完成");
    }
    
    /**
     * 清空状态栏消息
     *
     * @return void
     */
    public function clearStatusMessage(): void
    {
        $this->statusLabel->setText("  就绪");
    }
}