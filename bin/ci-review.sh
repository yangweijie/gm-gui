#!/bin/bash
# Continuous Integration script for multi-agent code review

echo "执行持续集成代码审查..."

# 运行代码审查
php bin/code-review.php src

# 检查审查结果
if [ $? -ne 0 ]; then
    echo "CI 失败：代码审查发现严重问题"
    exit 1
else
    echo "CI 通过：代码审查完成且未发现严重问题"
    exit 0
fi