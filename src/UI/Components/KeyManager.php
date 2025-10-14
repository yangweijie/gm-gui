<?php

namespace Yangweijie\GmGui\UI\Components;

use Exception;
use FFI\CData;
use Kingbes\Libui\App;
use Kingbes\Libui\SDK\LibuiComponent;
use Kingbes\Libui\SDK\LibuiCombobox;
use Kingbes\Libui\SDK\LibuiButton;
use Kingbes\Libui\SDK\LibuiForm;
use Kingbes\Libui\SDK\LibuiHBox;
use Kingbes\Libui\SDK\LibuiVBox;
use Kingbes\Libui\SDK\LibuiEntry;
use Kingbes\Libui\SDK\LibuiWindow;
use Kingbes\Libui\SDK\MessageBox;
use Kingbes\Libui\Window;
use ReflectionClass;
use Yangweijie\GmGui\Application\SmCryptoApp;
use Yangweijie\GmGui\Models\KeyPair;
use Yangweijie\GmGui\UI\Components\KeyFieldWithCopyButton;

class KeyManager extends LibuiComponent
{
    /**
     * 应用实例
     *
     * @var SmCryptoApp
     */
    protected SmCryptoApp $app;

    /**
     * 密钥选择下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $keyCombobox;

    /**
     * 生成密钥按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $generateButton;

    /**
     * 导入密钥按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $importButton;

    /**
     * 导出密钥按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $exportButton;

    /**
     * 删除密钥按钮
     *
     * @var LibuiButton
     */
    protected LibuiButton $deleteButton;

    /**
     * 密钥格式选择下拉框
     *
     * @var LibuiCombobox
     */
    protected LibuiCombobox $formatCombobox;

    /**
     * 按钮容器
     *
     * @var LibuiHBox
     */
    protected LibuiHBox $buttonContainer;

    /**
     * 主容器
     *
     * @var LibuiVBox
     */
    protected LibuiVBox $mainContainer;

    /**
     * 公钥显示框
     *
     * @var KeyFieldWithCopyButton
     */
    protected KeyFieldWithCopyButton $publicKeyEntry;

    /**
     * 私钥显示框
     *
     * @var KeyFieldWithCopyButton
     */
    protected KeyFieldWithCopyButton $privateKeyEntry;

    /**
     * 密钥列表
     *
     * @var array
     */
    protected array $keyList = [];

    /**
     * 密钥选择回调函数
     *
     * @var callable|null
     */
    protected $keySelectedCallback = null;

    /**
     * 当前选中的密钥对
     *
     * @var KeyPair|null
     */
    protected ?KeyPair $currentKeyPair = null;

    /**
     * 当前的消息框实例
     *
     * @var MessageBox|null
     */
    protected ?MessageBox $currentMessageBox = null;

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
        
