<?php

namespace Yangweijie\GmGui\UI\Components;

use Exception;
use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiGroup;
use Kingbes\Libui\SDK\LibuiMultilineEntry;
use Kingbes\Libui\SDK\LibuiCombobox;
use Kingbes\Libui\SDK\LibuiButton;
use Kingbes\Libui\SDK\LibuiLabel;
use FFI\CData;
use Kingbes\Libui\Box;
use Yangweijie\GmGui\Application\SmCryptoApp;

class Sm3HashTab extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 文本输入区域
     *
     * @var LibuiMultilineEntry
     */
    protected LibuiMultilineEntry $textEntry;

    /**
     * 哈希结果显示区域
     *
     * @var LibuiMultilineEntry
     */
    protected LibuiMultilineEntry $hashEntry;

    /**
     * 比较哈希输入框
     *
     * @var LibuiMultilineEntry
     */
    protected LibuiMultilineEntry $compareHashEntry;

    /**
     * 比较结果标签
     *
     * @var LibuiComponent
     */
    protected LibuiComponent $compareResultLabel;

    /**
     * 计算哈希按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $calculateButton;

    /**
     * 比较哈希按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $compareButton;

    /**
     * 复制按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $copyButton;

    /**
     * 输出格式下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $outputFormatCombobox;

    /**
     * 文件选择组件
     *
     * @var FileSelector
     */
    protected FileSelector $fileSelector;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     */
    public function __construct(SmCryptoApp $app)
    {
        $this->app = $app;
        parent::__construct();
        
        // 初始化组件
        $this->initComponents();
    }

    /**
     * 创建组件句柄
     *
     * @return CData 组件句柄
     */
    protected function createHandle(): CData
    {
        // 使用垂直布局容器作为句柄
        return Box::create(1); // 1表示垂直布局
    }

    /**
     * 初始化组件
     *
     * @return void
     */
    protected function initComponents(): void
    {
        // 创建主垂直布局容器
        $mainContainer = new LibuiVBox();
        $mainContainer->setPadded(false); // 减少主容器间距
        
        // 创建哈希计算区域
        $this->createHashArea($mainContainer);
        
        // 创建哈希比较区域
        $this->createCompareArea($mainContainer);
        
        // 设置组件句柄
        $this->handle = $mainContainer->getHandle();
    }

    /**
     * 创建哈希计算区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createHashArea(LibuiVBox $container): void
    {
        $hashGroup = new LibuiGroup("SM3 哈希计算");
        $hashGroup->setPadded(false); // 减少组间距
        
        $hashContainer = new LibuiVBox();
        $hashContainer->setPadded(true); // 减少容器间距
        
        // 文本输入区域
        $textLabel = new LibuiLabel("文本数据:");
        $hashContainer->append($textLabel, false);
        
        // 创建文件选择组件
        $this->fileSelector = new FileSelector($this->app, "选择文件");
        $this->fileSelector->onFileSelected(function($filePath) {
            // 文件选择后自动读取内容到输入区域
            try {
                $content = $this->app->getFileService()->readFile($filePath);
                $this->fileSelector->setContent($content);
            } catch (Exception $e) {
                $this->app->showError("读取文件时出错: " . $e->getMessage());
            }
        });
        $hashContainer->append($this->fileSelector, true); // 可扩展

        // 计算哈希按钮
        $buttonContainer = new LibuiHBox();
        $buttonContainer->setPadded(true);

        // 输出格式选项
        $outputFormatLabel = new LibuiLabel("输出格式:");
        $buttonContainer->append($outputFormatLabel, false);

        $this->outputFormatCombobox = new LibuiCombobox();
        $this->outputFormatCombobox->append("HEX");
        $this->outputFormatCombobox->append("Base64");
        $this->outputFormatCombobox->setSelected(0); // 默认选择HEX
        $buttonContainer->append($this->outputFormatCombobox, false);
        
        $this->calculateButton = new LibuiButton("计算哈希");
        $this->calculateButton->onClick(function() {
            $this->onCalculateClicked();
        });
        $buttonContainer->append($this->calculateButton, false);
        
        $this->copyButton = new LibuiButton("复制结果");
        $this->copyButton->onClick(function() {
            $this->onCopyClicked();
        });
        $buttonContainer->append($this->copyButton, false);

        $hashContainer->append($buttonContainer, false);
        
        // 哈希结果显示
        $hashResultLabel = new LibuiLabel("哈希值:");
        $hashContainer->append($hashResultLabel, false);
        
        $this->hashEntry = new LibuiMultilineEntry();
        $this->hashEntry->setText(""); // 初始为空
        $this->hashEntry->setReadOnly(true); // 只读
        $hashContainer->append($this->hashEntry, true); // 可扩展
        
        $hashGroup->append($hashContainer, true);
        $container->append($hashGroup, true); // 可扩展
    }

    /**
     * 创建哈希比较区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createCompareArea(LibuiVBox $container): void
    {
        $compareGroup = new LibuiGroup("哈希比较");
        $compareGroup->setPadded(false); // 减少组间距
        
        $compareContainer = new LibuiVBox();
        $compareContainer->setPadded(true); // 减少容器间距
        
        // 比较哈希输入
        $compareLabel = new LibuiLabel("要比较的哈希值:");
        $compareContainer->append($compareLabel, false);
        
        // 创建水平布局容器来放置多行文本框和按钮/结果
        $compareHBoxLayout = new LibuiHBox();
        $compareHBoxLayout->setPadded(true);
        
        // 多行文本框
        $this->compareHashEntry = new LibuiMultilineEntry();
        $this->compareHashEntry->setText(""); // 初始为空
        
        // 创建垂直布局容器来放置按钮和结果
        $buttonResultContainer = new LibuiVBox();
        $buttonResultContainer->setPadded(true);
        
        // 比较按钮
        $this->compareButton = new LibuiButton("比较哈希");
        $this->compareButton->onClick(function() {
            $this->onCompareClicked();
        });
        
        // 比较结果显示
        $compareResultLabel = new LibuiLabel("比较结果:");
        $this->compareResultLabel = new LibuiLabel(" 未比较");
        // 添加按钮和结果到垂直容器
        $buttonResultContainer->append($this->compareButton, false);
        $buttonResultContainer->append($compareResultLabel, false);
        $buttonResultContainer->append($this->compareResultLabel, false);
        
        // 添加多行文本框和按钮/结果容器到水平布局
        $compareHBoxLayout->append($this->compareHashEntry, true); // 多行文本框可扩展
        $compareHBoxLayout->append($buttonResultContainer, false); // 按钮/结果容器不扩展
        
        $compareContainer->append($compareHBoxLayout, true); // 水平布局可扩展
        
        $compareGroup->append($compareContainer, true);
        $container->append($compareGroup, true);
    }

    /**
     * 计算哈希按钮点击事件处理
     *
     * @return void
     */
    protected function onCalculateClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            // 获取输入数据
            $data = $this->getInputData();
            if (empty($data)) {
                $this->app->showError("请输入数据或选择文件");
                return;
            }
            
            // 获取选项配置
            $options = $this->getHashOptions();
            
            // 执行哈希计算操作
            $sm3Service = $this->app->getSm3Service();
            $result = $sm3Service->hash($data, $options);
            
            // 显示结果
            if ($result->success) {
                $this->hashEntry->setText($result->data);
            } else {
                $this->app->showError("哈希计算失败: " . $result->error);
            }
        } catch (Exception $e) {
            $this->app->showError("哈希计算时出错: " . $e->getMessage());
        }
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
            $hash = $this->hashEntry->getText();
            if (empty($hash)) {
                $this->app->showError("没有可复制的哈希值");
                return;
            }
            
            // 获取LibUI应用实例并复制到剪贴板
            $uiApp = $this->app->getUiApp();
            if ($uiApp->copyToClipboard($hash)) {
                $this->app->log('哈希值已复制到剪贴板');
            } else {
                $this->app->showError("复制哈希值到剪贴板失败");
            }
        } catch (Exception $e) {
            $this->app->showError("复制哈希值时出错: " . $e->getMessage());
        }
    }

    /**
     * 比较哈希按钮点击事件处理
     *
     * @return void
     */
    protected function onCompareClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            // 获取计算出的哈希值
            $calculatedHash = $this->hashEntry->getText();
            if (empty($calculatedHash)) {
                $this->app->showError("请先计算哈希值");
                return;
            }
            
            // 获取要比较的哈希值
            $compareHash = $this->compareHashEntry->getText();
            if (empty($compareHash)) {
                $this->app->showError("请输入要比较的哈希值");
                return;
            }
            
            // 使用SM3服务比较哈希值
            $sm3Service = $this->app->getSm3Service();
            $comparisonResult = $sm3Service->compareHashes($calculatedHash, $compareHash);
            
            // 显示比较结果
            if ($comparisonResult['equal']) {
                $this->compareResultLabel->setText("相等");
            } else {
                $this->compareResultLabel->setText("不相等");
                $this->app->showError("两个哈希值不相等");
            }
        } catch (Exception $e) {
            $this->app->showError("比较哈希值时出错: " . $e->getMessage());
        }
    }

    /**
     * 获取输入数据
     *
     * @return string 输入数据
     */
    protected function getInputData(): string
    {
        // 直接返回文件选择器中多行文本框的内容
        return $this->fileSelector->getContent();
    }

    /**
     * 获取哈希选项
     *
     * @return array 哈希选项
     */
    protected function getHashOptions(): array
    {
        $outputFormats = ['hex', 'base64'];
        
        return [
            'outputFormat' => $outputFormats[$this->outputFormatCombobox->getSelected()] ?? 'hex'
        ];
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->hashEntry->setReadOnly(!$enabled);
        $this->compareHashEntry->setReadOnly(!$enabled);
        $this->calculateButton->setEnabled($enabled);
        $this->compareButton->setEnabled($enabled);
        $this->copyButton->setEnabled($enabled);
        $this->outputFormatCombobox->setEnabled($enabled);
        $this->fileSelector->setEnabled($enabled);
    }

    /**
     * 格式化哈希值显示
     *
     * @param string $hash 哈希值
     * @return string 格式化后的哈希值
     */
    protected function formatHash(string $hash): string
    {
        // 每4个字符添加一个空格
        return implode(' ', str_split($hash, 4));
    }
}