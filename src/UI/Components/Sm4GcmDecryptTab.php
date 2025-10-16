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
use Kingbes\Libui\SDK\LibuiEntry;
use Kingbes\Libui\SDK\LibuiLabel;
use FFI\CData;
use Kingbes\Libui\Box;
use Kingbes\Libui\Window;
use Yangweijie\GmGui\Application\SmCryptoApp;

class Sm4GcmDecryptTab extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

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
     * 解密按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $decryptButton;

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
        
        // 创建操作按钮组
        $this->createActionButtonsGroup($mainContainer);
        
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
     * 创建操作按钮组
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createActionButtonsGroup(LibuiVBox $container): void
    {
        // 使用水平布局容器来放置解密按钮
        $buttonContainer = new LibuiHBox();
        $buttonContainer->setPadded(true);
        
        // 解密按钮
        $this->decryptButton = new LibuiButton("解密");
        $this->decryptButton->onClick(function() {
            $this->onDecryptClicked();
        });
        $buttonContainer->append($this->decryptButton, true);
        
        $container->append($buttonContainer, true);
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
            // 获取密文数据
            $ciphertext = $this->getInputData();
            if (empty($ciphertext)) {
                $this->app->showError("请输入密文数据");
                return;
            }
            
            // 获取SM4 GCM主选项卡以获取密钥和IV
            $sm4GcmMainTab = $this->getSm4GcmMainTab();
            if (!$sm4GcmMainTab) {
                $this->app->showError("无法获取SM4 GCM主选项卡");
                return;
            }
            
            // 获取SM4密钥
            $key = $sm4GcmMainTab->getKey();
            if (empty($key)) {
                $this->app->showError("请输入SM4密钥");
                return;
            }
            
            // 验证密钥长度
            if (strlen($key) !== 16) {
                $this->app->showError("SM4密钥必须是16个字符");
                return;
            }
            
            // 获取IV
            $iv = $sm4GcmMainTab->getIv();
            if (empty($iv)) {
                $this->app->showError("IV不能为空");
                return;
            }
            
            // 验证IV长度
            if (strlen($iv) !== 12) {
                $this->app->showError("IV必须是12个字符");
                return;
            }
            
            // 获取AAD
            $aad = $sm4GcmMainTab->getAad();
            
            // 获取选项配置
            $options = $this->getDecryptionOptions();
            
            // 准备解密选项
            $decryptOptions = [
                'inputFormat' => $options['inputFormat'],
                'iv' => bin2hex($iv)
            ];
            
            // 如果有AAD，则添加到选项中
            if (!empty($aad)) {
                $decryptOptions['aad'] = $aad;
            }
            
            // 使用bin2hex转换密钥
            $sm4Service = $this->app->getSm4Service();
            $sm4Service->setKey($key); // 直接使用16字节密钥
            $result = $sm4Service->decryptGcm($ciphertext, $decryptOptions);
            
            // 显示结果
            if ($result->success) {
                $this->resultDisplay->setResult($result->data);
            } else {
                $this->app->showError("解密失败: " . $result->error);
            }
        } catch (Exception $e) {
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
        // 获取SM4 GCM主选项卡以获取选项配置
        $sm4GcmMainTab = $this->getSm4GcmMainTab();
        if (!$sm4GcmMainTab) {
            // 如果无法获取主选项卡，使用默认选项
            return [
                'inputFormat' => 'hex'
            ];
        }
        
        return [
            'inputFormat' => $sm4GcmMainTab->getInputFormat()
        ];
    }

    /**
     * 获取SM4 GCM主选项卡
     *
     * @return Sm4GcmMainTab|null SM4 GCM主选项卡
     */
    protected function getSm4GcmMainTab(): ?Sm4GcmMainTab
    {
        // 通过父组件链查找SM4 GCM主选项卡
        $parent = $this->getParent();
        if ($parent !== null) {
            // 继续向上查找父组件，直到找到Sm4GcmMainTab
            while ($parent !== null) {
                if ($parent instanceof Sm4GcmMainTab) {
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
        $this->decryptButton->setEnabled($enabled);
        $this->fileSelector->setEnabled($enabled);
        $this->resultDisplay->setEnabled($enabled);
    }
}