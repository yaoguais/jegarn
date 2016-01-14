<?php

namespace minions\util;

abstract class FileUtil {

    public static function createDir($path, $mode = 0777, $touch = true, $deep = 3) {

        if ($path && !file_exists($path)) {
            $dirs = explode(DIRECTORY_SEPARATOR, trim($path,DIRECTORY_SEPARATOR));
            $count = count($dirs);
            $lastDeep = $count - $deep;
            $path = $path[0] == DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '';
            for ($i = 0; $i < $count; ++$i) {
                $path .= $dirs[$i] . DIRECTORY_SEPARATOR;
                if ($i >= $lastDeep && !file_exists($path)) {
                    @mkdir($path, $mode);
                    if ($touch) {
                        @touch($path . 'index.html');
                    }
                }
            }
        }
        return true;
    }
}