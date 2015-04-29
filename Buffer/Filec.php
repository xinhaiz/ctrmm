<?php

namespace Buffer;

final class Filec {
    
    protected $_handle = null;
    protected $_size   = 0;
    protected $_mode = 'r';

    public function __construct($filePath, $mode = 'r') {
        if(!file_exists($filePath)) {
            throw new \Exception('invalid file path');
        }
       
        try {
            $this->_handle = fopen($filePath, $mode);
            $this->_size   = filesize($filePath);
        } catch (\Exception $e) {
            throw new \Exception('file open failed');
        }
        
        $this->_mode = $mode;
    }
    
    /**
     * 获取文件指针资源
     * 
     * @return resource
     * @throws \Exception
     */
    public function getHandle() {
        if(!is_resource($this->_handle)) {
            throw new \Exception('invalid file resource');
        }
        
        return $this->_handle;
    }
    
    /**
     * 当前文件大小
     * 
     * @return int
     */
    public function size() {
        return $this->_size;
    }
    
    /**
     * 读取所有字节
     * 
     * @param int $length
     * @return string
     */
    public function readAll() {
        return fread($this->getHandle(), $this->_size);
    }

    /**
     * 读取N字节
     * 
     * @param int $length
     * @return string
     */
    public function read($length = 1) {
        return fread($this->getHandle(), $length);
    }
    
    /**
     * 读取一行
     * 
     * @param int $maxSize
     * @return string
     */
    public function readLine($maxSize = 256) {
        return fgets($this->getHandle(), $maxSize);
    }
    
    /**
     * 文件指针向前移动N字节
     * 
     * @param int $offset
     * @return boolean
     */
    public function next($offset = 1) {
        return (fseek($this->getHandle(), abs($offset), SEEK_CUR) === 0 ? true : false);
    }

    /**
     * 文件指针定位在 $offset 位置
     * 
     * @param int $offset
     * @return boolean
     */
    public function position($offset) {
        return (fseek($this->getHandle(), abs($offset), SEEK_SET) === 0 ? true : false);
    }

    /**
     * 文件指针向后移动N字节
     * 
     * @param int $offset
     * @return boolean
     */
    public function prev($offset = 1) {
        return (fseek($this->getHandle(), 0 - abs($offset), SEEK_CUR) === 0 ? true : false);
    }
    
    /**
     * 文件指针重定向到起始
     * 
     * @param int $offset
     * @return boolean
     */
    public function reset() {
        return rewind($this->getHandle());
    }
    
    /**
     * 将缓冲内容输出到文件
     * 
     * @return boolean
     */
    public function flush() {
        return fflush($this->getHandle());
    }
    
    /**
     * 把 string 的内容写入文件指针 handle 处。
     * 
     * @param string $string
     * @return boolean
     */
    public function write($string) {
        return fwrite($this->getHandle(), $string);
    }
    
    /**
     * 文件指针重定向到结束位置
     * 
     * @param int $offset
     * @return boolean
     */
    public function end() {
        return ((fseek($this->getHandle(), 0, SEEK_END) === 0) ? true : false);
    }
    
    /**
     * 获取当前指针位置
     * 
     * @return int
     */
    public function current() {
        return ftell($this->getHandle());
    }
    
    /**
     * 检查指针是否到达结束位置 
     * 
     * @return bool
     */
    public function eof() {
        return ($this->size() <= $this->current() ? true : false);
    }

    /**
     * 关闭文件指针资源
     */
    public function close() {
        return fclose($this->getHandle());
    }
}