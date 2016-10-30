<?php
include_once 'framework/DBUserManager.php';
include_once 'framework/DBPromotionManager.php';
include_once 'framework/DBCouponManager.php';

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
?>