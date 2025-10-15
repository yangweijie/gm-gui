# 国密客户端 (GM GUI)

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

国密客户端是一个基于 PHP 和 LibUI 的桌面应用程序，旨在展示国密算法（SM2、SM3、SM4）的完整使用场景。应用采用模块化架构设计，通过直观的图形界面为用户提供加密、解密、签名、验签、哈希计算等功能。

## 功能特性

- **SM2 非对称加密**：支持加密、解密、签名、验签操作
- **SM3 哈希算法**：支持数据哈希计算和比较功能
- **SM4 对称加密**：支持多种工作模式的加密和解密操作
- **密钥管理**：支持密钥生成、导入、导出和格式转换
- **多格式支持**：支持 HEX、Base64 等多种数据格式
- **跨平台**：支持 Windows、macOS 和 Linux 操作系统

## 技术栈

- **GUI 框架**: [kingbes-libui-sdk](https://github.com/yangweijie/kingbes-libui-sdk) (基于 LibUI 的 PHP 封装)
- **国密算法**: [yangweijie/gm-helper](https://github.com/yangweijie/gm-helper) (SM2 优化版) + [lpilp/guomi](https://github.com/lpilp/guomi) (SM2/SM3/SM4 基础实现)
- **语言**: PHP 8.0+
- **架构模式**: MVC + 事件驱动
- **测试框架**: [PestPHP](https://pestphp.com/)

## 系统要求

- PHP 8.0 或更高版本
- PHP GMP 扩展
- PHP JSON 扩展
- 图形环境支持

## 安装

1. 克隆项目仓库：
   ```bash
   git clone https://github.com/yangweijie/gm-gui.git
   cd gm-gui
   ```

2. 安装依赖：
   ```bash
   composer install
   ```

## 运行应用

### 开发模式

直接运行应用：
```bash
php app.php
```

### 打包分发

项目支持打包为跨平台的可执行文件：

1. 构建 PHAR 文件和分发包：
   ```bash
   ./build-phar.sh
   ```

2. 分发生成的压缩包（位于 `dist/` 目录中）给用户。

> ⚠️ **重要**：由于应用程序依赖于本地 GUI 库，分发包必须包含相应的本地库文件才能正常运行。

## 运行测试

```bash
# 运行所有测试
./test.sh

# 或使用 Pest 直接运行
./vendor/bin/pest
```

## 架构设计

### 目录结构

```
.
├── app.php                 # 应用入口文件
├── bin/                    # 可执行脚本目录
├── config/                 # 配置文件目录
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
└── vendor/                 # Composer 依赖目录
```

### 核心模块

1. **加密服务**：提供 SM2、SM3、SM4 算法的完整实现
2. **密钥管理**：处理密钥的生成、存储和格式转换
3. **文件处理**：支持文件的读写和拖拽操作
4. **配置管理**：管理应用配置和用户偏好设置
5. **UI 组件**：提供直观的图形用户界面

## 贡献

欢迎提交 Issue 和 Pull Request 来改进项目。

### 开发流程

1. Fork 项目仓库
2. 创建功能分支
3. 编写代码和测试
4. 提交 Pull Request

## 许可证

本项目采用 MIT 许可证。详情请见 [LICENSE](LICENSE) 文件。

## 作者

[yangweijie](mailto:917647288@qq.com)