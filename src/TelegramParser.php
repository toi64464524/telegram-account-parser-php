<?php

namespace Telegram\Account\Parser;

use Telegram\Account\Parser\Exceptions\ParserException;
use Telegram\Account\Parser\Parser\SessionParser;
use Telegram\Account\Parser\Parser\TDataParser;

/**
 * Telegram 解析器
 * 
 * 统一接口，用于解析Telegram tdata和session文件
 */
class TelegramParser
{
    // 解析器实例
    private $parser;
    
    /**
     * 构造函数
     * 
     * @param string $path tdata文件夹或session文件路径
     */
    public function __construct(string $path)
    {
        $this->parser = $this->createParser($path);
    }
    
    /**
     * 创建解析器
     * 
     * @param string $path 路径
     * @return TDataParser|SessionParser 解析器实例
     */
    private function createParser(string $path)
    {
        // 判断路径类型
        if (is_dir($path)) {
            // 目录，认为是tdata文件夹
            return new TDataParser($path);
        } else if (file_exists($path)) {
            // 文件，认为是session文件
            return new SessionParser($path);
        } else {
            throw new ParserException("无效的路径: {$path}");
        }
    }
    
    /**
     * 获取账户列表
     * 
     * @return array 账户列表
     */
    public function get_accounts(): array
    {
        return $this->parser->get_accounts();
    }
}
