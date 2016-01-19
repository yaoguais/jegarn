<?php

namespace jegarn\server;

use swoole_buffer;

class WebsocketBufferProcessor extends BufferProcessor{

    const MIN_HEAD_LEN            = 6;
    const MAX_HEAD_LEN            = 14;
    const BINARY_TYPE_BLOB        = "\x81";
    const BINARY_TYPE_ARRAYBUFFER = "\x82";

    protected     $websocketType     = self::BINARY_TYPE_BLOB;
    protected     $maxPacketLength   = 2048;
    protected     $defaultBufferSize = 2048;
    protected     $idHandshakeMap    = null;
    public static $PONG              = null;
    public static $CLOSE             = null;

    public function __construct($websocketType = self::BINARY_TYPE_BLOB, $defaultBufferSize = 2048, $maxPacketLength = 2048){
        $this->websocketType = $websocketType;
        $this->defaultBufferSize = $defaultBufferSize;
        $this->maxPacketLength = $maxPacketLength;
        self::$PONG = pack('H*', '8a00');
        self::$CLOSE = 'CLS';
    }

    public function setHandshakeAlreadySend($id){
        $this->idHandshakeMap[$id] = true;
    }

    public function isHandshakeAlreadySend($id){
        return isset($this->idHandshakeMap[$id]);
    }

    public function getPacketInfo($id, $buffer, &$isWebsocket, &$isHandshake, &$response){
        if(isset($this->idHandshakeMap[$id])){// it's a websocket client
            $isWebsocket = true;
            $isHandshake = false;
            return true;
        }else{
            // first message would be handshake, otherwise it will be look as a tcp packet
            $error = $this->tryToParseMessage($buffer, $isHandshake, $response);
            $isWebsocket = $isHandshake;
            return $error;
        }
    }

    /**
     * @param string $buffer
     * @return string
     * @author walkor<walkor@workerman.net>
     */
    public function encode($buffer){
        $len = strlen($buffer);
        $firstByte = $this->websocketType;
        if($len <= 125){
            $encodeBuffer = $firstByte . chr($len) . $buffer;
        }else if($len <= 65535){
            $encodeBuffer = $firstByte . chr(126) . pack("n", $len) . $buffer;
        }else{
            $encodeBuffer = $firstByte . chr(127) . pack("xxxxN", $len) . $buffer;
        }
        return $encodeBuffer;
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

    /**
     * destroy a buffer only when client is gone
     * @param $id
     */
    public function destroy($id){
        unset($this->idBufferMap[$id], $this->idHandshakeMap[$id]);
    }

    /**
     * @param $id
     * @return bool|null|string|$PONG|$CLOSE
     */
    public function consumePacket($id, &$return = null){
        $this->init($id);
        $currentBuffer = & $this->idBufferMap[$id];
        $currentBufferLen = strlen($currentBuffer);
        if($currentBufferLen < self::MIN_HEAD_LEN){
            return null;
        }
        //$header = substr($currentBuffer, 0, self::MAX_HEAD_LEN); //no need a string copy
        $header = & $currentBuffer;
        $dataLen = ord($header[1]) & 127;
        $firstByte = ord($header[0]);
        $isFinish = $firstByte >> 7;
        $opcode = $firstByte & 0xf;
        $return = null;
        switch($opcode){
            case 0x0:
            case 0x1:
            case 0x2:
                break;
            case 0x8:
                $return = self::$CLOSE;
                return true;
            case 0x9:// client send a ping response a pong
                $return = self::$PONG;
                if(!$dataLen){
                    $currentBuffer = substr($currentBuffer, self::MIN_HEAD_LEN) ? : '';
                    return true;
                }
                break;
            case 0xa:// client send a pong, do nothing
                if(!$dataLen){
                    $currentBuffer = substr($currentBuffer, self::MIN_HEAD_LEN) ? : '';
                    return true;
                }
                break;
            default :
               goto packet_crashed;
        }
        if($dataLen === 126){
            $headLen = 8;
            $masks = substr($header, 4, 4);
            if($headLen > $currentBufferLen && $isFinish){
                goto packet_crashed;
            }
            $pack = unpack('n', substr($header, 2, 2));
            if(!isset($pack[1])){
                goto packet_crashed;
            }
            $dataLen = $pack[1];
        }else if($dataLen === 127){
            $headLen = 14;
            $masks = substr($header, 10, 4);
            if($headLen > $currentBuffer && $isFinish){
                goto packet_crashed;
            }
            $arr = unpack('N2', substr($header, 2, 8));
            $dataLen = $arr[1] * 4294967296 + $arr[2];
        }else{
            $headLen = self::MIN_HEAD_LEN;
            $masks = substr($header, 2, 4);
        }
        $oneFullPacketLength = $headLen + $dataLen;
        if($currentBufferLen == $oneFullPacketLength){
            $packetStr = substr($currentBuffer, $headLen, $dataLen);
            $this->reset($id);
            return $this->decode($packetStr, $masks);
        }else if($currentBufferLen > $oneFullPacketLength){
            $packetStr = substr($currentBuffer, $headLen, $dataLen);
            $currentBuffer = substr($currentBuffer, $oneFullPacketLength);
            return $this->decode($packetStr, $masks);
        }else{
            return null;
        }
        packet_crashed:
        {
            $this->reset($id);
            return false;
        }
    }

    /**
     * @param $id
     * @return swoole_buffer
     */
    public function init($id){
        if(!isset($this->idBufferMap[$id])){
            $this->idBufferMap[$id] = '';
        }
        return $this->idBufferMap[$id];
    }

    /**
     * @param $buffer
     * @param $response
     * @return bool
     * @author walkor<walkor@workerman.net>
     */
    protected function tryToParseMessage($buffer, &$isHandshake, &$response){
        if(0 === strpos($buffer, 'GET')){
            $headerEndPos = strpos($buffer, "\r\n\r\n");
            if(!$headerEndPos){
                return false;
            }
            if(preg_match("/Sec-WebSocket-Key: *(.*?)\r\n/i", $buffer, $match)){
                $Sec_WebSocket_Key = $match[1];
            }else{
                $response = "HTTP/1.1 400 Bad Request\r\n\r\n<b>400 Bad Request</b><br>Sec-WebSocket-Key not found.<br>This is a WebSocket service and can not be accessed via HTTP.";
                return false;
            }
            $new_key = base64_encode(sha1($Sec_WebSocket_Key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
            $response = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nSec-WebSocket-Version: 13\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: ". $new_key. "\r\n\r\n";
            return $isHandshake = true;
        }elseif(0 === strpos($buffer, '<polic')){
            $response = '<?xml version="1.0"?><cross-domain-policy><site-control permitted-cross-domain-policies="all"/><allow-access-from domain="*" to-ports="*"/></cross-domain-policy>' . "\0";
            return $isHandshake = true;
        }else{
            $isHandshake = false;
            return true;
        }
    }

    /**
     * @param $data
     * @param $masks
     * @return string
     * @author walkor<walkor@workerman.net>
     */
    protected function decode($data, $masks){
        for($i = 0, $l = strlen($data); $i < $l; ++$i){
            $data[$i] = $data[$i] ^ $masks[$i % 4];
        }
        return $data;
    }
}