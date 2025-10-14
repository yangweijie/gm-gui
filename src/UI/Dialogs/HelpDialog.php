<?php

namespace Yangweijie\GmGui\UI\Dialogs;

use Kingbes\Libui\SDK\LibuiWindow;
use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiVerticalBox;
use Kingbes\Libui\SDK\LibuiHorizontalBox;
use Kingbes\Libui\SDK\LibuiGroup;
use Kingbes\Libui\SDK\LibuiButton;
use Kingbes\Libui\SDK\LibuiMultilineEntry;
use Kingbes\Libui\SDK\LibuiTab;
use Yangweijie\GmGui\Application\SmCryptoApp;

class HelpDialog extends LibuiWindow
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 选项卡控件
     *
     * @var LibuiTab
     */
    protected LibuiTab $tabControl;

    /**
     * 关闭按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $closeButton;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     */
    public function __construct(SmCryptoApp $app)
    {
        $this->app = $app;
        
        // 调用父类构造函数创建对话框
        parent::__construct("帮助文档", 800, 600, true); // 模态对话框
        
        // 初始化组件
        $this->initComponents();
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
        
        // 创建选项卡控件
        $this->createTabControl($mainContainer);
        
        // 创建按钮区域
        $this->createButtonArea($mainContainer);
        
        // 设置窗口内容
        $this->setChild($mainContainer);
    }

    /**
     * 创建选项卡控件
     *
     * @param LibuiVerticalBox $container 容器
     * @return void
     */
    protected function createTabControl(LibuiVerticalBox $container): void
    {
        $this->tabControl = new LibuiTab();
        
        // 添加介绍选项卡
        $this->addIntroductionTab();
        
        // 添加SM2选项卡
        $this->addSm2Tab();
        
        // 添加SM3选项卡
        $this->addSm3Tab();
        
        // 添加SM4选项卡
        $this->addSm4Tab();
        
        // 添加密钥管理选项卡
        $this->addKeyManagementTab();
        
        // 添加PHP包注意事项选项卡
        $this->addPhpPackagesTab();
        
        $container->append($this->tabControl, true); // 可扩展
    }

    /**
     * 添加介绍选项卡
     *
     * @return void
     */
    protected function addIntroductionTab(): void
    {
        $content = new LibuiVerticalBox();
        $content->setPadded(true);
        
        $introGroup = new LibuiGroup("应用介绍");
        $introGroup->setMargined(true);
        
        $introContainer = new LibuiVerticalBox();
        $introContainer->setPadded(true);
        
        $introText = new LibuiMultilineEntry();
        $introText->setText(
            "国密客户端是一款基于 PHP 和 LibUI 开发的国密算法演示工具。\n\n" .
            "本应用支持以下国密算法:\n" .
            "• SM2: 国家标准的椭圆曲线公钥密码算法，用于加密和数字签名\n" .
            "• SM3: 国家标准的密码杂凑算法，用于生成消息摘要\n" .
            "• SM4: 国家标准的分组密码算法，用于对称加密\n\n" .
            "主要功能包括:\n" .
            "• 数据加密/解密\n" .
            "• 数字签名/验签\n" .
            "• 哈希值计算\n" .
            "• 密钥生成和管理\n" .
            "• 文件批量处理\n\n" .
            "本工具旨在为开发者和安全研究人员提供一个直观的界面来学习和测试国密算法。"
        );
        $introText->setReadOnly(true);
        $introContainer->append($introText, true);
        
        $introGroup->setChild($introContainer);
        $content->append($introGroup, true);
        
        $this->tabControl->append("介绍", $content, true);
    }

    /**
     * 添加SM2选项卡
     *
     * @return void
     */
    protected function addSm2Tab(): void
    {
        $content = new LibuiVerticalBox();
        $content->setPadded(true);
        
        $sm2Group = new LibuiGroup("SM2算法说明");
        $sm2Group->setMargined(true);
        
        $sm2Container = new LibuiVerticalBox();
        $sm2Container->setPadded(true);
        
        $sm2Text = new LibuiMultilineEntry();
        $sm2Text->setText(
            "SM2是国家密码管理局发布的椭圆曲线公钥密码算法，基于椭圆曲线离散对数问题。\n\n" .
            "主要特点:\n" .
            "• 基于椭圆曲线密码学(ECC)\n" .
            "• 使用256位素数域上的椭圆曲线\n" .
            "• 提供数字签名、密钥交换和公钥加密功能\n" .
            "• 安全强度相当于RSA-3072\n\n" .
            "在本应用中的使用:\n" .
            "• 加密/解密: 支持C1C3C2和C1C2C3格式\n" .
            "• 数字签名: 支持ASN.1和RS格式\n" .
            "• 支持自定义用户ID\n" .
            "• 支持HEX和Base64编码格式\n\n" .
            "密钥格式:\n" .
            "• 公钥: 64字节十六进制字符串\n" .
            "• 私钥: 32字节十六进制字符串"
        );
        $sm2Text->setReadOnly(true);
        $sm2Container->append($sm2Text, true);
        
        $sm2Group->setChild($sm2Container);
        $content->append($sm2Group, true);
        
        $this->tabControl->append("SM2算法", $content, true);
    }

    /**
     * 添加SM3选项卡
     *
     * @return void
     */
    protected function addSm3Tab(): void
    {
        $content = new LibuiVerticalBox();
        $content->setPadded(true);
        
        $sm3Group = new LibuiGroup("SM3算法说明");
        $sm3Group->setMargined(true);
        
        $sm3Container = new LibuiVerticalBox();
        $sm3Container->setPadded(true);
        
        $sm3Text = new LibuiMultilineEntry();
        $sm3Text->setText(
            "SM3是国家密码管理局发布的密码杂凑算法，用于生成256位的消息摘要。\n\n" .
            "主要特点:\n" .
            "• 输出256位(32字节)哈希值\n" .
            "• 基于Merkle-Damgård结构\n" .
            "• 具有抗碰撞性和单向性\n" .
            "• 安全强度与SHA-256相当\n\n" .
            "在本应用中的使用:\n" .
            "• 支持文本和文件哈希计算\n" .
            "• 支持HEX和Base64编码格式\n" .
            "• 提供哈希值比较功能\n" .
            "• 支持大文件分块处理\n\n" .
            "输出格式:\n" .
            "• HEX格式: 64个十六进制字符\n" .
            "• Base64格式: 44个字符"
        );
        $sm3Text->setReadOnly(true);
        $sm3Container->append($sm3Text, true);
        
        $sm3Group->setChild($sm3Container);
        $content->append($sm3Group, true);
        
        $this->tabControl->append("SM3算法", $content, true);
    }

    /**
     * 添加SM4选项卡
     *
     * @return void
     */
    protected function addSm4Tab(): void
    {
        $content = new LibuiVerticalBox();
        $content->setPadded(true);
        
        $sm4Group = new LibuiGroup("SM4算法说明");
        $sm4Group->setMargined(true);
        
        $sm4Container = new LibuiVerticalBox();
        $sm4Container->setPadded(true);
        
        $sm4Text = new LibuiMultilineEntry();
        $sm4Text->setText(
            "SM4是国家密码管理局发布的分组密码算法，用于对称加密。\n\n" .
            "主要特点:\n" .
            "• 分组长度为128位(16字节)\n" .
            "• 密钥长度为128位(16字节)\n" .
            "• 支持多种工作模式\n" .
            "• 具有良好的安全性和性能\n\n" .
            "支持的工作模式:\n" .
            "• ECB: 电子密码本模式\n" .
            "• CBC: 密码分组链接模式\n" .
            "• CFB: 密码反馈模式\n" .
            "• OFB: 输出反馈模式\n" .
            "• CTR: 计数器模式\n\n" .
            "在本应用中的使用:\n" .
            "• 支持文本和文件加密/解密\n" .
            "• 自动IV生成和管理\n" .
            "• 支持HEX和Base64编码格式\n" .
            "• 提供大文件分块处理"
        );
        $sm4Text->setReadOnly(true);
        $sm4Container->append($sm4Text, true);
        
        $sm4Group->setChild($sm4Container);
        $content->append($sm4Group, true);
        
        $this->tabControl->append("SM4算法", $content, true);
    }

    /**
     * 添加密钥管理选项卡
     *
     * @return void
     */
    protected function addKeyManagementTab(): void
    {
        $content = new LibuiVerticalBox();
        $content->setPadded(true);
        
        $keyGroup = new LibuiGroup("密钥管理说明");
        $keyGroup->setMargined(true);
        
        $keyContainer = new LibuiVerticalBox();
        $keyContainer->setPadded(true);
        
        $keyText = new LibuiMultilineEntry();
        $keyText->setText(
            "密钥管理功能用于生成、导入、导出和管理国密算法所需的密钥。\n\n" .
            "支持的密钥类型:\n" .
            "• SM2公钥/私钥对\n" .
            "• SM4对称密钥\n\n" .
            "支持的密钥格式:\n" .
            "• HEX格式: 十六进制字符串\n" .
            "• PEM格式: Base64编码的文本格式\n" .
            "• ASN.1格式: 二进制编码格式\n\n" .
            "密钥操作:\n" .
            "• 生成: 自动生成新的密钥对\n" .
            "• 导入: 从文件导入现有密钥\n" .
            "• 导出: 将密钥保存到文件\n" .
            "• 删除: 安全删除密钥文件\n" .
            "• 转换: 在不同格式间转换密钥\n\n" .
            "安全注意事项:\n" .
            "• 私钥应妥善保管，避免泄露\n" .
            "• 密钥文件应存储在安全位置\n" .
            "• 定期更换密钥以提高安全性\n" .
            "• 删除密钥时会安全擦除文件内容"
        );
        $keyText->setReadOnly(true);
        $keyContainer->append($keyText, true);
        
        $keyGroup->setChild($keyContainer);
        $content->append($keyGroup, true);
        
        $this->tabControl->append("密钥管理", $content, true);
    }

    /**
     * 添加PHP包注意事项选项卡
     *
     * @return void
     */
    protected function addPhpPackagesTab(): void
    {
        $content = new LibuiVerticalBox();
        $content->setPadded(true);
        
        $packageGroup = new LibuiGroup("PHP包注意事项");
        $packageGroup->setMargined(true);
        
        $packageContainer = new LibuiVerticalBox();
        $packageContainer->setPadded(true);
        
        $packageText = new LibuiMultilineEntry();
        $packageText->setText(
            "本应用使用了以下两个主要的PHP国密算法包:\n\n" .
            "1. yangweijie/gm-helper:\n" .
            "   • 作者: yangweijie\n" .
            "   • 说明: 一个优化版的SM2国密算法实现\n" .
            "   • 依赖: lpilp/guomi ^2.0.0\n" .
            "   • 特点: 提供了更高效的SM2算法实现，包含了一些优化\n" .
            "   • 注意事项:\n" .
            "     - 需要PHP 7.0或更高版本\n" .
            "     - 需要安装gmp扩展\n" .
            "     - 使用了Symfony的PHP 8.0兼容性包\n\n" .
            "2. lpilp/guomi:\n" .
            "   • 作者: recent\n" .
            "   • 说明: 基础的国密算法实现，主要实现SM2算法\n" .
            "   • 依赖: paragonie/ecc ^2.0 (椭圆曲线密码学库)\n" .
            "   • 特点: 提供了基础的SM2算法实现\n" .
            "   • 注意事项:\n" .
            "     - 需要PHP 7.2或更高版本\n" .
            "     - 基于paragonie/ecc库实现椭圆曲线运算\n\n" .
            "使用注意事项:\n" .
            "• 确保PHP环境中已安装必要的扩展(gmp, json)\n" .
            "• 两个包的版本兼容性需要保持一致\n" .
            "• 在生产环境中使用前应进行充分测试\n" .
            "• 密钥的安全存储和管理是应用安全的关键\n" .
            "• 建议定期更新依赖包以获取最新的安全修复"
        );
        $packageText->setReadOnly(true);
        $packageContainer->append($packageText, true);
        
        $packageGroup->setChild($packageContainer);
        $content->append($packageGroup, true);
        
        $this->tabControl->append("PHP包说明", $content, true);
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
        
        // 弹簧（可扩展空间）
        $spring = new LibuiComponent();
        $buttonContainer->append($spring, true);
        
        // 关闭按钮
        $this->closeButton = new LibuiButton("关闭");
        $this->closeButton->onClick(function() {
            $this->onCloseClicked();
        });
        $buttonContainer->append($this->closeButton, false);
        
        $container->append($buttonContainer, false);
    }

    /**
     * 关闭按钮点击事件处理
     *
     * @return void
     */
    protected function onCloseClicked(): void
    {
        // 关闭对话框
        $this->close();
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->tabControl->setEnabled($enabled);
        $this->closeButton->setEnabled($enabled);
    }
}