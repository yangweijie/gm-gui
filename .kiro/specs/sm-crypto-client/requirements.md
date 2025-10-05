# 国密客户端需求文档

## 介绍

本项目旨在开发一个基于国密算法的桌面客户端应用程序，展示国密 SM2/SM3/SM4 算法的具体用法。该客户端将提供直观的图形界面，让用户能够方便地进行国密加密、解密、签名、验签等操作，同时支持密钥管理和文件处理功能。应用将展示 SM2 非对称加密、SM3 哈希算法和 SM4 对称加密的完整使用场景。

## 需求

### 需求 1 - 国密加密解密功能

**用户故事:** 作为用户，我希望能够使用 SM2 算法对文本和文件进行加密解密，以便保护敏感数据的安全性

#### 验收标准

1. WHEN 用户输入明文文本 THEN 系统 SHALL 使用 SM2 公钥对文本进行加密并显示密文
2. WHEN 用户输入密文 THEN 系统 SHALL 使用 SM2 私钥对密文进行解密并显示明文
3. WHEN 用户选择文件进行加密 THEN 系统 SHALL 对文件内容进行 SM2 加密并保存加密文件
4. WHEN 用户选择加密文件进行解密 THEN 系统 SHALL 解密文件并保存原始文件
5. WHEN 加密操作失败 THEN 系统 SHALL 显示具体的错误信息
6. WHEN 用户选择输出格式 THEN 系统 SHALL 支持 hex 和 base64 两种格式输出

### 需求 2 - 国密签名验签功能

**用户故事:** 作为用户，我希望能够使用 SM2 算法对数据进行数字签名和验证，以确保数据的完整性和来源可信

#### 验收标准

1. WHEN 用户输入待签名数据 THEN 系统 SHALL 使用 SM2 私钥生成数字签名
2. WHEN 用户输入数据和签名 THEN 系统 SHALL 使用 SM2 公钥验证签名的有效性
3. WHEN 用户指定用户 ID THEN 系统 SHALL 在签名过程中使用指定的用户 ID
4. WHEN 签名格式选择为 ASN1 THEN 系统 SHALL 输出 ASN1 格式的签名
5. WHEN 签名格式选择为 RS THEN 系统 SHALL 输出 RS 格式的签名
6. WHEN 验签失败 THEN 系统 SHALL 明确显示验签失败的原因

### 需求 3 - 密钥管理功能

**用户故事:** 作为用户，我希望能够生成、导入、导出和管理 SM2 密钥对，以便进行各种国密操作

#### 验收标准

1. WHEN 用户请求生成密钥对 THEN 系统 SHALL 生成新的 SM2 公私钥对
2. WHEN 用户导入密钥文件 THEN 系统 SHALL 解析并验证密钥的有效性
3. WHEN 用户导出密钥 THEN 系统 SHALL 支持多种格式（PEM、HEX、ASN1）导出
4. WHEN 密钥为 ASN1 格式 THEN 系统 SHALL 自动转换为 HEX 格式用于加密操作
5. WHEN 用户保存密钥 THEN 系统 SHALL 将密钥安全存储到本地文件
6. WHEN 用户删除密钥 THEN 系统 SHALL 提示确认并安全删除密钥文件

### 需求 4 - SM4 对称加密功能

**用户故事:** 作为用户，我希望能够使用 SM4 算法进行对称加密解密，以便快速处理大量数据的加密需求

#### 验收标准

1. WHEN 用户输入明文和 SM4 密钥 THEN 系统 SHALL 使用指定的加密模式进行加密
2. WHEN 用户选择 ECB 模式 THEN 系统 SHALL 使用 SM4-ECB 算法进行加密解密
3. WHEN 用户选择 CBC 模式 THEN 系统 SHALL 使用 SM4-CBC 算法和 IV 进行加密解密
4. WHEN 用户选择 CFB 模式 THEN 系统 SHALL 使用 SM4-CFB 算法和 IV 进行加密解密
5. WHEN 用户选择 OFB 模式 THEN 系统 SHALL 使用 SM4-OFB 算法和 IV 进行加密解密
6. WHEN 用户选择 CTR 模式 THEN 系统 SHALL 使用 SM4-CTR 算法和 IV 进行加密解密
7. WHEN 用户未提供 IV THEN 系统 SHALL 自动生成 16 字节的随机 IV
8. WHEN 用户输入的密钥长度不是 16 字节 THEN 系统 SHALL 显示密钥长度错误提示
9. WHEN 加密模式需要 IV 但未提供 THEN 系统 SHALL 显示 IV 缺失错误提示
10. WHEN 用户处理大文件 THEN 系统 SHALL 显示加密进度并支持取消操作

### 需求 5 - 哈希计算功能

**用户故事:** 作为用户，我希望能够使用 SM3 算法计算数据的哈希值，以便进行数据完整性校验

#### 验收标准

1. WHEN 用户输入文本数据 THEN 系统 SHALL 计算并显示 SM3 哈希值
2. WHEN 用户选择文件 THEN 系统 SHALL 计算文件的 SM3 哈希值
3. WHEN 哈希计算完成 THEN 系统 SHALL 支持复制哈希值到剪贴板
4. WHEN 用户比较两个哈希值 THEN 系统 SHALL 显示比较结果
5. WHEN 文件过大 THEN 系统 SHALL 显示计算进度

### 需求 6 - 用户界面功能

**用户故事:** 作为用户，我希望有一个直观易用的图形界面，以便方便地进行各种国密操作

#### 验收标准

1. WHEN 应用启动 THEN 系统 SHALL 显示主窗口和功能选项卡
2. WHEN 用户切换功能选项卡 THEN 系统 SHALL 显示对应的操作界面
3. WHEN 用户进行操作 THEN 系统 SHALL 提供实时的状态反馈
4. WHEN 操作完成 THEN 系统 SHALL 显示成功提示和结果
5. WHEN 发生错误 THEN 系统 SHALL 显示友好的错误提示信息
6. WHEN 用户复制结果 THEN 系统 SHALL 支持一键复制到剪贴板

### 需求 7 - 文件操作功能

**用户故事:** 作为用户，我希望能够通过拖拽或选择的方式处理文件，以便批量进行国密操作

#### 验收标准

1. WHEN 用户拖拽文件到应用 THEN 系统 SHALL 自动识别文件类型并提供相应操作
2. WHEN 用户点击文件选择按钮 THEN 系统 SHALL 打开文件选择对话框
3. WHEN 用户选择多个文件 THEN 系统 SHALL 支持批量处理
4. WHEN 文件处理完成 THEN 系统 SHALL 提供保存位置选择
5. WHEN 文件格式不支持 THEN 系统 SHALL 显示格式错误提示
6. WHEN 文件访问权限不足 THEN 系统 SHALL 显示权限错误提示

### 需求 8 - 配置管理功能

**用户故事:** 作为用户，我希望能够配置应用的各种参数，以便根据需要自定义国密操作的行为

#### 验收标准

1. WHEN 用户打开设置界面 THEN 系统 SHALL 显示所有可配置选项
2. WHEN 用户修改默认输出格式 THEN 系统 SHALL 保存设置并在后续操作中使用
3. WHEN 用户修改密钥存储路径 THEN 系统 SHALL 验证路径有效性并保存
4. WHEN 用户启用 C1 补 04 选项 THEN 系统 SHALL 在加密时自动添加 04 前缀
5. WHEN 用户重启应用 THEN 系统 SHALL 自动加载之前保存的配置
6. WHEN 配置文件损坏 THEN 系统 SHALL 使用默认配置并提示用户