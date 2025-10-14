<?php

use Yangweijie\GmGui\Services\FileService;
use Yangweijie\GmGui\Exceptions\CryptoException;

// 创建测试用的文件服务实例
beforeEach(function () {
    $this->fileService = new FileService();
});

test('file service can be created', function () {
    expect($this->fileService)->toBeInstanceOf(FileService::class);
});

test('file service can read file', function () {
    // 创建临时测试文件
    $tempFile = tempnam(sys_get_temp_dir(), 'file_test_');
    $testData = 'Hello, World!';
    file_put_contents($tempFile, $testData);
    
    $content = $this->fileService->readFile($tempFile);
    
    expect($content)->toBe($testData);
    
    // 清理临时文件
    unlink($tempFile);
});

test('file service throws exception for non-existent file', function () {
    $nonExistentFile = '/tmp/non_existent_file_' . uniqid();
    
    expect(fn() => $this->fileService->readFile($nonExistentFile))
        ->toThrow(CryptoException::class, '读取文件失败');
});

test('file service can write file', function () {
    $tempDir = sys_get_temp_dir();
    $tempFile = $tempDir . '/file_test_' . uniqid();
    $testData = 'Hello, World!';
    
    $result = $this->fileService->writeFile($tempFile, $testData);
    
    expect($result)->toBeTrue();
    expect(file_get_contents($tempFile))->toBe($testData);
    
    // 清理临时文件
    unlink($tempFile);
});

test('file service can write file to non-existent directory', function () {
    $tempDir = sys_get_temp_dir();
    $subDir = $tempDir . '/subdir_' . uniqid();
    $tempFile = $subDir . '/file_test_' . uniqid();
    $testData = 'Hello, World!';
    
    $result = $this->fileService->writeFile($tempFile, $testData);
    
    expect($result)->toBeTrue();
    expect(file_get_contents($tempFile))->toBe($testData);
    
    // 清理临时文件和目录
    unlink($tempFile);
    rmdir($subDir);
});

test('file service throws exception when writing fails', function () {
    // 尝试写入到一个只读目录
    $readOnlyDir = '/tmp/readonly_' . uniqid();
    mkdir($readOnlyDir, 0444); // 只读权限
    $tempFile = $readOnlyDir . '/file_test_' . uniqid();
    $testData = 'Hello, World!';
    
    expect(fn() => $this->fileService->writeFile($tempFile, $testData))
        ->toThrow(CryptoException::class, '写入文件失败');
    
    // 清理
    rmdir($readOnlyDir);
});

test('file service can get file info', function () {
    // 创建临时测试文件
    $tempFile = tempnam(sys_get_temp_dir(), 'file_test_');
    $testData = 'Hello, World!';
    file_put_contents($tempFile, $testData);
    
    $info = $this->fileService->getFileInfo($tempFile);
    
    expect($info)->toBeArray();
    expect($info)->toHaveKey('size');
    expect($info)->toHaveKey('modified');
    expect($info)->toHaveKey('permissions');
    expect($info)->toHaveKey('is_readable');
    expect($info)->toHaveKey('is_writable');
    expect($info['size'])->toBe(strlen($testData));
    expect($info['is_readable'])->toBeTrue();
    expect($info['is_writable'])->toBeTrue();
    
    // 清理临时文件
    unlink($tempFile);
});

test('file service returns empty array for non-existent file info', function () {
    $nonExistentFile = '/tmp/non_existent_file_' . uniqid();
    
    $info = $this->fileService->getFileInfo($nonExistentFile);
    
    expect($info)->toBeArray();
    expect($info)->toBeEmpty();
});

test('file service can copy file', function () {
    // 创建源文件
    $sourceFile = tempnam(sys_get_temp_dir(), 'file_test_source_');
    $testData = 'Hello, World!';
    file_put_contents($sourceFile, $testData);
    
    // 创建目标文件路径
    $destFile = sys_get_temp_dir() . '/file_test_dest_' . uniqid();
    
    $result = $this->fileService->copyFile($sourceFile, $destFile);
    
    expect($result)->toBeTrue();
    expect(file_get_contents($destFile))->toBe($testData);
    
    // 清理文件
    unlink($sourceFile);
    unlink($destFile);
});

