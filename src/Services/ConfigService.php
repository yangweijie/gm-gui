<?php

namespace Yangweijie\GmGui\Services;

use Yangweijie\GmGui\Application\Config\AppConfig;
use Yangweijie\GmGui\Exceptions\CryptoException;

class ConfigService
{
    /**
     * 配置文件路径
     *
     * @var string
     */
    protected string $configPath;

    /**
     * 配置对象
     *
     * @var AppConfig
     */
    protected AppConfig $config;

    /**
     * 构造函数
     *
     * @param string $configPath 配置文件路径
     */
    public function __construct(string $configPath = './config/config.json')
    {
        $this->configPath = $configPath;
        $this->config = $this->loadConfig();
    }

    /**
     * 加载配置
     *
     * @return AppConfig 配置对象
     */
    protected function loadConfig(): AppConfig
    {
        // 确保配置目录存在
        $configDir = dirname($this->configPath);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        // 如果配置文件不存在，创建默认配置
        if (!file_exists($this->configPath)) {
            $defaultConfig = $this->getDefaultConfig();
            $this->saveConfig($defaultConfig);
            return new AppConfig($defaultConfig);
        }

        // 读取配置文件
        try {
            $configData = file_get_contents($this->configPath);
            if ($configData === false) {
                throw new \Exception("无法读取配置文件");
            }

            $configArray = json_decode($configData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("配置文件格式错误: " . json_last_error_msg());
            }

            return new AppConfig($configArray ?? []);
        } catch (\Exception $e) {
            // 如果配置文件损坏，使用默认配置
            error_log("配置文件加载失败，使用默认配置: " . $e->getMessage());
            $defaultConfig = $this->getDefaultConfig();
            return new AppConfig($defaultConfig);
        }
    }

    /**
     * 获取默认配置
     *
     * @return array 默认配置数组
     */
    protected function getDefaultConfig(): array
    {
        return [
            'app' => [
                'version' => '1.0.0',
                'language' => 'zh-CN',
                'theme' => 'default'
            ],
            'crypto' => [
                'defaultOutputFormat' => 'hex',
                'sm2' => [
                    'appendZeroFour' => false,
                    'mode' => 'C1C3C2'
                ],
                'sm4' => [
                    'defaultMode' => 'cbc',
                    'autoGenerateIV' => true
                ],
                'signature' => [
                    'defaultFormat' => 'asn1',
                    'defaultUserId' => ''
                ]
            ],
            'storage' => [
                'keyStorePath' => './keys',
                'configPath' => './config',
                'tempPath' => './temp'
            ],
            'ui' => [
                'windowWidth' => 1200,
                'windowHeight' => 800,
                'rememberWindowState' => true
            ]
        ];
    }

