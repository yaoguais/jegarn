<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 上午8:12
 */
require(__DIR__."/../base/SingleInstanceBase.php");
require('IEventManager.php');
require('EventManager.php');
require('Event.php');

function c_1($event,$a){
    echo "c_1 $a\n";
    var_dump($event);
}

function c_2($event,$a,$b){
    echo "c_2 $a $b\n";
    var_dump($event);
}

function c_3($event,$a,$b,$c){
    echo "c_3 $a $b $c\n";
    var_dump($event);
}


class A{

    public function on($name,$callback){

    }

    public static function c_4($event,$a,$b,$c,$d){
        echo "c_4 $a $b $c $d\n";
        var_dump($event);
    }

}

$eventManager = \minions\event\EventManager::getInstance();
$target = null;
$event1 = new \minions\event\Event($target,'c_1','c_1',false);
$eventManager->addEvent($event1);
$event2 = new \minions\event\Event($target,'c_2','c_2',true);
$eventManager->addEvent($event2);
$event3 = new \minions\event\Event($target,'c_3','c_3',true);
//$eventManager->addEvent($event3);
$obj = new A();
$event4 = new \minions\event\Event($obj,'c_4','A::c_4',true);
var_dump($eventManager->attachEvent($event4,'on'));
$eventManager->forUnitTest();
var_dump($eventManager->hasEvent($event1));
var_dump($eventManager->hasEvent($event2));
var_dump($eventManager->hasEvent($event3));
var_dump($eventManager->hasEvent($event4));
$eventManager->dispatchEvent('c_1','1');
$eventManager->dispatchEvent('c_2','1','2');
$eventManager->dispatchEvent('c_3','1','2','3');
$eventManager->dispatchEvent('c_4','1','2','3','4');
var_dump($event2);
var_dump("remove:".$eventManager->removeEvent($event2));
/*$obj2 = $obj;
var_dump($obj2 === $obj);
$arr = [
    1,2,3
];
foreach($arr as $k=>&$v){
    if(2==$v){
        unset($arr[$k]);
    }
}
print_r($arr);*/
$eventManager->forUnitTest();




