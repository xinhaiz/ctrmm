<?php

namespace Buffer;

final class Char {
    
    protected $_nWord  = array();
    protected $_buff   = null;
    protected $_point  = 0;
    protected $_length = 0;
    protected $_encoding = 'UTF-8';

    public function __construct($buff, $encoding = null) {
        if(!empty($encoding)) {
            $this->_encoding = $encoding;
        }
        
        $this->_buff = (string)$buff;
        $this->_length = mb_strlen($this->_buff, $this->_encoding);
    }
    
    /**
     * @return string
     */
    public function __toString() {
        return $this->_buff;
    }
    
    /**
     * 长度大小
     * 
     * @return int
     */
    public function size() {
        return $this->_length;
    }

    /**
     * 读取N字字符长度
     * 
     * @param int $length
     * @return string
     */
    public function read($length = 1) {
        $point = $this->_point;
        $rSize = $this->size() - $point;
        $rLen  = $rSize > $length ? $length : $rSize;
        
        return mb_substr($this->_buff, $point, $rLen, $this->_encoding);
    }
    
    /**
     * 读取N字字符长度并向前移动指针位置(语法糖)
     * 
     * @param int $length
     * @return string
     */
    public function readMove($length = 1) {
        $read = $this->read($length);
        $this->next($length);
        
        return $read;
    }
    
    /**
     * 连续读取多个字符直到遇到 $symbol 或结束时停止
     * 
     * @param string $symbol
     * @return string
     */
    public function readMore($symbol = PHP_EOL) {
        $str = '';
        
        while ($this->eof() === false && ($char = $this->readMove()) !== $symbol) {
            $str .= $char;
        }
        
        return $str;
    }

    /**
     * 替换当前位置字符
     * 
     * @param string $mixed
     * @return string
     */
    public function replace($mixed){
        $encoding = $this->_encoding;
        $str      = (string)(mb_convert_encoding($mixed, $encoding, mb_detect_order()));
        $buff     = $this->_buff;
        $len      = mb_strlen($str, $encoding);
        
        $this->_buff = substr_replace($buff, $str, $this->_point - $len, $len);
    }
    
    /**
     * 统计单个字符出现的个数
     */
    public function countWord() {
        $words = array();
        $point = $this->current();
        $this->reset();
        
        while ($this->eof() === false) {
            $read = $this->readMove();
            
            if(!isset($words[$read])) {
                $words[$read] = 1;
            } else {
                $words[$read]++;
            }
        }
        
        $this->_point = $point;
        $this->_nWord = $words;
    }
    
    /**
     * 获取某个字符的出现次数
     * 
     * @param string $word
     * @desc 调用前需调用Char::countWord(), 
     *       不自动调用是为减少不必要的判断调用
     */
    public function getWordCount($word) {
        return (isset($this->_nWord[$word]) ? $this->_nWord[$word] : 0);
    }
    
    /**
     * 裁切 Char 大小(只能裁切末尾)
     * 
     * @param int $size
     * @return boolean
     */
    public function cut($size) {
        $length = $this->_length;
        $nSize  = $size > $length ? $length : $size;
        $this->_length -= $nSize;
        
        return true;
    }

    /**
     * 当前指针位置
     * 
     * @return int
     */
    public function current(){
        return ($this->_point < 0 ? 0 : $this->_point);
    }
    
    /**
     * 向前移动N字符长度
     * 
     * @param int $length
     */
    public function next($length = 1) {
        $this->_point += (int)$length;
    }
    
    /**
     * 向后移动N字符长度
     * 
     * @param int $length
     */
    public function prev($length = 1) {
        $point = $this->_point;
        $point -= (int)$length;
        
        $this->_point = $point < 0 ? 0 : $point;
    }
    
    /**
     * 将指针直接指向末尾
     * 
     * @param int $length
     */
    public function end() {
        $this->_point = $this->size();
    }
    
    /**
     * 结束标记
     * 
     * @return boolean
     */
    public function eof() {
        return (bool)($this->_length <= $this->_point);
    }
    
    /**
     * 指针重置
     */
    public function reset() {
        $this->_point = 0;
    }
    
}
