<?php

namespace Yangweijie\GmGui\UI\Components;

use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiTab;
use Kingbes\Libui\SDK\LibuiGroup;
use Kingbes\Libui\SDK\LibuiEntry;
use Kingbes\Libui\SDK\LibuiLabel;
use Kingbes\Libui\SDK\LibuiCombobox;
use Kingbes\Libui\SDK\LibuiForm;
use FFI\CData;
use Yangweijie\GmGui\Application\SmCryptoApp;

class Sm4GcmMainTab extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * SM4密钥输入框
     *
     * @var LibuiEntry
     */
    protected LibuiEntry $keyEntry;

    /**
     * IV输入框
     *
     * @var LibuiEntry
     */
    protected LibuiEntry $ivEntry;

    /**
     * AAD输入框
     *
     * @var LibuiEntry
     */
    protected LibuiEntry $aadEntry;

    /**
     * 输出格式下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $outputFormatCombobox;

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
        
        // 创建密钥和IV管理区域
        $this->createKeyIvManagementArea($mainContainer);
        
        // 创建AAD区域
        $this->createAadArea($mainContainer);
        
        // 创建选项配置区域
        $this->createOptionsArea($mainContainer);
        
        // 创建子选项卡区域
        $this->createSubTabArea($mainContainer);
        
        // 设置组件句柄
        $this->handle = $mainContainer->getHandle();
    }

    /**
     * 创建密钥和IV管理区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createKeyIvManagementArea(LibuiVBox $container): void
    {
        $keyIvGroup = new LibuiGroup("密钥和IV");
        $keyIvGroup->setPadded(false);
        
        // 使用LibuiForm来创建表单布局
        $form = new LibuiForm();
        $form->setPadded(true);
        
        // SM4密钥输入
        $this->keyEntry = new LibuiEntry();
        $this->keyEntry->setText(""); // 初始为空
        $form->append("SM4密钥 (16个字符):", $this->keyEntry, true);
        
        // IV输入
        $this->ivEntry = new LibuiEntry();
        $this->ivEntry->setText(""); // 初始为空
        $form->append("IV (12个字符):", $this->ivEntry, true);
        
        $keyIvGroup->append($form,true);
        $container->append($keyIvGroup, false);
    }

    /**
     * 创建AAD区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createAadArea(LibuiVBox $container): void
    {
        $aadGroup = new LibuiGroup("AAD (可选)");
        $aadGroup->setPadded(false);
        
        // 使用LibuiForm来创建表单布局
        $form = new LibuiForm();
        $form->setPadded(true);
        
        // AAD输入
        $this->aadEntry = new LibuiEntry();
        $this->aadEntry->setText(""); // 初始为空
        $form->append("AAD数据 (可选):", $this->aadEntry, true);
        
        $aadGroup->append($form,true);
        $container->append($aadGroup, false);
    }

    /**
     * 创建选项配置区域
     *
     * @param LibuiVBox $container 容器
     * @return void
     */
    protected function createOptionsArea(LibuiVBox $container): void
    {
        $optionsGroup = new LibuiGroup("选项配置");
        $optionsGroup->setPadded(false);
        
        $optionsContainer = new LibuiHBox();
        $optionsContainer->setPadded(true);
        
        // 输出格式选项
        $outputFormatLabel = new LibuiLabel("输出格式:");
        $optionsContainer->append($outputFormatLabel, false);
        
        $this->outputFormatCombobox = new LibuiCombobox();
        $this->outputFormatCombobox->append("HEX");
        $this->outputFormatCombobox->append("Base64");
        $this->outputFormatCombobox->setSelected(0); // 默认选择HEX
        $optionsContainer->append($this->outputFormatCombobox, false);
        
        $optionsGroup->append($optionsContainer);
        $container->append($optionsGroup, false);
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
        $encryptTab = new Sm4GcmEncryptTab($this->app);
        $this->subTabControl->append("加密", $encryptTab, false);
        $this->addChild($encryptTab); // 建立父子关系
        
        // 添加解密子选项卡
        $decryptTab = new Sm4GcmDecryptTab($this->app);
        $this->subTabControl->append("解密", $decryptTab, false);
        $this->addChild($decryptTab); // 建立父子关系
        
        $container->append($this->subTabControl, true); // 可扩展
    }

    /**
     * 获取SM4密钥
     *
     * @return string SM4密钥
     */
    public function getKey(): string
    {
        return $this->keyEntry->getText();
    }

    /**
     * 获取IV
     *
     * @return string IV
     */
    public function getIv(): string
    {
        return $this->ivEntry->getText();
    }

    /**
     * 获取AAD
     *
     * @return string AAD
     */
    public function getAad(): string
    {
        return $this->aadEntry->getText();
    }

    /**
     * 获取输出格式
     *
     * @return string 输出格式
     */
    public function getOutputFormat(): string
    {
        return $this->outputFormatCombobox->getSelected() === 0 ? 'hex' : 'base64';
    }

    /**
     * 获取输入格式
     *
     * @return string 输入格式
     */
    public function getInputFormat(): string
    {
        return $this->outputFormatCombobox->getSelected() === 0 ? 'hex' : 'base64';
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->keyEntry->setReadOnly(!$enabled);
        $this->ivEntry->setReadOnly(!$enabled);
        $this->aadEntry->setReadOnly(!$enabled);
        $this->outputFormatCombobox->setEnabled($enabled);
//        $this->subTabControl->setEnabled($enabled);
    }
}