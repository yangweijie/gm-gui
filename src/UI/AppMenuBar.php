<?php

namespace Yangweijie\GmGui\UI;

use Kingbes\Libui\SDK\LibuiApplication;
use Kingbes\Libui\SDK\LibuiWindow;
use Kingbes\Libui\Menu;
use Kingbes\Libui\MenuItem;
use FFI\CData;
use Yangweijie\GmGui\Application\SmCryptoApp;

class AppMenuBar
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * Libui应用实例
     *
     * @var LibuiApplication
     */
    protected LibuiApplication $uiApp;

    /**
     * 文件菜单
     *
     * @var CData
     */
    protected CData $fileMenu;

    /**
     * 编辑菜单
     *
     * @var CData
     */
    protected CData $editMenu;

    /**
     * 工具菜单
     *
     * @var CData
     */
    protected CData $toolsMenu;

    /**
     * 帮助菜单
     *
     * @var CData
     */
    protected CData $helpMenu;

    /**
     * 构造函数
     *
     * @param SmCryptoApp $app 应用实例
     * @param LibuiApplication $uiApp Libui应用实例
     */
    public function __construct(SmCryptoApp $app, LibuiApplication $uiApp)
    {
        $this->app = $app;
        $this->uiApp = $uiApp;
        
        // 创建菜单栏
        $this->createMenuBar();
    }

    /**
     * 创建菜单栏
     *
     * @return void
     */
    protected function createMenuBar(): void
    {
        // 创建文件菜单
        $this->createFileMenu();
        
        // 创建编辑菜单
        $this->createEditMenu();
        
        // 创建工具菜单
        $this->createToolsMenu();
        
        // 创建帮助菜单
        $this->createHelpMenu();
    }

    /**
     * 创建文件菜单
     *
     * @return void
     */
    protected function createFileMenu(): void
    {
        $this->fileMenu = $this->uiApp->createMenu("文件");
        
        // 新建
        $newItem = $this->uiApp->appendMenuItem($this->fileMenu, "新建");
        MenuItem::onClicked($newItem, function() {
            $this->onNew();
        });
        
        // 打开
        $openItem = $this->uiApp->appendMenuItem($this->fileMenu, "打开...");
        MenuItem::onClicked($openItem, function() {
            $this->onOpen();
        });
        
        // 保存
        $saveItem = $this->uiApp->appendMenuItem($this->fileMenu, "保存");
        MenuItem::onClicked($saveItem, function() {
            $this->onSave();
        });
        
        // 分隔符
        $this->uiApp->appendMenuSeparator($this->fileMenu);
        
        // 退出
        $exitItem = $this->uiApp->appendMenuItem($this->fileMenu, "退出");
        MenuItem::onClicked($exitItem, function() {
            $this->onExit();
        });
    }

    /**
     * 创建编辑菜单
     *
     * @return void
     */
    protected function createEditMenu(): void
    {
        $this->editMenu = $this->uiApp->createMenu("编辑");
        
        // 撤销
        $undoItem = $this->uiApp->appendMenuItem($this->editMenu, "撤销");
        MenuItem::onClicked($undoItem, function() {
            $this->onUndo();
        });
        
        // 重做
        $redoItem = $this->uiApp->appendMenuItem($this->editMenu, "重做");
        MenuItem::onClicked($redoItem, function() {
            $this->onRedo();
        });
        
        // 分隔符
        $this->uiApp->appendMenuSeparator($this->editMenu);
        
        // 复制
        $copyItem = $this->uiApp->appendMenuItem($this->editMenu, "复制");
        MenuItem::onClicked($copyItem, function() {
            $this->onCopy();
        });
        
        // 粘贴
        $pasteItem = $this->uiApp->appendMenuItem($this->editMenu, "粘贴");
        MenuItem::onClicked($pasteItem, function() {
            $this->onPaste();
        });
        
        // 清空
        $clearItem = $this->uiApp->appendMenuItem($this->editMenu, "清空");
        MenuItem::onClicked($clearItem, function() {
            $this->onClear();
        });
    }

    /**
     * 创建工具菜单
     *
     * @return void
     */
    protected function createToolsMenu(): void
    {
        $this->toolsMenu = $this->uiApp->createMenu("工具");
        
        // 密钥管理
        $keyManagementItem = $this->uiApp->appendMenuItem($this->toolsMenu, "密钥管理...");
        MenuItem::onClicked($keyManagementItem, function() {
            $this->onKeyManagement();
        });
        
        // 分隔符
        $this->uiApp->appendMenuSeparator($this->toolsMenu);
        
        // 设置
        $settingsItem = $this->uiApp->appendMenuItem($this->toolsMenu, "设置...");
        MenuItem::onClicked($settingsItem, function() {
            $this->onSettings();
        });
    }

    /**
     * 创建帮助菜单
     *
     * @return void
     */
    protected function createHelpMenu(): void
    {
        $this->helpMenu = $this->uiApp->createMenu("帮助");
        
        // 帮助文档
        $helpItem = $this->uiApp->appendMenuItem($this->helpMenu, "帮助文档...");
        MenuItem::onClicked($helpItem, function() {
            $this->onHelp();
        });
        
        // 分隔符
        $this->uiApp->appendMenuSeparator($this->helpMenu);
        
        // 关于
        $aboutItem = $this->uiApp->appendMenuItem($this->helpMenu, "关于...");
        MenuItem::onClicked($aboutItem, function() {
            $this->onAbout();
        });
    }

    /**
     * 新建文件
     *
     * @return void
     */
    protected function onNew(): void
    {
        // 实现新建文件功能
        $this->app->showError("新建文件功能尚未实现");
    }

    /**
     * 打开文件
     *
     * @return void
     */
    protected function onOpen(): void
    {
        // 实现打开文件功能
        $this->app->showError("打开文件功能尚未实现");
    }

    /**
     * 保存文件
     *
     * @return void
     */
    protected function onSave(): void
    {
        // 实现保存文件功能
        $this->app->showError("保存文件功能尚未实现");
    }

    /**
     * 退出应用
     *
     * @return void
     */
    protected function onExit(): void
    {
        $this->app->exit();
    }

    /**
     * 撤销操作
     *
     * @return void
     */
    protected function onUndo(): void
    {
        // 实现撤销功能
        $this->app->showError("撤销功能尚未实现");
    }

    /**
     * 重做操作
     *
     * @return void
     */
    protected function onRedo(): void
    {
        // 实现重做功能
        $this->app->showError("重做功能尚未实现");
    }

    /**
     * 复制操作
     *
     * @return void
     */
    protected function onCopy(): void
    {
        // 实现复制功能
        $this->app->showError("复制功能尚未实现");
    }

    /**
     * 粘贴操作
     *
     * @return void
     */
    protected function onPaste(): void
    {
        // 实现粘贴功能
        $this->app->showError("粘贴功能尚未实现");
    }

    /**
     * 清空操作
     *
     * @return void
     */
    protected function onClear(): void
    {
        // 实现清空功能
        $this->app->showError("清空功能尚未实现");
    }

    /**
     * 密钥管理
     *
     * @return void
     */
    protected function onKeyManagement(): void
    {
        // 实现密钥管理功能
        $this->app->showError("密钥管理功能尚未实现");
    }

    /**
     * 设置
     *
     * @return void
     */
    protected function onSettings(): void
    {
        // 实现设置功能
        $this->app->showError("设置功能尚未实现");
    }

    /**
     * 帮助文档
     *
     * @return void
     */
    protected function onHelp(): void
    {
        // 显示帮助对话框
        $helpDialog = new \Yangweijie\GmGui\UI\Dialogs\HelpDialog($this->app);
        $helpDialog->show();
    }

    /**
     * 关于对话框
     *
     * @return void
     */
    protected function onAbout(): void
    {
        // 显示关于对话框
        $this->uiApp->createMessageDialog(
            null, // 没有父窗口
            "关于国密客户端",
            "国密客户端 v1.0.0\n基于 PHP 和 LibUI 开发的国密算法演示工具",
            LibuiWindow::MSGBOX_INFO
        );
    }
}