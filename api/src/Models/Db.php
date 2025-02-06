<?php

namespace App\Models;

use \PDO;

require_once "../../../config-inc.php";


class Db
{
    private $conn;

    public function connect()
    {
        $conn_str = "mysql:host=" . SQL_SERVER . ";dbname=" . SQL_DATABASE;
        $this->conn = new PDO($conn_str, SQL_USERNAME, SQL_PASSWORD);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this->conn;
    }


    public function IsSystemOpen() : bool
    {
        $sql = "SELECT `value` FROM config WHERE `key` ='mode';";
        $stmt = $this->conn->prepare($sql);        
        $stmt->execute();
        $value = $stmt->fetchColumn();
        return $value != "0";
    }

    public function GetAllFlights() : array
    {
        $sql = "SELECT f.callsign, f.booked_by, f.aircraft_icao, f.gate, f.departure_time as eobt,
                   f.origin_icao, f.destination_icao,
                   CASE WHEN dep_apt.icao IS NOT NULL THEN 'departure' ELSE 'arrival' END AS type_of_flight
            FROM flights f
            LEFT OUTER JOIN airports as dep_apt ON f.origin_icao = dep_apt.icao
            UNION ALL
            SELECT s.callsign, s.booked_by, s.aircraft_icao, s.gate, t.time,
                   s.origin_icao, s.destination_icao,
                   CASE WHEN dep_apt.icao IS NOT NULL THEN 'departure' ELSE 'arrival' END AS TypeOfFlight
            FROM slots s
            INNER JOIN timeframes t ON s.timeframe_id = t.id
            LEFT OUTER JOIN airports as dep_apt ON s.origin_icao = dep_apt.icao";

         
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function GetFlight(string $callsign, string $dof) : array
    {
        $params = array(":callsign" => $callsign, ":dof" => $dof);

        $sql = "SELECT f.callsign, f.booked_by, f.aircraft_icao, f.gate,  f.departure_time as eobt,
                f.origin_icao, f.destination_icao,
                CASE WHEN dep_apt.icao IS NOT NULL THEN 'departure' ELSE 'arrival' END AS type_of_flight
                FROM flights f
                LEFT OUTER JOIN airports as dep_apt ON f.origin_icao = dep_apt.icao
                WHERE f.callsign = :callsign
                AND DATE_FORMAT(CASE WHEN dep_apt.icao IS NOT NULL THEN f.departure_time ELSE f.arrival_time END, '%Y%m%d') = :dof
                UNION ALL
                SELECT s.callsign, s.booked_by, s.aircraft_icao, s.gate,  t.time,
                    s.origin_icao, s.destination_icao,
                    CASE WHEN dep_apt.icao IS NOT NULL THEN 'departure' ELSE 'arrival' END AS type_of_flight
                FROM slots s
                INNER JOIN timeframes t ON s.timeframe_id = t.id
                LEFT OUTER JOIN airports as dep_apt ON s.origin_icao = dep_apt.icao
                where s.callsign = :callsign
                AND DATE_FORMAT(t.time, '%Y%m%d') = :dof";
         
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}