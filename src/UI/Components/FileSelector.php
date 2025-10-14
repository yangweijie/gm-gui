<?php

namespace Yangweijie\GmGui\UI\Components;

use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiButton;
use Kingbes\Libui\SDK\LibuiEntry;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiLabel;
use Kingbes\Libui\SDK\LibuiMultilineEntry;
use FFI\CData;
use Kingbes\Libui\Box;
use Kingbes\Libui\Window;
use Yangweijie\GmGui\Application\SmCryptoApp;

class FileSelector extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    

    /**
     * 浏览按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $browseButton;

    /**
     * 文件内容显示区域
     *
     * @var LibuiMultilineEntry
     */
    protected LibuiMultilineEntry $contentEntry;

    /**
     * 水平布局容器
     *
     * @var LibuiHBox
     */
    protected LibuiHBox $container;

    /**
     * 文件选择回调函数
     *
     * @var callable|null
     */
    protected $fileSelectedCallback = null;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     * @param string $label 标签文本
     */
    public function __construct(SmCryptoApp $app, string $label = "选择文件")
    {
        $this->app = $app;
        parent::__construct();
        
        // 初始化组件
        $this->initComponents($label);
    }

    /**
     * 创建组件句柄
     *
     * @return CData 组件句柄
     */
    protected function createHandle(): CData
    {
        // 使用水平布局容器作为句柄，包含多行文本框和按钮
        $hbox = new \Kingbes\Libui\SDK\LibuiHBox();
        $hbox->setPadded(true);
        $hbox->append($this->contentEntry, true);
        $hbox->append($this->container, false);
        return $hbox->getHandle();
    }

    /**
     * 初始化组件
     *
     * @param string $label 标签文本
     * @return void
     */
    protected function initComponents(string $label): void
    {
        // 创建文件内容显示区域
        $this->contentEntry = new LibuiMultilineEntry();
        $this->contentEntry->setText(""); // 初始为空
        
        // 创建水平布局容器用于文件选择按钮
        $this->container = new LibuiHBox();
        $this->container->setPadded(true);
        
        // 创建浏览按钮
        $this->browseButton = new LibuiButton($label);
        $this->browseButton->onClick(function() {
            $this->onBrowseClicked();
        });
        
        // 添加按钮到容器
        $this->container->append($this->browseButton, true); // 按钮不扩展
        
        // 设置组件句柄
        $this->handle = $this->createHandle();
    }

    /**
     * 浏览按钮点击事件处理
     *
     * @return void
     */
    protected function onBrowseClicked(): void
    {
        // 清空状态栏消息
        $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
        $mainWindow->clearStatusMessage();
        
        try {
            // 使用Window::openFile方法选择文件
            $selectedFile = Window::openFile($mainWindow->getHandle());
            
            if ($selectedFile !== '') {
                // 读取文件内容并显示在多行文本框中
                $content = file_get_contents($selectedFile);
                if ($content !== false) {
                    $this->contentEntry->setText($content);
                    
                    // 触发文件选择回调
                    $this->triggerFileSelectedCallback($selectedFile);
                } else {
                    $this->app->showError("无法读取文件: " . $selectedFile);
                }
            }
        } catch (\Exception $e) {
            $this->app->showError("选择文件时出错: " . $e->getMessage());
        }
    }

    /**
     * 设置文件内容
     *
     * @param string $content 文件内容
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->contentEntry->setText($content);
    }

    /**
     * 获取文件内容
     *
     * @return string 文件内容
     */
    public function getContent(): string
    {
        return $this->contentEntry->getText();
    }

    /**
     * 清空文件内容
     *
     * @return void
     */
    public function clearContent(): void
    {
        $this->contentEntry->setText("");
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->browseButton->setEnabled($enabled);
        $this->contentEntry->setReadOnly(!$enabled);
    }

    /**
     * 绑定文件选择事件
     *
     * @param callable $callback 回调函数
     * @return void
     */
    public function onFileSelected(callable $callback): void
    {
        // 保存回调函数
        $this->fileSelectedCallback = $callback;
    }

    /**
     * 触发文件选择回调
     *
     * @param string $filePath 文件路径
     * @return void
     */
    protected function triggerFileSelectedCallback(string $filePath): void
    {
        if (isset($this->fileSelectedCallback)) {
            ($this->fileSelectedCallback)($filePath);
        }
    }
}