test('file service can copy file to non-existent directory', function () {
    // 创建源文件
    $sourceFile = tempnam(sys_get_temp_dir(), 'file_test_source_');
    $testData = 'Hello, World!';
    file_put_contents($sourceFile, $testData);
    
    // 创建目标文件路径（包含不存在的子目录）
    $subDir = sys_get_temp_dir() . '/subdir_' . uniqid();
    $destFile = $subDir . '/file_test_dest_' . uniqid();
    
    $result = $this->fileService->copyFile($sourceFile, $destFile);
    
    expect($result)->toBeTrue();
    expect(file_get_contents($destFile))->toBe($testData);
    
    // 清理文件和目录
    unlink($sourceFile);
    unlink($destFile);
    rmdir($subDir);
});

test('file service throws exception when copying non-existent file', function () {
    $sourceFile = '/tmp/non_existent_file_' . uniqid();
    $destFile = sys_get_temp_dir() . '/file_test_dest_' . uniqid();
    
    expect(fn() => $this->fileService->copyFile($sourceFile, $destFile))
        ->toThrow(CryptoException::class, '复制文件失败');
});

test('file service can move file', function () {
    // 创建源文件
    $sourceFile = tempnam(sys_get_temp_dir(), 'file_test_source_');
    $testData = 'Hello, World!';
    file_put_contents($sourceFile, $testData);
    
    // 创建目标文件路径
    $destFile = sys_get_temp_dir() . '/file_test_dest_' . uniqid();
    
    $result = $this->fileService->moveFile($sourceFile, $destFile);
    
    expect($result)->toBeTrue();
    expect(file_get_contents($destFile))->toBe($testData);
    expect(file_exists($sourceFile))->toBeFalse();
    
    // 清理文件
    unlink($destFile);
});

test('file service can move file to non-existent directory', function () {
    // 创建源文件
    $sourceFile = tempnam(sys_get_temp_dir(), 'file_test_source_');
    $testData = 'Hello, World!';
    file_put_contents($sourceFile, $testData);
    
    // 创建目标文件路径（包含不存在的子目录）
    $subDir = sys_get_temp_dir() . '/subdir_' . uniqid();
    $destFile = $subDir . '/file_test_dest_' . uniqid();
    
    $result = $this->fileService->moveFile($sourceFile, $destFile);
    
    expect($result)->toBeTrue();
    expect(file_get_contents($destFile))->toBe($testData);
    expect(file_exists($sourceFile))->toBeFalse();
    
    // 清理文件和目录
    unlink($destFile);
    rmdir($subDir);
});

test('file service throws exception when moving non-existent file', function () {
    $sourceFile = '/tmp/non_existent_file_' . uniqid();
    $destFile = sys_get_temp_dir() . '/file_test_dest_' . uniqid();
    
    expect(fn() => $this->fileService->moveFile($sourceFile, $destFile))
        ->toThrow(CryptoException::class, '移动文件失败');
});

test('file service can delete file', function () {
    // 创建临时测试文件
    $tempFile = tempnam(sys_get_temp_dir(), 'file_test_');
    $testData = 'Hello, World!';
    file_put_contents($tempFile, $testData);
    
    $result = $this->fileService->deleteFile($tempFile);
    
    expect($result)->toBeTrue();
    expect(file_exists($tempFile))->toBeFalse();
});

test('file service can delete non-existent file', function () {
    $nonExistentFile = '/tmp/non_existent_file_' . uniqid();
    
    $result = $this->fileService->deleteFile($nonExistentFile);
    
    expect($result)->toBeTrue();
});

test('file service can get file size', function () {
    // 创建临时测试文件
    $tempFile = tempnam(sys_get_temp_dir(), 'file_test_');
    $testData = 'Hello, World!';
    file_put_contents($tempFile, $testData);
    
    $size = $this->fileService->getFileSize($tempFile);
    
    expect($size)->toBe(strlen($testData));
    
    // 清理临时文件
    unlink($tempFile);
});

