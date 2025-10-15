#!/bin/bash
# 构建 PHAR 文件的脚本

echo "正在构建国密客户端 PHAR 文件..."

# 检查 vendor/bin/box 是否存在
if [ ! -f "vendor/bin/box" ]; then
    echo "错误: 未找到 box 命令。请先运行 'composer install' 安装开发依赖。"
    exit 1
fi

# 构建 PHAR 文件
echo "正在使用 Box 构建 PHAR 文件..."
php vendor/bin/box compile --config=box-full.json

# 检查构建结果
if [ $? -eq 0 ]; then
    echo "PHAR 文件构建成功！"
    echo "生成的文件: gm-gui.phar"
    echo "文件大小: $(du -h gm-gui.phar | cut -f1)"
    
    # 创建分发包
    echo "正在创建分发包..."
    ./create-dist.sh
else
    echo "PHAR 文件构建失败。"
    exit 1
fi