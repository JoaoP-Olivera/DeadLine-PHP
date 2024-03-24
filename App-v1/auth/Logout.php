<?php
require_once('../controller/UserController.php');
require_once('../auth/SessionJWT.php');
?>
<?php
if($_SERVER['REQUEST_METHOD'] === 'DELETE')
{
    $Delete = new SessionJWT();
    $logoutUser = new UserController();
    $logoutUser->logout($Delete);
    
}


?>