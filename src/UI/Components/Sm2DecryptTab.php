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
use Kingbes\Libui\SDK\LibuiCheckbox;
use Kingbes\Libui\SDK\LibuiLabel;
use FFI\CData;
use Kingbes\Libui\Box;
use Kingbes\Libui\Window;
use Yangweijie\GmGui\Application\SmCryptoApp;

class Sm2DecryptTab extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 输入区域
     *
     * @var LibuiMultilineEntry
     */
    protected LibuiMultilineEntry $inputEntry;

    /**
     * 解密按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $decryptButton;

    /**
     * 输出格式下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $outputFormatCombobox;

    /**
     * C1补04复选框
     *
     * @var LibuiCheckbox
     */
    protected LibuiCheckbox $appendZeroFourCheckbox;

    /**
     * 加密模式下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $modeCombobox;

    /**
     * 文件选择组件
     *
     * @var FileSelector
     */
    protected FileSelector $fileSelector;

    /**
     * 结果显示组件
     *
     * @var ResultDisplay
     */
    protected ResultDisplay $resultDisplay;

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
        
        // 创建输入区域组
        $this->createInputGroup($mainContainer);
        
        // 创建选项配置组
        $this->createOptionsGroup($mainContainer);
        
        // 创建结果显示组
        $this->createResultGroup($mainContainer);
        
        // 设置组件句柄
        $this->handle = $mainContainer->getHandle();
    }

    /**
     * 创建输入区域组
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createInputGroup(LibuiVBox $container): void
    {
        $inputGroup = new LibuiGroup("输入数据");
        $inputGroup->setPadded(false); // 使用setPadded而不是setMargined
        
        $inputContainer = new LibuiVBox();
        $inputContainer->setPadded(true);
        
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
        $inputContainer->append($this->fileSelector, true); // 可扩展
        
        $inputGroup->append($inputContainer);
        $container->append($inputGroup, true); // 可扩展
    }

    /**
     * 创建选项配置组
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createOptionsGroup(LibuiVBox $container): void
    {
        $optionsGroup = new LibuiGroup("选项配置");
        $optionsGroup->setPadded(false); // 使用setPadded而不是setMargined
        
        $optionsContainer = new LibuiHBox(); // 使用水平布局
        $optionsContainer->setPadded(true);
        
        // 输出格式选项
        $outputFormatLabel = new LibuiLabel("输出格式:");
        $optionsContainer->append($outputFormatLabel, false);
        
        $this->outputFormatCombobox = new LibuiCombobox();
        $this->outputFormatCombobox->append("HEX");
        $this->outputFormatCombobox->append("Base64");
        $this->outputFormatCombobox->setSelected(0); // 默认选择HEX
        $optionsContainer->append($this->outputFormatCombobox, false);
        
        // 加密模式选项
        $modeLabel = new LibuiLabel("加密模式:");
        $optionsContainer->append($modeLabel, false);
        
        $this->modeCombobox = new LibuiCombobox();
        $this->modeCombobox->append("C1C3C2");
        $this->modeCombobox->append("C1C2C3");
        $this->modeCombobox->setSelected(0); // 默认选择C1C3C2
        $optionsContainer->append($this->modeCombobox, false);

        // C1补04选项
        $this->appendZeroFourCheckbox = new LibuiCheckbox("C1补04");
        $this->appendZeroFourCheckbox->setChecked(false);
        $optionsContainer->append($this->appendZeroFourCheckbox, false);
        
        $optionsGroup->append($optionsContainer);
        $container->append($optionsGroup, false);

        // 创建操作按钮组
        $this->createActionButtonsGroup($optionsContainer);
    }

    /**
     * 创建操作按钮组
     *
     * @param LibuiHBox $container 容器
     * @return void
     */
    protected function createActionButtonsGroup(LibuiHBox $container): void
    {
        // 解密按钮
        $this->decryptButton = new LibuiButton("解密");
        $this->decryptButton->onClick(function() {
            $this->onDecryptClicked();
        });
        $container->append($this->decryptButton, false);
    }

    /**
     * 创建结果显示组
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createResultGroup(LibuiVBox $container): void
    {
        $resultGroup = new LibuiGroup("结果");
        $resultGroup->setPadded(false); // 使用setPadded而不是setMargined
        
        // 创建结果显示组件
        $this->resultDisplay = new ResultDisplay($this->app);
        $resultGroup->append($this->resultDisplay);
        
        $container->append($resultGroup, true); // 可扩展
    }

    /**
     * 解密按钮点击事件处理
     *
     * @return void
     */
    protected function onDecryptClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            // 获取输入数据
            $inputData = $this->getInputData();
            if (empty($inputData)) {
                $this->app->showError("请输入要解密的数据");
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
            $options = $this->getDecryptionOptions();
            $options['privateKey'] = $privateKey;
            if (!empty($userId)) {
                $options['userId'] = $userId;
            }
            
            // 显示进度条
//            $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
//            Window::msgBox(
//                $mainWindow->getHandle(),
//                "提示",
//                "正在解密..."
//            );
            
            // 执行解密操作
            $sm2Service = $this->app->getSm2Service();
            $result = $sm2Service->decrypt($inputData, $options);
            
            // 显示结果
            if ($result->success) {
                $this->resultDisplay->setResult($result->data);
//                $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
//                Window::msgBox(
//                    $mainWindow->getHandle(),
//                    "成功",
//                    "解密成功，耗时: " . round($result->executionTime, 4) . " 秒"
//                );
            } else {
                $this->app->showError("解密失败: " . $result->error);
            }
        } catch (Exception $e) {
            var_dump($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->app->showError("解密时出错: " . $e->getMessage());
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
     * 获取解密选项
     *
     * @return array 解密选项
     */
    protected function getDecryptionOptions(): array
    {
        return [
            'inputFormat' => $this->outputFormatCombobox->getSelected() === 0 ? 'hex' : 'base64',
            'appendZeroFour' => $this->appendZeroFourCheckbox->isChecked(),
            'mode' => $this->modeCombobox->getSelected() === 0 ? 'C1C3C2' : 'C1C2C3'
        ];
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
        while ($parent !== null) {
            if ($parent instanceof Sm2MainTab) {
                return $parent;
            }
            $parent = $parent->getParent();
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
        $this->decryptButton->setEnabled($enabled);
        $this->outputFormatCombobox->setEnabled($enabled);
        $this->appendZeroFourCheckbox->setEnabled($enabled);
        $this->modeCombobox->setEnabled($enabled);
        $this->fileSelector->setEnabled($enabled);
        $this->resultDisplay->setEnabled($enabled);
    }
}