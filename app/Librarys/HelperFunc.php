<?php

namespace App\Librarys;

use Godruoyi\Snowflake\Snowflake;
use Godruoyi\Snowflake\LaravelSequenceResolver;
use Hashids\Hashids;
use Illuminate\Support\Arr;

class HelperFunc
{
    public static function makeTree($allLevelData, $uniqueKeyName = 'id')
    {
        $items = [];
        foreach ($allLevelData as $value) {
            $value instanceof \StdClass && $value = (array)$value;
            $items[$value[$uniqueKeyName]] = $value;
        }

        $tree = [];
        foreach ($items as $key => $value) {
            if (isset($items[$value['pid']]) && !empty($items[$value['pid']])) {
                $items[$value['pid']]['children'][] = &$items[$key];
            } else {
                $tree[] = &$items[$key];
            }
        }

        return $tree;
    }

    /**
     * 同批次子订单生成批量流水号
     * @param int $num
     * @return array
     */
    public static function batchGetSequenceOrder($prefix, $num = 1)
    {
        $order_no = $prefix . date('YmdHis')
            . substr(microtime(), 2, 5)
            . sprintf('%04d', rand(1, 9999));

        $return = [];
        for ($i = 1; $i <= $num; $i++) {
            $return[] = $order_no . $i;
        }

        return $return;
    }

    /**
     * 生成唯一ID
     * @param int $workerId 业务ID 最大31
     */
    public static function generateSequenceId($workerId = 1)
    {
        $datacenterId = env('DATA_CENTER_ID');
        if(empty($datacenterId)) $datacenterId = 1;
        $snowflake = (new \Godruoyi\Snowflake\Snowflake($datacenterId, $workerId))
        ->setStartTimeStamp(strtotime('2021-04-01') * 1000)
        ->setSequenceResolver(new \Godruoyi\Snowflake\LaravelSequenceResolver(app('cache')->store()));
        return $snowflake->id();
    }

    /**
     * 加密解密用户邀请码,
     * @param unknown $string
     * @param string $action encode|decode
     * @return string
     */
    function endecodeUserId($string, $action = 'encode')
    {
        $startLen = 13;
        $endLen = 8;

        $coderes = '';
        #TOD 暂设定uid字符长度最大到9
        if ($action == 'encode') {
            $uidlen = strlen($string);
            $salt = 'yourself_code';
            $codestr = $string . $salt;
            $encodestr = hash('md4', $codestr);
            $coderes = $uidlen . substr($encodestr, 5, $startLen - $uidlen) . $string . substr($encodestr, -12, $endLen);
            $coderes = strtoupper($coderes);
        } elseif ($action == 'decode') {
            $strlen = strlen($string);
            $uidlen = $string[0];
            $coderes = substr($string, $startLen - $uidlen + 1, $uidlen);
        }
        return $coderes;
    }

    /**
     *
     *加密id
     *Author qpf
     *
     * @param array|int $id
     * @param string $salt
     * @return string
     */
    public static function encodeId($id, $salt = "") : string
    {
        $hashids = new Hashids($salt);
        return $hashids->encode($id);

    }

    /**
     *
     *解密id
     *Author qpf
     *
     * @param string $string
     * @param string $salt
     * @return array
     */
    public static function decodeId($string, $salt = "") : array
    {
        $hashids = new Hashids($salt);
        return $hashids->decode($string);

    }


    /**
     *
     *周期日期
     *Author qpf
     *
     * @param $startDate
     * @param $endDate
     * @param $format
     * @return array
     */
    public static function periodDate($startDate, $endDate,$format = 'Y-m-d'){
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        $arr = array();
        while ($startTime <= $endTime){
            $arr[] = date($format, $startTime);
            $startTime = strtotime('+1 day', $startTime);
        }
        return $arr;
    }

    /**
     * 日期相差天数
     */
    public static function dateDiffDays($startDate, $endDate){
        $datetime_start = new \DateTime($startDate);
        $datetime_end = new \DateTime($endDate);
        $days = $datetime_start->diff($datetime_end)->days;
        return $days;
    }

    //递归去除空元素
    public static function array_remove_empty($arr){
        $narr = array();
        foreach($arr as $key => $val){
            if (is_array($val)){
                $val = self::array_remove_empty($val);
                // does the result array contain anything?
                if (count($val)!=0){
                    // yes :-)
                    $narr[$key] = $val;
                }
            }
            else {
                if (!is_null($val)){
                    $narr[$key] = $val;
                }
            }
        }
        unset($arr);
        return $narr;
    }

    /**
     * Example:
     * AffiliatePedding ==> Affiliate Pending
     *
     * @param string $str
     * @return string
     */
    public static function CamelWordSplit($str) {
        if (! isset ( $str ) || empty ( $str )) {
            return '';
        }

        $len = strlen ( $str );
        $ret = '';
        for($i = 0; $i < $len; $i ++) {
            if ($i > 0 && $str [$i] >= 'A' && $str [$i] <= 'Z') {
                $ret .= ' ' . $str [$i];
            } else {
                $ret .= $str [$i];
            }
        }
        return $ret;
    }
}
