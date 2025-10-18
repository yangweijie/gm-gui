<?php

namespace Yangweijie\GmGui\Services;

use Yangweijie\GmGui\Models\KeyPair;

/**
 * 代码生成服务类
 * 用于生成国密算法操作的PHP代码示例
 */
class CodeGenerationService
{
    /**
     * 生成SM2加密代码示例
     *
     * @param string $data 要加密的数据
     * @param string $publicKey 公钥
     * @param array $options 加密选项
     * @return string PHP代码示例
     */
    public function generateSm2EncryptCode(string $data, string $publicKey, array $options = []): string
    {
        $outputFormat = $options['outputFormat'] ?? 'hex';
        $mode = $options['mode'] ?? 'C1C3C2';
        $appendZeroFour = $options['appendZeroFour'] ?? false;
        
        $code = "<?php\n\n";
        $code .= "// SM2加密代码示例\n";
        $code .= "// 本代码由国密客户端自动生成\n\n";
        $code .= "require_once 'vendor/autoload.php';\n\n";
        $code .= "use Yangweijie\\GmGui\\Services\\Crypto\\Sm2Service;\n\n";
        $code .= "// 初始化SM2服务\n";
        $code .= "\$sm2Service = new Sm2Service();\n\n";
        $code .= "// 准备加密数据\n";
        $code .= "\$data = " . var_export($data, true) . ";\n";
        $code .= "\$publicKey = " . var_export($publicKey, true) . ";\n\n";
        $code .= "// 设置加密选项\n";
        $code .= "\$options = [\n";
        $code .= "    'outputFormat' => " . var_export($outputFormat, true) . ",\n";
        $code .= "    'mode' => " . var_export($mode, true) . ",\n";
        $code .= "    'appendZeroFour' => " . var_export($appendZeroFour, true) . ",\n";
        $code .= "    'publicKey' => \$publicKey\n";
        $code .= "];\n\n";
        $code .= "// 执行加密\n";
        $code .= "\$result = \$sm2Service->encrypt(\$data, \$options);\n\n";
        $code .= "if (\$result->success) {\n";
        $code .= "    echo \"加密成功！\\n\";\n";
        $code .= "    echo \"密文: \" . \$result->data . \"\\n\";\n";
        $code .= "} else {\n";
        $code .= "    echo \"加密失败: \" . \$result->error . \"\\n\";\n";
        $code .= "}\n";
        
        return $code;
    }

    /**
     * 生成SM2解密代码示例
     *
     * @param string $data 要解密的数据
     * @param string $privateKey 私钥
     * @param array $options 解密选项
     * @return string PHP代码示例
     */
    public function generateSm2DecryptCode(string $data, string $privateKey, array $options = []): string
    {
        $inputFormat = $options['inputFormat'] ?? 'hex';
        $mode = $options['mode'] ?? 'C1C3C2';
        
        $code = "<?php\n\n";
        $code .= "// SM2解密代码示例\n";
        $code .= "// 本代码由国密客户端自动生成\n\n";
        $code .= "require_once 'vendor/autoload.php';\n\n";
        $code .= "use Yangweijie\\GmGui\\Services\\Crypto\\Sm2Service;\n\n";
        $code .= "// 初始化SM2服务\n";
        $code .= "\$sm2Service = new Sm2Service();\n\n";
        $code .= "// 准备解密数据\n";
        $code .= "\$data = " . var_export($data, true) . ";\n";
        $code .= "\$privateKey = " . var_export($privateKey, true) . ";\n\n";
        $code .= "// 设置解密选项\n";
        $code .= "\$options = [\n";
        $code .= "    'inputFormat' => " . var_export($inputFormat, true) . ",\n";
        $code .= "    'mode' => " . var_export($mode, true) . ",\n";
        $code .= "    'privateKey' => \$privateKey\n";
        $code .= "];\n\n";
        $code .= "// 执行解密\n";
        $code .= "\$result = \$sm2Service->decrypt(\$data, \$options);\n\n";
        $code .= "if (\$result->success) {\n";
        $code .= "    echo \"解密成功！\\n\";\n";
        $code .= "    echo \"明文: \" . \$result->data . \"\\n\";\n";
        $code .= "} else {\n";
        $code .= "    echo \"解密失败: \" . \$result->error . \"\\n\";\n";
        $code .= "}\n";
        
        return $code;
    }

