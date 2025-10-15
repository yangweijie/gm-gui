#!/bin/bash
# 创建分发包的脚本

echo "正在创建国密客户端分发包..."

# 检查必要的文件和目录
if [ ! -f "gm-gui.phar" ]; then
    echo "错误: 未找到 gm-gui.phar 文件。请先运行 './build-phar.sh' 构建 PHAR 文件。"
    exit 1
fi

# 获取当前平台
OS=$(uname -s)
case $OS in
    Darwin*)
        PLATFORM="macos"
        LIB_FILE="vendor/kingbes/libui/lib/macos/libui.dylib"
        ;;
    Linux*)
        PLATFORM="linux"
        LIB_FILE="vendor/kingbes/libui/lib/linux/libui.so"
        ;;
    MINGW*|MSYS*|CYGWIN*)
        PLATFORM="windows"
        LIB_FILE="vendor/kingbes/libui/lib/windows/libui.dll"
        ;;
    *)
        echo "警告: 未知平台 $OS，使用 macOS 配置"
        PLATFORM="macos"
        LIB_FILE="vendor/kingbes/libui/lib/macos/libui.dylib"
        ;;
esac

# 检查库文件是否存在
if [ ! -f "$LIB_FILE" ]; then
    echo "错误: 未找到库文件 $LIB_FILE"
    exit 1
fi

# 创建分发目录
DIST_DIR="dist"
PACKAGE_NAME="gm-gui-$PLATFORM"
PACKAGE_DIR="$DIST_DIR/$PACKAGE_NAME"

echo "正在创建分发目录: $PACKAGE_DIR"

# 清理旧的分发目录
rm -rf "$PACKAGE_DIR"
mkdir -p "$PACKAGE_DIR"

# 复制文件到分发目录
cp gm-gui.phar "$PACKAGE_DIR/"
cp "$LIB_FILE" "$PACKAGE_DIR/"

# 创建运行脚本
if [ "$PLATFORM" = "windows" ]; then
    # Windows 批处理脚本
    echo "@echo off" > "$PACKAGE_DIR/run.bat"
    echo "php gm-gui.phar" >> "$PACKAGE_DIR/run.bat"
    echo "pause" >> "$PACKAGE_DIR/run.bat"
else
    # Unix/Linux/macOS shell 脚本
    echo "#!/bin/bash" > "$PACKAGE_DIR/run.sh"
    echo "php gm-gui.phar" >> "$PACKAGE_DIR/run.sh"
    chmod +x "$PACKAGE_DIR/run.sh"
fi

# 创建 README 文件
cat > "$PACKAGE_DIR/README.txt" << EOF
国密客户端 (GM GUI) 分发包
=========================

平台: $PLATFORM

包含文件:
- gm-gui.phar: 主应用程序
- $(basename $LIB_FILE): 本地 GUI 库
- run.$([ "$PLATFORM" = "windows" ] && echo "bat" || echo "sh"): 运行脚本

系统要求:
- PHP 8.0 或更高版本
- PHP GMP 扩展
- PHP JSON 扩展
- 图形环境支持

运行方法:
1. 确保系统满足上述要求
2. 运行 ./$([ "$PLATFORM" = "windows" ] && echo "run.bat" || echo "run.sh")

注意: 
- 请勿移动或重命名库文件，否则应用程序无法正常运行
- 在某些系统上可能需要给予执行权限
EOF

# 创建压缩包
echo "正在创建压缩包..."
cd "$DIST_DIR"
if [ "$PLATFORM" = "windows" ]; then
    zip -r "$PACKAGE_NAME.zip" "$PACKAGE_NAME"
    echo "分发包已创建: $DIST_DIR/$PACKAGE_NAME.zip"
else
    tar -czf "$PACKAGE_NAME.tar.gz" "$PACKAGE_NAME"
    echo "分发包已创建: $DIST_DIR/$PACKAGE_NAME.tar.gz"
fi

echo "分发包创建完成！"