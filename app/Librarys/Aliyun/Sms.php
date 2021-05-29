<?php
namespace App\Librarys\Aliyun;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Constants\CacheKey\SmsCacheKey;

class Sms
{

    const  SEND_EVERY_MAX = 10; // 短信最多发送数量
    /***
     * 发送验证码
     * @params $to 手机号
     * @params $code 验证码
     * @return bool
     */
    public static function sendCaptcha($to, $code)
    {
        AlibabaCloud::accessKeyClient(config('sms.accesskeyId'), config('sms.accesskeySecret'))
        ->regionId('cn-hangzhou')
        ->asDefaultClient();

        try {
            $totalKey = SmsCacheKey::getTotalKey($to);
            if (Redis::get($totalKey) > self::SEND_EVERY_MAX) {
                Log::error('send sms error: 此手机发送短信超出最大限制', [$to, $code]);
                return false;
            }

            Log::info('send sms code:', [$to, $code]);

            AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'PhoneNumbers' => $to,
                        'TemplateCode' => config('sms.templateCode'),
                        'SignName' => config('sms.signname'),
                        'TemplateParam' => json_encode(['code' => $code]),
                    ],
                ])
                ->request();
            
            Redis::incr($totalKey);
            Redis::expire($totalKey, 86400);

            return true;
        } catch (ClientException $e) {
            Log::info($e->getErrorMessage() . PHP_EOL);
            return false;
        } catch (ServerException $e) {
            Log::info($e->getErrorMessage() . PHP_EOL);
            return false;
        }
    }
}
