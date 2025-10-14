<?php

namespace Yangweijie\GmGui\UI\Components;

use Exception;
use Kingbes\Libui\App;
use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiGroup;
use Kingbes\Libui\SDK\LibuiMultilineEntry;
use Kingbes\Libui\SDK\LibuiCombobox;
use Kingbes\Libui\SDK\LibuiButton;
use Kingbes\Libui\SDK\LibuiEntry;
use Kingbes\Libui\SDK\LibuiLabel;
use FFI\CData;
use Kingbes\Libui\Box;
use Kingbes\Libui\Window;
use Yangweijie\GmGui\Application\SmCryptoApp;
use Yangweijie\GmGui\Models\KeyPair;

class Sm2SignTab extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 签名结果显示区域
     *
     * @var ResultDisplay
     */
    protected ResultDisplay $signatureDisplay;

    /**
     * 签名按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $signButton;

    /**
     * 签名格式下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $signatureFormatCombobox;

    /**
     * 输出格式下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $outputFormatCombobox;

    /**
     * 文件选择组件（签名数据）
     *
     * @var FileSelector
     */
    protected FileSelector $signFileSelector;
    private LibuiVBox $signatureContainer;

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
        
        // 创建签名区域
        $this->createSignArea($mainContainer);
        
        // 设置组件句柄
        $this->handle = $mainContainer->getHandle();
    }

    /**
     * 创建签名区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createSignArea(LibuiVBox $container): void
    {
        $signGroup = new LibuiGroup("待签名字符串");
        $signGroup->setPadded(false); // 使用setPadded而不是setMargined
        
        $this->signatureContainer = $signContainer = new LibuiVBox();
        $signContainer->setPadded(true);
        
        // 创建文件选择组件
        $this->signFileSelector = new FileSelector($this->app, "选择文件");
        $this->signFileSelector->onFileSelected(function($filePath) {
            // 文件选择后自动读取内容到输入区域
            try {
                $content = $this->app->getFileService()->readFile($filePath);
                $this->signFileSelector->setContent($content);
            } catch (Exception $e) {
                $this->app->showError("读取文件时出错: " . $e->getMessage());
            }
        });
        $signContainer->append($this->signFileSelector, true); // 可扩展

        $signGroup->append($signContainer);
        $container->append($signGroup, true); // 可扩展

        // 创建选项配置组（参考解密标签页）
        $this->createOptionsGroup($signContainer);

        // 签名结果区域
        $this->createResultGroup($this->signatureContainer);


    }

    /**
     * 创建选项配置组（参考加密标签页）
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createOptionsGroup(LibuiVBox $container): void
    {
        $optionsGroup = new LibuiGroup("结果");
        $optionsGroup->setPadded(false);

        $signatureContainer = new LibuiVBox();
        $signatureContainer->setPadded(true);

        $optionsContainer = new LibuiHBox();
        $optionsContainer->setPadded(true);
        
        // 签名格式选项
        $signatureFormatLabel = new LibuiLabel("签名格式:");
        $optionsContainer->append($signatureFormatLabel, false);
        
        $this->signatureFormatCombobox = new LibuiCombobox();
        $this->signatureFormatCombobox->append("ASN.1");
        $this->signatureFormatCombobox->append("Plain");
        $this->signatureFormatCombobox->setSelected(0); // 默认选择ASN.1
        $optionsContainer->append($this->signatureFormatCombobox, false);
        
        // 输出格式选项
        $outputFormatLabel = new LibuiLabel("输出格式:");
        $optionsContainer->append($outputFormatLabel, false);
        
        $this->outputFormatCombobox = new LibuiCombobox();
        $this->outputFormatCombobox->append("HEX");
        $this->outputFormatCombobox->append("Base64");
        $this->outputFormatCombobox->setSelected(0); // 默认选择HEX
        $optionsContainer->append($this->outputFormatCombobox, false);

        // 创建操作按钮组
        $this->createActionButtonsGroup($optionsContainer);

        $signatureContainer->append($optionsContainer, true);
        $optionsGroup->append($signatureContainer);
        $container->append($optionsGroup, false);
    }

    /**
     * 创建操作按钮组
     *
     * @param LibuiHBox $container 容器
     * @return void
     */
    protected function createActionButtonsGroup(LibuiHBox $container): void
    {
        // 签名按钮
        $this->signButton = new LibuiButton("签名");
        $this->signButton->onClick(function() {
            $this->onSignClicked();
        });
        $container->append($this->signButton, false);
    }

    /**
     * 签名按钮点击事件处理
     *
     * @return void
     */
    protected function onSignClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            // 获取输入数据
            $inputData = $this->getInputData();
            if (empty($inputData)) {
                $this->app->showError("请输入待签名的数据");
                return;
            }
            
            // 获取SM2主选项卡以获取密钥和用户ID
            $sm2MainTab = $this->getSm2MainTab();
            if (!$sm2MainTab) {
                $this->app->showError("无法获取SM2主选项卡");
                return;
            }
            
            // 获取选中的密钥对
            $keyPair = $sm2MainTab->getCurrentKeyPair();
            if ($keyPair === null) {
                $this->app->showError("请选择密钥");
                return;
            }
            
            // 获取选中的私钥
            $privateKey = $keyPair->privateKey ?? '';
            if (empty($privateKey)) {
                $this->app->showError("请选择包含私钥的密钥");
                return;
            }
            
            // 获取用户ID
            $userId = $sm2MainTab->getUserId();
            
            // 获取选项配置
            $options = $this->getSignOptions();
            $options['privateKey'] = $privateKey;
            if (!empty($userId)) {
                $options['userId'] = $userId;
            }
            
            // 显示进度条
