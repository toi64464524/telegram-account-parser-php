<?php

namespace Telegram\TDataParser\Utils;

/**
 * 二进制读取器
 * 
 * 用于读取二进制文件
 */
class BinaryReader
{
    // 文件句柄
    private $handle;
    
    // 文件大小
    private $fileSize;
    
    /**
     * 构造函数
     * 
     * @param string $filePath 文件路径
     */
    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("文件不存在: {$filePath}");
        }
        
        $this->handle = fopen($filePath, 'rb');
        if (!$this->handle) {
            throw new \Exception("无法打开文件: {$filePath}");
        }
        
        fseek($this->handle, 0, SEEK_END);
        $this->fileSize = ftell($this->handle);
        fseek($this->handle, 0, SEEK_SET);
    }
    
    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->handle) {
            fclose($this->handle);
        }
    }
    
    /**
     * 读取指定长度的字节
     * 
     * @param int $length 长度
     * @return string 字节数据
     */
    public function readBytes(int $length): string
    {
        if ($length <= 0) {
            return '';
        }
        
        $data = fread($this->handle, $length);
        if ($data === false) {
            throw new \Exception("读取失败");
        }
        
        return $data;
    }
    
    /**
     * 读取指定长度的字符串
     * 
     * @param int $length 长度
     * @return string 字符串
     */
    public function readString(int $length): string
    {
        return $this->readBytes($length);
    }
    
    /**
     * 读取无符号8位整数
     * 
     * @return int 整数
     */
    public function readUInt8(): int
    {
        $data = $this->readBytes(1);
        return ord($data);
    }
    
    /**
     * 读取无符号16位整数
     * 
     * @return int 整数
     */
    public function readUInt16(): int
    {
        $data = $this->readBytes(2);
        return unpack('v', $data)[1];
    }
    
    /**
     * 读取无符号32位整数
     * 
     * @return int 整数
     */
    public function readUInt32(): int
    {
        $data = $this->readBytes(4);
        return unpack('V', $data)[1];
    }
    
    /**
     * 读取无符号64位整数
     * 
     * @return int 整数
     */
    public function readUInt64(): int
    {
        $data = $this->readBytes(8);
        $values = unpack('V2', $data);
        
        // 合并低32位和高32位
        $low = $values[1];
        $high = $values[2];
        
        // 在PHP中处理64位整数
        if (PHP_INT_SIZE >= 8) {
            // 64位PHP
            return ($high * 4294967296) + $low;
        } else {
            // 32位PHP，可能会有精度问题
            $result = bcadd(bcmul($high, '4294967296'), $low);
            return $result;
        }
    }
    
    /**
     * 读取有符号32位整数
     * 
     * @return int 整数
     */
    public function readInt32(): int
    {
        $data = $this->readBytes(4);
        $value = unpack('V', $data)[1];
        
        // 处理有符号整数
        if ($value & 0x80000000) {
            $value = -((~$value & 0xFFFFFFFF) + 1);
        }
        
        return $value;
    }
    
    /**
     * 读取64位整数
     * 
     * @return int 整数
     */
    public function readInt64(): int
    {
        $data = $this->readBytes(8);
        $values = unpack('V2', $data);
        
        // 合并低32位和高32位
        $low = $values[1];
        $high = $values[2];
        
        // 在PHP中处理64位整数
        if (PHP_INT_SIZE >= 8) {
            // 64位PHP
            return $high * 4294967296 + $low;
        } else {
            // 32位PHP，可能会有精度问题
            $result = bcadd(bcmul($high, '4294967296'), $low);
            return $result;
        }
    }
    
    /**
     * 读取剩余所有字节
     * 
     * @return string 剩余字节数据
     */
    public function readRemaining(): string
    {
        $currentPosition = $this->tell();
        $remainingLength = $this->fileSize - $currentPosition;
        
        if ($remainingLength <= 0) {
            return '';
        }
        
        return $this->readBytes($remainingLength);
    }
    
    /**
     * 获取剩余可读字节数
     * 
     * @return int 剩余字节数
     */
    public function getBytesLeft(): int
    {
        $currentPosition = $this->tell();
        return $this->fileSize - $currentPosition;
    }
    
    /**
     * 获取当前位置
     * 
     * @return int 位置
     */
    public function tell(): int
    {
        return ftell($this->handle);
    }
    
    /**
     * 设置位置
     * 
     * @param int $offset 偏移量
     * @param int $whence 起始位置
     * @return bool 是否成功
     */
    public function seek(int $offset, int $whence = SEEK_SET): bool
    {
        return fseek($this->handle, $offset, $whence) === 0;
    }
    
    /**
     * 获取文件大小
     * 
     * @return int 文件大小
     */
    public function size(): int
    {
        return $this->fileSize;
    }
}
