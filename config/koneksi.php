<?php
//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

/*
class Database
{
    // properti
    private $dbHost="10.0.0.10";
    private $dbUser="dit";
    private $dbPass="4dm1n";
    private $dbName="invqc";

    // method koneksi mysql
    public function connectMySQL()
    {
        mysql_connect($this->dbHost, $this->dbUser, $this->dbPass);
        mysql_select_db($this->dbName) or die("Database Tidak Ditemukan di Server");
    }
}
*/
class Database
{

    var $mysqli = "";

    function connectMySQLi()
    {
        //konek ke mysql server
        $mysqli = new mysqli("10.0.0.10", "dit", "4dm1n", "invqc");
        //mengecek jika terjadi gagal koneksi
        if (mysqli_connect_errno()) {
            echo "Error: Could not connect to database. ";
            exit;
        }
        return $mysqli;
    }

    function connectSqlServer()
    {
        $hostSVR19 = "10.0.0.221";
        $usernameSVR19 = "sa";
        $passwordSVR19 = "Ind@taichen2024";

        // if connection error due to certificate, please remove "TrustServerCertificate" => true ini array below
        $dbnow_gkg = array("Database" => "invqc", "UID" => $usernameSVR19, "PWD" => $passwordSVR19, "TrustServerCertificate" => true);
        $con = sqlsrv_connect($hostSVR19, $dbnow_gkg);

        if ($con === false) {
            echo "Koneksi Gagal. <br>";
            die(print_r(sqlsrv_errors(), true));
        }

        return $con;
    }
}
