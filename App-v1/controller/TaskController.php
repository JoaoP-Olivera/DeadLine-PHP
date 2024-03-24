<?php
require_once('db.php');

require('./model/Response.php');
require('./model/Task.php');
class TaskController 
{

    public function getAllTask():array
    {
        $DB = Database::lerConexao();
        $sql = $DB->prepare("SELECT*FROM tbltasks");
        $sql->execute();
        $rows = $sql->rowCount();
        while($rows = $sql->fetch(PDO::FETCH_ASSOC))
        {
            $dadosTask = new Task($rows['id'], $rows['nome'], $rows['descricao'],$rows['deadline'], $rows['completado']);
            $tasksArr[] = $dadosTask->arraydeTasks();
        }
        return $tasksArr;
        
    }
    public function getTask(int $id):array
    {
        if($id == 0 || $id < 0)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("O id não pode ser um nulo ou menor que zero");
            $resposta->enviar();

        }
        $DB = Database::lerConexao();
         $sql = $DB->prepare("SELECT id, nome,descricao,deadline,completado FROM tbltasks WHERE id = :id ");
         $sql->bindParam(':id', $id);
         $sql->execute();
         $rows = $sql->rowCount();
        if($rows == 0)
        {
            $resposta = New Response();
            $resposta->setstatusCode(404);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Tarefa não encontrada!");
            $resposta->enviar();
            exit;
        }
        while($rows = $sql->fetch(PDO::FETCH_ASSOC))
        {
            $dadosTask = new Task($rows['id'], $rows['nome'], $rows['descricao'],$rows['deadline'], $rows['completado']);
            $tasksArr[] = $dadosTask->arraydeTasks();
        }
        return $tasksArr;

        

    }
    public function getByCompletados(string $completados):array
    {
        if($completados != "S" || $completados != "N")
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Parametros aceitos são apenas S e N");
            $resposta->enviar();
        }
        $DB = Database::lerConexao();
        $sql = $DB->prepare("SELECT id, nome,descricao,deadline,completado FROM tbltasks WHERE completado = :completado ");
        $sql->bindParam(':completado', $completados);
        $sql->execute();
        while($rows = $sql->fetch(PDO::FETCH_ASSOC))
        {
            $dadosTask = new Task($rows['id'], $rows['nome'], $rows['descricao'],$rows['deadline'], $rows['completado']);
            $tasksArr[] = $dadosTask->arraydeTasks();
        }
        return $tasksArr;


    }
    public function deleteTask(int $id)
    {
        if($id == 0 || $id < 0)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("O id não pode ser um nulo ou menor que zero");
            $resposta->enviar();

        }
        $DB = Database::conectar();
        $sql = $DB->prepare("DELETE FROM tbltasks WHERE id = :id");
        $sql->bindParam(':id', $id);
        $sql->execute();
        $rows_aff = $sql->rowCount();
        if($rows_aff == 0)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("A task não existe");
            $resposta->enviar();
            exit;

        }
        $resposta = new Response();
        $resposta->setstatusCode(200);
        $resposta->setSucesso(true);
        $resposta->addMensagem("Task deletada com sucesso");
        $resposta->enviar();
        exit;
    }
    public function createTask()
    {
        $DB = Database::conectar();
        $request = new Request();
        $request->ehJSON();
        $dadosBrutos = file_get_contents('php://input');
        if(!$dadosJSON =json_decode($dadosBrutos))
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Formato do JSON inválido!");
            $resposta->enviar();  
        }
        if(!isset($dadosJSON->nome) || !isset($dadosJSON->completado))
        {

            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
           (!isset($dadosJSON->nome) ? $resposta->addMensagem("A requisição precisa conter um nome"): false);
           (!isset($dadosJSON->completado) ? $resposta->addMensagem("A requisição precisa conter o status de completado"): false);
            $resposta->enviar();
            exit;
        }
        $nova_task = new Task(null, $dadosJSON->nome, $dadosJSON->descricao, $dadosJSON->deadline, $dadosJSON->completado);
        $nome = $nova_task->getNome();
        $descricao = $nova_task->getDescricao();
        $deadline = $nova_task->getDeadline();
        $completado = $nova_task->foiCompletado();

        $sql = $DB->prepare("INSERT INTO tbltasks (nome, descricao, deadline, completado) VALUES (:nome,:descricao, STR_TO_DATE(:deadline,'%Y-%m-%d %H:%i:%s'), :completado)");
        $sql->bindParam(':nome', $nome, PDO::PARAM_STR);
        $sql->bindParam(':descricao', $descricao, PDO::PARAM_STR);
        $sql->bindParam(':deadline', $deadline, PDO::PARAM_STR);
        $sql->bindParam(':completado', $completado, PDO::PARAM_STR);
        $sql->execute();

        $linhas_aff = intval($sql->rowCount());

        if($linhas_aff == 0)
        {
            $resposta = new Response();
            $resposta->setstatusCode(500);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Falha ao criar a Task");
            $resposta->enviar();
            exit;
        }
        $ultimoInserido = $DB->lastInsertId();
        $sql = $DB->prepare("SELECT id,nome,descricao,deadline,completado FROM tbltasks WHERE id = :id");
        $sql->bindParam(':id',$ultimoInserido,PDO::PARAM_INT);
        $sql->execute();
        $linhasAff = $sql->rowCount();
        if($linhasAff == 0)
        {
            $resposta = new Response();
            $resposta->setstatusCode(500);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Falha ao pegar a Task recém criada");
            $resposta->enviar();
            exit;
        }

        $taskRecemCriada = array();
        while($linhasAff = $sql->fetch(PDO::FETCH_ASSOC))
        {
            $dadosTask = new Task($linhasAff['id'], $linhasAff['nome'], $linhasAff['descricao'],$linhasAff['deadline'], $linhasAff['completado']); 
            $taskRecemCriada[] = $dadosTask->arraydeTasks();  
        }
        $dadosRetornados = array();
        $dadosRetornados['linhas_afetadas'] = $linhasAff;
        $dadosRetornados['task_criada'] = $taskRecemCriada;
        $resposta =  new Response();
        $resposta->setstatusCode(201);
        $resposta->setSucesso(true);
        $resposta->addMensagem('Task criada com sucesso');
        $resposta->setDados($dadosRetornados);
        $resposta->enviar();
        exit;
    }
    public function updateTask( int $id):array
    {
        $DB = Database::conectar();
       $request = new Request();
       $request->ehJSON();
        $dadosAtualizados = file_get_contents('php://input');
        if(!$jsonDados =json_decode($dadosAtualizados))
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Formato do JSON inválido!");
            $resposta->enviar();  
        }
        $nomeAtualizado = false;
        $descricaoAtualizada = false;
        $deadlineAtualizada = false;
        $completadoAtualizado = false;

        $camposDaQuery = "";
        if(isset($jsonDados->nome))
        {
            $nomeAtualizado = true;

        }
        if(isset($jsonDados->descricao))
        {
            $descricaoAtualizada = true;
        }
        if(isset($jsonDados->deadline))
        {
            $deadlineAtualizada = true;
        }
        if(isset($jsonDados->completado))
        {
            $completadoAtualizado = true;
        }
        if($nomeAtualizado === false && $descricaoAtualizada === false && $deadlineAtualizada === false && $completadoAtualizado === false)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Requisição com dados invalidos :(");
            $resposta->enviar();
            exit;
        }
        $sql = $DB->prepare("SELECT*FROM tbltasks WHERE id = :id");
        $sql->bindParam(':id',$id, PDO::PARAM_INT);
        $sql->execute();
        $linhasAff = $sql->rowCount();
        if($linhasAff === 0 )
        {
            $resposta = new Response();
            $resposta->setstatusCode(404);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Task não encontrada :(");
            $resposta->enviar();
            exit;
        }
        $dados = array();
       
        while( $dadosDoDB = $sql->fetch(PDO::FETCH_ASSOC))
        {
            $task = new Task($dadosDoDB['id'], $dadosDoDB['nome'], $dadosDoDB['descricao'], $dadosDoDB['deadline'], $dadosDoDB['completado']);
        }
        if($nomeAtualizado === true)
        {
            
            $task->setNome($jsonDados->nome);
            $novoNome = $task->getNome();
            $sql = $DB->prepare("UPDATE tbltasks SET nome = :nome WHERE id = :id");
            $sql->bindParam(':nome', $novoNome, PDO::PARAM_STR);
            $sql->bindParam(':id', $id);
            $sql->execute();
            $linhasAff = $sql->rowCount();
         if($linhasAff === 0)
         {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Falha ao atualizar");
            $resposta->enviar();
            exit;
         }
        }
        if($descricaoAtualizada === true)
        {
            $sql = $DB->prepare("UPDATE tbltasks SET descricao = :descricao WHERE id = :id");
            $task->setDescricao($jsonDados->descricao);
            $novaDescricao = $task->getDescricao();
            $sql->bindParam(':descricao', $novaDescricao, PDO::PARAM_STR);
            $sql->bindParam(':id', $id);
            $sql->execute();
            $linhasAff = $sql->rowCount();
            if($linhasAff === 0)
            {
                $resposta = new Response();
                $resposta->setstatusCode(400);
                $resposta->setSucesso(false);
                $resposta->addMensagem("Falha ao atualizar");
                $resposta->enviar();
                exit;
            }
        }
        if($deadlineAtualizada === true)
        {
            $sql = $DB->prepare("UPDATE tbltasks SET deadline = STR_TO_DATE(:deadline,'%Y-%m-%d %H:%i:%s') WHERE id = :id");
            $task->setDeadLine($jsonDados->deadline);
            $novaDeadline = $task->getDeadline();
            $sql->bindParam(':deadline', $novaDeadline, PDO::PARAM_STR);
            $sql->bindParam(':id', $id);
            $sql->execute();
            $linhasAff = $sql->rowCount();
        if($linhasAff === 0)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Falha ao atualizar");
            $resposta->enviar();
            exit;
        }
        }
        if($completadoAtualizado === true )
        { 
            $task->setTarefaStatus($jsonDados->completado);
            $novoCompletado = $task->foiCompletado();
            $sql = $DB->prepare("UPDATE tbltasks SET completado = :completado WHERE id = :id");
            $sql->bindParam(':completado', $novoCompletado); 
            $sql->bindParam(':id', $id);
            $sql->execute();
        }       
       
        if($nomeAtualizado === true && $descricaoAtualizada === true && $deadlineAtualizada === true && $completadoAtualizado === true)
        {
            $sql = $DB->prepare("UPDATE tbltasks SET nome = :nome, descricao = :descricao, deadline = STR_TO_DATE(:deadline,'%Y-%m-%d %H:%i:%s'), completado = :completado  WHERE id = :id");
            $task->setNome($jsonDados->nome);
            $novoNome = $task->getNome();
            $task->setDescricao($jsonDados->descricao);
            $novaDescricao = $task->getDescricao();
            $task->setDeadLine($jsonDados->deadline);
            $novaDeadline = $task->getDeadline();
            $task->setTarefaStatus($jsonDados->completado);
            $novoCompletado = $task->foiCompletado();
            
            $sql->bindParam(':nome', $novoNome, PDO::PARAM_STR);
            $sql->bindParam(':descricao', $novaDescricao, PDO::PARAM_STR);
            $sql->bindParam(':deadline', $novaDeadline, PDO::PARAM_STR);
            $sql->bindParam(':completado', $novoCompletado, PDO::PARAM_STR);
            $sql->bindParam(':id', $id);
            $sql->execute();
            $linhasAff = $sql->rowCount();
            if($linhasAff === 0)
           {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Falha ao atualizar");
            $resposta->enviar();
            exit;
           }
        }
        
        $sql = $DB->prepare("SELECT*FROM tbltasks WHERE id = :id");
        $sql->bindParam('id', $id);
        $sql->execute();
        $dados = array();
        while($dadosDoDB = $sql->fetch(PDO::FETCH_ASSOC))
        {
            $task = new Task($dadosDoDB['id'], $dadosDoDB['nome'], $dadosDoDB['descricao'], $dadosDoDB['deadline'], $dadosDoDB['completado']);
            $dados[] = $task->arraydeTasks();
        }
        return $dados;
        
    }

}















