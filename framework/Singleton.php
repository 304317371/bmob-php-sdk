<?php
abstract class Singleton {
    protected function __construct() {
    }

    final private function __clone() {
    }

    final private function __wakeup() {
    }

    final public static function getInstance() {
        static $instance = array();
        $calledClass = get_called_class();
        if (!isset($instance[$calledClass]))
            $instance[$calledClass] = new static();
        return $instance[$calledClass];
    }
}
?>