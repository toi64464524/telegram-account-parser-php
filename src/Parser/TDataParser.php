<?php

namespace Telegram\Account\Parser\Parser;;


use Telegram\Account\Parser\Exceptions\ParserException;
use Telegram\Account\Parser\Types\TelegramAccount;

/**
 * Telegram tdata 文件夹解析器
 * 
 * 用于解析Telegram Desktop的tdata文件夹
 */
class TDataParser
{
    // 常量定义
    const WIDE_IDS_TAG = 0x1;
    
    // 默认API信息（非关键字段可以保留默认值）
    private static $defaultApiId = 2040;
    private static $defaultApiHash = 'b18441a1ff607e10a989891a5462e627';
    private static $defaultDeviceModel = 'Desktop';
    private static $defaultSystemVersion = 'Windows 10';
    private static $defaultAppVersion = '3.4.3 x64';
    private static $defaultLangCode = 'en';
    
    // 账户信息
    private $accounts = [];
    // session文件路径
    private $tdata_path;
    /**
     * 构造函数
     * 
     * @param string $tdataPath tdata文件夹路径
     * @throws ParserException 如果解析失败
     */
    public function __construct(string $tdata_path)
    {
        if (!str_ends_with($tdata_path, 'tdata')) {
            $tdata_path = $tdata_path . '/tdata';
        }

        // 查找账户目录
        if (!is_file($tdata_path . '/D877F783D5D3EF8C/maps') || !is_file($tdata_path . '/D877F783D5D3EF8Cs') || !is_file($tdata_path . '/key_datas')) {
            throw new ParserException("未找到有效的账户tdata目录");
        }
        $this->tdata_path = $tdata_path;
        $this->load_tdata_file();
    }

    /**
     * 解析账户信息
     * 
     * @throws ParserException 如果解析失败 
     */
    private function load_tdata_file()
    {
        $response = file_get_contents("http://127.0.0.1:18661/read_tdata?session_file_path=" .$this->tdata_path);
        if ($response === false) {
            throw new \Exception("请求失败：无法连接本地服务");
        }

        if ($res = json_decode($response, true)) {
            if (isset($res['error'])) {
                throw new \Exception($res['error']);
            }
            
            foreach ($res as $account) {
                if (is_array($account)) {
                    $account['auth_key'] = hex2bin($account['auth_key']);
                    $this->accounts[] = new TelegramAccount($account);
                }
            }
        }
    }   
    
    /**
     * 获取账户列表
     * 
     * @return array 账户列表
     */
    public function get_accounts(): array
    {
        return $this->accounts;
    }
}
