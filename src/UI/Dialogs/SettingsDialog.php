<?php

namespace Yangweijie\GmGui\UI\Dialogs;

use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiWindow;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiGroup;
use Kingbes\Libui\SDK\LibuiLabel;
use Kingbes\Libui\SDK\LibuiButton;
use Kingbes\Libui\SDK\LibuiEntry;
use Kingbes\Libui\SDK\LibuiCombobox;
use Kingbes\Libui\SDK\LibuiRadio;
use Kingbes\Libui\SDK\LibuiCheckbox;
use Yangweijie\GmGui\Application\SmCryptoApp;
use Yangweijie\GmGui\Application\Config\AppConfig;

class SettingsDialog extends LibuiWindow
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 配置对象
     *
     * @var AppConfig
     */
    protected AppConfig $config;

    /**
     * 确定按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $okButton;

    /**
     * 取消按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $cancelButton;

    /**
     * 重置按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $resetButton;

    /**
     * 默认输出格式单选按钮
     *
     * @var LibuiRadio
     */
    protected LibuiRadio $defaultOutputFormatRadio;

    /**
     * SM2 C1补04复选框
     *
     * @var LibuiCheckbox
     */
    protected LibuiCheckbox $sm2AppendZeroFourCheckbox;

    /**
     * SM2模式单选按钮
     *
     * @var LibuiRadio
     */
    protected LibuiRadio $sm2ModeRadio;

    /**
     * SM4默认模式单选按钮
     *
     * @var LibuiRadio
     */
    protected LibuiRadio $sm4DefaultModeRadio;

    /**
     * SM4自动IV复选框
     *
     * @var LibuiCheckbox
     */
    protected LibuiCheckbox $sm4AutoGenerateIvCheckbox;

    /**
     * 签名格式单选按钮
     *
     * @var LibuiRadio
     */
    protected LibuiRadio $signatureFormatRadio;

    /**
     * 默认用户ID输入框
     *
     * @var LibuiEntry
     */
    protected LibuiEntry $defaultUserIdEntry;

    /**
     * 密钥存储路径输入框
     *
     * @var LibuiEntry
     */
    protected LibuiEntry $keyStorePathEntry;

    /**
     * 窗口宽度输入框
     *
     * @var LibuiEntry
     */
    protected LibuiEntry $windowWidthEntry;

    /**
     * 窗口高度输入框
     *
     * @var LibuiEntry
     */
    protected LibuiEntry $windowHeightEntry;

    /**
     * 记住窗口状态复选框
     *
     * @var LibuiCheckbox
     */
    protected LibuiCheckbox $rememberWindowStateCheckbox;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     */
    public function __construct(SmCryptoApp $app)
    {
        $this->app = $app;
        
        // 获取当前配置
        $this->config = $this->app->getConfigService()->getConfig();
        
        // 调用父类构造函数创建对话框
        parent::__construct("设置", 600, 500, true); // 模态对话框
        
        // 初始化组件
        $this->initComponents();
        
        // 加载配置值
        $this->loadConfigValues();
    }

    /**
     * 初始化组件
     *
     * @return void
     */
    protected function initComponents(): void
    {
        // 创建主垂直布局容器
        $mainContainer = new LibuiVerticalBox();
        $mainContainer->setPadded(true);
        
        // 创建加密设置区域
        $this->createCryptoSettingsArea($mainContainer);
        
        // 创建存储设置区域
        $this->createStorageSettingsArea($mainContainer);
        
        // 创建UI设置区域
        $this->createUiSettingsArea($mainContainer);
        
        // 创建按钮区域
        $this->createButtonArea($mainContainer);
        
        // 设置窗口内容
        $this->setChild($mainContainer);
    }

    /**
     * 创建加密设置区域
     *
     * @param LibuiVerticalBox $container 容器
     * @return void
     */
    protected function createCryptoSettingsArea(LibuiVerticalBox $container): void
    {
        $cryptoGroup = new LibuiGroup("加密设置");
        $cryptoGroup->setMargined(true);
        
        $cryptoContainer = new LibuiVerticalBox();
        $cryptoContainer->setPadded(true);
        
        // 默认输出格式选项
        $defaultOutputFormatLabel = new LibuiLabel("默认输出格式:");
        $settingsContainer->append($defaultOutputFormatLabel, false);
        
        $this->defaultOutputFormatRadio = new LibuiRadio();
        $this->defaultOutputFormatRadio->append("HEX");
        $this->defaultOutputFormatRadio->append("Base64");
        $settingsContainer->append($this->defaultOutputFormatRadio, false);
        
        // SM2设置
        $sm2Group = new LibuiGroup("SM2设置");
        $sm2Group->setMargined(true);
        
        $sm2Container = new LibuiVerticalBox();
        $sm2Container->setPadded(true);
        
        // SM2 C1补04
        $this->sm2AppendZeroFourCheckbox = new LibuiCheckbox("C1补04");
        $sm2Container->append($this->sm2AppendZeroFourCheckbox, false);
        
        // SM2模式选项
        $sm2ModeLabel = new LibuiLabel("SM2模式:");
        $settingsContainer->append($sm2ModeLabel, false);
        
        $this->sm2ModeRadio = new LibuiRadio();
        $this->sm2ModeRadio->append("C1C3C2");
        $this->sm2ModeRadio->append("C1C2C3");
        $settingsContainer->append($this->sm2ModeRadio, false);
        
        $sm2Group->setChild($sm2Container);
        $cryptoContainer->append($sm2Group, false);
        
        // SM4设置
        $sm4Group = new LibuiGroup("SM4设置");
        $sm4Group->setMargined(true);
        
        $sm4Container = new LibuiVerticalBox();
        $sm4Container->setPadded(true);
        
        // SM4默认模式选项
        $sm4DefaultModeLabel = new LibuiLabel("SM4默认模式:");
        $settingsContainer->append($sm4DefaultModeLabel, false);
        
        $this->sm4DefaultModeRadio = new LibuiRadio();
        $this->sm4DefaultModeRadio->append("ECB");
        $this->sm4DefaultModeRadio->append("CBC");
        $this->sm4DefaultModeRadio->append("CFB");
        $this->sm4DefaultModeRadio->append("OFB");
        $this->sm4DefaultModeRadio->append("CTR");
        $settingsContainer->append($this->sm4DefaultModeRadio, false);
        
        // SM4自动IV
        $this->sm4AutoGenerateIvCheckbox = new LibuiCheckbox("自动生成IV");
        $sm4Container->append($this->sm4AutoGenerateIvCheckbox, false);
        
        $sm4Group->setChild($sm4Container);
        $cryptoContainer->append($sm4Group, false);
        
        // 签名设置
        $signatureGroup = new LibuiGroup("签名设置");
        $signatureGroup->setMargined(true);
        
        $signatureContainer = new LibuiVerticalBox();
        $signatureContainer->setPadded(true);
        
        // 签名格式选项
        $signatureFormatLabel = new LibuiLabel("签名格式:");
        $settingsContainer->append($signatureFormatLabel, false);
        
        $this->signatureFormatRadio = new LibuiRadio();
        $this->signatureFormatRadio->append("DER");
        $this->signatureFormatRadio->append("SM2");
        $settingsContainer->append($this->signatureFormatRadio, false);
        
        // 默认用户ID
        $defaultUserIdLabel = new LibuiComponent(); // 简化的标签实现
        $defaultUserIdLabel->setText("默认用户ID:");
        $signatureContainer->append($defaultUserIdLabel, false);
        
        $this->defaultUserIdEntry = new LibuiEntry();
        $this->defaultUserIdEntry->setText(""); // 初始为空
        $signatureContainer->append($this->defaultUserIdEntry, false);
        
        $signatureGroup->setChild($signatureContainer);
        $cryptoContainer->append($signatureGroup, false);
        
        $cryptoGroup->setChild($cryptoContainer);
        $container->append($cryptoGroup, true); // 可扩展
    }

    /**
     * 创建存储设置区域
     *
     * @param LibuiVerticalBox $container 容器
     * @return void
     */
    protected function createStorageSettingsArea(LibuiVerticalBox $container): void
    {
        $storageGroup = new LibuiGroup("存储设置");
        $storageGroup->setMargined(true);
        
        $storageContainer = new LibuiVerticalBox();
        $storageContainer->setPadded(true);
        
        // 密钥存储路径
        $keyStorePathLabel = new LibuiComponent(); // 简化的标签实现
        $keyStorePathLabel->setText("密钥存储路径:");
        $storageContainer->append($keyStorePathLabel, false);
        
        $this->keyStorePathEntry = new LibuiEntry();
        $this->keyStorePathEntry->setText(""); // 初始为空
        $storageContainer->append($this->keyStorePathEntry, false);
        
        $storageGroup->setChild($storageContainer);
        $container->append($storageGroup, false);
    }

    /**
     * 创建UI设置区域
     *
     * @param LibuiVerticalBox $container 容器
     * @return void
     */
    protected function createUiSettingsArea(LibuiVerticalBox $container): void
    {
        $uiGroup = new LibuiGroup("界面设置");
        $uiGroup->setMargined(true);
        
        $uiContainer = new LibuiVerticalBox();
        $uiContainer->setPadded(true);
        
        // 窗口尺寸
        $windowSizeLabel = new LibuiComponent(); // 简化的标签实现
        $windowSizeLabel->setText("窗口尺寸:");
        $uiContainer->append($windowSizeLabel, false);
        
        $windowSizeContainer = new LibuiHorizontalBox();
        $windowSizeContainer->setPadded(true);
        
        $widthLabel = new LibuiComponent(); // 简化的标签实现
        $widthLabel->setText("宽度:");
        $windowSizeContainer->append($widthLabel, false);
        
        $this->windowWidthEntry = new LibuiEntry();
        $this->windowWidthEntry->setText(""); // 初始为空
        $windowSizeContainer->append($this->windowWidthEntry, true);
        
        $heightLabel = new LibuiComponent(); // 简化的标签实现
        $heightLabel->setText("高度:");
        $windowSizeContainer->append($heightLabel, false);
        
        $this->windowHeightEntry = new LibuiEntry();
        $this->windowHeightEntry->setText(""); // 初始为空
        $windowSizeContainer->append($this->windowHeightEntry, true);
        
        $uiContainer->append($windowSizeContainer, false);
        
        // 记住窗口状态
        $this->rememberWindowStateCheckbox = new LibuiCheckbox("记住窗口状态");
        $uiContainer->append($this->rememberWindowStateCheckbox, false);
        
        $uiGroup->setChild($uiContainer);
        $container->append($uiGroup, false);
    }

    /**
     * 创建按钮区域
     *
     * @param LibuiVerticalBox $container 容器
     * @return void
     */
    protected function createButtonArea(LibuiVerticalBox $container): void
    {
        $buttonContainer = new LibuiHorizontalBox();
        $buttonContainer->setPadded(true);
        
        // 重置按钮
        $this->resetButton = new LibuiButton("重置为默认");
        $this->resetButton->onClick(function() {
            $this->onResetClicked();
        });
        $buttonContainer->append($this->resetButton, false);
        
        // 弹簧（可扩展空间）
        $spring = new LibuiComponent();
        $buttonContainer->append($spring, true);
        
        // 取消按钮
        $this->cancelButton = new LibuiButton("取消");
        $this->cancelButton->onClick(function() {
            $this->onCancelClicked();
        });
        $buttonContainer->append($this->cancelButton, false);
        
        // 确定按钮
        $this->okButton = new LibuiButton("确定");
        $this->okButton->onClick(function() {
            $this->onOkClicked();
        });
        $buttonContainer->append($this->okButton, false);
        
        $container->append($buttonContainer, false);
    }

    /**
     * 加载配置值
     *
     * @return void
     */
    protected function loadConfigValues(): void
    {
        // 默认输出格式
        if ($this->config->crypto->outputFormat === 'base64') {
            $this->defaultOutputFormatRadio->setSelected(1);
        } else {
            $this->defaultOutputFormatRadio->setSelected(0);
        }
        
        // SM2 C1补04
        $this->sm2AppendZeroFourCheckbox->setChecked($this->config->crypto->appendZeroFour);
        
        // SM2模式
        if ($this->config->crypto->sm2Mode === 'C1C2C3') {
            $this->sm2ModeRadio->setSelected(1);
        } else {
            $this->sm2ModeRadio->setSelected(0);
        }
        
        // SM4默认模式
        $sm4Modes = ['ecb', 'cbc', 'cfb', 'ofb', 'ctr'];
        $sm4ModeIndex = array_search($this->config->crypto->sm4Mode, $sm4Modes);
        if ($sm4ModeIndex !== false) {
            $this->sm4DefaultModeRadio->setSelected($sm4ModeIndex);
        } else {
            $this->sm4DefaultModeRadio->setSelected(1); // 默认CBC
        }
        
        // SM4自动IV
        $this->sm4AutoGenerateIvCheckbox->setChecked($this->config->crypto->sm4->autoGenerateIV ?? true);
        
        // 签名格式
        if ($this->config->crypto->signatureFormat === 'rs') {
            $this->signatureFormatRadio->setSelected(1);
        } else {
            $this->signatureFormatRadio->setSelected(0);
        }
        
        // 默认用户ID
        $this->defaultUserIdEntry->setText($this->config->crypto->signature->defaultUserId ?? '');
        
        // 密钥存储路径
        $this->keyStorePathEntry->setText($this->config->storage['keyStorePath'] ?? './keys');
        
        // 窗口尺寸
        $this->windowWidthEntry->setText((string)($this->config->windowWidth ?? 1200));
        $this->windowHeightEntry->setText((string)($this->config->windowHeight ?? 800));
        
        // 记住窗口状态
        $this->rememberWindowStateCheckbox->setChecked($this->config->rememberWindowState ?? true);
    }

    /**
     * 重置为默认配置
     *
     * @return void
     */
    protected function onResetClicked(): void
    {
        try {
            // 确认重置
            $confirm = $this->app->getUiApp()->createMessageDialog(
                $this,
                "确认重置",
                "确定要重置所有设置为默认值吗？",
                \Kingbes\Libui\SDK\LibuiWindow::MSGBOX_YESNO
            );
            
            if ($confirm) {
                // 重置配置
                $this->app->getConfigService()->resetToDefault();
                
                // 重新加载配置
                $this->config = $this->app->getConfigService()->getConfig();
                
                // 重新加载配置值到UI
                $this->loadConfigValues();
                
                // 显示成功消息
                $this->app->getUiApp()->createMessageDialog(
                    $this,
                    "成功",
                    "设置已重置为默认值",
                    \Kingbes\Libui\SDK\LibuiWindow::MSGBOX_INFO
                );
            }
        } catch (\Exception $e) {
            $this->app->showError("重置设置时出错: " . $e->getMessage());
        }
    }

    /**
     * 取消按钮点击事件处理
     *
     * @return void
     */
    protected function onCancelClicked(): void
    {
        // 关闭对话框
        $this->close();
    }

    /**
     * 确定按钮点击事件处理
     *
     * @return void
     */
    protected function onOkClicked(): void
    {
        try {
            // 验证输入
            if (!$this->validateInputs()) {
                return;
            }
            
            // 构建新的配置数组
            $newConfig = $this->buildConfigArray();
            
            // 保存配置
            $this->app->getConfigService()->updateConfig($newConfig);
            
            // 显示成功消息
            $this->app->getUiApp()->createMessageDialog(
                $this,
                "成功",
                "设置已保存",
                \Kingbes\Libui\SDK\LibuiWindow::MSGBOX_INFO
            );
            
            // 关闭对话框
            $this->close();
        } catch (\Exception $e) {
            $this->app->showError("保存设置时出错: " . $e->getMessage());
        }
    }

    /**
     * 验证输入
     *
     * @return bool 是否验证通过
     */
    protected function validateInputs(): bool
    {
        // 验证窗口宽度
        $width = $this->windowWidthEntry->getText();
        if (!is_numeric($width) || (int)$width <= 0) {
            $this->app->showError("窗口宽度必须是正整数");
            return false;
        }
        
        // 验证窗口高度
        $height = $this->windowHeightEntry->getText();
        if (!is_numeric($height) || (int)$height <= 0) {
            $this->app->showError("窗口高度必须是正整数");
            return false;
        }
        
        // 验证密钥存储路径
        $keyStorePath = $this->keyStorePathEntry->getText();
        if (empty($keyStorePath)) {
            $this->app->showError("密钥存储路径不能为空");
            return false;
        }
        
        return true;
    }

    /**
     * 构建配置数组
     *
     * @return array 配置数组
     */
    protected function buildConfigArray(): array
    {
        // 获取SM4模式
        $sm4Modes = ['ecb', 'cbc', 'cfb', 'ofb', 'ctr'];
        $selectedSm4Mode = $this->sm4DefaultModeRadio->getSelected();
        $sm4Mode = $sm4Modes[$selectedSm4Mode] ?? 'cbc';
        
        $config = [
            'app' => [
                'version' => $this->config->version ?? '1.0.0',
                'language' => $this->config->language ?? 'zh-CN',
                'theme' => $this->config->theme ?? 'default'
            ],
            'crypto' => [
                'defaultOutputFormat' => $this->defaultOutputFormatRadio->getSelected() === 1 ? 'base64' : 'hex',
                'sm2' => [
                    'appendZeroFour' => $this->sm2AppendZeroFourCheckbox->getChecked(),
                    'mode' => $this->sm2ModeRadio->getSelected() === 1 ? 'C1C2C3' : 'C1C3C2'
                ],
                'sm4' => [
                    'defaultMode' => $sm4Mode,
                    'autoGenerateIV' => $this->sm4AutoGenerateIvCheckbox->getChecked()
                ],
                'signature' => [
                    'defaultFormat' => $this->signatureFormatRadio->getSelected() === 1 ? 'rs' : 'asn1',
                    'defaultUserId' => $this->defaultUserIdEntry->getText()
                ]
            ],
            'storage' => [
                'keyStorePath' => $this->keyStorePathEntry->getText(),
                'configPath' => $this->config->storage['configPath'] ?? './config',
                'tempPath' => $this->config->storage['tempPath'] ?? './temp'
            ],
            'ui' => [
                'windowWidth' => (int)$this->windowWidthEntry->getText(),
                'windowHeight' => (int)$this->windowHeightEntry->getText(),
                'rememberWindowState' => $this->rememberWindowStateCheckbox->getChecked()
            ]
        ];
        
        return $config;
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->defaultOutputFormatRadio->setEnabled($enabled);
        $this->sm2AppendZeroFourCheckbox->setEnabled($enabled);
        $this->sm2ModeRadio->setEnabled($enabled);
        $this->sm4DefaultModeRadio->setEnabled($enabled);
        $this->sm4AutoGenerateIvCheckbox->setEnabled($enabled);
        $this->signatureFormatRadio->setEnabled($enabled);
        $this->defaultUserIdEntry->setEnabled($enabled);
        $this->keyStorePathEntry->setEnabled($enabled);
        $this->windowWidthEntry->setEnabled($enabled);
        $this->windowHeightEntry->setEnabled($enabled);
        $this->rememberWindowStateCheckbox->setEnabled($enabled);
        $this->okButton->setEnabled($enabled);
        $this->cancelButton->setEnabled($enabled);
        $this->resetButton->setEnabled($enabled);
    }
}