    /**
     * 保存配置
     *
     * @param array $configData 配置数据
     * @return bool 是否保存成功
     * @throws CryptoException
     */
    protected function saveConfig(array $configData): bool
    {
        try {
            $configDir = dirname($this->configPath);
            if (!is_dir($configDir)) {
                if (!mkdir($configDir, 0755, true)) {
                    throw new \Exception("创建配置目录失败: {$configDir}");
                }
            }

            $json = json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new \Exception("配置数据编码失败: " . json_last_error_msg());
            }

            if (file_put_contents($this->configPath, $json) === false) {
                throw new \Exception("写入配置文件失败");
            }

            return true;
        } catch (\Exception $e) {
            throw CryptoException::systemError("保存配置失败: " . $e->getMessage());
        }
    }

    /**
     * 获取配置对象
     *
     * @return AppConfig 配置对象
     */
    public function getConfig(): AppConfig
    {
        return $this->config;
    }

    /**
     * 更新配置
     *
     * @param array $configData 配置数据
     * @return bool 是否更新成功
     * @throws CryptoException
     */
    public function updateConfig(array $configData): bool
    {
        try {
            // 验证配置
            $this->validateConfig($configData);
            
            // 保存配置
            if ($this->saveConfig($configData)) {
                // 重新加载配置
                $this->config = new AppConfig($configData);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            throw CryptoException::systemError("更新配置失败: " . $e->getMessage());
        }
    }

    /**
     * 验证配置
     *
     * @param array $configData 配置数据
     * @return bool 是否验证通过
     * @throws \Exception
     */
    protected function validateConfig(array $configData): bool
    {
        // 验证必需的配置项
        $requiredKeys = ['app', 'crypto', 'storage', 'ui'];
        foreach ($requiredKeys as $key) {
            if (!isset($configData[$key])) {
                throw new \Exception("缺少必需的配置项: {$key}");
            }
        }

        // 验证应用配置
        if (isset($configData['app'])) {
            $appConfig = $configData['app'];
            if (isset($appConfig['version']) && !is_string($appConfig['version'])) {
                throw new \Exception("应用版本必须是字符串");
            }
            if (isset($appConfig['language']) && !is_string($appConfig['language'])) {
                throw new \Exception("语言设置必须是字符串");
            }
        }

        // 验证加密配置
        if (isset($configData['crypto'])) {
            $cryptoConfig = $configData['crypto'];
            if (isset($cryptoConfig['defaultOutputFormat']) && 
                !in_array($cryptoConfig['defaultOutputFormat'], ['hex', 'base64'])) {
                throw new \Exception("不支持的输出格式: {$cryptoConfig['defaultOutputFormat']}");
            }
        }

        // 验证存储配置
        if (isset($configData['storage'])) {
            $storageConfig = $configData['storage'];
            if (isset($storageConfig['keyStorePath']) && !is_string($storageConfig['keyStorePath'])) {
                throw new \Exception("密钥存储路径必须是字符串");
            }
        }

        // 验证UI配置
        if (isset($configData['ui'])) {
            $uiConfig = $configData['ui'];
            if (isset($uiConfig['windowWidth']) && !is_int($uiConfig['windowWidth'])) {
                throw new \Exception("窗口宽度必须是整数");
            }
            if (isset($uiConfig['windowHeight']) && !is_int($uiConfig['windowHeight'])) {
                throw new \Exception("窗口高度必须是整数");
            }
        }

        return true;
    }

    /**
     * 获取配置值
     *
     * @param string $key 配置键（使用点号分隔，如 "app.version"）
     * @param mixed $default 默认值
     * @return mixed 配置值
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (is_object($value) && property_exists($value, $k)) {
                $value = $value->$k;
            } elseif (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }

    /**
     * 设置配置值
     *
     * @param string $key 配置键（使用点号分隔，如 "app.version"）
     * @param mixed $value 配置值
     * @return bool 是否设置成功
     */
    public function set(string $key, $value): bool
    {
        // 这个方法需要更复杂的实现来支持嵌套设置
        // 暂时只提供框架
        return false;
    }

    /**
     * 重置为默认配置
     *
     * @return bool 是否重置成功
     * @throws CryptoException
     */
    public function resetToDefault(): bool
    {
        try {
            $defaultConfig = $this->getDefaultConfig();
            if ($this->saveConfig($defaultConfig)) {
                $this->config = new AppConfig($defaultConfig);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw CryptoException::systemError("重置配置失败: " . $e->getMessage());
        }
    }

    /**
     * 备份配置
     *
     * @param string $backupPath 备份路径
     * @return bool 是否备份成功
     * @throws CryptoException
     */
    public function backupConfig(string $backupPath): bool
    {
        try {
            if (!file_exists($this->configPath)) {
                throw new \Exception("配置文件不存在: {$this->configPath}");
            }
            
            $backupDir = dirname($backupPath);
            if (!is_dir($backupDir)) {
                if (!mkdir($backupDir, 0755, true)) {
                    throw new \Exception("创建备份目录失败: {$backupDir}");
                }
            }
            
            return copy($this->configPath, $backupPath);
        } catch (\Exception $e) {
            throw CryptoException::systemError("备份配置失败: " . $e->getMessage());
        }
    }

    /**
     * 恢复配置
     *
     * @param string $backupPath 备份路径
     * @return bool 是否恢复成功
     * @throws CryptoException
     */
    public function restoreConfig(string $backupPath): bool
    {
        try {
            if (!file_exists($backupPath)) {
                throw new \Exception("备份文件不存在: {$backupPath}");
            }
            
            // 备份当前配置
            $currentBackup = $this->configPath . '.backup';
            if (file_exists($this->configPath)) {
                copy($this->configPath, $currentBackup);
            }
            
            // 恢复配置
            if (!copy($backupPath, $this->configPath)) {
                throw new \Exception("恢复配置文件失败");
            }
            
            // 重新加载配置
            $this->config = $this->loadConfig();
            return true;
        } catch (\Exception $e) {
            throw CryptoException::systemError("恢复配置失败: " . $e->getMessage());
        }
    }
}