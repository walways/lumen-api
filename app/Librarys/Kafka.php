<?php

namespace App\Librarys;

use App\Exceptions\CustomException;

class Kafka
{
    private static $conf;
    private static $producer;
    private static $topics = [];

    /**
     * 获取配置
     */
    public static function getConf()
    {
        $conf = new \RdKafka\Conf();
        // $conf->set('log_level', (string) LOG_DEBUG);
        // $conf->set('debug', 'all');

        if (!empty(env('KAFKA_USER'))) {
            $conf->set('security.protocol', 'sasl_plaintext'); // sasl_plaintext SASL_SSL
            $conf->set('sasl.mechanisms', 'PLAIN');
            $conf->set('sasl.username', env('KAFKA_USER'));
            $conf->set('sasl.password', env('KAFKA_PASSWORD'));
        }
        if (self::$conf != null) {
            return self::$conf;
        }
        self::$conf = $conf;
        return $conf;
    }

    /**
     * 实例化主题
     */
    public static function getProducer()
    {
        $producer = new \RdKafka\Producer(self::getConf());
        $producer->addBrokers(env('KAFKA_ADDRESS'));
        if (self::$producer != null) {
            return self::$producer;
        }
        self::$producer = $producer;
        return $producer;
    }

    /**
     * 实例化主题
     */
    public static function getTopic($topic_name = '')
    {
        if (empty($topic_name)) $topic_name = env('KAFKA_TOPIC');
        
        $producer = self::getProducer();
        $topic = $producer->newTopic($topic_name);
        if (isset(self::$topics[$topic_name])) {
            return self::$topics[$topic_name];
        }
        self::$topics[$topic_name] = $topic;
        return $topic;
    }

    /**
     * 生产消息
     */
    public static function createMessage($message, $topic_name = '')
    {
        if (is_array($message)) $message = json_encode($message, 320);
        $topic = self::getTopic($topic_name);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    }
}
