<?php

require('./controller/TaskController.php');

require('./controller/UserController.php');


if($_SERVER['REQUEST_METHOD'] === 'GET')
{
    if(array_key_exists('id', $_GET))
    {
        $id = $_GET['id'];
        $taskCont = new TaskController();
        $dados = $taskCont->getTask($id);
        $resposta = new Response();
        $resposta->setstatusCode(200);
        $resposta->setSucesso(true);
        $resposta->setDados($dados);
        $resposta->enviar();
        exit;
    }
    if(array_key_exists('completado', $_GET))
    {
        $completado = $_GET['completado'];
        $taskCont = new TaskController();
        $tasksPeloCompletados = $taskCont->getByCompletados($completado);
        $dados = array();
        $dados['dados'] = $tasksPeloCompletados;
        $resposta = new Response();
        $resposta->setstatusCode(200);
        $resposta->setSucesso(true);
        $resposta->setDados($dados);
        $resposta->enviar();
        exit;
    }
    
        $taskCont = new TaskController();
        $dadosDB = $taskCont->getAllTask();
        $dados = array();
        $dados['dados'] = $dadosDB;

        $resposta = new Response();
        $resposta->setstatusCode(200);
        $resposta->setSucesso(true);
        $resposta->setDados($dados);
        $resposta->enviar();
        exit;
   
}
if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $taskCont = new TaskController();
    $taskRecemCriada = $taskCont->createTask();
    $dados = array();
    $dados['dados'] = $taskRecemCriada;
    $resposta = new Response();
    $resposta->setstatusCode(201);
    $resposta->setSucesso(true);
    $resposta->setDados($dados);
    $resposta->enviar();
    exit;
}
if($_SERVER['REQUEST_METHOD'] === 'DELETE')
{
    $id = $_GET['id'];
    $taskCont = new TaskController();
    $taskCont->deleteTask($id);

}
if($_SERVER['REQUEST_METHOD'] === 'PATCH')
{
    $id = $_GET['id'];
    $taskCont = new TaskController();
    $dados = $taskCont->updateTask($id);
    $resposta = new Response();
    $resposta->setstatusCode(200);
    $resposta->setSucesso(true);
    $resposta->setDados($dados);
    $resposta->enviar();
    exit;
}

?>