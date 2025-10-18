<?php

namespace Yangweijie\GmGui\UI\Dialogs;

use Kingbes\Libui\SDK\Enums\WindowPosition;
use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiWindow;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiLabel;
use Kingbes\Libui\SDK\LibuiButton;
use FFI\CData;
use Yangweijie\GmGui\Application\SmCryptoApp;
use Kingbes\Libui\Window as LibuiWindowBase;

/**
 * 代码下载确认对话框
 */
class CodeDownloadDialog
{
    private SmCryptoApp $app;
    private string $title;
    private string $message;
    private string $code;
    private $onDownload = null;
    private $onCancel = null;
    
    // UI组件
    private LibuiWindow $window;
    private LibuiLabel $messageLabel;
    private LibuiButton $downloadButton;
    private LibuiButton $cancelButton;

    public function __construct(SmCryptoApp $app, string $title, string $message, string $code)
    {
        $this->app = $app;
        $this->title = $title;
        $this->message = $message;
        $this->code = $code;
        
        $this->initComponents();
    }

    protected function initComponents(): void
    {
        // 创建窗口
        $this->window = new LibuiWindow($this->title, 400, 200);
//        $this->window->setPadded(true);
        
        // 创建主容器
        $mainContainer = new LibuiVBox();
        $mainContainer->setPadded(true);
        
        // 创建消息标签
        $this->messageLabel = new LibuiLabel($this->message);
        $mainContainer->append($this->messageLabel, false);
        
        // 创建按钮容器
        $buttonContainer = new LibuiHBox();
        $buttonContainer->setPadded(true);
        
        // 创建下载按钮
        $this->downloadButton = new LibuiButton("下载代码");
        $this->downloadButton->onClick(function() {
            $this->onDownloadClicked();
        });
        
        // 创建取消按钮
        $this->cancelButton = new LibuiButton("取消");
        $this->cancelButton->onClick(function() {
            $this->onCancelClicked();
        });
        
        // 添加按钮到容器
        $buttonContainer->append($this->downloadButton, true);
        $buttonContainer->append($this->cancelButton, true);
        
        // 添加组件到主容器
        $mainContainer->append($buttonContainer, false);
//        $this->window->setRelativePosition(WindowPosition::CENTER, $this->app->getMainWindow());
        $this->window->center();
        // 设置窗口内容
        $this->window->setChild($mainContainer);
    }

    /**
     * 设置下载回调
     */
    public function onDownload(callable $callback): self
    {
        $this->onDownload = $callback;
        return $this;
    }

    /**
     * 设置取消回调
     */
    public function onCancel(callable $callback): self
    {
        $this->onCancel = $callback;
        return $this;
    }

    /**
     * 显示对话框
     */
    public function show(): void
    {
        $this->window->show();
    }

    /**
     * 隐藏对话框
     */
    public function hide(): void
    {
        $this->window->hide();
    }

    /**
     * 下载按钮点击事件
     */
    protected function onDownloadClicked(): void
    {
        if ($this->onDownload) {
            call_user_func($this->onDownload, $this->code, $this);
        }
        $this->hide();
    }

    /**
     * 取消按钮点击事件
     */
    protected function onCancelClicked(): void
    {
        if ($this->onCancel) {
            call_user_func($this->onCancel, $this);
        }
        $this->hide();
    }

    /**
     * 获取生成的代码
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * 设置是否启用
     */
    public function setEnabled(bool $enabled): void
    {
        $this->downloadButton->setEnabled($enabled);
        $this->cancelButton->setEnabled($enabled);
    }
}