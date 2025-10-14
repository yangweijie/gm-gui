<?php

namespace Yangweijie\GmGui\UI\Components;

use FFI\CData;
use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiEntry;
use Kingbes\Libui\SDK\LibuiMultilineEntry;
use Kingbes\Libui\SDK\LibuiButton;
use Yangweijie\GmGui\Application\SmCryptoApp;

class KeyFieldWithCopyButton extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 文本显示框（单行）
     *
     * @var LibuiEntry|null
     */
    protected ?LibuiEntry $entry = null;

    /**
     * 文本显示框（多行）
     *
     * @var LibuiMultilineEntry|null
     */
    protected ?LibuiMultilineEntry $multilineEntry = null;

    /**
     * 复制按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $copyButton;

    /**
     * 水平布局容器
     *
     * @var LibuiHBox
     */
    protected LibuiHBox $container;

    /**
     * 是否使用多行文本框
     *
     * @var bool
     */
    protected bool $isMultiline = false;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     * @param string $label 标签文本
     * @param bool $multiline 是否使用多行文本框
     */
    public function __construct(SmCryptoApp $app, string $label = "复制", bool $multiline = false)
    {
        $this->app = $app;
        $this->isMultiline = $multiline;
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
        // 使用水平布局容器作为句柄
        return $this->container->getHandle();
    }

    /**
     * 初始化组件
     *
     * @param string $label 按钮标签文本
     * @return void
     */
    protected function initComponents(string $label): void
    {
        // 创建水平布局容器
        $this->container = new LibuiHBox();
        $this->container->setPadded(true);
        
        // 根据是否多行创建相应的文本显示框
        if ($this->isMultiline) {
            // 创建多行文本显示框
            $this->multilineEntry = new LibuiMultilineEntry();
            $this->multilineEntry->setReadOnly(true);
        } else {
            // 创建单行文本显示框
            $this->entry = new LibuiEntry();
            $this->entry->setReadOnly(true);
        }
        
        // 创建复制按钮
        $this->copyButton = new LibuiButton($label);
        $this->copyButton->onClick(function() {
            $this->onCopyClicked();
        });
        
        // 添加组件到容器
        if ($this->isMultiline) {
            $this->container->append($this->multilineEntry, true); // 多行输入框可扩展
        } else {
            $this->container->append($this->entry, true); // 单行输入框可扩展
        }
        $this->container->append($this->copyButton, false); // 按钮不扩展
        
        // 设置组件句柄
        $this->handle = $this->createHandle();
    }

    /**
     * 复制按钮点击事件处理
     *
     * @return void
     */
    protected function onCopyClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            $text = $this->getText();
            if (!empty($text)) {
                // 使用LibUI的剪贴板功能复制文本
                $clipboard = $this->app->getUiApp()->copyToClipboard($text);
                $this->app->log('文本已复制到剪贴板');
            }
        } catch (\Exception $e) {
            $this->app->showError("复制文本时出错: " . $e->getMessage());
        }
    }

    /**
     * 设置文本内容
     *
     * @param string $text 文本内容
     * @return void
     */
    public function setText(string $text): void
    {
        if ($this->isMultiline) {
            $this->multilineEntry->setText($text);
        } else {
            $this->entry->setText($text);
        }
    }

    /**
     * 获取文本内容
     *
     * @return string 文本内容
     */
    public function getText(): string
    {
        if ($this->isMultiline) {
            return $this->multilineEntry->getText();
        } else {
            return $this->entry->getText();
        }
    }

    /**
     * 清空文本内容
     *
     * @return void
     */
    public function clearText(): void
    {
        if ($this->isMultiline) {
            $this->multilineEntry->setText("");
        } else {
            $this->entry->setText("");
        }
    }

    /**
     * 设置是否只读
     *
     * @param bool $readOnly 是否只读
     * @return void
     */
    public function setReadOnly(bool $readOnly): void
    {
        if ($this->isMultiline) {
            $this->multilineEntry->setReadOnly($readOnly);
        } else {
            $this->entry->setReadOnly($readOnly);
        }
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        if ($this->isMultiline) {
            $this->multilineEntry->setEnabled($enabled);
        } else {
            $this->entry->setReadOnly(!$enabled);
        }
        $this->copyButton->setEnabled($enabled);
    }
}