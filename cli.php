<?php
include_once 'cphomes.php';

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
?>