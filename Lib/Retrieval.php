<?php

namespace Lib;

final class Retrieval {
    
    protected static $_instance = null;
    
    protected $_init     = false;
    protected $_file     = null;
    protected $_fileC    = null;
    
    /**
     * 数据区开始位置
     *
     * @var int
     */
    protected $_start = 0;
    
    /**
     * 索引相关
     *
     * @var int,array
     */
    protected $_indexLen = 0;
    protected $_incrSize = array();
    
    /**
     * 字典词可用长度集合
     *
     * @var array 
     */
    protected $_ableCharLen = array(2 => 2, 3 => 3, 4 => 4);

    /**
     * 编译器
     *
     * @var \Lib\Compile
     */
    protected $_compile = null;
    
    /**
     * 已检索过的Char
     *
     * @var array 
     */
    protected $_retrieved = array();

    private function __construct() {}
    private function __sleep() {}
    private function __clone() {}

    /**
     * 单例
     * 
     * @return \Lib\Retrieval
     */
    public static function getInstance(){
        if (!self::$_instance instanceof self){
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * 检索源路径 
     * 
     * @desc 必须是由 \Lib\Make::run 编译的文件
     * @param string $file
     * @return \Lib\Retrieval
     */
    public function setFile($file) {
        if(is_file($file)) {
            $this->_file = (string)$file;
        }
        
        return $this;
    }
    
    /**
     * 检索源路径 
     * 
     * @return string
     */
    public function getFile() {
        return $this->_file;
    }
    
    /**
     * 检索文件资源
     * 
     * @return \Buffer\Filec
     */
    public function getFileC() {
        return $this->_fileC;
    }
    
    /**
     * 编译器
     * 
     * @return \Buffer\Filec
     */
    public function getCompile() {
        return $this->_compile;
    }
    
    /**
     * 获取词典最大词长度集合
     * 
     * @return array
     */
    public function getAbleCharLen() {
        return $this->_ableCharLen;
    }

    /**
     * 初始化检索
     * 
     * @return boolean
     */
    public function init() {
        if($this->_init === false) {
            $fileC = new \Buffer\Filec($this->getFile());
            $fileC->reset();
            
            if((strcasecmp($fileC->read(3), pack("C3", 0x21, 0xC7, 0x2C)) !== 0)){
                return false;
            }

            $delimiter  = pack('C', 0x2C);
            $enableSize = 0;

            while (($ableCharVal = $fileC->read()) !== $delimiter) {
                $ableCharLen = (int)(implode(unpack('H', $ableCharVal)));
                $this->_ableCharLen[$ableCharLen] = $ableCharLen;
                
                ++$enableSize;
            }

            $this->_fileC = $fileC;
            $this->parseIndex();
            
            $this->_start   = 3 + 2 + $enableSize + 1 + $this->_indexLen + 1; // BOM(3); 1 分隔符  2 索引长度值；1 换行符
            $this->_compile = new \Lib\Compile();
            $this->_init    = true;
        }

        return true;
    }
    
    /**
     * 检索是否匹配 $c
     * 
     * @param string $char
     * @return boolean
     */
    public function match($char) {
        if($this->_init === false) {
            throw new \Exception('检索资源未初始化或检索资源无效');
        }
        
        $compile = $this->getCompile();
        $fileC   = $this->getFileC();
        $fileC->reset();
        $fileC->next($this->_start);
        $compile->encode(trim($char));
     
        $this->moveY($compile->getMaskY());

        $matched   = false;
        $waitCode  = $compile->getCode();
        $lineC     = explode(pack('C', 0x3B), trim($fileC->readLine(0xFFF)));
        $moveX     = $compile->getMaskX();
        
        for($i = 0; $i < 0xFF; ++$i) {
            if($i === $moveX) {
                $matched = (!empty($lineC[$i])) ? mb_strpos($lineC[$i], $waitCode) >= 0 : false;
                break;
            }
            
            if(!empty($lineC[$i])) {
                --$moveX;
            }
        }
        
        return $matched;
    }

    /**
     * 结束检索，关闭资源
     * 
     * @return boolean
     */
    public function end() {
        $this->_init        = false;
        $this->_file        = null;
        $this->_incrSize    = array();
        $this->_start       = 0;
        $this->_ableCharLen = array(2, 3, 4);
        $this->getFileC()->close();
        
        return true;
    }

    /**
     * 移动 Y
     * 
     * @param int $y
     * @return boolean
     */
    protected function moveY($y) {
        $moveY = (isset($this->_incrSize[$y]) ? $this->_incrSize[$y] : 0);
        $this->getFileC()->next($moveY);
        
        return true;
    }
    
    /**
     * 索引内容解析
     * 
     * @return boolean
     */
    protected function parseIndex() {
        $fileC    = $this->getFileC();
        $indexLen = hexdec(implode(unpack('H*', $fileC->read(2)))) - 1; // 去掉最后的换行符
        $eolIndex = 0xFF + 1; // 每行的换行索引值
        $indexStr = '';

        for ($i = 0; $i < $indexLen; $i++) {
            $char = $fileC->read();

            if($char === PHP_EOL && (($i+1) % $eolIndex) === 0) {
                continue;
            }
             
            $indexStr .= implode(unpack('H*', $char));
        }

        $incrSize  = 0;
        $indexArrs = str_split($indexStr, 3);
        $lineIndex = array($incrSize);
        
        foreach ($indexArrs as $lineSize) {
            $incrSize += hexdec($lineSize);
            $lineIndex[] = $incrSize;
        }
   
        $this->_incrSize = $lineIndex;
        $this->_indexLen = $indexLen;
        
        return true;
    }
}
