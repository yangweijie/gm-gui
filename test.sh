#!/bin/bash
# Pest 测试运行脚本

# 检查 vendor/bin/pest 是否存在
if [ ! -f "vendor/bin/pest" ]; then
    echo "错误: Pest 未安装。请先运行 'composer install'。"
    exit 1
fi

# 运行所有测试
echo "运行所有测试..."
./vendor/bin/pest

# 检查测试结果
if [ $? -eq 0 ]; then
    echo "所有测试通过！"
else
    echo "一些测试失败了。"
    exit 1
fi