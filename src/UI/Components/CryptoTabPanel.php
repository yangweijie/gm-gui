<?php

namespace Yangweijie\GmGui\UI\Components;

use Kingbes\Libui\SDK\LibuiGroup;
use Kingbes\Libui\SDK\LibuiMultilineEntry;
use Kingbes\Libui\SDK\LibuiTab;
use FFI\CData;
use Kingbes\Libui\SDK\LibuiVBox;
use Yangweijie\GmGui\Application\SmCryptoApp;

class CryptoTabPanel extends LibuiTab
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     */
    public function __construct(SmCryptoApp $app)
    {
        $this->app = $app;
        parent::__construct();
        
        // 初始化选项卡
        $this->initTabs();
    }

    /**
     * 初始化选项卡
     *
     * @return void
     */
    protected function initTabs(): void
    {
        // 添加 SM2 主选项卡
        $this->addSm2MainTab();

        // 添加 SM3 哈希选项卡
        $this->addSm3HashTab();

        // 添加 SM4 对称加密选项卡
        $this->addSm4EncryptTab();
        
        // 添加帮助选项卡
        $this->addHelpTab();
    }

    /**
     * 添加 SM2 主选项卡
     *
     * @return void
     */
    protected function addSm2MainTab(): void
    {
        $sm2MainTab = new Sm2MainTab($this->app);
        $this->append("SM2 非对称", $sm2MainTab, false);
    }

    

    /**
     * 添加 SM4 对称加密选项卡
     *
     * @return void
     */
    protected function addSm4EncryptTab(): void
    {
        $sm4MainTab = new Sm4MainTab($this->app);
        $this->append("SM4 对称加密", $sm4MainTab, false);
    }

    /**
     * 添加 SM3 哈希选项卡
     *
     * @return void
     */
    protected function addSm3HashTab(): void
    {
        $sm3HashTab = new Sm3HashTab($this->app);
        $this->append("SM3 Hash", $sm3HashTab, false);
    }
    
    /**
     * 添加帮助选项卡
     *
     * @return void
     */
    protected function addHelpTab(): void
    {
        // 创建一个简单的帮助标签页，显示一些基本信息
        $helpContainer = new LibuiVBox();
        $helpContainer->setPadded(true);
        
        $helpGroup = new LibuiGroup("帮助信息");
        $helpGroup->setPadded(false);
        
        $helpTextContainer = new LibuiVBox();
        $helpTextContainer->setPadded(false);
        
        $helpText = new LibuiMultilineEntry();
        $helperText = file_get_contents(__DIR__.'/../../help.txt');
        $helpText->setText($helperText);
        $helpText->setReadOnly(true);
        $helpTextContainer->append($helpText, true);
        
        $helpGroup->append($helpTextContainer);
        $helpContainer->append($helpGroup, true);
        
        $this->append("帮助", $helpContainer, true);
    }
}