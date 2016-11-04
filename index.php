<?php
include_once 'framework/ServiceManager.php';

header('Content-Type: text/html; charset=utf-8');

$serviceManager = new ServiceManager();
$baseURL = $_SERVER['SCRIPT_NAME'];

function parseCommand($commandString, $baseURL) {
    $baseLen = strlen($baseURL);
    if (strncmp($commandString, $baseURL, $baseLen) == 0) {
        return substr($commandString, $baseLen);
    }
    return null;
}

function performRegisterCommand($serviceManager) {
    $mobilePhoneNumber = $_POST['mobilePhoneNumber'];
    if (isset($mobilePhoneNumber)) {
        try {
            $serviceManager->addUser($mobilePhoneNumber,
                $mobilePhoneNumber, $mobilePhoneNumber, $mobilePhoneNumber,
                'client', array('TypeA'));
            $user = $serviceManager->loginUser($mobilePhoneNumber,
                $mobilePhoneNumber);
            if ($user->verifyRequest())
                include 'layout/register_details.php';
            else
                include 'layout/error_permission.php';
        } catch (Exception $e) {
            echo "<div>$e</div>\n";
        }
    } else
        include 'layout/register.php';
}

function performActivateCommand($serviceManager) {
    $mobilePhoneNumber = $_POST['mobilePhoneNumber'];
    $authCode = $_POST['authCode'];
    if (isset($mobilePhoneNumber) && isset($authCode)) {
        try {
            $user = $serviceManager->loginUser($mobilePhoneNumber,
                $mobilePhoneNumber);
            if (!$user->isVerified()) {
                if ($user->verifyAcknowledge($authCode))
                    include 'layout/success.php';
                else
                    include 'layout/error_authcode.php';
            } else
                include 'layout/error_permission.php';
        } catch (Exception $e) {
            echo "<div>$e</div>\n";
        }
    } else
        include 'layout/activate.php';
}

$commandTable = array(
    '/register' => performRegisterCommand,
    '/activate' => performActivateCommand
);

$command = parseCommand($_SERVER['REQUEST_URI'], $baseURL);
if (isset($commandTable[$command]))
    $commandTable[$command]($serviceManager);
else
    include 'layout/index.php';
?>