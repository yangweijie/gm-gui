<?php

namespace Yangweijie\GmGui\UI\Components;

use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiTab;
use Kingbes\Libui\SDK\LibuiGroup;
use Kingbes\Libui\SDK\LibuiEntry;
use Kingbes\Libui\SDK\LibuiLabel;
use FFI\CData;
use Yangweijie\GmGui\Application\SmCryptoApp;
use Yangweijie\GmGui\Models\KeyPair;

class Sm2MainTab extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 密钥管理组件
     *
     * @var KeyManager
     */
    protected KeyManager $keyManager;

    /**
     * 用户ID输入框
     *
     * @var LibuiEntry
     */
    protected LibuiEntry $userIdEntry;

    /**
     * 子选项卡控件
     *
     * @var LibuiTab
     */
    protected LibuiTab $subTabControl;

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
        return \Kingbes\Libui\Box::create(1); // 1表示垂直布局
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
        $mainContainer->setPadded(true); // 设置适当的间距
        
        // 创建密钥管理区域
        $this->createKeyManagementArea($mainContainer);
        
        // 创建子选项卡区域
        $this->createSubTabArea($mainContainer);
        
        // 设置组件句柄
        $this->handle = $mainContainer->getHandle();
    }

    /**
     * 创建密钥管理区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createKeyManagementArea(LibuiVBox $container): void
    {
        // 创建密钥管理组件
        $this->keyManager = new KeyManager($this->app);
        
        // 将密钥管理组件添加到容器中，并设置为可扩展以占据可用空间
        $container->append($this->keyManager, true);
        
        // 添加用户ID输入框和按钮
        $userIdAndButtonContainer = new LibuiHBox();
        $userIdAndButtonContainer->setPadded(true);

        // 创建用户ID输入框部分
        $userIdContainer = new LibuiHBox();
        $userIdContainer->setPadded(false);

        $userIdLabel = new LibuiLabel("用户ID (可选-仅签名需要):");
        $this->userIdEntry = new LibuiEntry();
        $this->userIdEntry->setText(""); // 初始为空

        $userIdContainer->append($userIdLabel, false);
        $userIdContainer->append($this->userIdEntry, true);
        
        // 获取密钥管理器的按钮
        $generateButton = $this->keyManager->getGenerateButton();
        $importButton = $this->keyManager->getImportButton();
        $exportButton = $this->keyManager->getExportButton();
        $deleteButton = $this->keyManager->getDeleteButton();
        
        // 创建按钮容器
        $buttonContainer = new LibuiHBox();
        $buttonContainer->setPadded(true);
        
        // 添加按钮到按钮容器
        $buttonContainer->append($generateButton, false); // 按钮不扩展
        $buttonContainer->append($importButton, false); // 按钮不扩展
        $buttonContainer->append($exportButton, false); // 按钮不扩展
        $buttonContainer->append($deleteButton, false); // 按钮不扩展

        // 添加用户ID容器和按钮容器到主容器
        $userIdAndButtonContainer->append($userIdContainer, true); // 用户ID容器可扩展
        $userIdAndButtonContainer->append($buttonContainer, false); // 按钮容器不扩展
        
        $container->append($userIdAndButtonContainer, false);
    }

    /**
     * 创建子选项卡区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createSubTabArea(LibuiVBox $container): void
    {
        // 创建子选项卡控件
        $this->subTabControl = new LibuiTab();
        
        // 添加加密子选项卡
        $encryptTab = new Sm2EncryptTab($this->app);
        $this->subTabControl->append("加密", $encryptTab, false);
        $this->addChild($encryptTab); // 建立父子关系
        
        // 添加解密子选项卡
        $decryptTab = new Sm2DecryptTab($this->app);
        $this->subTabControl->append("解密", $decryptTab, false);
        $this->addChild($decryptTab); // 建立父子关系
        
        // 添加签名子选项卡
        $signTab = new Sm2SignTab($this->app);
        $this->subTabControl->append("签名", $signTab, false);
        $this->addChild($signTab); // 建立父子关系
        
        // 添加验签子选项卡
        $verifyTab = new Sm2VerifyTab($this->app);
        $this->subTabControl->append("验签", $verifyTab, false);
        $this->addChild($verifyTab); // 建立父子关系
        
        $container->append($this->subTabControl, false); // 可扩展
    }

    /**
     * 获取用户ID
     *
     * @return string 用户ID
     */
    public function getUserId(): string
    {
        return $this->userIdEntry->getText();
    }

    /**
     * 获取当前选中的密钥对
     *
     * @return KeyPair|null 当前选中的密钥对
     */
    public function getCurrentKeyPair(): ?KeyPair
    {
        return $this->keyManager->getCurrentKeyPair();
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->keyManager->setEnabled($enabled);
        $this->userIdEntry->setReadOnly(!$enabled);
//        $this->subTabControl->setEnabled($enabled);
    }
}