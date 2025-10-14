<?php

namespace Yangweijie\GmGui\Services;

use Yangweijie\GmGui\Exceptions\CryptoException;

class ConfigMigrationService
{
    /**
     * 当前配置版本
     */
    public const CURRENT_VERSION = '1.0.0';

    /**
     * 配置服务
     *
     * @var ConfigService
     */
    protected ConfigService $configService;

    /**
     * 构造函数
     *
     * @param ConfigService $configService 配置服务
     */
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * 验证配置版本
     *
     * @param array $configData 配置数据
     * @return string 配置版本
     */
    public function validateConfigVersion(array $configData): string
    {
        // 如果没有版本信息，假设是旧版本
        if (!isset($configData['app']['version'])) {
            return '0.0.0';
        }
        
        return $configData['app']['version'];
    }

    /**
     * 检查是否需要迁移
     *
     * @param array $configData 配置数据
     * @return bool 是否需要迁移
     */
    public function needsMigration(array $configData): bool
    {
        $currentVersion = $this->validateConfigVersion($configData);
        return version_compare($currentVersion, self::CURRENT_VERSION, '<');
    }

    /**
     * 迁移配置
     *
     * @param array $configData 配置数据
     * @return array 迁移后的配置数据
     * @throws CryptoException
     */
    public function migrateConfig(array $configData): array
    {
        try {
            $currentVersion = $this->validateConfigVersion($configData);
            
            // 如果已经是最新版本，直接返回
            if (version_compare($currentVersion, self::CURRENT_VERSION, '>=')) {
                return $configData;
            }
            
            // 备份原始配置
            $backupConfig = $configData;
            
            // 执行迁移步骤
            if (version_compare($currentVersion, '1.0.0', '<')) {
                $configData = $this->migrateToVersion100($configData);
            }
            
            // 设置新版本号
            if (!isset($configData['app'])) {
                $configData['app'] = [];
            }
            $configData['app']['version'] = self::CURRENT_VERSION;
            
            return $configData;
        } catch (\Exception $e) {
            throw CryptoException::systemError("配置迁移失败: " . $e->getMessage());
        }
    }

    /**
     * 迁移到版本1.0.0
     *
     * @param array $configData 配置数据
     * @return array 迁移后的配置数据
     */
    protected function migrateToVersion100(array $configData): array
    {
        // 确保必需的配置项存在
        if (!isset($configData['app'])) {
            $configData['app'] = [];
        }
        
        if (!isset($configData['crypto'])) {
            $configData['crypto'] = [];
        }
        
        if (!isset($configData['storage'])) {
            $configData['storage'] = [];
        }
        
        if (!isset($configData['ui'])) {
            $configData['ui'] = [];
        }
        
        // 设置默认值
        if (!isset($configData['app']['version'])) {
            $configData['app']['version'] = '1.0.0';
        }
        
        if (!isset($configData['app']['language'])) {
            $configData['app']['language'] = 'zh-CN';
        }
        
        if (!isset($configData['app']['theme'])) {
            $configData['app']['theme'] = 'default';
        }
        
        // 迁移加密配置
        if (!isset($configData['crypto']['defaultOutputFormat'])) {
            $configData['crypto']['defaultOutputFormat'] = 'hex';
        }
        
        if (!isset($configData['crypto']['sm2'])) {
            $configData['crypto']['sm2'] = [
                'appendZeroFour' => false,
                'mode' => 'C1C3C2'
            ];
        }
        
        if (!isset($configData['crypto']['sm4'])) {
            $configData['crypto']['sm4'] = [
                'defaultMode' => 'cbc',
                'autoGenerateIV' => true
            ];
        }
        
        if (!isset($configData['crypto']['signature'])) {
            $configData['crypto']['signature'] = [
                'defaultFormat' => 'asn1',
                'defaultUserId' => ''
            ];
        }
        
        // 迁移存储配置
        if (!isset($configData['storage']['keyStorePath'])) {
            $configData['storage']['keyStorePath'] = './keys';
        }
        
        if (!isset($configData['storage']['configPath'])) {
            $configData['storage']['configPath'] = './config';
        }
        
        if (!isset($configData['storage']['tempPath'])) {
            $configData['storage']['tempPath'] = './temp';
        }
        
        // 迁移UI配置
        if (!isset($configData['ui']['windowWidth'])) {
            $configData['ui']['windowWidth'] = 1200;
        }
        
        if (!isset($configData['ui']['windowHeight'])) {
            $configData['ui']['windowHeight'] = 800;
        }
        
        if (!isset($configData['ui']['rememberWindowState'])) {
            $configData['ui']['rememberWindowState'] = true;
        }
        
        return $configData;
    }

