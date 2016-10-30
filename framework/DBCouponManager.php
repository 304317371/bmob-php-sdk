<?php
include_once 'lib/BmobObject.class.php';
include_once 'framework/BmobWrapper.php';
include_once 'framework/Singleton.php';

class DBCouponManager extends DBObjectManager {
    protected static $OBJECT_NAME = 'Coupon';

    final public static function FindCouponGrant($user, $promotion) {
        $res = self::$bmobObj->get("",
            array('where={"$and":['.
                '{"userId":"'.$user->getObjectId().'"},'.
                '{"promotionId":"'.$promotion->getObjectId().'"}'.
            ']}'));
        return isset($res->results) && isset($res->results[0]) ?
            new DBCoupon($res->results[0]) : null;
    }

    final protected static function CreateCouponGrant($user, $promotion,
            $count) {
        $res = self::$bmobObj->create(
            array(
                'userId' => $user->getReference(),
                'promotionId' => $promotion->getReference(),
                'couponCount' => $count,
                'activatedCount' => 0,
                'consumedCount' => 0));
        $res = self::$bmobObj->get($res->objectId);
        return new DBCoupon($res);
    }

    final public static function GrantUserCoupon($user, $promotion, $count) {
        $coupon = self::FindCouponGrant($user, $promotion);
        if (isset($coupon))
            $coupon->grantCoupon($count);
        else
            $coupon = self::CreateCouponGrant($user, $promotion, $count);
        return $coupon;
    }
}

class DBCoupon extends DBCouponManager {
    private $couponInfo;

    public function __construct($couponInfo) {
        $this->couponInfo = $couponInfo;
    }

    public function grantCoupon($count) {
        $res = self::$bmobObj->increment($this->couponInfo->objectId,
            'couponCount', array($count));
        $this->couponInfo->couponCount += $count;
    }

    public function activateCoupon($count) {
        $res = self::$bmobObj->increment($this->couponInfo->objectId,
            'activatedCount', array($count));
        $this->couponInfo->activatedCount += $count;
    }

    public function consumeCoupon($count) {
        $res = self::$bmobObj->increment($this->couponInfo->objectId,
            'consumedCount', array($count));
        $this->couponInfo->consumedCount += $count;
    }

    final public function getReference() {
        return BmobPointer(static::$OBJECT_NAME, $this->couponInfo->objectId);
    }

    final public function getObjectId() {
        return $this->couponInfo->objectId;
    }
}
?>