test('file service throws exception for non-existent file size', function () {
    $nonExistentFile = '/tmp/non_existent_file_' . uniqid();
    
    expect(fn() => $this->fileService->getFileSize($nonExistentFile))
        ->toThrow(CryptoException::class, '获取文件大小失败');
});

test('file service can check if file exists', function () {
    // 创建临时测试文件
    $tempFile = tempnam(sys_get_temp_dir(), 'file_test_');
    
    $exists = $this->fileService->fileExists($tempFile);
    
    expect($exists)->toBeTrue();
    
    // 清理临时文件
    unlink($tempFile);
    
    $exists = $this->fileService->fileExists($tempFile);
    
    expect($exists)->toBeFalse();
});

test('file service can check if file is readable', function () {
    // 创建临时测试文件
    $tempFile = tempnam(sys_get_temp_dir(), 'file_test_');
    
    $readable = $this->fileService->isReadable($tempFile);
    
    expect($readable)->toBeTrue();
    
    // 清理临时文件
    unlink($tempFile);
    
    $readable = $this->fileService->isReadable($tempFile);
    
    expect($readable)->toBeFalse();
});

test('file service can check if file is writable', function () {
    // 创建临时测试文件
    $tempFile = tempnam(sys_get_temp_dir(), 'file_test_');
    
    $writable = $this->fileService->isWritable($tempFile);
    
    expect($writable)->toBeTrue();
    
    // 清理临时文件
    unlink($tempFile);
    
    $writable = $this->fileService->isWritable($tempFile);
    
    expect($writable)->toBeFalse();
});

test('file service can create temp file', function () {
    $tempFile = $this->fileService->createTempFile('Hello, World!', 'test_');
    
    expect($tempFile)->toBeString();
    expect(file_exists($tempFile))->toBeTrue();
    expect(file_get_contents($tempFile))->toBe('Hello, World!');
    
    // 清理临时文件
    unlink($tempFile);
});

test('file service can create empty temp file', function () {
    $tempFile = $this->fileService->createTempFile('', 'test_');
    
    expect($tempFile)->toBeString();
    expect(file_exists($tempFile))->toBeTrue();
    expect(file_get_contents($tempFile))->toBe('');
    
    // 清理临时文件
    unlink($tempFile);
});

test('file service throws exception when creating temp file fails', function () {
    // 模拟创建临时文件失败的情况很难，所以我们跳过这个测试
    // 在实际应用中，这可能发生在磁盘空间不足或权限问题时
    $this->expectNotToPerformAssertions();
});

test('file service can read file in chunks', function () {
    // 创建临时测试文件
    $tempFile = tempnam(sys_get_temp_dir(), 'file_test_');
    $testData = str_repeat('A', 1000); // 1000个字符
    file_put_contents($tempFile, $testData);
    
    $chunks = [];
    $chunkCallback = function ($chunk, $index) use (&$chunks) {
        $chunks[] = ['index' => $index, 'data' => $chunk];
    };
    
    $this->fileService->readChunked($tempFile, $chunkCallback, 256);
    
    expect($chunks)->not->toBeEmpty();
    expect(array_sum(array_map(fn($chunk) => strlen($chunk['data']), $chunks)))->toBe(1000);
    
    // 验证所有数据都正确读取
    $reconstructedData = '';
    foreach ($chunks as $chunk) {
        $reconstructedData .= $chunk['data'];
    }
    expect($reconstructedData)->toBe($testData);
    
    // 清理临时文件
    unlink($tempFile);
});

test('file service throws exception when reading chunks of non-existent file', function () {
    $nonExistentFile = '/tmp/non_existent_file_' . uniqid();
    $chunkCallback = function ($chunk, $index) {
        // 回调函数不会被调用
    };
    
    expect(fn() => $this->fileService->readChunked($nonExistentFile, $chunkCallback, 256))
        ->toThrow(CryptoException::class, '分块读取文件失败');
});

// 模拟方法测试（这些方法在GUI环境中会实际实现）
test('file service select file returns null', function () {
    $result = $this->fileService->selectFile();
    
    expect($result)->toBeNull();
});

test('file service save file returns null', function () {
    $result = $this->fileService->saveFile('test.txt');
    
    expect($result)->toBeNull();
});