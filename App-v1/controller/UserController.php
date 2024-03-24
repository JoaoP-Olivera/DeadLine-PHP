<?php
require_once('db.php');
require_once('Request.php');
require_once('../auth/SessionJWT.php');
class UserController 
{
    private int $id;
    public function criarUser()
    {
        $request = new Request();
        $request->validarHttp("POST");
        $dadosBrutos = file_get_contents('php://input');
        if(!$dadosJson =json_decode($dadosBrutos))
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Formato do JSON inválido!");
            $resposta->enviar();  
        }
        if(!isset($dadosJson->username)||!isset($dadosJson->senha)||!isset($dadosJson->nome))
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            !isset($dadosJson->username) ? : $resposta->addMensagem("O username é um campo obrigatorio");
            !isset($dadosJson->senha) ? : $resposta->addMensagem("A senha é um campo obrigatorio");
            !isset($dadosJson->nome) ? : $resposta->addMensagem("O nome é um campo obrigatorio");
            $resposta->enviar();
            exit;
        }
        if(strlen($dadosJson->nome) <= 1 || strlen($dadosJson->nome) >= 255)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("O campo nome contem mais que 255 caracteres ou está vazio");
            $resposta->enviar();
            exit;
        }
        if(strlen($dadosJson->username) <= 1 || strlen($dadosJson->username) >= 255)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("O campo username contem mais que 255 caracteres ou está vazio");
            $resposta->enviar();
            exit;
        }
        if(strlen($dadosJson->senha) <= 1 || strlen($dadosJson->senha) >= 255)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("O campo senha contem mais que 255 caracteres ou está vazio");
            $resposta->enviar();
            exit;
        }
        $DB = Database::conectar();
        $nome = $dadosJson->nome;
        $senha = password_hash($dadosJson->senha,PASSWORD_ARGON2ID);
        $username = $dadosJson->username;
         try
         {
            $SQL = $DB->prepare("INSERT INTO usuarios(nome,username,senha) VALUES (:nome,:username,:senha)");
            $SQL->bindParam(':nome',$nome);
            $SQL->bindParam(':username', $username);
            $SQL->bindParam(':senha', $senha);
            $SQL->execute();
         }
         catch(PDOException $ex)
         {
            
            $Resposta = new Response();
            $Resposta->setstatusCode(500);
            $Resposta->setSucesso(false);
            $Resposta->addMensagem($ex->getMessage(), $ex->getCode());
            $Resposta->enviar();
        }
        $affRows = $SQL->rowCount();
        if($affRows !== 0)
        {
         $ultimoInsert = $DB->lastInsertId();
         $query = $DB->prepare("SELECT nome, username,senha FROM usuarios WHERE id = :id");
         $query->bindParam(':id',$ultimoInsert);
         $query->execute();
         $dados = array();
         while( $linhas = $query->fetch(PDO::FETCH_ASSOC))
         {
            $dados = $linhas;
         }
         $resposta = new Response();
         $resposta->setstatusCode(200);
         $resposta->setSucesso(true);
         $resposta->addMensagem("Usuario criado com sucesso :)");
         $resposta->setDados($dados);
         $resposta->enviar();
        }
        
       
    }
    private function blockUser(array $dadosDoUsuario)
    {
        if($dadosDoUsuario['tentativas'] >= 3)
        {
            try {
                $DB = Database::lerConexao();
                $SQL = $DB->prepare("UPDATE usuarios SET isactive = 'N' WHERE id = :id");
                $SQL->bindParam(':id', $dadosDoUsuario['id']);
                $SQL->execute();
            }
            catch(PDOException $e)
            {
                $resposta = new Response();
                $resposta->setstatusCode(500);
                $resposta->setSucesso(false);
                $resposta->addMensagem("Falha no servidor:".$e->getMessage()."");
                $resposta->enviar();
            }
        }
    }
    private function allowUser(array $dados)
    {
        $DB = Database::lerConexao();
        $SQL = $DB->prepare('UPDATE usuarios SET tentativas = 0 WHERE id = :id');
        $SQL->bindParam('id',$dados['id']);
        $SQL->execute();
    }
    public function login( SessionJWT $sessionJWT)
    {
        $loginRequest = new Request();
        $loginRequest->validarHttp("POST");
        $dadosBrutos = file_get_contents('php://input');
        if(!$dadosDeLogin =json_decode($dadosBrutos))
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Formato do JSON inválido!");
            $resposta->enviar();  
        }
        if(!isset($dadosDeLogin->username) || !isset($dadosDeLogin->senha))
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            !isset($dadosDeLogin->username) ? : $resposta->addMensagem("O campo username é necessário!");
            !isset($dadosDeLogin->senha) ? : $resposta->addMensagem("O campo senha é necessário");
            $resposta->enviar();
            exit;
        }
        if(strlen($dadosDeLogin->username) <= 1 || strlen($dadosDeLogin->username) >= 255)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("O campo username contem mais que 255 caracteres ou está vazio");
            $resposta->enviar();
            exit;
        }
        if(strlen($dadosDeLogin->senha) <= 1 || strlen($dadosDeLogin->senha) >= 255)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("O campo senha contem mais que 255 caracteres ou está vazio");
            $resposta->enviar();
            exit;
        }

        $DB = Database::lerConexao();
        $username = $dadosDeLogin->username;
        $senha = $dadosDeLogin->senha;

        try
        {
            $SQL = $DB->prepare("SELECT  id,nome,username,senha,isactive,tentativas FROM usuarios WHERE username = :username");
            $SQL->bindParam(':username',$username);
            $SQL->execute();
        }
        catch(PDOException $ex)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem($ex->getMessage());
            $resposta->enviar();
            exit();
        }
        if($SQL->rowCount() == 0)
        {
            $resposta = new Response();
            $resposta->setstatusCode(401);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Usuario não autorizado! Username ou senha estão incorretas");
            $resposta->enviar();
            exit();
        }
        $JWT = new SessionJWT();
        $dados =  $SQL->fetch(PDO::FETCH_ASSOC);
        $id = intval($dados['id']);
        $senhaUsuario = $dados['senha'];
        $nomeUsuario = $dados['nome'];
        $usernameUsuario = $dados['username'];
        $userEstaAtivo = $dados['isactive'];
        $tentativasLogin = $dados['tentativas'];
        $this->blockUser($dados);
        if($userEstaAtivo !== 'S')
        {
            $resposta = new Response();
            $resposta->setstatusCode(401);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Usuario não está ativo");
            $resposta->enviar();
            exit();
        }
        if(!password_verify($senha,$senhaUsuario))
        {
            $SQL = $DB->prepare("UPDATE usuarios SET tentativas = tentativas+1 WHERE id = :id");
            $SQL->bindParam(':id', $id);
            $SQL->execute();
            $resposta = new Response();
            $resposta->setstatusCode(401);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Usuario não autorizado! Username ou senha estão incorretas");
            $resposta->enviar();
            exit();
            die();
        } 
        $JWT->criarSession($dados['id']);
        $dadosDoJWT = $JWT->pegarSessionPeloId($dados['id']);
        $dados['SessionId'] = $dadosDoJWT['id'];
        $this->id = $dadosDoJWT['id'];
        $dados['Token'] = $dadosDoJWT['token'];
        $this->allowUser($dados);
        $resposta = new Response();
        $resposta->setstatusCode(201);
        $resposta->setSucesso(true);
        $resposta->addMensagem("Bem Vindo a minha API ".$nomeUsuario." :)");
        $resposta->setDados($dados);
        $resposta->enviar();
        header("Location:http://localhost/php/DeadLINE/DeadLine-PHP/App-v1/auth/SessionController.php");
        
    }
    public function logout(SessionJWT $sessionJWT)
    {
        $id = $_GET['sessionid'];
        $loginRequest = new Request();
        $loginRequest->validarHttp("DELETE");
        if(!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1)
        {
            $resposta = new Response();
            $resposta->setstatusCode(401);
            $resposta->setSucesso(false);
            $resposta->addMensagem("O token é necessario para prosseguir a operação");
            $resposta->enviar();
            exit();
        }
        $token = $_SERVER['HTTP_AUTHORIZATION'];
        $deleteSession = new SessionJWT();
        $deleteSession->deletarSession($id,$token);    
    }
}



?>