<?php
require_once('Response.php');
require_once('Task.php');
$task = new Task(1,"Lavar a Louça", "Lavar a Louça antes do Meio-Dia", "26/11/2023", true);
$outra_task = new Task(2,"Estudar", "Estudar para o enem ", "26/11/2023", false);
try
{
    header('Content-type: application/json;');
    echo json_encode($task->arraydeTasks());
    echo json_encode($outra_task->arraydeTasks());

}
catch (Exception $e) {
    $teste_response = new Response();
$teste_response->setSucesso(false);
$teste_response->setstatusCode(500);
$teste_response->addMensagem("Error com valor");
$teste_response->enviar();

}





?>