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
            $realname, $telephone, $userType, $serviceType) {
        $res = self::$bmobUser->register(
            array(
                'username' => $username,
                'password' => self::hashPassword($username, $password),
                'mobilePhoneNumber' => $telephone,
                'mobilePhoneNumberVerified' => false,
                'name' => $realname,
                'userType' => $userType,
                'serviceType' => $serviceType));
        $res = self::$bmobUser->get($res->objectId);
        return new DBUser($res);
    }

    final public static function LoginUser($username, $password) {
        $res = self::$bmobUser->login($username,
            self::hashPassword($username, $password));
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
            /*$this->bmobSms = new BmobSms();
            try {
                $res = $this->bmobSms->sendSmsVerifyCode(
                    $this->userInfo->mobilePhoneNumber);
                $this->updateUserRecord(array('smsCode' => $res->smsId));
                return true;
            } catch (Exception $e) {
                echo "DBUser.verifyRequest: ".$e;
            }*/
            return true;
        }
        return false;
    }

    public function verifyAcknowledge($code) {
        /*$res = self::$bmobUser->loginByMobile($userInfo->mobilePhoneNumber,
            intval($code));
        var_dump($res);*/
        $this->updateUserRecord(array('mobilePhoneNumberVerified' => true));
        $this->userInfo->mobilePhoneNumberVerified = true;
        return true;
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

    public function addUser($username, $password, $name, $phone,
            $userType, $serviceType) {
        try {
            $user = $this->dbUser->RegisterUser($username, $password, $name,
                $phone, $userType, $serviceType);
        } catch (Exception $e) {
            if ($e->getCode() != 202)
                throw($e);
        }
        return $user;
    }

    public function loginUser($username, $password) {
        $user = $this->dbUser->LoginUser($username, $password);
        if ($user->verifyRequest()) {
            $code = readline('Enter SMS verification code: ');
            if (!$user->verifyAcknowledge($code)) {
                throw(new BmobException("unable to verify account", 1001));
            }
        }
    }
}

class Menu {
    private $commandList = array(
        array('key' => '1', 'desc' => 'Add User', 'func' => doAddUser),
        array('key' => '2', 'desc' => 'Login User', 'func' => doLoginUser));

    private $serviceManager;

    public function __construct() {
        $this->serviceManager = new ServiceManager();
    }

    private function getOption() {
        $prompt = "";
        foreach ($this->commandList as $command)
            $prompt = $prompt."[$command[key]] $command[desc]\n";
        return readline("$prompt\nChoice: ");
    }

    private function findCommand($choice) {
        foreach ($this->commandList as $command) {
            if ($command['key'] == $choice)
                return $command;
        }
        return null;
    }

    private function doAddUser() {
        $username = readline('Username: ');
        $password = readline('Password: ');
        $name = readline('Name: ');
        $phone = readline('Mobile Phone Number: ');
        $userType = readline('User Type [client/provider]: ');
        $serviceType = array();
        $counter = 0;
        while (($service = readline('Service Type '.++$counter.': ')) != null) {
            $serviceType[$counter-1] = $service;
        }
        if (isset($username) && isset($password) && isset($name) &&
                 isset($phone) && isset($userType)) {
            $user = $this->serviceManager->addUser($username, $password, $name, $phone, $userType, $serviceType);
            var_dump($user);
        } else
            echo "parameter error\n";
    }

    private function doLoginUser() {
        $username = readline('Username: ');
        $password = readline('Password: ');
        if (isset($username) && isset($password)) {
            $user = $this->serviceManager->loginUser($username, $password);
            var_dump($user);
        }
    }

    public function loop() {
        while (($choice = $this->getOption()) != null) {
            try {
                $command = $this->findCommand($choice);
                if (isset($command))
                    $this->$command['func']();
                else
                    echo "Invalid choice `$choice'.\n\n";
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

try {
    $prom = $dbPromotion->FindPromotion('TypeA', 'PromotionA');
    if (!isset($prom)) {
        $prom = $dbPromotion->CreatePromotion('TypeA', 'PromotionA',
            "Promotion A", "2016-10-19 09:00:00", "2016-10-19 17:59:59");
    }

    $coupon = $dbCoupon->GrantUserCoupon($user, $prom, 5);
    if (isset($coupon)) {
        /* ... */
    }

    $dbUser->GetProviderList('TypeA');

    $searchUser = $dbUser->FindUserByMobilePhoneNumber('18665365469');
    if (isset($searchUser)) {
        $coupon = $dbCoupon->FindCouponGrant($searchUser, $prom);
        var_dump($coupon);
    }
} catch (Exception $e) {
    echo $e;
}
exit(0);
