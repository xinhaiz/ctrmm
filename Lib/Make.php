<?php

namespace Lib;

use Lib\Consts;

final class Make {
    
    protected $dataPath    = null;
    protected $addonesPath = null;
    protected $compileFile = null;
    protected $compileData = array();
    protected $indexSet    = array();
    
    /**
     * 字典词可用长度集合大小，字典词可用长度集合
     *
     * @var int,array 
     */
    protected $enableSize  = 0;
    protected $ableCharLen = array();
    
    public function __construct() {
        !defined('APP_PATH') && define('APP_PATH', dirname(__FILE__));
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);
        
        $appPath           = trim(rtrim(APP_PATH, DS)) . DS;
        $this->dataPath    = $appPath . 'Data' . DS;
        $this->addonesPath = $this->dataPath . 'addones' . DS;
        $this->compileFile = $appPath . 'Bin' . DS . 'compile' . Consts::COMPILE_EXT;
    }
    
    /**
     * 数据基础路径自定义
     * 
     * @param string $dataPath
     * @return \Make
     */
    public function setDataPath($dataPath) {
        if(is_dir($dataPath)) {
            $this->dataPath = (string)$dataPath;
        }
        
        return $this;
    }
    
    /**
     * 获取数据基础路径
     * 
     * @return string
     */
    public function getDataPath() {
        return $this->dataPath;
    }
    
    /**
     * 附加组件路径自定义
     * 
     * @param string $addonesPath
     * @return \Make
     */
    public function setAddonePath($addonesPath) {
        if(is_dir($addonesPath)) {
            $this->addonesPath = (string)$addonesPath;
        }
        
        return $this;
    }
    
    /**
     * 获取附加组件路径
     * 
     * @return string
     */
    public function getAddonePath() {
        return $this->addonesPath;
    }
    
    /**
     * 编译目标文件自定义
     * 
     * @param string $compileFile
     * @return \Make
     */
    public function setCompileFile($compileFile) {
        $file = (string)$compileFile . Consts::COMPILE_EXT;
        
        if(is_file($file)) {
            $this->compileFile = (string)$file;
        }
        
        return $this;
    }
    
    /**
     * 编译目标文件
     * 
     * @return string
     */
    public function getCompileFile() {
        return $this->compileFile;
    }

    /**
     * 执行
     */
    public function run() {
        $this->compile() && $this->build();
    }
    
    /**
     * 编译相关数据
     * 
     * @return boolean
     */
    protected function compile() {
        $ctFiles = array_merge(array($this->getBase()), $this->getAddones());
        $compile = new \Lib\Compile(); 
        
        foreach ($ctFiles as $filev) {
            $fileC = new \Buffer\Filec($filev);
            
            while($fileC->eof() === false) {
                $char = trim($fileC->readLine());
                $this->setAbleCharLen($char);
                $compile->encode($char);
                
                $y = $compile->getMaskY();
                $x = $compile->getMaskX();

                $this->compileData[$y][$x][] = $compile->getCode();
            }
           
            $fileC->close();
        }

        return true;
    }
    
    /**
     * 整理编译后的相关数据并压入编译目标
     * 
     * @return boolean
     */
    protected function build() {
        $compileData = $this->compileData;
        
        if(empty($compileData)) {
            return false;
        }

        $targetC   = new \Buffer\Filec($this->getCompileFile(), 'r+');
        $holder    = pack('C', 0x3B);
        $delimiter = pack('C', 0x2C);
        $maskCode = Consts::MASKE_CODE;
        
        $targetC->reset();
        $targetC->write(pack('C3', 0x21, 0xC7, 0x2C));
        ksort($this->ableCharLen);
        
        foreach ($this->ableCharLen as $ableCharLen) {
            $targetC->write(pack('H', dechex($ableCharLen)));
            ++$this->enableSize;
        }

        $targetC->write($delimiter);

        // 索引总长度：3*行数 + 每255个索引单元加1个\n + 1 \n
        $totalIndexSize = (3*$maskCode)/2;
        $bankIndexSize  = $totalIndexSize + floor($totalIndexSize/0xFF) + 1; 
        $willKeepLen    = sprintf('%04s', dechex($bankIndexSize));
        $targetC->write(pack('H*',$willKeepLen));
        $targetC->next($bankIndexSize);
        
        for($i = 0; $i < $maskCode; $i++) {
            $xData = (isset($compileData[$i])) ? $compileData[$i] : array();
            $jData = array();

            for($j = 0; $j < 0xFF; $j++) {
                $zData   = (isset($xData[$j]) && !empty($xData[$j])) ? $xData[$j] : array($holder);
                $jData[] = implode($delimiter, array_flip(array_flip($zData)));
            }
            
            $iData = trim(implode($jData)) . PHP_EOL;
            $targetC->write($iData);
            $this->indexSet[$i] = strlen($iData);
        }
        
        $this->bIndex($targetC);
        $targetC->flush(); 
        $targetC->close();
        
        return true;
    }
    
    /**
     * 创建索引
     * 
     * @param \Buffer\Filec $targetC
     * @return boolean
     */
    protected function bIndex(\Buffer\Filec $targetC) {
        $targetC->reset();
        $targetC->next(5 + $this->enableSize + 1); // 3位BOM + 2位索引长度 + $this->enableSize字典最大长度索值 + 1分隔符
        
        $indexSet = $this->indexSet;
        $indexStr = '';
        ksort($indexSet);
         
        foreach ($indexSet as $size) {
            $indexStr .= sprintf("%03s", dechex($size));
        }

        $char = new \Buffer\Char($indexStr);
        $line = 0;
        
        while($char->eof() === false) {
            ++$line;
            
            $targetC->write(pack("H*", $char->readMove(2)));
            
            if(($line % 0xFF) === 0) {
                $targetC->write(PHP_EOL);
            }
        }
        
        $targetC->write(PHP_EOL);
        return true;
    }

    /**
     * 获取基础文件
     * 
     * @return string
     */
    protected function getBase() {
        return $this->dataPath . 'base' . Consts::FILE_EXT;
    }
    
    /**
     * 获取组件文件列表
     * 
     * @return array
     */
    protected function getAddones() {
        $folder = new \Buffer\Folder($this->addonesPath);
        $folder->open();
        $files  = $folder->files(Consts::FILE_EXT);
        $folder->close();
        
        return $files;
    }
    
    /**
     * 字典词分布的长度集合(0x01 - 0x0F)
     * 
     * @param string $char
     * @return boolean
     */
    protected function setAbleCharLen($char) {
        $len = mb_strlen($char, 'UTF-8');
        $this->ableCharLen[$len] = $len;
        
        return true;
    }
    
}