//            $this->app->getIntegrationManager()->getMainWindow();
            $self = $this; // 保存当前实例的引用
            App::queueMain(function()use($self, $inputData, $options) {
                // 执行签名操作
                $sm2Service = $self->app->getSm2Service();
                $result = $sm2Service->sign($inputData, $options);
                // 显示结果
                if ($result->success) {
                    $self->signatureDisplay->setResult($result->data);
                } else {
                    $self->app->showError("签名失败: " . $result->error);
                }
            });
        } catch (Exception $e) {
            var_dump($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->app->showError("签名时出错: " . $e->getMessage());
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
        return $this->signFileSelector->getContent();
    }

    /**
     * 获取签名选项
     *
     * @return array 签名选项
     */
    protected function getSignOptions(): array
    {
        return [
            'outputFormat' => $this->outputFormatCombobox->getSelected() === 0 ? 'hex' : 'base64',
            'toRS' => $this->signatureFormatCombobox->getSelected() === 1 // Plain对应toRS=true, ASN.1对应toRS=false
        ];
    }

    /**
     * 创建结果显示组
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createResultGroup(LibuiVBox $container): void
    {
        // 创建结果显示组件
        $this->signatureDisplay = new ResultDisplay($this->app, '');
        $container->append($this->signatureDisplay, true); // 可扩展
    }

    /**
     * 获取SM2主选项卡
     *
     * @return Sm2MainTab|null SM2主选项卡
     */
    protected function getSm2MainTab(): ?Sm2MainTab
    {
        // 通过父组件链查找SM2主选项卡
        $parent = $this->getParent();
        if ($parent !== null) {
            // 继续向上查找父组件，直到找到Sm2MainTab
            while ($parent !== null) {
                if ($parent instanceof Sm2MainTab) {
                    return $parent;
                }
                $parent = $parent->getParent();
            }
        }
        return null;
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
//        $this->signatureEntry->setReadOnly(!$enabled);
        $this->signButton->setEnabled($enabled);
        $this->signatureFormatCombobox->setEnabled($enabled);
        $this->outputFormatCombobox->setEnabled($enabled);
        $this->signFileSelector->setEnabled($enabled);
        $this->signatureDisplay->setEnabled($enabled);
    }
}