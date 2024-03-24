<?php
require_once('../controller/db.php');
require_once('../model/Response.php');

class SessionJWT
{
    //POST-LogIn e retorna o access Token
    //DELETE-LogOut e deleta o accsess Token
    //PATCH- Gera um novo Token sem deletar a sessão
    private string $accessToken;
    private int $accessTokenExpire = 1200;
    private string $refreshToken; //20 minutos
    private int $refreshTokenExpire = 1209600; //14 dias
    

    public function gerarToken()
    {
        $string = random_bytes(24);
        return $this->accessToken = bin2hex($string);
    }
    public function regerarToken()
    {
        
         $this->refreshToken = $this->gerarToken();
    }
    public function criarSession(int $id)
    {
        $DB = Database::conectar();
        $this->gerarToken();
        $this->regerarToken();
        $sessionCriada = array();
      try{  
        $sqlToken = $DB->prepare("INSERT INTO tblsession(userid,accesstoken,accesstokenexpire,refreshtoken,refreshtokenexpire) VALUES (:userid,:accesstoken,date_add(NOW(), INTERVAL :accessTokenExpire SECOND),:refreshtoken,date_add(NOW(), INTERVAL :refreshTokenExpire SECOND))");
        $sqlToken->bindParam(':userid',$id);
        $sqlToken->bindParam(':accesstoken', $this->accessToken);
        $sqlToken->bindParam(':accessTokenExpire', $this->accessTokenExpire);
        $sqlToken->bindParam(':refreshtoken', $this->refreshToken);
        $sqlToken->bindParam(':refreshTokenExpire', $this->refreshTokenExpire);
        $sqlToken->execute();
    } catch(PDOException $e)
    {
        $sessionCriada['mensagem_de_erro'] ="Não foi possivel criar a session :( ---- ".$e->getMessage();
    }
    $ultimoIDInserido = $DB->lastInsertId();
    $sessionCriada['Session_id'] = intval($ultimoIDInserido);
    $sessionCriada['Access_Token'] = $this->accessToken;
    $sessionCriada['Token_Expire'] = $this->accessTokenExpire;
    $sessionCriada['Refresh_Token'] = $this->refreshToken;
    $sessionCriada['Refresh_Token_Expire'] = $this->refreshTokenExpire;
    $resposta = new Response();
    $resposta->setSucesso(true);
    $resposta->setstatusCode(201);
    $resposta->setDados($sessionCriada);
    $resposta->addMensagem("Sessão criada com sucesso");
    $resposta->enviar();
    exit();
    }
    public function pegarSessionPeloId(int $id):array
    { 
        $DB = Database::conectar();
        $SQL = $DB->prepare("SELECT userid,accesstoken FROM tblsession WHERE id = :id");
        $SQL->bindParam(':id',$id);
        $SQL->execute();
        $dadosDaSession = array();
        while($linhas = $SQL->fetch(PDO::FETCH_ASSOC))
        {
            $dadosDaSession['id'] = $linhas['userid'];
            $dadosDaSession['token'] = $linhas['accesstoken'];
            
        }
        return $dadosDaSession;
    }
    public function deletarSession(int $id, string $token)
    {
        try{
        $DB = Database::conectar();
        $SQL = $DB->prepare("DELETE FROM tblsession WHERE id = :id AND accesstoken = :token");
        $SQL->bindParam(':id', $id);
        $SQL->bindParam(':token', $token);
        $SQL->execute();
        $sucessoDaQuery = $SQL->rowCount();
        if($sucessoDaQuery === 0)
        {
            $mensagem = [
                'Status-Code' => 401,
                'mensagem' => 'Falha ao fazer logout'
            ];
            echo json_encode($mensagem);
        }
        }
        catch(PDOException $e)
        {
            $resposta = new Response();
            $resposta->setstatusCode(500);
            $resposta->setSucesso(false);
            $resposta->addMensagem("falha ao deletar o JWT".$e->getMessage()."");
            $resposta->enviar();
            exit();      
        }
        $mensagem = [
        'mensagem'=> 'Logout com exito'
        ];
    } 
}

?> 