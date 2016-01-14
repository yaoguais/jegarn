<?php

namespace minions\upload;

use minions\base\Code;
use minions\util\FileUtil;

abstract class Uploader {

    const RULE_NONE = 0;
    const RULE_DATE = 1;

    const TYPE_ALL = 0;
    const TYPE_IMAGE = 1;
    const TYPE_ARCHIVE = 2;
    const TYPE_VOICE = 3;

    protected static $lastErrorCode;

    public static function saveFile($file, $category = null, $type = self::TYPE_ALL, $maxSize = 0, $rule = self::RULE_DATE){

        /* @var  UploadFile $file */
        if (!$file || !defined('UPLOAD_PATH')) {
            self::$lastErrorCode = Code::FAIL_UPLOAD_EMPTY_FILE;
            return null;
        }
        if($maxSize > 0 && $file->getSize() > $maxSize){
            self::$lastErrorCode = Code::FAIL_UPLOAD_FILE_SIZE;
            return null;
        }
        $extension = strtolower($file->getExtensionName());
        if(is_int($type)){
            switch($type){
                case self::TYPE_ALL: break;
                case self::TYPE_IMAGE:
                    if(!in_array($extension, ['jpg', 'jpeg', 'bmp', 'png', 'gif'])){
                        self::$lastErrorCode = Code::FAIL_UPLOAD_FILE_TYPE;
                        return null;
                    }
                break;
                case self::TYPE_ARCHIVE:
                    if(!in_array($extension, ['zip', 'rar', 'tgz', 'gz', 'bz2'])){
                        self::$lastErrorCode = Code::FAIL_UPLOAD_FILE_TYPE;
                        return null;
                    }
                break;
                case self::TYPE_VOICE:
                    if(!in_array($extension, ['wav', 'mp3', 'wma', 'au', 'aif'])){
                        self::$lastErrorCode = Code::FAIL_UPLOAD_FILE_TYPE;
                        return null;
                    }
                break;
            }
        }

        $root = rtrim(UPLOAD_PATH,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $path = ($category ? $category.DIRECTORY_SEPARATOR : '');
        if($rule == self::RULE_DATE){
            $path .= date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR.date('d').DIRECTORY_SEPARATOR;
            FileUtil::createDir($root.$path, 0777, true, 4);
        }else{
            FileUtil::createDir($root.$path, 0777, true, 1);
        }
        $path .= sprintf('%u%u', microtime(true) * 10000, mt_rand(0, 999999)).'.'.$extension;

        return $file->saveAs($root.$path) ? $path : null;
    }

    public static function getLastErrorCode(){

        return self::$lastErrorCode;
    }
}