    /**
     * 验证配置完整性
     *
     * @param array $configData 配置数据
     * @return bool 是否完整
     */
    public function validateConfigIntegrity(array $configData): bool
    {
        try {
            // 检查必需的配置项
            $requiredSections = ['app', 'crypto', 'storage', 'ui'];
            foreach ($requiredSections as $section) {
                if (!isset($configData[$section]) || !is_array($configData[$section])) {
                    throw new \Exception("缺少必需的配置项: {$section}");
                }
            }
            
            // 验证应用配置
            $this->validateAppConfig($configData['app']);
            
            // 验证加密配置
            $this->validateCryptoConfig($configData['crypto']);
            
            // 验证存储配置
            $this->validateStorageConfig($configData['storage']);
            
            // 验证UI配置
            $this->validateUiConfig($configData['ui']);
            
            return true;
        } catch (\Exception $e) {
            error_log("配置完整性验证失败: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 验证应用配置
     *
     * @param array $appConfig 应用配置
     * @return void
     * @throws \Exception
     */
    protected function validateAppConfig(array $appConfig): void
    {
        if (isset($appConfig['version']) && !is_string($appConfig['version'])) {
            throw new \Exception("应用版本必须是字符串");
        }
        
        if (isset($appConfig['language']) && !is_string($appConfig['language'])) {
            throw new \Exception("语言设置必须是字符串");
        }
        
        if (isset($appConfig['theme']) && !is_string($appConfig['theme'])) {
            throw new \Exception("主题设置必须是字符串");
        }
    }

    /**
     * 验证加密配置
     *
     * @param array $cryptoConfig 加密配置
     * @return void
     * @throws \Exception
     */
    protected function validateCryptoConfig(array $cryptoConfig): void
    {
        if (isset($cryptoConfig['defaultOutputFormat']) && 
            !in_array($cryptoConfig['defaultOutputFormat'], ['hex', 'base64'])) {
            throw new \Exception("不支持的输出格式: {$cryptoConfig['defaultOutputFormat']}");
        }
        
        if (isset($cryptoConfig['sm2']) && is_array($cryptoConfig['sm2'])) {
            $sm2Config = $cryptoConfig['sm2'];
            if (isset($sm2Config['mode']) && 
                !in_array($sm2Config['mode'], ['C1C3C2', 'C1C2C3'])) {
                throw new \Exception("不支持的SM2模式: {$sm2Config['mode']}");
            }
        }
        
        if (isset($cryptoConfig['sm4']) && is_array($cryptoConfig['sm4'])) {
            $sm4Config = $cryptoConfig['sm4'];
            if (isset($sm4Config['defaultMode']) && 
                !in_array($sm4Config['defaultMode'], ['ecb', 'cbc', 'cfb', 'ofb', 'ctr'])) {
                throw new \Exception("不支持的SM4模式: {$sm4Config['defaultMode']}");
            }
        }
    }

    /**
     * 验证存储配置
     *
     * @param array $storageConfig 存储配置
     * @return void
     * @throws \Exception
     */
    protected function validateStorageConfig(array $storageConfig): void
    {
        $storageKeys = ['keyStorePath', 'configPath', 'tempPath'];
        foreach ($storageKeys as $key) {
            if (isset($storageConfig[$key]) && !is_string($storageConfig[$key])) {
                throw new \Exception("存储路径 {$key} 必须是字符串");
            }
        }
    }

    /**
     * 验证UI配置
     *
     * @param array $uiConfig UI配置
     * @return void
     * @throws \Exception
     */
    protected function validateUiConfig(array $uiConfig): void
    {
        if (isset($uiConfig['windowWidth']) && !is_int($uiConfig['windowWidth'])) {
            throw new \Exception("窗口宽度必须是整数");
        }
        
        if (isset($uiConfig['windowHeight']) && !is_int($uiConfig['windowHeight'])) {
            throw new \Exception("窗口高度必须是整数");
        }
        
        if (isset($uiConfig['rememberWindowState']) && !is_bool($uiConfig['rememberWindowState'])) {
            throw new \Exception("记住窗口状态必须是布尔值");
        }
    }
}