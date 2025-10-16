<?php

// 严格模式
declare(strict_types=1);

namespace Kingbes\Libui;

abstract class Base
{
    // private \FFI $ffi;
    private static \FFI $ffi;

    /**
     * 获取 FFI 实例
     *
     * @return \FFI
     * @throws RuntimeException Missing libui dependencies.
     */
    public static function ffi(): \FFI
    {
        if (!isset(self::$ffi)) {
            $headerPath = __DIR__ . '/Libui.h';
            $dllPath = self::getLibFilePath();
            $libHeader = file_get_contents($headerPath);
            self::$ffi = \FFI::cdef($libHeader, $dllPath);
        }
        return self::$ffi;
    }

    /**
     * 获取 libui 库文件的路径
     *
     * 此方法根据当前操作系统的类型返回相应的 libui 库文件路径。
     * 支持 Windows 和 Linux 操作系统，若使用其他操作系统将抛出异常。
     *
     * @return string 包含 libui 库文件的完整路径
     * @throws \RuntimeException 如果当前操作系统不被支持
     */
    protected static function getLibFilePath(): string
    {
        // 检查是否在 PHAR 环境中运行
        $inPhar = defined('PATH_SEPARATOR') && 
                  (strpos(__DIR__, 'phar://') === 0 || 
                   strpos(__FILE__, 'phar://') === 0);

        // 检查是否是通过 PHAR 文件运行的
        $isPharExecution = isset($_SERVER['argv'][0]) && 
                           is_string($_SERVER['argv'][0]) && 
                           strpos($_SERVER['argv'][0], '.phar') !== false;

        if ($inPhar || $isPharExecution) {
            // 在 PHAR 环境中，我们需要将库文件提取到临时目录
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows 系统
                return self::extractLibFile('vendor/kingbes/libui/lib/windows/libui.dll');
            } else if (PHP_OS_FAMILY === 'Linux') {
                // Linux 系统
                return self::extractLibFile('vendor/kingbes/libui/lib/linux/libui.so');
            } elseif (PHP_OS_FAMILY === 'Darwin') {
                // macOS 系统
                return self::extractLibFile('vendor/kingbes/libui/lib/macos/libui.dylib');
            }
        } else {
            // 不在 PHAR 环境中，使用原来的逻辑
            if (PHP_OS_FAMILY === 'Windows') {
                // 返回 Windows 系统下的 libui 动态链接库文件路径
                return dirname(__DIR__) . '/lib/windows/libui.dll';
            } else if (PHP_OS_FAMILY === 'Linux') {
                // 返回 Linux 系统下的 libui 共享库文件路径
                return dirname(__DIR__) . '/lib/linux/libui.so';
            } elseif (PHP_OS_FAMILY === 'Darwin') {
                // 返回 macOS 系统下的 libui 共享库文件路径
                return dirname(__DIR__) . '/lib/macos/libui.dylib';
            } else {
                // 若当前操作系统不被支持，抛出异常
                throw new \RuntimeException("Unsupported operating system: " . PHP_OS_FAMILY . ": " . PHP_OS . "");
            }
        }
    }

    /**
     * 从 PHAR 包中提取库文件到临时目录
     *
     * @param string $libFile 相对于 PHAR 根目录的库文件路径
     * @return string 临时目录中库文件的路径
     */
    protected static function extractLibFile(string $libFile): string
    {
        // 获取临时目录
        $tempDir = sys_get_temp_dir();
        $tempLibPath = $tempDir . '/' . basename($libFile);

        // 检查临时目录中的文件是否已经存在且是最新的
        if (!file_exists($tempLibPath) || filemtime($tempLibPath) < filemtime(__FILE__)) {
            // 确定 PHAR 文件路径
            if (isset($_SERVER['argv'][0]) && strpos($_SERVER['argv'][0], '.phar') !== false) {
                // 通过命令行运行 PHAR 文件
                $pharPath = $_SERVER['argv'][0];
            } else {
                // 在 PHAR 环境中运行
                $pharPath = dirname(__DIR__, 3) . '/oha-gui.phar';
            }
            
            // 从 PHAR 包中读取库文件内容
            $libPathInPhar = 'phar://' . $pharPath . '/' . $libFile;
            
            // 检查 PHAR 中的文件是否存在
            if (file_exists($libPathInPhar)) {
                $libContent = file_get_contents($libPathInPhar);
                if ($libContent !== false) {
                    // 将库文件写入临时目录
                    file_put_contents($tempLibPath, $libContent);
                    // 设置适当的权限
                    chmod($tempLibPath, 0755);
                } else {
                    throw new \RuntimeException("Failed to read library file from PHAR: " . $libPathInPhar);
                }
            } else {
                // 如果在 PHAR 中找不到文件，尝试从当前目录查找
                $localLibPath = dirname(__DIR__, 3) . '/' . $libFile;
                if (file_exists($localLibPath)) {
                    return $localLibPath;
                }
                throw new \RuntimeException("Library file not found in PHAR: " . $libPathInPhar);
            }
        }

        return $tempLibPath;
    }
}