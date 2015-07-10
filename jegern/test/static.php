<?php

/*
class A{
    static $foo = 'A';
}

class B{
    static $foo = 'B';
}

echo A::$foo;
echo B::$foo;
B::$foo = 'b';
echo A::$foo;
echo B::$foo;
//ABAb
*/

/*
class A{
    static $foo = 'A';
}

class B{

}

echo A::$foo;
echo B::$foo;
B::$foo = 'b';
echo A::$foo;
echo B::$foo;
//Fatal error: Access to undeclared static property: B::$foo
*/

/*
class A{
    public static function model(){
        if(isset(static::$singleInstance)){
            if(is_object(static::$singleInstance)){
                return static::$singleInstance;
            }else{
                return static::$singleInstance = new static();
            }
        }
        return new static();
    }
}

class B extends A{

}

var_dump($a = B::model());
var_dump($b = B::model());
var_dump($a === $b);
//class B#1 (0) {
//}
//class B#2 (0) {
//}
//bool(false)

*/

/*
class A{
    public static function model(){
        if(isset(static::$singleInstance)){echo '1';
            if(is_object(static::$singleInstance)){echo '2';
                return static::$singleInstance;
            }else{echo '3';
                return static::$singleInstance = new static();
            }
        }echo '4';
        return new static();
    }
}

class B extends A{
    public static $singleInstance = true;
}

var_dump($a = B::model());
var_dump($b = B::model());
var_dump($a === $b);
//13class B#1 (0) {
//}
//12class B#1 (0) {
//}
//bool(true)
*/