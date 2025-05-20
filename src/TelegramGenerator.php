<?php

namespace Telegram\Account\Parser;

use Telegram\Account\Parser\Exceptions\GeneratorException;
use Telegram\Account\Parser\Generator\SessionGenerator;
use Telegram\Account\Parser\Generator\TDataGenerator;
use Telegram\Account\Parser\Types\TelegramAccount;

/**
 * Telegram 生成器
 * 
 * 统一接口，用于生成Telegram tdata和session文件
 */
class TelegramGenerator
{
    /**
     * 生成Telegram数据
     * 
     * @param array $accountInfo 账户信息
     * @param string $outputPath 输出路径
     * @param string $type 类型，'tdata'或'session'
     * @return bool 是否成功
     */
    public static function generate(array $accountInfo, string $outputPath, string $type = 'session'): bool
    {
        // 检查必要字段
        if (!isset($accountInfo['user_id']) || !isset($accountInfo['dc_id']) || !isset($accountInfo['auth_key'])) {
            throw new GeneratorException("缺少必要的账户信息字段");
        }

        $account = new TelegramAccount($accountInfo);
        
        // 根据类型选择生成器
        if ($type === 'session') {
            return SessionGenerator::generate($account, $outputPath);
        } else if ($type === 'tdata') {
            return TDataGenerator::generate($account, $outputPath);
        } else {
            throw new GeneratorException("不支持的类型: {$type}");
        }
    }
}
