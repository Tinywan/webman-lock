# based on lua script redis lock for webman plugin

[![Latest Stable Version](http://poser.pugx.org/tinywan/webman-lock/v)](https://packagist.org/packages/tinywan/webman-lock) 
[![Total Downloads](http://poser.pugx.org/tinywan/webman-lock/downloads)](https://packagist.org/packages/tinywan/webman-lock) 
[![Latest Unstable Version](http://poser.pugx.org/tinywan/webman-lock/v/unstable)](https://packagist.org/packages/tinywan/webman-lock) 
[![License](http://poser.pugx.org/tinywan/webman-lock/license)](https://packagist.org/packages/tinywan/webman-lock) 
[![PHP Version Require](http://poser.pugx.org/tinywan/webman-lock/require/php)](https://packagist.org/packages/tinywan/webman-lock)

## 安装

```shell
composer require tinywan/webman-lock
```

## 使用

```php
// 锁名称
$lockName = md5((string) time());
// 锁标识
$lockId = \Tinywan\Lock\RedisLock::acquire($lockName);
if (!$lockId) {
    return '未获得锁标识，请稍后再试';
}

// 业务处理
// 释放锁
\Tinywan\Lock\RedisLock::release($lockName, $lockId);
```

## Other

```php
vendor/bin/phpstan analyse src

vendor/bin/php-cs-fixer fix src
```

