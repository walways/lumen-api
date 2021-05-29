<?php

namespace App\Support;

use Illuminate\Support\Facades\Redis;
use Predis\Response\ServerException;

class RedisLock
{
    /**
     * 等待间隔
     */
    const LOCK_DEFAULT_WAIT_SLEEP = 0.005;

    /**
     * 最小锁定时间，单位秒
     */
    const LOCK_MIN_TIME = 1;

    const LUA_SCRIPT_RELEASE_LOCK_SHA1 = '9bdce90060b1eb1923ba581ffba7051865f063d7';
    const LUA_SCRIPT_RELEASE_LOCK = '
        if (ARGV[1] == redis.call("GET", KEYS[1])) then
            return redis.call("DEL", KEYS[1]);
        end;
        return 0;
    ';

    const LUA_SCRIPT_UPDATE_LOCK_SHA1 = 'b414769872ec8518662b9f29e83fc691b0349f45';
    const LUA_SCRIPT_UPDATE_LOCK = '
        if (ARGV[1] == redis.call("GET", KEYS[1])) then
            return redis.call("PEXPIRE", KEYS[1], ARGV[2]);
        end;
        return 0;
    ';

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var bool
     */
    protected $isAcquired = false;

    /**
     * 是否自动释放锁
     * @var bool
     */
    protected $autoRelease = true;

    /**
     * RedisLock constructor.
     * @param $key
     */
    public function __construct($key, $autoRelease = true)
    {
        $this->key = (string)$key;
        $this->token = $this->createToken();
        $this->autoRelease = $autoRelease;
    }


    /**
     * @throws ServerException
     */
    public function __destruct()
    {
        if ($this->isAcquired && $this->autoRelease) {
            $this->release();
        }
    }

    /**
     * @return string
     */
    protected function createToken()
    {
        return uniqid();
    }

    /**
     * @param $lockTime
     * @param int $waitTime
     * @param null $sleep
     * @return bool
     */
    public function acquire($lockTime, $waitTime = 0, $sleep = null)
    {
        if ($lockTime < self::LOCK_MIN_TIME) {
            return false;
        }
        if ($this->isAcquired) {
            return false;
        }
        $time = microtime(true);
        $exitTime = $waitTime + $time;
        $sleep = ($sleep ?: self::LOCK_DEFAULT_WAIT_SLEEP) * 1000000;
        do {
            if (Redis::set($this->key, $this->token, 'EX', $lockTime, 'NX')) {
                $this->isAcquired = true;
                return true;
            }
            if ($waitTime) {
                usleep($sleep);
            }
        } while ($waitTime && microtime(true) < $exitTime);
        $this->isAcquired = false;
        return false;
    }

    /**
     * @return bool
     * @throws ServerException
     */
    public function release()
    {
        if (!$this->isAcquired) {
            return false;
        }

        try {
            $result = Redis::evalsha(self::LUA_SCRIPT_RELEASE_LOCK_SHA1, 1, $this->key, $this->token);
        } catch (ServerException $e) {
            if (0 === strpos($e->getMessage(), 'NOSCRIPT')) {
                $result = Redis::eval(self::LUA_SCRIPT_RELEASE_LOCK, 1, $this->key, $this->token);
            } else {
                throw $e;
            }
        }

        $this->isAcquired = false;
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @param $lockTime
     * @return bool
     * @throws ServerException
     */
    public function update($lockTime)
    {
        if ($lockTime < self::LOCK_MIN_TIME) {
            return false;
        }
        if (!$this->isAcquired) {
            return false;
        }

        try {
            $result = Redis::evalsha(self::LUA_SCRIPT_UPDATE_LOCK_SHA1, 1, $this->key, $this->token, $lockTime);
        } catch (ServerException $e) {
            if (0 === strpos($e->getMessage(), 'NOSCRIPT')) {
                $result = Redis::eval(self::LUA_SCRIPT_UPDATE_LOCK, 1, $this->key, $this->token, $lockTime);
            } else {
                throw $e;
            }
        }

        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isAcquired()
    {
        return $this->isAcquired;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        if (!$this->isAcquired) {
            return false;
        }
        $token = Redis::get($this->key);
        if ($token && $token === $this->token) {
            return true;
        }
        $this->isAcquired = false;
        return false;
    }

    /**
     * @return bool
     */
    public function isExists()
    {
        return Redis::get($this->key) ? true : false;
    }

    public function releaseWithoutThrowException()
    {
        try {
            return $this->release();
        }catch (ServerException $e){
            return false;
        }
    }
}