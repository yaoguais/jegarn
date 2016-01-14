<?php

namespace jegarn\server;

class HeaderNBufferProcessor extends BufferProcessor{

    protected $maxPacketLength   = 0;
    protected $defaultBufferSize = 2048;
    protected $idBufferMap       = null;
    protected $headerSize        = 0;
    protected $packChar          = null;

    public function __construct($headerSize = 4, $packChar = 'N', $defaultBufferSize = 2048, $maxPacketLength = 2048){
        $this->headerSize = $headerSize;
        $this->packChar   = $packChar;
        $this->defaultBufferSize = $defaultBufferSize;
        $this->maxPacketLength = $maxPacketLength;
    }

    /**
     * @param integer $id
     * @param string  $buffer
     */
    public function append($id, $buffer){
        $this->init($id);
        $this->idBufferMap[$id] .= $buffer;
    }

    public function reset($id){
        $this->idBufferMap[$id] = '';
    }

    public function destroy($id){
        unset($this->idBufferMap[$id]);
    }

    /**
     * @param $id
     * @return bool|null|string
     */
    public function consumePacket($id, &$return = null){
        $this->init($id);
        $currentBuffer = & $this->idBufferMap[$id];
        $currentBufferLen = strlen($currentBuffer);
        if($currentBufferLen >= $this->headerSize){
            $sizeInfo = unpack($this->packChar,substr($currentBuffer,0 , $this->headerSize));
            if($sizeInfo !== false && isset($sizeInfo[1])){
                $oneFullPacketLength = $this->headerSize + $sizeInfo[1];
                if($currentBufferLen == $oneFullPacketLength){
                    $packetStr = substr($currentBuffer, $this->headerSize);
                    $this->reset($id);
                    return $packetStr;
                }else if($currentBufferLen > $oneFullPacketLength){
                    $packetStr = substr($currentBuffer,$this->headerSize,$sizeInfo[1]);
                    $currentBuffer = substr($currentBuffer, $oneFullPacketLength);
                    return $packetStr;
                }else{
                    return null;
                }
            }else{
                // header size info is error, ignore current buffer
                $this->reset($id);
                return false;
            }
        }else{
            return null;
        }
    }

    /**
     * @param $id
     * @return string
     */
    public function init($id){
        if(!isset($this->idBufferMap[$id])){
            $this->idBufferMap[$id] = '';
        }
        return $this->idBufferMap[$id];
    }
}