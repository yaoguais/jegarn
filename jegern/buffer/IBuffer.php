<?php

namespace jegern\buffer;

interface IBuffer{
    /**
     * @param $size
     * @return object/null
     */
    public function init($size);
    /**
     * @param $size
     * @return bool
     */
    public function reSize($size);
    /**
     * @param $data
     * @return int
     */
    public function append(&$data);
    /**
     * @return bool
     */
    public function clear();
    /**
     * @param $offset
     * @param int $length
     * @return string/null
     */
    public function get($offset=0,$length=-1);
    /**
     *
     * @return bool
     */
    public function destroy();
}