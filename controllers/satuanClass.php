<?php
class Satuan extends Database
{
    private $conn;
    private $connSqlSrv;

    public function __construct()
    {
        // $this->conn = $this->connectMySQLi();
        $this->connSqlSrv = $this->connectSqlServer();
    }
    // tampilkan data dari tabel satuan
    public function tampil_data()
    {
        //   $query=$this->conn->query("SELECT * FROM tbl_satuan");
        //   while ($d=mysqli_fetch_array($query)) {
        //       $result[]=$d;
        //   }
        //   return $result;

        $sql = "SELECT id, satuan, ket  FROM invqc.tbl_satuan";

        $query = sqlsrv_query($this->connSqlSrv, $sql);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $result = [];
        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }

    // proses input data satuan
    public function input_satuan($satu, $ket)
    {
        // $sql="INSERT INTO tbl_satuan(satuan,ket) VALUES ('$satu','$ket')";
        // $this->conn->query($sql);

        $sql = "
        INSERT INTO invqc.tbl_satuan (satuan, ket)
        VALUES (?, ?)
    ";

        $params = [$satu, $ket];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }

    // tampilkan data dari tabel users yang akan di edit
    public function edit_satuan($id)
    {
        // $data = $this->conn->query("SELECT * FROM tbl_satuan WHERE id='$id'");
        // while ($x = mysqli_fetch_array($data)) {
        //     $hasil[] = $x;
        // }
        // return $hasil;

        $sql = "SELECT * FROM invqc.tbl_satuan WHERE id = ?";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC) ?: null;
    }

    // proses update data user
    public function update_satuan($id, $satu, $ket)
    {
        // $this->conn->query("UPDATE tbl_satuan SET
        // 		satuan='$satu',
        // 		ket='$ket'
        // 		WHERE id='$id'");

        $sql = "
        UPDATE invqc.tbl_satuan
        SET
            satuan = ?,
            ket    = ?
        WHERE id = ?
    ";

        $params = [$satu, $ket, $id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
    // proses delete data project
    public function hapus_satuan($id)
    {
        // $this->conn->query("DELETE FROM tbl_satuan where id='$id'");

        $sql = "DELETE FROM invqc.tbl_satuan WHERE id = ?";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
}
