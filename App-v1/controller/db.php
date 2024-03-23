<?php

class Database 
{
  private static $escreveConexaoDB;
  private static $lerConexaoDB;

  public static function conectar()
  {
    if( self::$escreveConexaoDB === null)
    {
        self::$escreveConexaoDB = new PDO("mysql:host=localhost; dbname=taskdb;", "root", "");
        self::$escreveConexaoDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$escreveConexaoDB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
    return Self::$escreveConexaoDB;
  }
  public static function lerConexao()
  {
    if( self::$lerConexaoDB === null)
    {
        self::$lerConexaoDB = new PDO("mysql:host=localhost; dbname=taskdb;", "root", "");
        self::$lerConexaoDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$lerConexaoDB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
    return Self::$lerConexaoDB;
  }

}












?>