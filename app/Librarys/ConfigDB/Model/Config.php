<?php
/**
 * Created by PhpStorm.
 * User: tan wei
 * Date: 2018/6/20
 * Time: 12:45
 */

namespace App\Librarys\ConfigDB\Model;

use App\Models\BaseModel;

/**
 * Class Config
 * @package Lxk\Base\Models
 *
 * @property string  $value
 * @property string  $key
 * @property integer $json
 */
class Config extends BaseModel
{
    const JSON_VALUE_FALSE = 1;
    const JSON_VALUE_TRUE = 2;

    protected $table = 'configs';

    public function getValueAttribute($value)
    {
        if($this->json == static::JSON_VALUE_TRUE){
            try{
                return \GuzzleHttp\json_decode($value, true);
            }catch (\Exception $e){
                return [];
            }
        }

        return $value;
    }
}