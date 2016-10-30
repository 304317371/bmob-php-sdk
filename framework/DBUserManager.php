<?php
include_once 'lib/BmobUser.class.php';
include_once 'lib/BmobSms.class.php';
include_once 'framework/Singleton.php';

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
            $res = $this->bmobSms->sendSmsVerifyCode(
                $this->userInfo->mobilePhoneNumber);
            return true;
        }
        return false;
    }

    public function verifyAcknowledge($code) {
        try {
            $res = $this->bmobSms->verifySmsCode(
                $this->userInfo->mobilePhoneNumber, $code);
            if (isset($res->msg) && $res->msg == "ok") {
                $this->updateUserRecord(
                    array('mobilePhoneNumberVerified' => true));
                $this->userInfo->mobilePhoneNumberVerified = true;
                return true;
            }
        } catch (Exception $e) {
            if ($e->getCode() != 207)
                throw($e);
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
?>