        // 加载密钥列表
        $this->loadKeyList();
    }

    /**
     * 创建组件句柄
     *
     * @return CData 组件句柄
     */
    protected function createHandle(): CData
    {
        // 使用主容器的句柄作为组件句柄
        return $this->mainContainer->getHandle();
    }

    /**
     * 初始化组件
     * @return void
     */
    protected function initComponents(): void
    {
        // 创建主垂直布局容器
        $mainContainer = new LibuiVBox();
        $mainContainer->setPadded(true); // 设置适当的间距
        
        // 创建表单容器
        $formContainer = new LibuiForm();
        $formContainer->setPadded(true); // 表单内部不额外添加间距

        // 创建密钥选择下拉框
        $this->keyCombobox = new LibuiCombobox();
        $this->keyCombobox->append("请选择密钥..."); // 初始选项
        $this->keyCombobox->setSelected(0);
        $this->keyCombobox->onSelected(function() {
            App::queueMain(function(){
                $this->onKeySelected();
            });
        });

        // 创建公钥显示框（使用多行文本框）
        $this->publicKeyEntry = new KeyFieldWithCopyButton($this->app, "复制", false);
        $this->publicKeyEntry->setReadOnly(false);
        
        // 创建私钥显示框（使用单行文本框）
        $this->privateKeyEntry = new KeyFieldWithCopyButton($this->app, "复制", false);
        $this->privateKeyEntry->setReadOnly(false);
        
        // 创建密钥格式选择下拉框
        $this->formatCombobox = new LibuiCombobox();
        $this->formatCombobox->append("HEX");
        $this->formatCombobox->append("PEM");
        $this->formatCombobox->setSelected(0); // 默认选择HEX格式
        $this->formatCombobox->onSelected(function() {
            App::queueMain(function(){
                $this->onFormatSelected();
            });
        });
        
        // 创建生成密钥按钮
        $this->generateButton = new LibuiButton("生成");
        $this->generateButton->onClick(function() {
            $this->onGenerateClicked();
        });
        
        // 创建导入密钥按钮
        $this->importButton = new LibuiButton("导入");
        $this->importButton->onClick(function() {
            $this->onImportClicked();
        });
        
        // 创建导出密钥按钮
        $this->exportButton = new LibuiButton("导出");
        $this->exportButton->onClick(function() {
            $this->onExportClicked();
        });
        
        // 创建删除密钥按钮
        $this->deleteButton = new LibuiButton("删除");
        $this->deleteButton->onClick(function() {
            $this->onDeleteClicked();
        });
        
        // 添加组件到表单容器
        $formContainer->append('密钥:', $this->keyCombobox, false); // 下拉框扩展
        $formContainer->append('公钥:', $this->publicKeyEntry, false); // 输入框扩展
        $formContainer->append('私钥:', $this->privateKeyEntry, false); // 输入框扩展
        $formContainer->append('密钥格式:', $this->formatCombobox, false); // 下拉框扩展
        
        // 添加表单容器到主容器
        $mainContainer->append($formContainer, false); // 表单容器扩展
        
        // 保存主容器引用
        $this->mainContainer = $mainContainer;
        
        // 设置组件句柄
        $this->handle = $this->mainContainer->getHandle();
    }

    /**
     * 加载密钥列表
     *
     * @return void
     */
    protected function loadKeyList(): void
    {
        try {
            // 获取密钥管理服务
            $keyService = $this->app->getKeyManagementService();
            
            // 获取密钥列表
            $keys = $keyService->listKeys();
            
            // 清空现有选项
            $this->keyCombobox->clear();
            $this->keyCombobox->append("请选择密钥..."); // 默认选项
            
            // 添加密钥到下拉框
            foreach ($keys as $key) {
                $this->keyCombobox->append($key);
            }
            
            // 保存密钥列表
            $this->keyList = $keys;
            
            // 设置默认选中项
            $this->keyCombobox->setSelected(0);
        } catch (Exception $e) {
            $this->app->showError("加载密钥列表时出错: " . $e->getMessage());
        }
    }

    /**
     * 密钥选择事件处理
     *
     * @return void
     */
    protected function onKeySelected(): void
    {
        // 清空状态栏消息
        $this->app->onButtonClick();
        
        try {
            $selectedIndex = $this->keyCombobox->getSelected();
            
            // 检查是否选择了有效的密钥
            if ($selectedIndex <= 0 || $selectedIndex > count($this->keyList)) {
                // 清空公钥和私钥显示
                $this->publicKeyEntry->setText("");
                $this->privateKeyEntry->setText("");
                $this->currentKeyPair = null;
                return;
            }
            
            $keyName = $this->keyList[$selectedIndex - 1]; // 减去默认选项的索引
            
            // 获取密钥管理服务
            $keyService = $this->app->getKeyManagementService();
            
            // 获取选择的格式
            $format = $this->formatCombobox->getSelected() === 0 ? 'hex' : 'pem';
            
            // 加载密钥对
            $keyPair = $keyService->loadKeyPair($keyName, $format);
            
            // 保存当前选中的密钥对
            $this->currentKeyPair = $keyPair;
            
            // 显示公钥和私钥
            $publicKey = $keyPair->publicKey ?? "";
            $privateKey = $keyPair->privateKey ?? "";
            
            $this->publicKeyEntry->setText($publicKey);
            $this->privateKeyEntry->setText($privateKey);
            
            // 触发密钥选择回调
            $this->triggerKeySelectedCallback($keyName, $keyPair);
        } catch (Exception $e) {
            $this->app->showError("加载密钥时出错: " . $e->getMessage());
            // 清空公钥和私钥显示
            $this->publicKeyEntry->setText("");
            $this->privateKeyEntry->setText("");
            $this->currentKeyPair = null;
        }
    }

    /**
     * 密钥格式选择事件处理
     *
     * @return void
     */
    protected function onFormatSelected(): void
    {
        // 清空状态栏消息
        $this->app->onButtonClick();
        
        // 当格式改变时，重新加载当前选中的密钥
        $this->onKeySelected();
    }

    /**
     * 生成密钥按钮点击事件处理
     *
     * @return void
     */
    protected function onGenerateClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            // 获取密钥管理服务
            $keyService = $this->app->getKeyManagementService();
            
            // 生成新的密钥对
            $keyPair = $keyService->generateKeyPair();
            
            // 生成默认文件名
            $filename = "sm2_key_" . date("YmdHis") . ".key";
            
            // 获取选择的格式
            $format = $this->formatCombobox->getSelected() === 0 ? 'hex' : 'pem';
            
            // 保存密钥对
            $keyService->saveKeyPair($keyPair, $filename, $format);
            
            // 重新加载密钥列表
            $this->loadKeyList();
            
            // 显示成功消息
            $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
            Window::msgBox(
                $mainWindow->getHandle(),
                "成功",
                "密钥对生成并保存成功"
            );
        } catch (Exception $e) {
            $this->app->showError("生成密钥时出错: " . $e->getMessage());
        }
    }

    /**
     * 导入密钥按钮点击事件处理
     *
     * @return void
     */
    protected function onImportClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
            
            // 使用Window::openFile直接选择文件
            Window::openFile(
                $mainWindow->getHandle(),
                "选择密钥文件",
                ['key' => '密钥文件'],
                function($filePath) use ($mainWindow) {
                    try {
                        if ($filePath !== null) {
                            // 获取密钥管理服务
                            $keyService = $this->app->getKeyManagementService();
                            
                            // 从文件导入密钥
                            $result = $keyService->importKeyFromFile($filePath);
                            
                            if ($result) {
                                // 重新加载密钥列表
                                $this->loadKeyList();
                                
                                // 显示成功消息
                                Window::msgBox(
                                    $mainWindow->getHandle(),
                                    "成功",
                                    "密钥导入成功"
                                );
                            } else {
                                $this->app->showError("密钥导入失败");
                            }
                        }
                    } catch (Exception $e) {
                        $this->app->showError("导入密钥时出错: " . $e->getMessage());
                    }
                }
            );
        } catch (Exception $e) {
            $this->app->showError("导入密钥时出错: " . $e->getMessage());
        }
    }

    /**
     * 导出密钥按钮点击事件处理
     *
     * @return void
     */
    protected function onExportClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            $selectedIndex = $this->keyCombobox->getSelected();
            
            // 检查是否选择了密钥
            if ($selectedIndex <= 0 || $selectedIndex > count($this->keyList)) {
                $this->app->showError("请先选择一个密钥");
                return;
            }
            
            $keyName = $this->keyList[$selectedIndex - 1]; // 减去默认选项的索引
            
            $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
            
            // 使用Window::saveFile直接保存文件
            $defaultFilename = pathinfo($keyName, PATHINFO_FILENAME) . '_export.key';
            
            Window::saveFile(
                $mainWindow->getHandle(),
                "导出密钥",
                ['key' => '密钥文件'],
                $defaultFilename,
                function($filePath) use ($keyName, $mainWindow) {
                    try {
                        if ($filePath !== null) {
                            // 获取密钥管理服务
                            $keyService = $this->app->getKeyManagementService();
                            
                            // 加载密钥对
                            $keyPair = $keyService->loadKeyPair($keyName);
                            
                            // 确定导出格式（根据文件扩展名）
                            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                            $format = 'hex'; // 默认格式
                            
                            if ($extension === 'pem') {
                                $format = 'pem';
                            } elseif ($extension === 'der' || $extension === 'asn1') {
                                $format = 'asn1';
                            }
                            
                            // 保存到文件
                            $result = $keyService->saveKeyPair($keyPair, $filePath, $format);
                            
                            if ($result) {
                                // 显示成功消息
                                Window::msgBox(
                                    $mainWindow->getHandle(),
                                    "成功",
                                    "密钥导出成功"
                                );
                            } else {
                                $this->app->showError("密钥导出失败");
                            }
                        }
                    } catch (Exception $e) {
                        $this->app->showError("导出密钥时出错: " . $e->getMessage());
                    }
                }
            );
        } catch (Exception $e) {
            $this->app->showError("导出密钥时出错: " . $e->getMessage());
        }
    }

    /**
     * 删除密钥按钮点击事件处理
     *
     * @return void
     */
    protected function onDeleteClicked(): void
    {
        // 统一按钮点击事件处理
        $this->app->onButtonClick();
        
        try {
            $selectedIndex = $this->keyCombobox->getSelected();
            
            // 检查是否选择了密钥
            if ($selectedIndex <= 0 || $selectedIndex > count($this->keyList)) {
                $this->app->showError("请先选择一个密钥");
                return;
            }
            
            $keyName = $this->keyList[$selectedIndex - 1]; // 减去默认选项的索引
            
            $mainWindow = $this->app->getIntegrationManager()->getMainWindow();
            
            // 确认删除
            // 使用ConfirmWindow确认删除操作
            $confirmWindow = $this->app->getUiApp()->createConfirmWindow(
                "确认删除", 
                "确定要删除密钥 '{$keyName}' 吗？此操作不可恢复。",
                function($window) use ($keyName, $mainWindow) {  // 确认回调
                    try {
                        // 获取密钥管理服务
                        $keyService = $this->app->getKeyManagementService();

                        // 删除密钥
                        $result = $keyService->deleteKey($keyName);

                        if ($result) {
                            // 重新加载密钥列表
                            $this->loadKeyList();
                            // 清空公钥和私钥显示
                            $this->publicKeyEntry->setText("");
                            $this->privateKeyEntry->setText("");
                            // 显示成功消息
                            Window::msgBox(
                                $mainWindow->getHandle(),
                                "成功",
                                "密钥删除成功"
                            );
                        } else {
                            $this->app->showError("密钥删除失败");
                        }
                    } catch (Exception $e) {
                        $this->app->showError("删除密钥时出错: " . $e->getMessage());
                    }
                },
                function($window) {  // 取消回调
                    // 用户取消删除操作，不需要做任何事情
                }
            );
            $confirmWindow->show();
        } catch (Exception $e) {
            $this->app->showError("删除密钥时出错: " . $e->getMessage());
        }
    }

    /**
     * 获取选中的密钥
     *
     * @return string|null 选中的密钥名称
     */
    public function getSelectedKey(): ?string
    {
        $selectedIndex = $this->keyCombobox->getSelected();
        
        if ($selectedIndex <= 0 || $selectedIndex > count($this->keyList)) {
            return null;
        }
        
        return $this->keyList[$selectedIndex - 1]; // 减去默认选项的索引
    }

    /**
     * 设置是否启用
     *
     * @param bool $enabled 是否启用
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->keyCombobox->setEnabled($enabled);
        $this->generateButton->setEnabled($enabled);
        $this->importButton->setEnabled($enabled);
        $this->exportButton->setEnabled($enabled);
        $this->deleteButton->setEnabled($enabled);
        $this->formatCombobox->setEnabled($enabled);
        $this->publicKeyEntry->setReadOnly(!$enabled);
        $this->privateKeyEntry->setReadOnly(!$enabled);
    }

    /**
     * 绑定密钥选择事件
     *
     * @param callable $callback 回调函数
     * @return void
     */
    public function onKeySelectedCallback(callable $callback): void
    {
        // 保存回调函数
        $this->keySelectedCallback = $callback;
    }

    /**
     * 触发密钥选择回调
     *
     * @param string $keyName 密钥名称
     * @param KeyPair $keyPair 密钥对
     * @return void
     */
    protected function triggerKeySelectedCallback(string $keyName, KeyPair $keyPair): void
    {
        if (isset($this->keySelectedCallback)) {
            ($this->keySelectedCallback)($keyName, $keyPair);
        }
    }

    /**
     * 刷新密钥列表
     *
     * @return void
     */
    public function refresh(): void
    {
        $this->loadKeyList();
    }

    /**
     * 获取当前选中的密钥对
     *
     * @return KeyPair|null 当前选中的密钥对
     */
    public function getCurrentKeyPair(): ?KeyPair
    {
        return $this->currentKeyPair;
    }

    /**
     * 获取生成密钥按钮
     *
     * @return LibuiButton 生成密钥按钮
     */
    public function getGenerateButton(): LibuiButton
    {
        return $this->generateButton;
    }

    /**
     * 获取导入密钥按钮
     *
     * @return LibuiButton 导入密钥按钮
     */
    public function getImportButton(): LibuiButton
    {
        return $this->importButton;
    }

    /**
     * 获取导出密钥按钮
     *
     * @return LibuiButton 导出密钥按钮
     */
    public function getExportButton(): LibuiButton
    {
        return $this->exportButton;
    }

    /**
     * 获取删除密钥按钮
     *
     * @return LibuiButton 删除密钥按钮
     */
    public function getDeleteButton(): LibuiButton
    {
        return $this->deleteButton;
    }

    /**
     * 获取密钥格式下拉框
     *
     * @return LibuiCombobox 密钥格式下拉框
     */
    public function getFormatCombobox(): LibuiCombobox
    {
        return $this->formatCombobox;
    }
}