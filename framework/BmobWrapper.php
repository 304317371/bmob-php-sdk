<?php
function BmobDate($dateString) {
    return array(
        '__type' => 'Date',
        'iso' => $dateString);
}

function BmobPointer($className, $objectId) {
    return array(
        '__type' => 'Pointer',
        'className' => $className,
        'objectId' => $objectId);
}

function BmobIncrement($amount) {
    return array('__op' => 'Increment', 'amount' => $amount);
}
?>