    /**
     * 生成SM2签名代码示例
     *
     * @param string $data 要签名的数据
     * @param string $privateKey 私钥
     * @param array $options 签名选项
     * @return string PHP代码示例
     */
    public function generateSm2SignCode(string $data, string $privateKey, array $options = []): string
    {
        $userId = $options['userId'] ?? '';
        $outputFormat = $options['outputFormat'] ?? 'hex';
        $toRS = $options['toRS'] ?? false;
        
        $code = "<?php\n\n";
        $code .= "// SM2签名代码示例\n";
        $code .= "// 本代码由国密客户端自动生成\n\n";
        $code .= "require_once 'vendor/autoload.php';\n\n";
        $code .= "use Yangweijie\\GmGui\\Services\\Crypto\\Sm2Service;\n\n";
        $code .= "// 初始化SM2服务\n";
        $code .= "\$sm2Service = new Sm2Service();\n\n";
        $code .= "// 准备签名数据\n";
        $code .= "\$data = " . var_export($data, true) . ";\n";
        $code .= "\$privateKey = " . var_export($privateKey, true) . ";\n";
        $code .= "\$userId = " . var_export($userId, true) . ";\n\n";
        $code .= "// 设置签名选项\n";
        $code .= "\$options = [\n";
        $code .= "    'userId' => \$userId,\n";
        $code .= "    'outputFormat' => " . var_export($outputFormat, true) . ",\n";
        $code .= "    'toRS' => " . var_export($toRS, true) . ",\n";
        $code .= "    'privateKey' => \$privateKey\n";
        $code .= "];\n\n";
        $code .= "// 执行签名\n";
        $code .= "\$result = \$sm2Service->sign(\$data, \$options);\n\n";
        $code .= "if (\$result->success) {\n";
        $code .= "    echo \"签名成功！\\n\";\n";
        $code .= "    echo \"签名值: \" . \$result->data . \"\\n\";\n";
        $code .= "} else {\n";
        $code .= "    echo \"签名失败: \" . \$result->error . \"\\n\";\n";
        $code .= "}\n";
        
        return $code;
    }

    /**
     * 生成SM2验签代码示例
     *
     * @param string $data 原始数据
     * @param string $signature 签名
     * @param string $publicKey 公钥
     * @param array $options 验签选项
     * @return string PHP代码示例
     */
    public function generateSm2VerifyCode(string $data, string $signature, string $publicKey, array $options = []): string
    {
        $userId = $options['userId'] ?? '';
        $inputFormat = $options['inputFormat'] ?? 'hex';
        $signatureFormat = $options['signatureFormat'] ?? 'asn1';
        
        $code = "<?php\n\n";
        $code .= "// SM2验签代码示例\n";
        $code .= "// 本代码由国密客户端自动生成\n\n";
        $code .= "require_once 'vendor/autoload.php';\n\n";
        $code .= "use Yangweijie\\GmGui\\Services\\Crypto\\Sm2Service;\n\n";
        $code .= "// 初始化SM2服务\n";
        $code .= "\$sm2Service = new Sm2Service();\n\n";
        $code .= "// 准备验签数据\n";
        $code .= "\$data = " . var_export($data, true) . ";\n";
        $code .= "\$signature = " . var_export($signature, true) . ";\n";
        $code .= "\$publicKey = " . var_export($publicKey, true) . ";\n";
        $code .= "\$userId = " . var_export($userId, true) . ";\n\n";
        $code .= "// 设置验签选项\n";
        $code .= "\$options = [\n";
        $code .= "    'userId' => \$userId,\n";
        $code .= "    'inputFormat' => " . var_export($inputFormat, true) . ",\n";
        $code .= "    'signatureFormat' => " . var_export($signatureFormat, true) . ",\n";
        $code .= "    'publicKey' => \$publicKey\n";
        $code .= "];\n\n";
        $code .= "// 执行验签\n";
        $code .= "\$result = \$sm2Service->verify(\$data, \$signature, \$options);\n\n";
        $code .= "if (\$result->success) {\n";
        $code .= "    echo \"验签\" . (\$result->data === 'valid' ? '成功' : '失败') . \"！\\n\";\n";
        $code .= "} else {\n";
        $code .= "    echo \"验签失败: \" . \$result->error . \"\\n\";\n";
        $code .= "}\n";
        
        return $code;
    }

    /**
     * 生成SM3哈希代码示例
     *
     * @param string $data 要哈希的数据
     * @param array $options 哈希选项
     * @return string PHP代码示例
     */
    public function generateSm3HashCode(string $data, array $options = []): string
    {
        $outputFormat = $options['outputFormat'] ?? 'hex';
        
        $code = "<?php\n\n";
        $code .= "// SM3哈希代码示例\n";
        $code .= "// 本代码由国密客户端自动生成\n\n";
        $code .= "require_once 'vendor/autoload.php';\n\n";
        $code .= "use Yangweijie\\GmGui\\Services\\Crypto\\Sm3Service;\n\n";
        $code .= "// 初始化SM3服务\n";
        $code .= "\$sm3Service = new Sm3Service();\n\n";
        $code .= "// 准备哈希数据\n";
        $code .= "\$data = " . var_export($data, true) . ";\n\n";
        $code .= "// 设置哈希选项\n";
        $code .= "\$options = [\n";
        $code .= "    'outputFormat' => " . var_export($outputFormat, true) . "\n";
        $code .= "];\n\n";
        $code .= "// 执行哈希\n";
        $code .= "\$result = \$sm3Service->hash(\$data, \$options);\n\n";
        $code .= "if (\$result->success) {\n";
        $code .= "    echo \"哈希成功！\\n\";\n";
        $code .= "    echo \"哈希值: \" . \$result->data . \"\\n\";\n";
        $code .= "} else {\n";
        $code .= "    echo \"哈希失败: \" . \$result->error . \"\\n\";\n";
        $code .= "}\n";
        
        return $code;
    }

