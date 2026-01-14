<?php
class BarangNow extends Database
{
    private $conn;
    private $connSqlSrv;

    public function __construct()
    {
        $this->conn = $this->connectMySQLi();
        $this->connSqlSrv = $this->connectSQLServer();
    }

    public function getBarangList()
    {
        // $query  = "SELECT id, DESCRIPTION FROM tbl_master_barang";
        // $result = $this->conn->query($query);

        // return $result;

        $sql = "SELECT id, DESCRIPTION FROM invqc.tbl_master_barang";

        $stmt = sqlsrv_query($this->connSqlSrv, $sql);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $result = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }

    public function renderBarangSelect($selectedBarangID = null)
    {
        $barangList = $this->getBarangList();

        echo '<select id="nama_barang" name="nama_barang" class="form-control" required>';
        echo '<option value="">Pilih Barang</option>';

        foreach ($barangList as $row) {
            $selected = ($selectedBarangID == $row['id']) ? 'selected' : '';
            echo '<option value="' . $row['id'] . '" ' . $selected . '>'
                . htmlspecialchars($row['DESCRIPTION'], ENT_QUOTES, 'UTF-8')
                . '</option>';
        }

        echo '</select>';
    }
}
