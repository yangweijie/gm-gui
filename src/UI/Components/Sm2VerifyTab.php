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
use Yangweijie\GmGui\Models\KeyPair;
use Yangweijie\GmGui\UI\Dialogs\CodeDownloadDialog;

class Sm2VerifyTab extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 待验签数据输入区域
     *
     * @var LibuiMultilineEntry
     */
    protected LibuiMultilineEntry $dataEntry;

    /**
     * 签名输入区域
     *
     * @var LibuiMultilineEntry
     */
    protected LibuiMultilineEntry $signatureEntry;

    /**
     * 验签结果标签
     *
     * @var LibuiLabel
     */
    protected LibuiLabel $verifyResultLabel;

    /**
     * 验签按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $verifyButton;

    /**
     * 签名格式下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $signatureFormatCombobox;

    /**
     * 输入格式下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $inputFormatCombobox;

    /**
     * 文件选择组件（验签数据）
     *
     * @var FileSelector
     */
    protected FileSelector $verifyFileSelector;

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
        
        // 创建验签数据区域
        $this->createVerifyDataArea($mainContainer);
        
        // 创建签名区域
        $this->createSignatureArea($mainContainer);
        
        // 设置组件句柄
        $this->handle = $mainContainer->getHandle();
    }

    /**
     * 创建验签数据区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createVerifyDataArea(LibuiVBox $container): void
    {
        $dataGroup = new LibuiGroup("待验签字符串");
        $dataGroup->setPadded(false); // 使用setPadded而不是setMargined
        
        $dataContainer = new LibuiVBox();
        $dataContainer->setPadded(true);
        
        // 创建文件选择组件
        $this->verifyFileSelector = new FileSelector($this->app, "选择文件");
        $this->verifyFileSelector->onFileSelected(function($filePath) {
            // 文件选择后自动读取内容到输入区域
            try {
                $content = $this->app->getFileService()->readFile($filePath);
                $this->verifyFileSelector->setContent($content);
            } catch (Exception $e) {
                $this->app->showError("读取文件时出错: " . $e->getMessage());
            }
        });
        $dataContainer->append($this->verifyFileSelector, true); // 可扩展
        
        $dataGroup->append($dataContainer);
        $container->append($dataGroup, true);
    }

    /**
     * 创建签名区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createSignatureArea(LibuiVBox $container): void
    {
        $signatureGroup = new LibuiGroup("签名");
        $signatureGroup->setPadded(false); // 使用setPadded而不是setMargined
        
        $signatureContainer = new LibuiVBox();
        $signatureContainer->setPadded(true);
        
        // 创建选项容器（水平布局）
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
        
        // 输入格式选项
        $inputFormatLabel = new LibuiLabel("输入格式:");
        $optionsContainer->append($inputFormatLabel, false);
        
        $this->inputFormatCombobox = new LibuiCombobox();
        $this->inputFormatCombobox->append("HEX");
        $this->inputFormatCombobox->append("Base64");
        $this->inputFormatCombobox->setSelected(0); // 默认选择HEX
        $optionsContainer->append($this->inputFormatCombobox, false);
        
        // 验签按钮
        $this->verifyButton = new LibuiButton("验签");
        $this->verifyButton->onClick(function() {
            $this->onVerifyClicked();
        });
        $optionsContainer->append($this->verifyButton, false);
        
        // 验签结果标签
        $this->verifyResultLabel = new LibuiLabel("未验签");
        $optionsContainer->append($this->verifyResultLabel, false);
        
        // 添加选项容器到签名容器
        $signatureContainer->append($optionsContainer, false);
        
        // 创建文本输入区域
        $this->signatureEntry = new LibuiMultilineEntry();
        $this->signatureEntry->setText(""); // 初始值设为空
        
        // 添加签名输入区域到签名容器
        $signatureContainer->append($this->signatureEntry, true); // 可扩展
        
        $signatureGroup->append($signatureContainer);
        $container->append($signatureGroup, true); // 可扩展
    }

    /**
     * 验签按钮点击事件处理
     *
     * @return void
     */
    protected function onVerifyClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            // 获取待验签数据
            $data = $this->getVerifyData();
            if (empty($data)) {
                $this->app->showError("请输入待验签的数据");
                return;
            }
            
            // 获取签名
            $signature = $this->signatureEntry->getText();
            
            // 如果签名区域为空，检查待验签数据区域是否包含签名数据
            if (empty($signature)) {
                // 检查待验签数据是否可能是签名数据
                $possibleSignature = $this->dataEntry->getText();
                if (!empty($possibleSignature) && $this->isLikelySignature($possibleSignature)) {
                    $signature = $possibleSignature;
                    // 清空待验签数据区域，因为数据实际上是签名
                    $this->dataEntry->setText("");
                    // 将签名数据放到签名区域
                    $this->signatureEntry->setText($signature);
                } else {
                    $this->app->showError("请输入签名");
                    return;
                }
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
            
            // 获取选中的公钥
            $publicKey = $keyPair->publicKey ?? '';
            if (empty($publicKey)) {
                $this->app->showError("请选择包含公钥的密钥");
                return;
            }
            
            // 获取用户ID
            $userId = $sm2MainTab->getUserId();
            
            // 获取选项配置
            $options = $this->getVerifyOptions();
            $options['publicKey'] = $publicKey;
            if (!empty($userId)) {
                $options['userId'] = $userId;
            }
            
            // 显示进度条
//            $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
            
            // 执行验签操作
            $sm2Service = $this->app->getSm2Service();
            $result = $sm2Service->verify($data, $signature, $options);
            
            // 显示结果
            if ($result->success) {
                if ($result->data === 'valid') {
                    $this->verifyResultLabel->setText("验签通过");
                    
                    // 生成代码示例
                    $code = $this->app->getCodeGenerationService()->generateSm2VerifyCode(
                        $data,
                        $signature,
                        $publicKey,
                        $options
                    );
                    
                    // 显示代码下载对话框
                    $this->showCodeDownloadDialog($code);
                } else {
                    $this->verifyResultLabel->setText("验签失败");
                    $this->app->showError("验签失败");
                }
            } else {
                $this->verifyResultLabel->setText("验签失败");
                $this->app->showError("验签失败: " . $result->error);
            }
        } catch (Exception $e) {
            $this->app->showError("验签时出错: " . $e->getMessage());
        }
    }

    /**
     * 获取验签数据
     *
     * @return string 验签数据
     */
    protected function getVerifyData(): string
    {
        // 直接返回文件选择器中多行文本框的内容
        return $this->verifyFileSelector->getContent();
    }

    /**
     * 获取验签选项
     *
     * @return array 验签选项
     */
    protected function getVerifyOptions(): array
    {
        // 签名格式映射：
        // 0 - ASN.1 (DER格式)
        // 1 - Plain (R|S格式)
        $signatureFormatMap = [
            0 => 'asn1',
            1 => 'plain'
        ];
        
        return [
            'inputFormat' => $this->inputFormatCombobox->getSelected() === 0 ? 'hex' : 'base64',
            'signatureFormat' => $signatureFormatMap[$this->signatureFormatCombobox->getSelected()] ?? 'asn1'
        ];
    }

    /**
     * 检查数据是否可能是签名
     *
     * @param string $data 待检查的数据
     * @return bool 是否可能是签名
     */
    protected function isLikelySignature(string $data): bool
    {
        // 去除空格和换行符
        $data = preg_replace('/\s+/', '', $data);
        
        // 检查是否是十六进制字符串
        if (!ctype_xdigit($data)) {
            return false;
        }
        
        // 检查长度是否符合签名特征
        // ASN.1 DER格式的SM2签名通常是140个十六进制字符左右（70字节）
        // 纯R|S格式的SM2签名通常是128个十六进制字符（64字节）
        $length = strlen($data);
        if ($length < 128 || $length > 200) {
            return false;
        }
        
        // 检查是否以30开头（ASN.1 DER序列标识符）
        if (strpos($data, '30') === 0) {
            return true;
        }
        
        // 检查是否是128个字符的纯R|S格式
        if ($length == 128) {
            return true;
        }
        
        return false;
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
        $this->signatureEntry->setReadOnly(!$enabled);
        $this->verifyButton->setEnabled($enabled);
        $this->signatureFormatCombobox->setEnabled($enabled);
        $this->inputFormatCombobox->setEnabled($enabled);
        $this->verifyFileSelector->setEnabled($enabled);
    }
}