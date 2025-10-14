#!/bin/bash
# Pre-commit hook for multi-agent code review

# 检查是否有 PHP 文件被修改
PHP_FILES=$(git diff --cached --name-only --diff-filter=ACMR | grep "\.php$" | tr '\n' ' ')

if [ -n "$PHP_FILES" ]; then
    echo "检测到 PHP 文件变更，执行代码审查..."
    
    # 运行代码审查
    php bin/code-review.php src
    
    # 检查审查结果
    if [ $? -ne 0 ]; then
        echo "代码审查发现严重问题，请修复后再提交。"
        echo "查看 logs/ 目录中的审查报告获取详细信息。"
        exit 1
    else
        echo "代码审查通过。"
    fi
else
    echo "没有 PHP 文件变更，跳过代码审查。"
fi

# 继续执行提交
exit 0