<?php
require_once('../controller/UserController.php');
require_once('../auth/SessionJWT.php');
if($_SERVER['REQUEST_METHOD'] == 'POST')
{       $logUser = new UserController();
        $seshJWT = new SessionJWT();
        $logUser->login($seshJWT);
}
?>