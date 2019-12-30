<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2020 Donat Marko | www.donatus.hu
 */

class DB
{
    protected $sql;
    
    public function __construct($host, $username, $password, $database)
    {
        $this->sql = new mysqli($host, $username, $password, $database) or die("Connection failed to the database.");
	}
	
	public function GetSQL()
	{
		return $this->sql;
	}

	public function Close()
	{
		$this->sql->close();
	}
}