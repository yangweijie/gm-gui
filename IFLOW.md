# 国密客户端 (GM GUI) 项目概述

## 项目简介

国密客户端是一个基于 PHP 和 LibUI 的桌面应用程序，旨在展示国密算法（SM2、SM3、SM4）的完整使用场景。应用采用模块化架构设计，通过直观的图形界面为用户提供加密、解密、签名、验签、哈希计算等功能。

## 技术栈

- **GUI 框架**: kingbes-libui-sdk (基于 LibUI 的 PHP 封装)
- **国密算法**: yangweijie/gm-helper (SM2 优化版) + lpilp/guomi (SM2/SM3/SM4 基础实现)
- **语言**: PHP 8.0+
- **架构模式**: MVC + 事件驱动
- **测试框架**: PestPHP

## 目录结构

```
.
├── app.php                 # 应用入口文件
├── bin/                    # 可执行脚本目录
│   ├── code-review.php     # 代码审查脚本
│   ├── ci-review.sh        # CI集成审查脚本
│   └── pre-commit-hook.sh  # 预提交钩子脚本
├── config/                 # 配置文件目录
│   └── config.json         # 应用配置文件
├── keys/                   # 密钥存储目录
├── logs/                   # 日志文件目录
├── src/                    # 源代码目录
│   ├── Application/        # 应用核心类
│   ├── Exceptions/         # 异常处理类
│   ├── Integration/        # 集成管理类
│   ├── Interfaces/         # 接口定义
│   ├── Models/             # 数据模型
│   ├── Optimization/       # 性能优化类
│   ├── Services/           # 业务服务类
│   ├── UI/                 # 用户界面组件
│   └── Utils/              # 工具类
├── tests/                  # 测试文件目录
│   ├── Unit/               # 单元测试
│   └── Feature/            # 功能测试
└── vendor/                 # Composer依赖目录
```

## 核心功能模块

### 1. 加密服务
- **SM2服务**: 提供非对称加密、解密、签名、验签功能
- **SM3服务**: 提供哈希计算功能
- **SM4服务**: 提供对称加密、解密功能

### 2. 密钥管理
- 密钥对生成
- 密钥导入/导出（支持HEX、PEM等格式）
- 密钥格式转换
- 密钥文件存储和管理

### 3. 文件处理
- 文件读写操作
- 大文件分块处理
- 文件拖拽支持
- 安全文件删除

### 4. 配置管理
- 应用配置加载和保存
- 配置验证和备份
- 用户界面配置

### 5. 性能优化
- 应用启动优化
- 自动加载优化
- 内存使用优化
- OPcache优化

## 构建和运行

### 环境要求
- PHP 8.0 或更高版本
- Composer 依赖管理工具
- 必需的 PHP 扩展

### 安装依赖
```bash
composer install
```

### 运行应用
```bash
php app.php
```

### 运行测试
```bash
# 运行所有测试
./test.sh

# 或使用Pest直接运行
./vendor/bin/pest
```

## 开发规范

### 代码结构
- 遵循 PSR-4 自动加载标准
- 使用命名空间组织代码
- 采用 MVC 架构模式
- 接口驱动的设计

### 编码规范
- 遵循 PSR-12 编码规范
- 使用类型提示和返回类型声明
- 提供完整的 PHPDoc 注释
- 遵循 SOLID 设计原则

### 测试策略
- 单元测试覆盖核心业务逻辑
- 集成测试验证组件间协作
- 使用 PestPHP 作为测试框架
- 测试覆盖率要求达到 80% 以上

### 代码审查
项目集成了多代理代码审查系统，包括：
- **安全审查**: 检测潜在安全漏洞
- **性能审查**: 识别性能瓶颈
- **质量审查**: 检查代码质量
- **架构审查**: 确保架构一致性

## 配置说明

应用配置文件位于 `config/config.json`，主要配置项包括：

```json
{
  "app": {
    "version": "1.0.0",
    "language": "zh-CN",
    "theme": "default"
  },
  "crypto": {
    "defaultOutputFormat": "hex",
    "sm2": {
      "appendZeroFour": false,
      "mode": "C1C3C2"
    },
    "sm4": {
      "defaultMode": "cbc",
      "autoGenerateIV": true
    },
    "signature": {
      "defaultFormat": "asn1",
      "defaultUserId": ""
    }
  },
  "storage": {
    "keyStorePath": "./keys",
    "configPath": "./config",
    "tempPath": "./temp"
  },
  "ui": {
    "windowWidth": 1200,
    "windowHeight": 800,
    "rememberWindowState": true
  }
}
```

## 部署说明

### 打包分发
项目可以打包为独立的可执行文件，支持以下平台：
- Windows 10/11
- macOS 10.15+
- 主流 Linux 发行版

### 系统要求
- 最低内存: 256MB
- 推荐内存: 512MB 或更高
- 磁盘空间: 100MB 可用空间

## 贡献指南

### 开发流程
1. Fork 项目仓库
2. 创建功能分支
3. 编写代码和测试
4. 运行代码审查
5. 提交 Pull Request

### 代码质量要求
- 所有新功能必须包含单元测试
- 代码审查必须通过
- 遵循项目编码规范
- 提供完整的文档注释

### 提交规范
- 使用清晰的提交信息
- 遵循 conventional commits 规范
- 每个提交只包含一个功能变更