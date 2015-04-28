<?php

namespace Lib;

final class Splitword {
    
    protected static $_instance = null;
    protected $_retrieval   = null;
    protected $_maxCharLen  = 0;
    protected $_minCharLen  = 2;
    protected $_type        = 'General';
    protected $_retrieved   = array();
    protected $_compileFile = null;

    private function __construct() {}
    private function __sleep() {}
    private function __clone() {}

    /**
     * 单例
     * 
     * @return \Lib\Splitword
     */
    public static function getInstance(){
        if (!self::$_instance instanceof self){
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * 检索器初始化
     * 
     * @param string $file
     * @return \Lib\Splitword
     */
    public function initRetrieval($file) {
        $retrieval = \Lib\Retrieval::getInstance();
        $retrieval->setFile($file);
        $retrieval->init();
        
        $this->_retrieval = $retrieval;
        
        return $this;
    }
    
    /**
     * 设置检索类型
     * 
     * @param string $type
     * @return \Lib\Splitword
     */
    public function setType($type) {
        $this->_type = ucfirst(strtolower($type));
        
        return $this;
    }
    
    /**
     * 设置最大检索词长度
     * 
     * @param int $len
     * @return boolean
     */
    public function setMaxCharLen($len) {
        $this->_maxCharLen = (int)$len;
        
        return true;
    }
    
    /**
     * 最大检索词长度
     * 
     * @return int
     */
    public function getMaxCharLen() {
        $sMax = max($this->getAbleCharLen());
        $uMax = $this->_maxCharLen;
        $rMax = ($sMax > $uMax && $uMax > 0 ? $uMax : $sMax);
        
        return $rMax;
    }
    
    /**
     * 最大检索词长度集合
     * 
     * @return int
     */
    public function getAbleCharLen() {
        $ables = $this->getRetrieval()->getAbleCharLen();
        rsort($ables);
        
        return $ables;
    }
    
    /**
     * 设置最小检索词长度
     * 
     * @param int $len
     * @return boolean
     */
    public function setMinCharLen($len) {
        $this->_minCharLen = abs((int)$len);
        
        return true;
    }
    
    /**
     * 最小检索词长度
     * 
     * @return int
     */
    public function getMinCharLen() {
        $sMin = min($this->getAbleCharLen());
        $uMin = $this->_minCharLen;
        
        return ($sMin > $uMin ? $sMin : $uMin);
    }

    /**
     * 检索处理器
     * 
     * @return \Lib\Retrieval
     */
    public function getRetrieval() {
        return $this->_retrieval;
    }
    
    /**
     * 编译处理器
     * 
     * @return \Lib\Compile
     */
    public function getCompile() {
        return $this->getRetrieval()->getCompile();
    }

    public function exec($str, $reset = true) {
        $reset === true && $this->reset();
        $func = 'retrieval' . $this->_type;
        method_exists($this, $func) && call_user_func_array(array($this, $func), array($str));
        
        return true;
    }
    
    /**
     * 获取检索到词
     * 
     * @return array
     */
    public function getRetrieved() {
        return $this->_retrieved;
    }
    
    /**
     * 一般性检索 不重复检索词
     * 
     * @param string $str
     * @return boolean
     */
    protected function retrievalGeneral($str) {
        $retrieval = $this->getRetrieval();
        $compile   = $this->getCompile();
        $char      = $this->getChar($str);
        $ables     = $this->getAbleCharLen();
        $max       = $this->getMaxCharLen();
        $min       = $this->getMinCharLen();
        $size      = $char->size();
        $per       = $size > $max ? $max : $size;

        do {
            $char->end();
            $char->prev($per);
            $isRetrieved= false;

            foreach($ables as $ableLen) {
                $word = trim($char->read($ableLen));
               
                if($retrieval->match($word) === true) {
                    $this->push($compile->getCode(), $word);
                    $char->cut($char->getRealLen());
                    $isRetrieved = true;
                    break;
                } else {
                    $char->next(1);
                }
            }
            
            $isRetrieved === false && $min > 1 && $char->cut($min - 1);
         
        } while ($char->current() > 0 && $char->size() > 0);
        
        return true;
    }
    
    /**
     * 最大限度的检索出所有的词
     * 
     * @param string $str
     * @return boolean
     */
    protected function retrievalMax($str) {
        $retrieval = $this->getRetrieval();
        $compile   = $this->getCompile();
        $char      = $this->getChar($str);
        $ables     = $this->getAbleCharLen();
        $max       = $this->getMaxCharLen();
        $min       = $this->getMinCharLen();
        $size      = $char->size();
        $per       = $size > $max ? $max : $size;

        foreach($ables as $ableLen) {
            $char->end();
            $char->prev($ableLen);
       
            do {
                $word = trim($char->read($ableLen));
                $pass = (bool)($char->current() > 0);
                
                if($retrieval->match($word) === true) {
                    $char->prev($char->getRealLen());
                    $this->push($compile->getCode(), $word);
                } else {
                    $char->prev(1);
                }
            } while ($pass);
        }
        
        return true;
    }
    
    /**
     * 字符 Buffer
     * 
     * @param string $str
     * @return \Buffer\Char
     */
    protected function getChar($str) {
        return new \Buffer\Char(trim(str_replace(PHP_EOL, '', $str)));
    }

    /**
     * 压入检索成功的词
     * 
     * @param string $code
     * @param string $word
     * @return boolean
     */
    protected function push($code, $word) {
        $this->_retrieved[$code] = (string)$word;
        
        return true;
    }
    
    /**
     * 重置
     * 
     * @return boolean
     */
    protected function reset() {
        $this->_retrieved = array();
        
        return true;
    }
}