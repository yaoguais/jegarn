<?php

namespace minions\base;

class UploadFile {
    static private $_files;

    private $_name;
    private $_tempName;
    private $_type;
    private $_size;
    private $_error;

    public static function getInstanceByName($name) {
        if (null === self::$_files)
            self::prefetchFiles();
        return isset(self::$_files[$name]) && self::$_files[$name]->getError() != UPLOAD_ERR_NO_FILE ? self::$_files[$name] : null;
    }

    public static function getInstancesByName($name) {
        if (null === self::$_files)
            self::prefetchFiles();

        $len = strlen($name);
        $results = [];
        foreach (array_keys(self::$_files) as $key)
            if (0 === strncmp($key, $name . '[', $len + 1) && self::$_files[$key]->getError() != UPLOAD_ERR_NO_FILE)
                $results[] = self::$_files[$key];
        return $results;
    }

    public static function reset() {
        self::$_files = null;
    }

    protected static function prefetchFiles() {
        self::$_files = [];
        if (!isset($_FILES) || !is_array($_FILES))
            return;

        foreach ($_FILES as $class => $info)
            self::collectFilesRecursive($class, $info['name'], $info['tmp_name'], $info['type'], $info['size'], $info['error']);
    }

    protected static function collectFilesRecursive($key, $names, $tmp_names, $types, $sizes, $errors) {
        if (is_array($names)) {
            foreach ($names as $item => $name)
                self::collectFilesRecursive($key . '[' . $item . ']', $names[$item], $tmp_names[$item], $types[$item], $sizes[$item], $errors[$item]);
        } else{
            self::$_files[$key] = new self($names, $tmp_names, $types, $sizes, $errors);
        }
    }

    public function __construct($name, $tempName, $type, $size, $error) {
        $this->_name = $name;
        $this->_tempName = $tempName;
        $this->_type = $type;
        $this->_size = $size;
        $this->_error = $error;
    }

    public function __toString() {
        return $this->_name;
    }

    public function saveAs($file, $deleteTempFile = true) {
        if ($this->_error == UPLOAD_ERR_OK) {
            if ($deleteTempFile)
                return move_uploaded_file($this->_tempName, $file);
            elseif (is_uploaded_file($this->_tempName))
                return copy($this->_tempName, $file);
            else
                return false;
        } else
            return false;
    }

    public function getName() {
        return $this->_name;
    }

    public function getTempName() {
        return $this->_tempName;
    }

    public function getType() {
        return $this->_type;
    }

    public function getSize() {
        return $this->_size;
    }

    public function getError() {
        return $this->_error;
    }

    public function getHasError() {
        return $this->_error != UPLOAD_ERR_OK;
    }

    public function getExtensionName() {
        return pathinfo($this->_name, PATHINFO_EXTENSION);
    }
}
