
<?php
class Request
{
    private array $dados;
    public function setDados( $dados):array
    {
        return $this->dados = $dados;
    }
    public function criarJson()
    {
        return json_encode($this->dados);
    }
    
    public function validarHttp( string $http)
    {
        if($_SERVER['REQUEST_METHOD'] !== $http)
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Método HTTP não suportado!");
            $resposta->enviar();  
        }
    }
    public function ehJSON()
    {
        if($_SERVER['CONTENT_TYPE'] !== 'application/json')
        {
            $resposta = new Response();
            $resposta->setstatusCode(400);
            $resposta->setSucesso(false);
            $resposta->addMensagem("Os dados enviados da requisição não são JSON");
            $resposta->enviar();
        }
    }
    
}

?>