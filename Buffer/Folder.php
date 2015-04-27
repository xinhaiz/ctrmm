<?php

namespace Buffer;

final class Folder {
    
    protected $_dir    = null;
    protected $_handle = null;

    public function __construct($dir = null) {
        !empty($dir) && $this->setDir($dir);
    }
    
    /**
     * 获取文件夹目录句柄
     * 
     * @return \Directory
     * @throws \Exception
     */
    public function getHandle() { 
        if(!$this->_handle instanceof \Directory) {
            throw new \Exception('invalid directory instance');
        }
       
        return $this->_handle;
    }
    
    /**
     * 设置操作目录
     * 
     * @param string $dir
     * @return \Lib\Filev
     */
    public function setDir($dir) {
        if(is_dir($dir)) {
            $this->_dir = (string)$dir;
        }

        return $this;
    }
    
    /**
     * 打开目录
     */
    public function open() {
        $this->_handle = (is_dir($this->_dir) ? dir($this->_dir) : null);
    }
    
    /**
     * 关闭目录
     */
    public function close() {
        $this->getHandle()->close();
    }
    
    /**
     * 倒回目录句柄
     */
    public function rewind() {
        $this->getHandle()->rewind();
    }
    
    /**
     * 从目录句柄中读取条目
     * 
     * @return string
     */
    public function read() {
        return $this->getHandle()->read();
    }

    /**
     * 获取文件
     * 
     * @param string $ext
     * @return array
     */
    public function files($ext = '*') {
        $files = array();
        $felen = strlen($ext);
        $path  = trim(rtrim($this->_dir, DS)) . DS;
        
        while(($name = $this->read()) !== false) {
            if($name === '.' || $name === '..'){
                continue;
            }
            
            $fileName = $path . $name;

            if(is_file($fileName) && ($ext === '*' || strrpos($name, $ext) === (strlen($name) - $felen))) {
                $files[] = $fileName;
            }
        }

        return $files;
    }
    
    /**
     * 获取文件夹
     * 
     * @param string $ext
     */
    public function dirs() {
        $dirs = array();
        $path = trim(rtrim($this->_dir, DS)) . DS;
        
        while(($name = $this->read()) !== false) {
            $dirPath = $path . $name;
            
            if($name !== '.' && $name !== '..' && is_dir($dirPath)){
                $dirs[] = $dirPath;
            }
        }
        
        return $dirs;
    }
}