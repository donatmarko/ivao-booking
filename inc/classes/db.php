<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

class DB
{
	protected $sql;
	
	public function __construct($host, $username, $password, $database)
	{
		$this->sql = new mysqli($host, $username, $password, $database) or die("Connection failed to the database.");
	}	
	
	protected function GetSQL()
	{
		return $this->sql;
	}

	public function Close()
	{
		$this->sql->close();
	}

  public function GetInsertID()
  {
    return $this->sql->insert_id;
  }

	protected function prep(string $sql, array $arguments): string
  {
    foreach ($arguments as $arg)
    {
      if ($arg === null)
      {
        $sql = str_replace_first("§", "NULL", $sql);
      }
      elseif (is_bool($arg))
      {
        $sql = str_replace_first("§", $arg ? 1 : 0, $sql);
      }
      elseif (is_surely_number($arg))
      {
        $sql = str_replace_first("§", $this->sql->real_escape_string($arg), $sql);
      }
      else
      {
        $sql = str_replace_first("§", sprintf("'%s'", $this->sql->real_escape_string($arg)), $sql);
      }
    }
    return $sql;
  }

	public function Query(string $sql): bool|mysqli_result
  {
    if (empty($sql) || !$this->sql)
      return false;
      
    $arguments = array_slice(func_get_args(), 1);
    $sql = $this->prep($sql, $arguments);

		$res = $this->sql->query($sql);
    return $res;
  }
}