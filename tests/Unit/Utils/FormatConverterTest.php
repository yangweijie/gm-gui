<?php

use Yangweijie\GmGui\Utils\FormatConverter;

test('format converter can convert hex to bin', function () {
    $hex = '48656c6c6f20576f726c64'; // "Hello World"的十六进制表示
    $expected = 'Hello World';
    
    $result = FormatConverter::hexToBin($hex);
    
    expect($result)->toBe($expected);
});

test('format converter can convert bin to hex', function () {
    $bin = 'Hello World';
    $expected = '48656c6c6f20576f726c64';
    
    $result = FormatConverter::binToHex($bin);
    
    expect($result)->toBe($expected);
});

test('format converter can convert base64 to bin', function () {
    $base64 = base64_encode('Hello World');
    $expected = 'Hello World';
    
    $result = FormatConverter::base64ToBin($base64);
    
    expect($result)->toBe($expected);
});

test('format converter can convert bin to base64', function () {
    $bin = 'Hello World';
    $expected = base64_encode('Hello World');
    
    $result = FormatConverter::binToBase64($bin);
    
    expect($result)->toBe($expected);
});

test('format converter can validate valid hex', function () {
    $hex = '0123456789abcdefABCDEF';
    
    $result = FormatConverter::isValidHex($hex);
    
    expect($result)->toBeTrue();
});

test('format converter rejects invalid hex with non-hex characters', function () {
    $hex = '0123456789abcdefG'; // 包含'G'
    
    $result = FormatConverter::isValidHex($hex);
    
    expect($result)->toBeFalse();
});

test('format converter rejects invalid hex with odd length', function () {
    $hex = '0123456789abcdefA'; // 17个字符，奇数长度
    
    $result = FormatConverter::isValidHex($hex);
    
    expect($result)->toBeFalse();
});

test('format converter rejects empty hex', function () {
    $hex = '';
    
    $result = FormatConverter::isValidHex($hex);
    
    expect($result)->toBeFalse();
});

test('format converter can validate valid base64', function () {
    $base64 = base64_encode('Hello World');
    
    $result = FormatConverter::isValidBase64($base64);
    
    expect($result)->toBeTrue();
});

test('format converter can validate empty base64', function () {
    $base64 = '';
    
    $result = FormatConverter::isValidBase64($base64);
    
    expect($result)->toBeTrue();
});

test('format converter rejects invalid base64', function () {
    $base64 = 'invalid_base64!!!';
    
    $result = FormatConverter::isValidBase64($base64);
    
    expect($result)->toBeFalse();
});

test('format converter rejects base64 with extra characters', function () {
    $base64 = base64_encode('Hello World') . 'extra';
    
    $result = FormatConverter::isValidBase64($base64);
    
    expect($result)->toBeFalse();
});