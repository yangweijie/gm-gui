<?php

namespace Yangweijie\GmGui\Application\Config;

use Yangweijie\GmGui\Models\CryptoConfig;

class AppConfig
{
    /**
     * 应用版本
     *
     * @var string
     */
    public string $version = '1.0.0';

    /**
     * 语言设置
     *
     * @var string
     */
    public string $language = 'zh-CN';

    /**
     * 主题设置
     *
     * @var string
     */
    public string $theme = 'default';

    /**
     * 窗口宽度
     *
     * @var int
     */
    public int $windowWidth = 1200;

    /**
     * 窗口高度
     *
     * @var int
     */
    public int $windowHeight = 800;

    /**
     * 是否记住窗口状态
     *
     * @var bool
     */
    public bool $rememberWindowState = true;

    /**
     * 加密配置
     *
     * @var CryptoConfig
     */
    public CryptoConfig $crypto;

    /**
     * 存储配置
     *
     * @var array
     */
    public array $storage = [
        'keyStorePath' => './keys',
        'configPath' => './config',
        'tempPath' => './temp'
    ];

    /**
     * 构造函数
     *
     * @param array $config 配置数组
     */
    public function __construct(array $config = [])
    {
        // 初始化加密配置
        $this->crypto = new CryptoConfig($config['crypto'] ?? []);
        
        // 设置应用配置
        if (isset($config['app']['version'])) {
            $this->version = $config['app']['version'];
        }
        
        if (isset($config['app']['language'])) {
            $this->language = $config['app']['language'];
        }
        
        if (isset($config['app']['theme'])) {
            $this->theme = $config['app']['theme'];
        }
        
        if (isset($config['ui']['windowWidth'])) {
            $this->windowWidth = $config['ui']['windowWidth'];
        }
        
        if (isset($config['ui']['windowHeight'])) {
            $this->windowHeight = $config['ui']['windowHeight'];
        }
        
        if (isset($config['ui']['rememberWindowState'])) {
            $this->rememberWindowState = $config['ui']['rememberWindowState'];
        }
        
        if (isset($config['storage'])) {
            $this->storage = array_merge($this->storage, $config['storage']);
        }
    }
}