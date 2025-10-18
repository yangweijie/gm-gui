# Project Context

## Purpose
国密客户端 (GM GUI) 是一个基于 PHP 和 LibUI 的桌面应用程序，旨在展示国密算法（SM2、SM3、SM4）的完整使用场景。应用采用模块化架构设计，通过直观的图形界面为用户提供加密、解密、签名、验签、哈希计算等功能，满足国密算法在实际应用中的需求。

## Tech Stack
- PHP 8.0+ (primary language)
- kingbes-libui-sdk (GUI framework based on LibUI)
- yangweijie/gm-helper (optimized SM2 implementation)
- lpilp/guomi (SM2/SM3/SM4 basic implementation)
- yangweijie/sm4-gcm (SM4 GCM mode implementation)
- PestPHP (testing framework)
- Composer (dependency management)
- PSR (standards: PSR-4 autoloading, PSR-12 coding style, PSR-3/Log)

## Project Conventions

### Code Style
- Follow PSR-12 coding standards
- Use type hints and return type declarations
- Provide complete PHPDoc comments for all public methods
- Follow camelCase naming convention for methods and variables
- Use PascalCase for class names
- Use UPPER_SNAKE_CASE for constants
- 4-space indentation
- Maximum line length of 120 characters
- All exceptions should extend from CryptoException base class

### Architecture Patterns
- MVC (Model-View-Controller) architecture pattern
- Event-driven programming model
- Service-oriented architecture with dedicated service classes
- Dependency injection for service components
- PSR-4 autoloading standard
- Interface-driven design with CryptoServiceInterface, FileServiceInterface, and KeyManagementInterface
- Single-responsibility principle with dedicated service classes for each function

### Testing Strategy
- Unit tests covering core business logic using PestPHP
- Integration tests verifying component collaboration
- Test coverage requirement of at least 80%
- Test files located in tests/ directory with Unit/ and Feature/ subdirectories
- Follow PestPHP conventions for test organization
- Mock external dependencies in unit tests

### Git Workflow
- Feature branch workflow
- Conventional commits format for clear commit messages
- Each commit should contain a single functional change
- Branch naming: feature/feature-name, bugfix/issue-description, hotfix/urgent-fix
- Pull requests require code review before merging
- Follow semantic versioning principles

## Domain Context
This project implements Chinese National Cryptography Standards (SM2, SM3, SM4) for secure data processing:
- SM2: Elliptic Curve Cryptography (ECC) based public key algorithm for encryption, decryption, digital signatures and verification
- SM3: Cryptographic hash algorithm producing 256-bit hash values
- SM4: Block cipher algorithm for symmetric encryption supporting various modes (ECB, CBC, CTR, GCM)
- Key formats include PEM, HEX, and binary representations
- Support for different output formats (HEX, Base64, binary)
- Multiple padding schemes (PKCS#5, PKCS#7, Zero padding)
- Integration with GUI for user-friendly operations

## Important Constraints
- Requires PHP 8.0+ with GMP extension
- GUI requires local platform-specific library files (libui.dylib/.so/.dll) that cannot be bundled in PHAR
- PHAR distribution requires separate distribution of native GUI libraries
- Application needs graphical environment support to run
- Cross-platform compatibility (Windows, macOS, Linux)
- Memory limitations: minimum 256MB recommended 512MB+
- Large file processing requires chunked handling to avoid memory exhaustion

## External Dependencies
- kingbes-libui-sdk: PHP GUI framework based on LibUI
- yangweijie/gm-helper: Optimized SM2 cryptographic implementation
- lpilp/guomi: Basic implementations of SM2/SM3/SM4 algorithms
- yangweijie/sm4-gcm: SM4 algorithm with GCM mode support
- humbug/box: PHAR packaging tool
- pestphp/pest: Testing framework
- psr/log: Logging interface standard
- Native system libraries: libui for GUI rendering
