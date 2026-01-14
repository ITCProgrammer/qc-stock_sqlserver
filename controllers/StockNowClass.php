<?php
class StockNow extends Database
{
    public $conn;
    private $connSqlSrv;

    public function __construct()
    {
        // Panggil parent constructor jika ada, lalu set koneksi
        $this->conn = $this->connectMySQLi();
        $this->connSqlSrv = $this->connectSqlServer();
    }

    public function LihatDataNow()
    {
        // $datastoknow = array();
        // // Pastikan koneksi sudah benar
        // if (!$this->conn) {
        //     return $datastoknow;
        // }
        // $data = $this->conn->query("SELECT * FROM tbl_master_barang where ITEMTYPECODE ='PCK' ORDER BY id ASC");
        // if ($data) {
        //     while ($x = mysqli_fetch_assoc($data)) {
        //         $datastoknow[] = $x;
        //     }
        // }
        // return $datastoknow;

        $sql = "SELECT * FROM invqc.tbl_master_barang where ITEMTYPECODE ='PCK' ORDER BY id ASC";
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

    public function UpdateMinMina($id, $min, $mina)
    {
        // if (!$this->conn) return false;
        // $id = intval($id);
        // $min = intval($min);
        // $mina = intval($mina);
        // $sql = "UPDATE tbl_master_barang SET `MIN` = '$min', `MINA` = '$mina' WHERE id = '$id'";
        // return $this->conn->query($sql);

        $id   = (int) $id;
        $min  = (int) $min;
        $mina = (int) $mina;

        $sql = "
        UPDATE invqc.tbl_master_barang
        SET
            [MIN]  = ?,
            [MINA] = ?
        WHERE id = ?
    ";

        $params = [$min, $mina, $id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }


    // Fungsi per-barang: ambil stok satu barang dari DB2
    public function getLastBalanceByBarang($row = [])
    {
        // Koneksi DB2
        $hostname = "10.0.0.21";
        $database = "NOWPRD";
        $user = "db2admin";
        $passworddb2 = "Sunkam@24809";
        $port = "25000";
        $conn_string = "DRIVER={IBM ODBC DB2 DRIVER}; HOSTNAME=$hostname; PORT=$port; PROTOCOL=TCPIP; UID=$user; PWD=$passworddb2; DATABASE=$database;";
        $conn1 = db2_connect($conn_string, '', '');

        if (!$conn1) {
            return '-'; // Gagal koneksi ke DB2
        }

        $itemTypeCode = isset($row['ITEMTYPECODE']) ? $row['ITEMTYPECODE'] : '';
        $decoSubCode01 = isset($row['DECOSUBCODE01']) ? $row['DECOSUBCODE01'] : '';
        $decoSubCode02 = isset($row['DECOSUBCODE02']) ? $row['DECOSUBCODE02'] : '';
        $decoSubCode03 = isset($row['DECOSUBCODE03']) ? $row['DECOSUBCODE03'] : '';
        $decoSubCode04 = isset($row['DECOSUBCODE04']) ? $row['DECOSUBCODE04'] : '';
        $decoSubCode05 = isset($row['DECOSUBCODE05']) ? $row['DECOSUBCODE05'] : '';
        $decoSubCode06 = isset($row['DECOSUBCODE06']) ? $row['DECOSUBCODE06'] : '';
        $logicalWarehouseCode = !empty($row['LOGICALWAREHOUSECODE']) ? $row['LOGICALWAREHOUSECODE'] : 'M301';

        $sql = "SELECT 
                SUM(BASEPRIMARYQUANTITYUNIT) AS BASEPRIMARYQUANTITYUNIT
                FROM BALANCE
                WHERE ITEMTYPECODE = '$itemTypeCode'
                  AND DECOSUBCODE01 = '$decoSubCode01'
                  AND DECOSUBCODE02 = '$decoSubCode02'
                  AND DECOSUBCODE03 = '$decoSubCode03'
                  AND DECOSUBCODE04 = '$decoSubCode04'
                  AND DECOSUBCODE05 = '$decoSubCode05'
                  AND DECOSUBCODE06 = '$decoSubCode06'
                  AND LOGICALWAREHOUSECODE = '$logicalWarehouseCode'
               ";

        $stmt = db2_exec($conn1, $sql);
        if ($stmt && $rowBalance = db2_fetch_assoc($stmt)) {
            return $rowBalance['BASEPRIMARYQUANTITYUNIT'];
        } else {
            return '-';
        }
    }

    // // Fungsi untuk debug: mengembalikan query yang akan dieksekusi untuk barang tertentu
    // public function getLastBalanceByBarangQuery($row = [])
    // {
    //     $itemTypeCode = isset($row['ITEMTYPECODE']) ? $row['ITEMTYPECODE'] : '';
    //     $decoSubCode01 = isset($row['DECOSUBCODE01']) ? $row['DECOSUBCODE01'] : '';
    //     $decoSubCode02 = isset($row['DECOSUBCODE02']) ? $row['DECOSUBCODE02'] : '';
    //     $decoSubCode03 = isset($row['DECOSUBCODE03']) ? $row['DECOSUBCODE03'] : '';
    //     $decoSubCode04 = isset($row['DECOSUBCODE04']) ? $row['DECOSUBCODE04'] : '';
    //     $decoSubCode05 = isset($row['DECOSUBCODE05']) ? $row['DECOSUBCODE05'] : '';
    //     $decoSubCode06 = isset($row['DECOSUBCODE06']) ? $row['DECOSUBCODE06'] : '';
    //     $logicalWarehouseCode = !empty($row['LOGICALWAREHOUSECODE']) ? $row['LOGICALWAREHOUSECODE'] : 'M301';
    //     $sql = "SELECT SUM (BASEPRIMARYQUANTITYUNIT)
    //             FROM BALANCE
    //             WHERE ITEMTYPECODE = '$itemTypeCode'
    //               AND DECOSUBCODE01 = '$decoSubCode01'
    //               AND DECOSUBCODE02 = '$decoSubCode02'
    //               AND DECOSUBCODE03 = '$decoSubCode03'
    //               AND DECOSUBCODE04 = '$decoSubCode04'
    //               AND DECOSUBCODE05 = '$decoSubCode05'
    //               AND DECOSUBCODE06 = '$decoSubCode06'
    //               AND LOGICALWAREHOUSECODE = '$logicalWarehouseCode'
    //             ";
    //     return $sql;
    // }
}
