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
use Yangweijie\GmGui\UI\Dialogs\CodeDownloadDialog;

class Sm4DecryptTab extends LibuiComponent
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
            $options = $this->getDecryptionOptions();
            
            // 如果不是ECB模式，则需要IV
            $isEcb = $options['mode'] === 'sm4-ecb';
            if (!$isEcb) {
                if (empty($iv)) {
                    $this->app->showError("在CBC/CFB/OFB/CTR模式下，IV不能为空");
                    return;
                }
                
                // 验证IV长度
                if (strlen($iv) !== 16) {
                    $this->app->showError("IV必须是16个字符");
                    return;
                }
                
                // 使用bin2hex转换IV
                $options['iv'] = bin2hex($iv);
            }
            
            // 使用bin2hex转换密钥
            $sm4Service = $this->app->getSm4Service();
            $sm4Service->setKey($key); // 直接使用16字节密钥
            $result = $sm4Service->decrypt($ciphertext, $options);
            
            // 显示结果
            if ($result->success) {
                $this->resultDisplay->setResult($result->data);
                
                // 生成代码示例
                $code = $this->app->getCodeGenerationService()->generateSm4DecryptCode(
                    $ciphertext,
                    $key,
                    $options
                );
                
                // 显示代码下载对话框
                $this->showCodeDownloadDialog($code);
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
        // 获取SM4主选项卡以获取选项配置
        $sm4MainTab = $this->getSm4MainTab();
        if (!$sm4MainTab) {
            // 如果无法获取主选项卡，使用默认选项
            return [
                'mode' => 'sm4-cbc',
                'inputFormat' => 'hex'
            ];
        }
        
        return [
            'mode' => $sm4MainTab->getMode(),
            'inputFormat' => $sm4MainTab->getInputFormat()
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
        if ($parent !== null) {
            // 继续向上查找父组件，直到找到Sm4MainTab
            while ($parent !== null) {
                if ($parent instanceof Sm4MainTab) {
                    return $parent;
                }
                $parent = $parent->getParent();
            }
        }
        return null;
    }

    /**
     * 显示代码下载对话框
     *
     * @param string $code 生成的代码
     * @return void
     */
    protected function showCodeDownloadDialog(string $code): void
    {
        $dialog = new CodeDownloadDialog(
            $this->app,
            "功能代码下载",
            "操作已完成。是否需要下载实现此功能的PHP代码示例？",
            $code
        );
        
        $dialog->onDownload(function($code, $dialog) {
            // 处理下载代码的逻辑
            $this->handleCodeDownload($code);
        });
        
        $dialog->onCancel(function() {
            // 用户取消操作，无需处理
        });
        
        $dialog->show();
    }

    /**
     * 处理代码下载
     *
     * @param string $code 生成的代码
     * @return void
     */
    protected function handleCodeDownload(string $code): void
    {
        // 将代码复制到剪贴板
        if ($this->app->getUiApp()->copyToClipboard($code)) {
            // 获取主窗口句柄
            $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
            
            // 询问用户是否要保存到文件
            Window::msgBox(
                $mainWindow->getHandle(),
                "代码已复制",
                "代码已复制到剪贴板。是否要将代码保存到文件？\n\n点击确定保存文件，点击取消仅复制到剪贴板。"
            );
            
            // 打开文件保存对话框
            $filePath = Window::saveFile($mainWindow->getHandle());
            
            if (!empty($filePath)) {
                // 确保文件有.php扩展名
                if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'php') {
                    $filePath .= '.php';
                }
                
                // 保存文件
                if (file_put_contents($filePath, $code)) {
                    Window::msgBox(
                        $mainWindow->getHandle(),
                        "保存成功",
                        "代码已成功保存到: " . $filePath
                    );
                } else {
                    $this->app->showError("保存文件失败: " . $filePath);
                }
            }
        } else {
            // 显示错误消息
            $this->app->showError("复制代码到剪贴板失败");
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
        $this->decryptButton->setEnabled($enabled);
        $this->fileSelector->setEnabled($enabled);
        $this->resultDisplay->setEnabled($enabled);
    }
}