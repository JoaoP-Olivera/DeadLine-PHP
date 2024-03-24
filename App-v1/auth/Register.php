<?php
require_once('../controller/UserController.php');
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $criarUser = new UserController();
    $criarUser->criarUser();
}
        
?>