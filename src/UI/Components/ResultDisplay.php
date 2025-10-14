<?php

namespace Yangweijie\GmGui\UI\Components;

use Exception;
use FFI\CData;
use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiMultilineEntry;
use Kingbes\Libui\SDK\LibuiButton;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiLabel;
use Kingbes\Libui\SDK\LibuiGroup;
use Kingbes\Libui\Window;
use Yangweijie\GmGui\Application\SmCryptoApp;

class ResultDisplay extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 结果显示区域
     *
     * @var LibuiMultilineEntry
     */
    protected LibuiMultilineEntry $resultEntry;

    /**
     * 复制按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $copyButton;

    /**
     * 清空按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $clearButton;

    /**
     * 按钮容器
     *
     * @var LibuiVBox
     */
    protected LibuiVBox $buttonContainer;

    /**
     * 主容器
     *
     * @var LibuiHBox
     */
    protected LibuiHBox $mainContainer;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     * @param string $label 标签文本
     */
    public function __construct(SmCryptoApp $app, string $label = "结果")
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
        // 使用主容器的句柄作为组件句柄
        return $this->mainContainer->getHandle();
    }

    /**
     * 初始化组件
     *
     * @param string $label 标签文本
     * @return void
     */
    protected function initComponents(string $label): void
    {
        // 创建水平布局容器用于结果显示区域和按钮
        $this->mainContainer = new LibuiHBox();
        $this->mainContainer->setPadded(false);
        
        // 创建结果显示区域
        $this->resultEntry = new LibuiMultilineEntry();
        $this->resultEntry->setText(' '); // 初始为空
        $this->resultEntry->setReadOnly(true); // 设置为只读
        
        // 创建按钮垂直布局容器
        $this->buttonContainer = new LibuiVBox();
        $this->buttonContainer->setPadded(true);

        // 创建复制按钮
        $this->copyButton = new LibuiButton("复制");
        $this->copyButton->onClick(function() {
            $this->onCopyClicked();
        });
        
        // 创建清空按钮
        $this->clearButton = new LibuiButton("清空");
        $this->clearButton->onClick(function() {
            $this->onClearClicked();
        });
        
        // 添加按钮到按钮容器
        $this->buttonContainer->append($this->copyButton, true); // 按钮不扩展
        $this->buttonContainer->append($this->clearButton, true); // 按钮不扩展
        
        // 添加组件到主容器
        $this->mainContainer->append($this->resultEntry, true); // 结果显示区域可扩展
        $this->mainContainer->append($this->buttonContainer, false); // 按钮区域不扩展
        
        // 设置组件句柄
        $this->handle = $this->mainContainer->getHandle();
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
            $result = $this->getResult();
            if (!empty($result)) {
                // 使用LibUI的剪贴板功能
                $clipboard = $this->app->getUiApp()->copyToClipboard($result);
                $this->app->log('结果已复制到剪贴板');
            } else {
                $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
                // 显示提示信息而不是错误信息
                Window::msgBox(
                    $mainWindow->getHandle(),
                    "提示",
                    "没有可复制的内容"
                );
            }
        } catch (Exception $e) {
            $this->app->showError("复制结果时出错: " . $e->getMessage());
        }
    }

    /**
     * 清空按钮点击事件处理
     *
     * @return void
     */
    protected function onClearClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        $this->clearResult();
    }

    /**
     * 设置结果显示
     *
     * @param string $result 结果内容
     * @return void
     */
    public function setResult(string $result): void
    {
        $this->resultEntry->setText($result);
    }

    /**
     * 获取结果显示
     *
     * @return string 结果内容
     */
    public function getResult(): string
    {
        return $this->resultEntry->getText();
    }

    /**
     * 清空结果显示
     *
     * @return void
     */
    public function clearResult(): void
    {
        $this->resultEntry->setText("");
    }

    /**
     * 追加结果显示
     *
     * @param string $result 结果内容
     * @return void
     */
    public function appendResult(string $result): void
    {
        $current = $this->getResult();
        if (!empty($current)) {
            $result = $current . "\n" . $result;
        }
        $this->setResult($result);
    }

    /**
     * 格式化并设置结果显示
     *
     * @param string $result 结果内容
     * @param bool $format 是否格式化
     * @return void
     */
    public function setFormattedResult(string $result, bool $format = true): void
    {
        if ($format) {
            // 简单的格式化，每4个字符添加一个空格
            $result = implode(' ', str_split(str_replace(' ', '', $result), 4));
        }
        $this->setResult($result);
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->resultEntry->setReadOnly(!$enabled);
        $this->copyButton->setEnabled($enabled);
        $this->clearButton->setEnabled($enabled);
    }

    /**
     * 绑定结果变更事件
     *
     * @param callable $callback 回调函数
     * @return void
     */
    public function onResultChanged(callable $callback): void
    {
        // 这里可以绑定结果变更后的回调事件
        $this->resultEntry->onChanged(function() use ($callback) {
            $callback($this->getResult());
        });
    }
}
