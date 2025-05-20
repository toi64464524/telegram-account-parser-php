<?php

namespace Telegram\Account\Parser\Types;

use Telegram\Account\Parser\Exceptions\AccountException;

/**
 * Telegram session 文件解析器
 * 
 * 用于解析Telegram session文件，提取用户ID、DC ID和认证密钥等信息
 */
class TelegramAccount
{
    // 账户信息
    private ?int $dc_id;
    private string $server_address;
    private int $port;
    private string $auth_key;
    private ?int $user_id;
          
    // 默认API信息（非关键字段可以保留默认值）
    private $api_id = 2040;
    private $api_hash = 'b18441a1ff607e10a989891a5462e627';
    private $device_model = 'Desktop';
    private $system_version = 'Windows 10';
    private $app_version = 'app_version';
    private $lang_code = 'en';
    private $register_at=null;
    
    /**
     * 构造函数
     * 
     * @param array $accountInfo 账户信息数组
     * @throws AccountException 如果未解析到DC ID
     */
    public function __construct(array $data)
    {
        $this->dc_id = (int)$data['dc_id'];
        $this->auth_key = $data['auth_key'];
        $this->server_address = $data['server_address'];
        $this->port = 443;
        $this->user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;
        $this->api_id = isset($data['api_id']) ? (int)$data['api_id'] : $this->api_id;
        $this->api_hash = isset($data['api_hash']) ? (string)$data['api_hash'] : $this->api_hash;  
        $this->device_model = isset($data['device_model']) ? (string)$data['device_model'] : $this->device_model;
        $this->system_version = isset($data['system_version']) ? (string)$data['system_version'] : $this->system_version;
        $this->app_version = isset($data['app_version']) ? (string)$data['app_version'] : $this->app_version;
        $this->lang_code = isset($data['lang_code']) ? (string)$data['lang_code'] : $this->lang_code;
        $this->register_at = isset($data['register_at']) ? $data['register_at'] : $this->register_at;
        $this->get_server_address();
        $this->get_port();
    }
    
    /**
     * 获取server_address
     * 
     * @return string 服务器地址
     * @throws AccountException 如果未解析到DC ID
     */
    public function get_server_address(): string
    {
        $dc_ips = [
            1 => "149.154.175.53",
            2 => "149.154.167.51",
            3 => "149.154.175.100",
            4 => "149.154.167.91",
            5 => "91.108.56.130",
            121 => "95.213.217.195",
        ];
        if (!$this->server_address) {
            $this->server_address = isset($dc_ips[$this->dc_id]) ? $dc_ips[$this->dc_id] : null;
        }
        return $this->server_address;
    }

    /**
     * 获取端口
     * 
     * @return int 端口
     * @throws AccountException 如果未解析到DC ID
     */
    public function get_port(): int
    {
        return $this->port;
    }
    
    /**
     * 获取用户ID
     * 
     * @return int|null 用户ID，可能为null
     * @throws AccountException 如果未解析到DC ID
     */
    public function get_user_id(): ?int
    {
        return $this->user_id;
    }
    
    /**
     * 获取DC ID
     * 
     * @return int DC ID
     * @throws AccountException 如果未解析到DC ID
     */
    public function get_dc_id(): int
    {
        if ($this->dc_id >= 1 && $this->dc_id <= 5) {
            throw new AccountException("未解析到DC ID");
        }
        return $this->dc_id;
    }
    
    /**
     * 获取认证密钥
     * 
     * @return string 认证密钥
     * @throws AccountException 如果未解析到认证密钥
     */
    public function get_auth_key(): string
    {
        if (strlen($this->auth_key) != 256) {
            throw new AccountException("未解析到认证密钥");
        }
        return $this->auth_key;
    }

    /**
     * 获取认证密钥 （十六进制）
     * 
     * @return string 认证密钥
     * @throws AccountException 如果未解析到认证密钥
     */
    public function get_auth_key_hex(): string
    {
        return bin2hex($this->auth_key);
    }
}
