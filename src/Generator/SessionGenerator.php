<?php

namespace Telegram\Account\Parser\Generator;

use Telegram\Account\Parser\Exceptions\GeneratorException;
use Telegram\Account\Parser\Types\TelegramAccount;
use PDO;

/**
 * Telegram session 文件生成器
 * 
 * 用于生成Telegram session文件
 */
class SessionGenerator
{
    /**
     * 生成session文件
     * 
     * @param TelegramAccount $account 账户
     * @param string $outputPath 输出路径
     * @return bool 是否成功
     */
    public static function generate(TelegramAccount $account, string $outputPath): bool
    {
        $db = new PDO('sqlite:' . $outputPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $db->beginTransaction();
        
            // 创建表：sessions
            $db->exec("
                CREATE TABLE IF NOT EXISTS sessions (
                    dc_id integer primary key,
                    server_address text,
                    port integer,
                    auth_key blob,
                    takeout_id integer
                );
            ");

            // 创建表：entities
            $db->exec("
                CREATE TABLE IF NOT EXISTS entities (
                    id integer primary key,
                    hash integer not null,
                    username text,
                    phone integer,
                    name text,
                    date integer
                );
            ");

            // 创建表：version
            $db->exec("
                CREATE TABLE IF NOT EXISTS version (
                    version integer primary key
                );
            ");
            
            // 创建表：sent_files
            $db->exec("
                CREATE TABLE IF NOT EXISTS sent_files (
                    md5_digest blob,
                    file_size integer,
                    type integer,
                    id integer,
                    hash integer,
                    primary key(md5_digest, file_size, type)
                );
            ");

            // 创建表：update_state
            $db->exec("
                CREATE TABLE IF NOT EXISTS update_state (
                    id integer PRIMARY KEY,
                    pts integer,
                    qts integer,
                    date integer,
                    seq integer
                );
            ");
            
            // 插入 sessions 数据
            $stmt = $db->prepare("
                INSERT INTO sessions (dc_id, server_address, port, auth_key)
                VALUES (:dc_id, :server_address, :port, :auth_key)
            ");

            $stmt->bindValue(':dc_id', $account->get_dc_id(), PDO::PARAM_INT);
            $stmt->bindValue(':server_address', $account->get_server_address(), PDO::PARAM_STR);
            $stmt->bindValue(':port', $account->get_port(), PDO::PARAM_INT);
            $stmt->bindValue(':auth_key', $account->get_auth_key(), PDO::PARAM_LOB); // 👈 指定二进制类型
            $stmt->execute();
            // 插入 version 数据
            $db->exec("INSERT INTO version (version) VALUES (7);");
        
            // 提交事务
            $db->commit();
            return true;
        } catch (\PDOException $e) {
            // 回滚事务
            $db->rollBack();
            $db =null;
            throw $e; // 或记录错误日志
        } catch (GeneratorException $e) {
            // 回滚事务
            $db->rollBack();
            $db =null;
            throw $e; // 或记录错误日志
        } catch (\Exception $e) {
            // 回滚事务
            $db->rollBack();
            $db =null;
            throw new GeneratorException("生成session文件失败: " . $e->getMessage());
        } catch (\Exception $e) {
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
            throw new GeneratorException("生成session文件失败: " . $e->getMessage());
        }
    }
}
