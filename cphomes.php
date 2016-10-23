<?php
include_once 'lib/BmobObject.class.php';
include_once 'lib/BmobUser.class.php';
include_once 'lib/BmobBatch.class.php';
include_once 'lib/BmobFile.class.php';
include_once 'lib/BmobImage.class.php';
include_once 'lib/BmobRole.class.php';
include_once 'lib/BmobPush.class.php';
include_once 'lib/BmobPay.class.php';
include_once 'lib/BmobSms.class.php';
include_once 'lib/BmobApp.class.php';
include_once 'lib/BmobSchemas.class.php';
include_once 'lib/BmobTimestamp.class.php';
include_once 'lib/BmobCloudCode.class.php';
include_once 'lib/BmobBql.class.php';

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

class DBUserManager extends Singleton {
    protected static $bmobUser;

    protected function __construct() {
        self::$bmobUser = new BmobUser();
    }

    protected static function hashPassword($username, $password) {
        return hash("sha256", $username.'#'.$password);
    }

    final public static function RegisterUser($username, $password,
            $mobilePhoneNumber, $realname, $userType, $serviceType) {
        $res = self::$bmobUser->register(
            array(
                'username' => $username,
                'password' => self::hashPassword($username, $password),
                'mobilePhoneNumber' => $mobilePhoneNumber,
                'mobilePhoneNumberVerified' => false,
                'name' => $realname,
                'userType' => $userType,
                'serviceType' => $serviceType));
        $res = self::$bmobUser->get($res->objectId);
        return new DBUser($res);
    }

    final public static function LoginUser($mobilePhoneNumber, $password) {
        $res = self::$bmobUser->login($mobilePhoneNumber,
            self::hashPassword($mobilePhoneNumber, $password));
        return new DBUser($res);
    }

    final public static function GetProviderList($serviceType) {
        $res = self::$bmobUser->get("",
            array('where={"$and":['.
                '{"userType":"provider"},'.
                '{"serviceType":"'.$serviceType.'"}'.
            ']}'));
        $providerList = array();
        foreach ($res->results as $key => $value)
            $providerList[$key] = new DBUser($value);
        return $providerList;
    }

    final public static function FindUserByMobilePhoneNumber(
            $mobilePhoneNumber) {
        $res = self::$bmobUser->get("",
            array('where={"mobilePhoneNumber":"'.$mobilePhoneNumber.'"}'));
        if (isset($res->results))
            return new DBUser($res->results[0]);
        return null;
    }
}

class DBUser extends DBUserManager {
    private $userInfo;
    private $bmobSms;

    public function __construct($userInfo) {
        $this->bmobSms = new BmobSms();
        $this->userInfo = $userInfo;
    }

    protected function updateUserRecord($content) {
        return self::$bmobUser->update($this->userInfo->objectId,
            $this->userInfo->sessionToken, $content);
    }

    public function isVerified() {
        return $this->userInfo->mobilePhoneNumberVerified;
    }

    public function verifyRequest() {
        if (!$this->isVerified()) {
            try {
                $res = $this->bmobSms->sendSmsVerifyCode(
                    $this->userInfo->mobilePhoneNumber);
                return true;
            } catch (Exception $e) {
                echo "DBUser.verifyRequest: ".$e;
            }
            return true;
        }
        return false;
    }

    public function verifyAcknowledge($code) {
        $res = $this->bmobSms->verifySmsCode(
            $this->userInfo->mobilePhoneNumber, $code);
        if (isset($res->msg) && $res->msg == "ok") {
            $this->updateUserRecord(array('mobilePhoneNumberVerified' => true));
            $this->userInfo->mobilePhoneNumberVerified = true;
            return true;
        }
        return false;
    }

    final public function getReference() {
        return BmobPointer('_User', $this->userInfo->objectId);
    }

    final public function getObjectId() {
        return $this->userInfo->objectId;
    }
}

abstract class DBObjectManager extends Singleton {
    protected static $bmobObj;

    protected function __construct() {
        static::$bmobObj = new BmobObject(static::$OBJECT_NAME);
    }
}

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

class ServiceManager {
    private $dbUser;
    private $dbPromotion;
    private $dbCoupon;

    public function __construct() {
        $this->dbUser = DBUserManager::getInstance();
        $this->dbPromotion = DBPromotionManager::getInstance();
        $this->dbCoupon = DBCouponManager::getInstance();
    }

    public function addUser($username, $password, $mobilePhoneNumber, $name,
            $userType, $serviceType) {
        try {
            $user = $this->dbUser->RegisterUser($username, $password,
                $mobilePhoneNumber, $name, $userType, $serviceType);
        } catch (Exception $e) {
            if ($e->getCode() != 202)
                throw($e);
        }
        return $user;
    }

    public function loginUser($username, $password) {
        $user = $this->dbUser->LoginUser($username, $password);
        return $user;
    }
}

class Menu {
    private $commandList = array(
        array('key' => '1', 'desc' => '预约认证',
            'func' => doMakeAppointment),
        array('key' => '2', 'desc' => '确认消费',
            'func' => doVerifyTransaction));

    private $serviceManager;

    public function __construct() {
        $this->serviceManager = new ServiceManager();
    }

    private function getOption() {
        $prompt = "";
        foreach ($this->commandList as $command)
            $prompt = $prompt."[$command[key]] $command[desc]\n";
        return readline("$prompt\n选择：");
    }

    private function findCommand($choice) {
        foreach ($this->commandList as $command) {
            if ($command['key'] == $choice)
                return $command;
        }
        return null;
    }

    private function doMakeAppointment() {
        $mobilePhoneNumber = readline('请输入手机号：');
        if (isset($mobilePhoneNumber)) {
            $this->serviceManager->addUser($mobilePhoneNumber,
                $mobilePhoneNumber, $mobilePhoneNumber, $mobilePhoneNumber,
                'client', array('TypeA'));
            $user = $this->serviceManager->loginUser($mobilePhoneNumber,
                $mobilePhoneNumber);
            if ($user->verifyRequest()) {
            } else
                echo "\n该用户无权免费使用本服务。\n";
        } else
            echo "\n输入失败。\n";
    }

    private function doVerifyTransaction() {
        $mobilePhoneNumber = readline('请输入手机号：');
        $user = $this->serviceManager->loginUser($mobilePhoneNumber,
            $mobilePhoneNumber);
        if (isset($user)) {
            if (!$user->isVerified()) {
                $authCode = readline('请输入授权码：');
                $user->verifyAcknowledge($authCode);
            }
        }
    }

    public function loop() {
        while (($choice = $this->getOption()) != null) {
            try {
                $command = $this->findCommand($choice);
                if (isset($command))
                    $this->$command['func']();
                else
                    echo "“$choice”没有对应的选择。\n\n";
            } catch (Exception $e) {
                echo $e;
            }
        }
    }
}

try {
    $menu = new Menu();
    $menu->loop();
} catch(Exception $e) {
    echo "Uncaught exception: $e\n";
}
exit(0);