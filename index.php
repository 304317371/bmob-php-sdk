<?php
include_once 'cphomes.php';

header('Content-Type: text/html; charset=utf-8');

$baseURL = $_SERVER['REQUEST_URI'];

$serviceManager = new ServiceManager();

$command = $_GET['command'];
if ($command == "register") {
    $mobilePhoneNumber = $_POST['mobilePhoneNumber'];
    if (isset($mobilePhoneNumber)) {
        try {
            $serviceManager->addUser($mobilePhoneNumber,
                $mobilePhoneNumber, $mobilePhoneNumber, $mobilePhoneNumber,
                'client', array('TypeA'));
            $user = $serviceManager->loginUser($mobilePhoneNumber,
                $mobilePhoneNumber);
            if ($user->verifyRequest()) {
                echo
                    "<div>\n".
                    "  <form action=\"index.php\" method=\"post\">\n".
                    "    <div>手机号".
                         "<input type=\"text\" name=\"mobilePhoneNumber\" ".
                             "value=\"$mobilePhoneNumber\"></div>\n".
                    "    <div>姓名".
                         "<input type=\"text\" name=\"realname\"></div>\n".
                    "    <div><input type=\"submit\" value=\"提交\"></div>\n".
                    "  </form>\n".
                    "</div>\n";
            } else {
                echo "<div>该用户无权免费使用本服务。</div>\n";
            }
        } catch (Exception $e) {
            echo "<div>$e</div>\n";
        }
    } else {
        echo
            "<div>\n".
            "  <form action=\"$baseURL\" method=\"post\">\n".
            "    请输入手机号".
                 "<input type=\"text\" name=\"mobilePhoneNumber\">".
                 "<input type=\"submit\" value=\"提交\">\n".
            "  </form>\n".
            "</div>";
    }
} else if ($command == "activate") {
    $mobilePhoneNumber = $_POST['mobilePhoneNumber'];
    $authCode = $_POST['authCode'];
    if (isset($mobilePhoneNumber) && isset($authCode)) {
        try {
            $user = $serviceManager->loginUser($mobilePhoneNumber,
                $mobilePhoneNumber);
            if (!$user->isVerified()) {
                if ($user->verifyAcknowledge($authCode)) {
                    echo "<div>授权成功！</div>\n";
                } else {
                    echo "<div>授权码不正确，请重试。</div>\n";
                }
            } else {
                echo "<div>该用户无权免费使用本服务。</div>\n";
            }
        } catch (Exception $e) {
            echo "<div>$e</div>\n";
        }
    } else {
        echo
            "<div>\n".
            "  <form action=\"$baseURL\" method=\"post\">\n".
            "    <div>手机号".
                 "<input type=\"text\" name=\"mobilePhoneNumber\" ".
                     "value=\"$mobilePhoneNumber\"></div>\n".
            "    <div>授权码".
                 "<input type=\"text\" name=\"authCode\" ".
                     "value=\"$authCode\"></div>\n".
            "    <div><input type=\"submit\" value=\"提交\"></div>\n".
            "  </form>\n".
            "</div>\n";
    }
} else {
    echo 
        "<div>\n".
        "  <div>cphomes系统</div>\n".
        "  <div><a href=\"$baseURL?command=register\">预约认证</a></div>\n".
        "  <div><a href=\"$baseURL?command=activate\">确认消费</a></div>\n".
        "</div>\n";
}
?>
