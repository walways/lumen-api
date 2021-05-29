<?php
/**
 * Created by PhpStorm.
 * User: tan wei
 * Date: 2018/7/24
 * Time: 19:34
 */

namespace App\Librarys\ConfigDB;

use App\Librarys\ConfigDB\Exception\ConfigSaveException;
use App\Librarys\ConfigDB\Traits\HasCache;
use ArrayAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Librarys\ConfigDB\Model\Config as ConfigModel;


/**
 * Class ConfigDB
 *
 * @package Lxk\Base\ConfigDB
 */
class ConfigDB implements ArrayAccess, ConfigDBContract
{
    use HasCache;

    /**
     * @var Collection
     */
    protected $configs;

    /**
     * ConfigRepository constructor.
     */
    public function __construct()
    {
        $this->configs = new Collection();
    }

    public function get($key, $default = null)
    {
        if (!$key) {
            return $default;
        }

        if ($value = $this->cacheGet($key, false)) {
            return $value;
        }

        $config = $this->getConfigModel($key);

        if (!$config) {
            return $default;
        }

        return $config->value;
    }

    public function getWithoutCache($key, $default = null)
    {
        if (!$key) {
            return $default;
        }

        $config = $this->getConfigModel($key);

        if (!$config) {
            return $default;
        }

        return $config->value;
    }

    public function has($key)
    {
        if (!$key) {
            return false;
        }

        $config = $this->getConfigModel($key);

        if (!$config) {
            return false;
        }

        return true;
    }

    /**
     * @param $key
     * @param null $value
     * @throws ConfigSaveException
     *
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $this->_set($key, $value);
        }
    }

    /**
     * @param $key
     * @param $value
     * @throws ConfigSaveException
     *
     * @return void
     */
    protected function _set($key, $value)
    {
        try {
            DB::transaction(function () use ($key, $value) {
                /** @var ConfigModel $config */
                $config = ConfigModel::query()->firstOrCreate(['key' => $key]);
                $config->key = $key;
                if (is_array($value)) {
                    $config->value = json_encode($value);
                    $config->json = ConfigModel::JSON_VALUE_TRUE;
                } else {
                    $config->value = $value;
                    $config->json = ConfigModel::JSON_VALUE_FALSE;
                }

                $config->save();
                $this->forget($key);
            });
        } catch (\Exception $e) {
            Log::error('config_db_save_error', [
                'key' => $key,
                'value' => $value,
                'msg' => $e->getMessage()
            ]);

            throw new ConfigSaveException('save config failed, msg: ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * forget one key
     * @param $key
     */
    public function forget($key)
    {
        if($this->configs->has($key)){
            $this->configs->forget($key);
        }
        $this->cacheForget($key);
    }

    /**
     * clear all cache
     */
    public function flush()
    {
        $this->configs = new Collection();
        $this->cacheFlush();
    }

    /**
     * get config model.
     * @param $key
     * @return mixed
     */
    protected function getConfigModel($key)
    {
        if (!$this->configs->has($key)) {
            $this->configs[$key] = ConfigModel::query()->where('key', $key)->first();

            if($this->configs[$key]){
                //set cache
                $this->cacheForever($key, $this->configs[$key]->value);
            }
        }

        return $this->configs[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws ConfigSaveException
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     * @throws ConfigSaveException
     */
    public function offsetUnset($offset)
    {
        if ($this->has($offset)) {
            $this->set($offset, null);
        }
    }
}