<?php

namespace Yangweijie\GmGui\Interfaces;

interface FileServiceInterface
{
    /**
     * 读取文件内容
     *
     * @param string $path 文件路径
     * @return string 文件内容
     */
    public function readFile(string $path): string;

    /**
     * 写入文件内容
     *
     * @param string $path 文件路径
     * @param string $content 文件内容
     * @return bool 写入结果
     */
    public function writeFile(string $path, string $content): bool;

    /**
     * 选择文件
     *
     * @param array $filters 文件过滤器
     * @return string|null 选择的文件路径
     */
    public function selectFile(array $filters = []): ?string;

    /**
     * 保存文件
     *
     * @param string $defaultName 默认文件名
     * @param array $filters 文件过滤器
     * @return string|null 保存的文件路径
     */
    public function saveFile(string $defaultName, array $filters = []): ?string;

    /**
     * 获取文件信息
     *
     * @param string $path 文件路径
     * @return array 文件信息
     */
    public function getFileInfo(string $path): array;
}