<?php
include_once 'framework/DBObjectManager.php';

class DBPromotionManager extends DBObjectManager {
    protected static $OBJECT_NAME = 'Promotion';

    final public static function CreatePromotion($type, $title, $description,
            $startDate, $endDate) {
        $res = self::$bmobObj->create(
            array(
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'startDate' => BmobDate($startDate),
                'endDate' => BmobDate($endDate)));
        $res = self::$bmobObj->get($res->objectId);
        return new DBPromotion($res);
    }

    final public static function FindPromotion($type, $title) {
        $res = self::$bmobObj->get("",
            array('where={"$and":['.
                '{"type":"'.$type.'"},'.
                '{"title":"'.$title.'"}'.
            ']}'));
        $promotionList = array();
        if (isset($res->results)) {
            foreach ($res->results as $key => $promotion)
                $promotionList[$key] = new DBPromotion($promotion);
        }
        return $promotionList;
    }
}

class DBPromotion extends DBPromotionManager {
    private $promInfo;

    public function __construct($promInfo) {
        $this->promInfo = $promInfo;
    }

    final public function getReference() {
        return BmobPointer(static::$OBJECT_NAME, $this->promInfo->objectId);
    }

    final public function getObjectId() {
        return $this->promInfo->objectId;
    }
}
?>