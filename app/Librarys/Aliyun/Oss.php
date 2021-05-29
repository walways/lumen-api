<?php

namespace App\Librarys\Aliyun;

use OSS\OssClient;
use OSS\Core\OssUtil;
use OSS\Core\OssException;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;

class Oss
{

    protected static $END_POINT;
    protected static $APP_KEY;
    protected static $APP_SECRET;
    protected static $BUCKET;

    /**
     * Get an OSSClient instance according to config.
     *
     * @return OssClient An OssClient instance
     */
    public static function getOssClient($end_point = null, $app_key = null, $app_secret = null, $bucket = null)
    {

        self::$END_POINT = $end_point ? $end_point : config('oss.end_point');
        self::$APP_KEY = $app_key ? $app_key : config('oss.app_key');
        self::$APP_SECRET = $app_secret ? $app_secret : config('oss.app_secret');
        try {
            $ossClient = new OssClient(self::$APP_KEY, self::$APP_SECRET, self::$END_POINT);
        } catch (OssException $e) {
            throw new CustomException($e->getMessage());
        }
        return $ossClient;
    }

    /**
     * Get an Oss Bucket
     * @param null $bucket
     *
     * @return \Illuminate\Config\Repository|mixed|null
     */
    public static function getBucketName($bucket = null)
    {
        self::$BUCKET = $bucket ? $bucket : config('oss.bucket');
        return self::$BUCKET;
    }

    /**
     * A tool function which creates a bucket and exists the process if there are exceptions
     */
    public static function createBucket()
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) {
            exit(1);
        }
        $bucket = self::getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            $ossClient->createBucket($bucket, $acl);
        } catch (OssException $e) {
            $message = $e->getMessage();
            if (\OSS\Core\OssUtil::startsWith($message, 'http status: 403')) {
                echo "Please Check your AccessKeyId and AccessKeySecret" . "\n";
                exit(0);
            } elseif (strpos($message, "BucketAlreadyExists") !== false) {
                echo "Bucket already exists. Please check whether the bucket belongs to you, or it was visited with correct endpoint. " . "\n";
                exit(0);
            }
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
    }

    public static function println($message)
    {
        if (!empty($message)) {
            echo strval($message) . "\n";
        }
    }

    /**
     * 分片上传本地文件
     */
    public static function uploadPartLocal($object, $uploadFile)
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) {
            exit(1);
        }

        $bucket = self::getBucketName();
        $options = array(
            OssClient::OSS_CHECK_MD5 => true,
            OssClient::OSS_PART_SIZE => 1,
        );

        $res = $ossClient->multiuploadFile($bucket, $object, $uploadFile, $options);
        return $res;
    }

    /**
     * 分片上传
     */
    public static function uploadPart($object, $uploadFile)
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) {
            exit(1);
        }

        $bucket = self::getBucketName();

        /**
         *  步骤1：初始化一个分片上传事件，获取uploadId。
         */

        try {
            //返回uploadId。uploadId是分片上传事件的唯一标识，您可以根据uploadId发起相关的操作，如取消分片上传、查询分片上传等。
            $uploadId = $ossClient->initiateMultipartUpload($bucket, $object);
        } catch (OssException $e) {
            Log::error(__FUNCTION__ . ": initiateMultipartUpload FAILED\n");
            Log::error($e->getMessage() . "\n");
            return;
        }

        /*
        * 步骤2：上传分片。
        */

        $partSize = 2 * 1024 * 1024;
        $uploadFileSize = filesize($uploadFile);
        $pieces = $ossClient->generateMultiuploadParts($uploadFileSize, $partSize);
        $responseUploadPart = array();
        $uploadPosition = 0;
        $isCheckMd5 = true;
        foreach ($pieces as $i => $piece) {
            $fromPos = $uploadPosition + (int)$piece[$ossClient::OSS_SEEK_TO];
            $toPos = (int)$piece[$ossClient::OSS_LENGTH] + $fromPos - 1;
            $upOptions = array(
                // 上传文件。
                $ossClient::OSS_FILE_UPLOAD => $uploadFile,
                // 设置分片号。
                $ossClient::OSS_PART_NUM => ($i + 1),
                // 指定分片上传起始位置。
                $ossClient::OSS_SEEK_TO => $fromPos,
                // 指定文件长度。
                $ossClient::OSS_LENGTH => $toPos - $fromPos + 1,
                // 是否开启MD5校验，true为开启。
                $ossClient::OSS_CHECK_MD5 => $isCheckMd5,
            );
            // 开启MD5校验。
            if ($isCheckMd5) {
                $contentMd5 = OssUtil::getMd5SumForFile($uploadFile, $fromPos, $toPos);
                $upOptions[$ossClient::OSS_CONTENT_MD5] = $contentMd5;
            }
            try {
                // 上传分片。
                $responseUploadPart[] = $ossClient->uploadPart($bucket, $object, $uploadId, $upOptions);
            } catch (OssException $e) {
                Log::error(__FUNCTION__ . ": initiateMultipartUpload, uploadPart - part#{$i} FAILED\n");
                Log::error($e->getMessage() . "\n");
                return;
            }
            // printf(__FUNCTION__ . ": initiateMultipartUpload, uploadPart - part#{$i} OK\n");
        }
        // $uploadParts是由每个分片的ETag和分片号（PartNumber）组成的数组。
        $uploadParts = array();
        foreach ($responseUploadPart as $i => $eTag) {
            $uploadParts[] = array(
                'PartNumber' => ($i + 1),
                'ETag' => $eTag,
            );
        }

        /**
         * 步骤3：完成上传。
         */

        try {
            // 执行completeMultipartUpload操作时，需要提供所有有效的$uploadParts。OSS收到提交的$uploadParts后，会逐一验证每个分片的有效性。当所有的数据分片验证通过后，OSS将把这些分片组合成一个完整的文件。
            $res = $ossClient->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
        } catch (OssException $e) {
            Log::error(__FUNCTION__ . ": completeMultipartUpload FAILED\n");
            Log::error($e->getMessage() . "\n");
            return;
        }

        return $res;
    }
}