    /**
     * 生成SM4加密代码示例
     *
     * @param string $data 要加密的数据
     * @param string $key 密钥
     * @param array $options 加密选项
     * @return string PHP代码示例
     */
    public function generateSm4EncryptCode(string $data, string $key, array $options = []): string
    {
        $mode = $options['mode'] ?? 'cbc';
        $iv = $options['iv'] ?? '';
        $outputFormat = $options['outputFormat'] ?? 'hex';
        $padding = $options['padding'] ?? 'pkcs7';
        
        $code = "<?php\n\n";
        $code .= "// SM4加密代码示例\n";
        $code .= "// 本代码由国密客户端自动生成\n\n";
        $code .= "require_once 'vendor/autoload.php';\n\n";
        $code .= "use Yangweijie\\GmGui\\Services\\Crypto\\Sm4Service;\n\n";
        $code .= "// 初始化SM4服务\n";
        $code .= "\$sm4Service = new Sm4Service();\n\n";
        $code .= "// 准备加密数据\n";
        $code .= "\$data = " . var_export($data, true) . ";\n";
        $code .= "\$key = " . var_export($key, true) . ";\n";
        $code .= "\$iv = " . var_export($iv, true) . ";\n\n";
        $code .= "// 设置加密选项\n";
        $code .= "\$options = [\n";
        $code .= "    'mode' => " . var_export($mode, true) . ",\n";
        $code .= "    'iv' => \$iv,\n";
        $code .= "    'outputFormat' => " . var_export($outputFormat, true) . ",\n";
        $code .= "    'padding' => " . var_export($padding, true) . ",\n";
        $code .= "    'key' => \$key\n";
        $code .= "];\n\n";
        $code .= "// 执行加密\n";
        $code .= "\$result = \$sm4Service->encrypt(\$data, \$options);\n\n";
        $code .= "if (\$result->success) {\n";
        $code .= "    echo \"加密成功！\\n\";\n";
        $code .= "    echo \"密文: \" . \$result->data . \"\\n\";\n";
        $code .= "} else {\n";
        $code .= "    echo \"加密失败: \" . \$result->error . \"\\n\";\n";
        $code .= "}\n";
        
        return $code;
    }

    /**
     * 生成SM4解密代码示例
     *
     * @param string $data 要解密的数据
     * @param string $key 密钥
     * @param array $options 解密选项
     * @return string PHP代码示例
     */
    public function generateSm4DecryptCode(string $data, string $key, array $options = []): string
    {
        $mode = $options['mode'] ?? 'cbc';
        $iv = $options['iv'] ?? '';
        $inputFormat = $options['inputFormat'] ?? 'hex';
        $padding = $options['padding'] ?? 'pkcs7';
        
        $code = "<?php\n\n";
        $code .= "// SM4解密代码示例\n";
        $code .= "// 本代码由国密客户端自动生成\n\n";
        $code .= "require_once 'vendor/autoload.php';\n\n";
        $code .= "use Yangweijie\\GmGui\\Services\\Crypto\\Sm4Service;\n\n";
        $code .= "// 初始化SM4服务\n";
        $code .= "\$sm4Service = new Sm4Service();\n\n";
        $code .= "// 准备解密数据\n";
        $code .= "\$data = " . var_export($data, true) . ";\n";
        $code .= "\$key = " . var_export($key, true) . ";\n";
        $code .= "\$iv = " . var_export($iv, true) . ";\n\n";
        $code .= "// 设置解密选项\n";
        $code .= "\$options = [\n";
        $code .= "    'mode' => " . var_export($mode, true) . ",\n";
        $code .= "    'iv' => \$iv,\n";
        $code .= "    'inputFormat' => " . var_export($inputFormat, true) . ",\n";
        $code .= "    'padding' => " . var_export($padding, true) . ",\n";
        $code .= "    'key' => \$key\n";
        $code .= "];\n\n";
        $code .= "// 执行解密\n";
        $code .= "\$result = \$sm4Service->decrypt(\$data, \$options);\n\n";
        $code .= "if (\$result->success) {\n";
        $code .= "    echo \"解密成功！\\n\";\n";
        $code .= "    echo \"明文: \" . \$result->data . \"\\n\";\n";
        $code .= "} else {\n";
        $code .= "    echo \"解密失败: \" . \$result->error . \"\\n\";\n";
        $code .= "}\n";
        
        return $code;
    }
}
