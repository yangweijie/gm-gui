<?php

use Yangweijie\GmGui\Exceptions\CryptoException;

test('crypto exception constants are defined', function () {
    expect(CryptoException::INPUT_VALIDATION_ERROR)->toBeInt();
    expect(CryptoException::KEY_ERROR)->toBeInt();
    expect(CryptoException::ALGORITHM_ERROR)->toBeInt();
    expect(CryptoException::FILE_OPERATION_ERROR)->toBeInt();
    expect(CryptoException::SYSTEM_ERROR)->toBeInt();
});

test('crypto exception can be created with default values', function () {
    $exception = new CryptoException();
    
    expect($exception)->toBeInstanceOf(CryptoException::class);
    expect($exception)->toBeInstanceOf(Exception::class);
    expect($exception->getErrorType())->toBe('unknown');
});

test('crypto exception can be created with custom values', function () {
    $message = 'Test error message';
    $code = 123;
    $errorType = 'test_type';
    
    $exception = new CryptoException($message, $code, null, $errorType);
    
    expect($exception->getMessage())->toBe($message);
    expect($exception->getCode())->toBe($code);
    expect($exception->getErrorType())->toBe($errorType);
});

test('crypto exception factory methods work correctly', function () {
    $message = 'Test message';
    
    // Test input validation error
    $exception = CryptoException::inputValidationError($message);
    expect($exception)->toBeInstanceOf(CryptoException::class);
    expect($exception->getMessage())->toBe($message);
    expect($exception->getCode())->toBe(CryptoException::INPUT_VALIDATION_ERROR);
    expect($exception->getErrorType())->toBe('input_validation');
    
    // Test key error
    $exception = CryptoException::keyError($message);
    expect($exception)->toBeInstanceOf(CryptoException::class);
    expect($exception->getMessage())->toBe($message);
    expect($exception->getCode())->toBe(CryptoException::KEY_ERROR);
    expect($exception->getErrorType())->toBe('key');
    
    // Test algorithm error
    $exception = CryptoException::algorithmError($message);
    expect($exception)->toBeInstanceOf(CryptoException::class);
    expect($exception->getMessage())->toBe($message);
    expect($exception->getCode())->toBe(CryptoException::ALGORITHM_ERROR);
    expect($exception->getErrorType())->toBe('algorithm');
    
    // Test file operation error
    $exception = CryptoException::fileOperationError($message);
    expect($exception)->toBeInstanceOf(CryptoException::class);
    expect($exception->getMessage())->toBe($message);
    expect($exception->getCode())->toBe(CryptoException::FILE_OPERATION_ERROR);
    expect($exception->getErrorType())->toBe('file_operation');
    
    // Test system error
    $exception = CryptoException::systemError($message);
    expect($exception)->toBeInstanceOf(CryptoException::class);
    expect($exception->getMessage())->toBe($message);
    expect($exception->getCode())->toBe(CryptoException::SYSTEM_ERROR);
    expect($exception->getErrorType())->toBe('system');
});