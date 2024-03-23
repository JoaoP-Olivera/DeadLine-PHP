<?php
class TaskException extends Exception
{

}

class Task 
{
    private  $id;
    private  $nome;
    private  $descricao;
    private  $deadline;
    private  $completado;

    public function __construct($id , $nome, $descricao, $deadline, $completado)
    {
        $this->setID($id);
        $this->setNome($nome);
        $this->setDescricao($descricao);
        $this->setDeadLine($deadline);
        $this->setTarefaStatus($completado);
    }

    public function getId():int
    {
        return $this->id;
    }
    public function getNome()
    {
        return $this->nome;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }
    public function getDeadline()
    {
        return $this->deadline;
    }
    public function foiCompletado()
    {
        return $this->completado;
    }
    public function setID( $id)
    {
        $this->id = $id;
    }
    public function setNome( $nome)
    {
        $this->nome = $nome;
    }
    public function setDescricao( $descricao)
    {
        $this->descricao = $descricao;
    }
    public function setDeadLine( $deadline)
    {
        $this->deadline = $deadline;
    }
    public function setTarefaStatus( $status)
    {
        $this->completado = $status;
    }
    public function arraydeTasks():array
    {
        $task = array();
        $task['id'] = $this->getId();
        $task['nome'] = $this->getNome();
        $task['descricao'] = $this->getDescricao();
        $task['deadline'] = $this->getDeadline();
        $task['completado'] = $this->foiCompletado();

        return $task;
    }
}




?>