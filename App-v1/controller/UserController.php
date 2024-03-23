<?php
require_once('db.php');
require_once('Request.php');
require_once('../auth/SessionJWT.php');
//TODO: Refatorar e retirar o excesso de código (DRY)
class UserController 
{
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
    public function login( SessionJWT $sessionJWT):array
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
            exit;
        }
        if($SQL->rowCount() == 0)
        {
            $resposta = new Response();
            $resposta->setstatusCode(401);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Usuario não autorizado! Username ou senha estão incorretas");
            $resposta->enviar();
            exit;
        }
        $JWT = new SessionJWT();
        $dados =  $SQL->fetch(PDO::FETCH_ASSOC);
        $id = intval($dados['id']);
        $senhaUsuario = $dados['senha'];
        $nomeUsuario = $dados['nome'];
        $usernameUsuario = $dados['username'];
        $userEstaAtivo = $dados['isactive'];
        $tentativasLogin = $dados['tentativas'];
        if(!password_verify($senha,$senhaUsuario))
        {
            $resposta = new Response();
            $resposta->setstatusCode(401);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Usuario não autorizado! Username ou senha estão incorretas");
            $resposta->enviar();
            exit;
        }
        $JWT->criarSession($id);
        $dadosDoJWT = $JWT->pegarSessionPeloId($id);
        $dados['Token'] = $dadosDoJWT['Access_Id'];
        $dados['SessionUserID'] = $dadosDoJWT['User_Id'];
        $resposta = new Response();
        $resposta->setstatusCode(201);
        $resposta->setSucesso(true);
        $resposta->addMensagem("Bem Vindo a minha API ".$nomeUsuario." :)");
        $resposta->setDados($dados);
        $resposta->enviar();
        header("Location:http://localhost/php/DeadLINE/DeadLine-PHP/App-v1/auth/SessionController.php");
        return $dados;
    }
}



?>