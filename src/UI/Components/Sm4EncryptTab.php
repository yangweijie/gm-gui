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

class Sm4EncryptTab extends LibuiComponent
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
     * 加密按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $encryptButton;

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
        // 使用水平布局容器来放置加密按钮
        $buttonContainer = new LibuiHBox();
        $buttonContainer->setPadded(true);
        
        // 加密按钮
        $this->encryptButton = new LibuiButton("加密");
        $this->encryptButton->onClick(function() {
            $this->onEncryptClicked();
        });
        $buttonContainer->append($this->encryptButton, true);
        
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
     * 加密按钮点击事件处理
     *
     * @return void
     */
    protected function onEncryptClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            // 获取明文数据
            $plaintext = $this->getInputData();
            if (empty($plaintext)) {
                $this->app->showError("请输入明文数据");
                return;
            }
            
            // 获取SM4主选项卡以获取密钥和IV
            $sm4MainTab = $this->getSm4MainTab();
            if (!$sm4MainTab) {
                $this->app->showError("无法获取SM4主选项卡");
                return;
            }
            
            // 获取SM4密钥
            $key = $sm4MainTab->getKey();
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
            $iv = $sm4MainTab->getIv();
            
            // 获取选项配置
            $options = $this->getEncryptionOptions();
            
            // 如果不是ECB模式，则需要IV
            $isEcb = $options['mode'] === 'sm4-ecb';
            $isGcm = $options['mode'] === 'sm4-gcm';
            if (!$isEcb) {
                if (empty($iv)) {
                    $this->app->showError("在CBC/CFB/OFB/CTR/GCM模式下，IV不能为空");
                    return;
                }
                
                // 验证IV长度（GCM模式需要12字节，其他模式需要16字节）
                $requiredIvLength = $isGcm ? 12 : 16;
                if (strlen($iv) !== $requiredIvLength) {
                    $this->app->showError($isGcm ? "GCM模式下IV必须是12个字符" : "IV必须是16个字符");
                    return;
                }
                
                // 使用bin2hex转换IV
                $options['iv'] = bin2hex($iv);
            }
            
            // 使用bin2hex转换密钥
            $sm4Service = $this->app->getSm4Service();
            $sm4Service->setKey($key); // 直接使用16字节密钥
            $result = $sm4Service->encrypt($plaintext, $options);
            
            // 显示结果
            if ($result->success) {
                $this->resultDisplay->setResult($result->data);
            } else {
                $this->app->showError("加密失败: " . $result->error);
            }
        } catch (Exception $e) {
            $this->app->showError("加密时出错: " . $e->getMessage());
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
     * 获取加密选项
     *
     * @return array 加密选项
     */
    protected function getEncryptionOptions(): array
    {
        // 获取SM4主选项卡以获取选项配置
        $sm4MainTab = $this->getSm4MainTab();
        if (!$sm4MainTab) {
            // 如果无法获取主选项卡，使用默认选项
            return [
                'mode' => 'sm4-cbc',
                'outputFormat' => 'hex'
            ];
        }
        
        return [
            'mode' => $sm4MainTab->getMode(),
            'outputFormat' => $sm4MainTab->getOutputFormat()
        ];
    }

    /**
     * 获取SM4主选项卡
     *
     * @return Sm4MainTab|null SM4主选项卡
     */
    protected function getSm4MainTab(): ?Sm4MainTab
    {
        // 通过父组件链查找SM4主选项卡
        $parent = $this->getParent();
        // 继续向上查找父组件，直到找到Sm4MainTab
        while ($parent !== null) {
            if ($parent instanceof Sm4MainTab) {
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
        $this->encryptButton->setEnabled($enabled);
        $this->fileSelector->setEnabled($enabled);
        $this->resultDisplay->setEnabled($enabled);
    }
}