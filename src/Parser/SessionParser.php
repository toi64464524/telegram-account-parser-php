<?php

namespace Telegram\Account\Parser\Parser;

use Telegram\Account\Parser\Exceptions\ParserException;
use Telegram\Account\Parser\Types\TelegramAccount;
use \PDO;

/**
 * Telegram session 文件解析器
 * 
 * 用于解析Telegram session文件，提取用户ID、DC ID和认证密钥等信息
 */
class SessionParser
{
    // 账户信息
    private $accounts = [];
    
    // session文件路径
    private $session_path;
    
    /**
     * 构造函数
     * 
     * @param string $sessionPath session文件路径
     */
    public function __construct(string $session_path)
    {
        $this->session_path = $session_path;
        // 检查文件是否存在
        if (!file_exists($this->session_path) || !is_file($this->session_path)) {
            throw new ParserException("Session文件不存在: {$this->session_path}");
        }
        
        try {
            // 创建 SQLite 连接
            $db = new PDO('sqlite:' . $this->session_path);
            if ($db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='sessions'")->fetch()) {
                $session_data = $db->query("SELECT * FROM sessions LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
                if ($session_data) {
                    $dc_id = $session_data['dc_id'];
                    $server_address = $session_data['server_address'];
                    $port = $session_data['port'];
                    $auth_key = $session_data['auth_key'];
                    $user_id = isset($session_data['user_id']) ? $session_data['user_id'] : null;
                    $register_at = isset($session_data['date']) ? date('Y-m-d H:i:s', $session_data['date']) : null;
                }
            }

            if (!$user_id && $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='entities'")->fetch()) {
                $user_data = $db->query("SELECT * FROM entities WHERE id =0 LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
                if ($user_data) {
                    $user_id = $user_data['hash'];
                    $register_at = date('Y-m-d H:i:s', $user_data['date']);
                }
            }

            if (!$user_id  && $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name=peers")->fetch()) {
                $user_data = $db->query("SELECT * FROM peers WHERE id =0 LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
                if ($user_data) {
                    $user_id = $user_data['hash'];
                    $register_at = date('Y-m-d H:i:s', $user_data['last_update_on']);
                }
            }
            $account = [
                'dc_id' => $dc_id,
                'server_address' => $server_address,
                'port' => $port,
                'auth_key' => $auth_key,
                'user_id' => $user_id,
                'register_at' => $register_at
            ];
            $this->accounts[] = new TelegramAccount($account);
        } catch (\PDOException $e) {
            // 捕获PDO异常
            throw new ParserException("解析session文件失败: " . $e->getMessage());
        } catch (ParserException $e) {
            // 重新抛出解析异常
            throw $e;
        } catch (\Exception $e) {
            // 将其他异常转换为解析异常
            throw new ParserException("解析session文件失败: " . $e->getMessage());
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
