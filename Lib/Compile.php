<?php

namespace Lib;

final class Compile {
    
    protected static $_instance = null;
    protected $_maskX = -1;
    protected $_maskY = -1;
    protected $_maskZ = -1;
    protected $_code  = null;

    public function __construct() {}
    private function __sleep() {}
    private function __clone() {}
    
    /**
     * 转成特定编码
     * 
     * @param string $str
     * @return boolean
     */
    public function encode($str) {
        $this->reset();

        $md5Str = str_split(sha1(trim($str)), 8);
        $code = hexdec($md5Str[0]) ^ hexdec($md5Str[2]);
        $code += hexdec($md5Str[1]) ^ hexdec($md5Str[3]);
        $code ^= hexdec($md5Str[4]);
        $code ^= 0x7FFFFFFF;
        
        $y = $code % \Lib\Consts::MASKE_CODE;
        $x = $code % 0xFF;
        $z = dechex(($code + ($y*$x)) % 0x0F);
        
        $this->_maskZ = $z;
        $this->_maskX = $x;
        $this->_maskY = $y;
        
        $this->_code = sprintf("%06s", ($z . dechex($code%0xFFFFF)));
        
        return true;
    }
    
    /**
     * 获取编码
     * 
     * @return string
     */
    public function getCode() {
        return $this->_code;
    }

    /**
     * 获取掩码 Y
     * 
     * @return int
     */
    public function getMaskY() {
        return $this->_maskY;
    }
    
    /**
     * 获取掩码 X
     * 
     * @return int
     */
    public function getMaskX() {
        return $this->_maskX;
    }
    
    /**
     * 获取掩码 Z
     * 
     * @return int
     */
    public function getMaskZ() {
        return $this->_maskZ;
    }
    
    /**
     * 重置
     */
    public function reset() {
        $this->_maskX = -1;
        $this->_maskY = -1;
        $this->_maskZ = -1;
    }
}