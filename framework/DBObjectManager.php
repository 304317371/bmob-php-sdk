<?php
include_once 'lib/BmobObject.class.php';
include_once 'framework/Singleton.php';

abstract class DBObjectManager extends Singleton {
    protected static $bmobObj;

    protected function __construct() {
        static::$bmobObj = new BmobObject(static::$OBJECT_NAME);
    }
}
?>