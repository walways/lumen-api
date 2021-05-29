<?php
/**
 * Created by PhpStorm.
 * User: tan wei
 * Date: 2018/7/25
 * Time: 17:07
 */

namespace App\Librarys\ConfigDB\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Trait HasCache.
 */
trait HasCache
{
    protected function getTag()
    {
        return get_called_class();
    }

    protected function getCacheKey($key)
    {
        return $key;
    }

    protected function cacheSet($key, $value, $min = 10080)
    {
        return $this->getCacheDriver()->put($this->getCacheKey($key), $value, $min);
    }

    protected function cacheGet($key, $def = null)
    {
        return $this->getCacheDriver()->get($this->getCacheKey($key), $def);
    }

    protected function cacheForever($key, $value)
    {
        return $this->getCacheDriver()->forever($this->getCacheKey($key), $value);
    }

    protected function cacheForget($key)
    {
        return $this->getCacheDriver()->forget($this->getCacheKey($key));
    }

    protected function cacheFlush()
    {
        return $this->getCacheDriver()->flush();
    }

    /**
     * @return mixed
     */
    protected function getCacheDriver()
    {
        return Cache::tags($this->getTag());
    }
}
