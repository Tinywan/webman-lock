# based on lua script redis lock for webman plugin

## 安装

```shell
composer require tinywan/webman-lock
```

## 使用

```php
$lockName = md5(time());
$lockId = \Tinywan\Lock\RedisLock::createLock($lockName);
if (!$lockId) {
    return '未获得锁标识，请稍后再试';
}

try {
    // 业务处理
} finally {
    \Tinywan\Lock\RedisLock::release($lockName, $lockId);
}
return 'success';
```

## Other

```php
vendor/bin/phpstan analyse src

vendor/bin/php-cs-fixer fix src
```

