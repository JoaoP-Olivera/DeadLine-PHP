<?php

class Response 
{
    private bool $sucesso;
    private int $statusCode;
    private  $_mensagens = array();
    private $dados;
    private bool $_paraCache = false;
    private array $respostaJSON;

    public function setSucesso( bool $sucesso)
    {
        $this->sucesso = $sucesso;
    
    }
    public function setstatusCode(int $statuscode)
    {
        $this->statusCode = $statuscode;
    }
    public function addMensagem(string $mensagem)
    {
        $this->_mensagens[] = $mensagem;

    }
    public function setDados($dados)
    {
        $this->dados = $dados;
    }
    public function paraCache(bool $cache)
    {
        $this->_paraCache = $cache;
    }
    public function  enviar()
    {
        header('Content-type: application/json;charset=utf-8');
        if($this->_paraCache == true)
        {
            header('Cache-control: max-age=60');
        }
        else 
        {
            header('Cache-control: no-cache, no-store');
        }
        if($this->sucesso !== false && $this->sucesso !== true || !is_numeric($this->statusCode))
        {
            http_response_code(500);
            $this->respostaJSON['statusCode'] = 500;
            $this->respostaJSON['sucesso'] = false;
            $this->addMensagem("Erro na criação da resposta");
            $this->respostaJSON['mensagens'] = $this->_mensagens;
        }
        else 
        {
            http_response_code($this->statusCode);
            $this->respostaJSON['statusCode'] = $this->statusCode;
            $this->respostaJSON['sucesso'] = $this->sucesso;
            $this->respostaJSON['mensagens'] = $this->_mensagens;
            $this->respostaJSON['dados'] = $this->dados;
        }
        echo json_encode($this->respostaJSON);
    } 
}


?>