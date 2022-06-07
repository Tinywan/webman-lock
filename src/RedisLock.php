<?php
/**
 * @desc RedisLock
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/6/7 16:43
 */
declare(strict_types=1);

namespace Tinywan\Lock;

use support\Redis;

class RedisLock
{
    public const DISTRIBUTED_CONCURRENT_LOCK = 'DISTRIBUTED_CONCURRENT_LOCK:';

    public const DISTRIBUTED_CREATE_SCRIPT_SHA = 'DISTRIBUTED_CREATE_SCRIPT_SHA:';

    public const DISTRIBUTED_RELEASE_SCRIPT_SHA = 'DISTRIBUTED_RELEASE_SCRIPT_SHA:';

    /**
     * @desc: 获取锁
     * @param string $lock_name 锁名称
     * @param int $acquire_time 获取锁时间
     * @param int $lock_timeout 锁超时时间
     * @return bool|string
     */
    public static function acquire(string $lock_name, int $acquire_time = 3, int $lock_timeout = 20)
    {
        $identifier = md5($_SERVER['REQUEST_TIME'] . mt_rand(1, 10000000));
        $lockName = self::DISTRIBUTED_CONCURRENT_LOCK . $lock_name;
        $lockTimeout = intval(ceil($lock_timeout));
        $endTime = time() + $acquire_time;
        $scriptSha = Redis::get(self::DISTRIBUTED_CREATE_SCRIPT_SHA);
        if (!$scriptSha) {
            $script = <<<luascript
            local result = redis.call('setnx',KEYS[1],ARGV[1]);
            if result == 1 then
                return redis.call('expire',KEYS[1],ARGV[2])
            elseif redis.call('ttl',KEYS[1]) == -1 then
                return redis.call('expire',KEYS[1],ARGV[2]) -- 续租（renew）
            else
                return 0
            end
luascript;
            $scriptSha = Redis::script('load', $script);
            Redis::set(self::DISTRIBUTED_CREATE_SCRIPT_SHA, $scriptSha);
        }
        while (time() < $endTime) {
            $result = Redis::rawCommand('evalsha', $scriptSha, 1, $lockName, $identifier, $lockTimeout);
            if ($result == '1') {
                return $identifier;
            }
            usleep(100000);
        }
        return false;
    }

    /**
     * @desc: 释放锁
     * @param string $lock_name
     * @param string $identifier
     * @return bool
     */
    public static function release(string $lock_name, string $identifier): bool
    {
        $lockName = self::DISTRIBUTED_CONCURRENT_LOCK . $lock_name;
        $scriptSha = Redis::get(self::DISTRIBUTED_RELEASE_SCRIPT_SHA);
        if (!$scriptSha) {
            $script = <<<luascript
            if redis.call("get",KEYS[1]) == ARGV[1] then
                return redis.call("del",KEYS[1]);
            else
                return 0
            end
luascript;
            $scriptSha = Redis::script('load', $script);
            Redis::set(self::DISTRIBUTED_RELEASE_SCRIPT_SHA, $scriptSha);
        }
        while (true) {
            $result = Redis::rawCommand('evalsha', $scriptSha, 1, $lockName, $identifier);
            if ($result == 1) {
                return true;
            }
        }
    }
}
