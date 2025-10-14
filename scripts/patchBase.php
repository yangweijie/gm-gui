<?php
echo "检查 Base 文件...\n";
$checkFile = __DIR__ . '/../vendor/kingbes/libui/src/Base.php';
$sourceFile = __DIR__ . '/../kingbes/libui/src/Base.php';

// 检查源文件是否存在
if (!file_exists($sourceFile)) {
    echo "源 Base.php 文件不存在\n";
    exit(1);
}

// 检查目标目录是否存在，如果不存在则创建
$vendorDir = __DIR__ . '/../vendor/kingbes/libui/src';
if (!is_dir($vendorDir)) {
    echo "创建目录: $vendorDir\n";
    mkdir($vendorDir, 0755, true);
}

// 复制我们修改的Base.php到vendor目录
echo "复制 Base.php 到 vendor 目录...\n";
if (copy($sourceFile, $checkFile)) {
    echo "Base.php 已更新到 vendor 目录\n";
} else {
    echo "复制 Base.php 失败\n";
    exit(1);
}

echo "Base 已修复\n";
exit(0);