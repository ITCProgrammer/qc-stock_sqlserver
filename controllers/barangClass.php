<?php
class Barang extends Database
{
    private $conn;
    private $connSqlSrv;

    public function __construct()
    {
        $this->conn = $this->connectMySQLi();
        $this->connSqlSrv = $this->connectSqlServer();
    }
    //cek stok Minimal
    public function cekMinimal($idsub)
    {
        $data = $this->conn->query("SELECT * from tbl_barang WHERE jumlah<=jumlah_min_a and sub_dept='$idsub' ORDER BY kode ASC");
        while ($x = mysqli_fetch_array($data)) {
            $hasil[] = $x;
        }
        return $hasil;
    }
    public function jmlMinRow($idsub)
    {
        $sql = "SELECT count(*) as jml from invqc.tbl_barang WHERE jumlah<=jumlah_min_a and sub_dept=?";
        $params = [$idsub];

        $stmt = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        return $row['jml'];
    }
    public function jmlStock($idsub)
    {
        $sql = "SELECT count(*) as jml from invqc.tbl_barang where sub_dept=?";
        $params = [$idsub];

        $stmt = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        return $row['jml'];
    }
    // ambil harga
    public function ambilHarga($id)
    {
        // $query = $this->conn->query("SELECT * FROM tbl_barang WHERE id='$id'");
        // $row = mysqli_fetch_array($query);
        // return $row['harga'];


        $sql = "SELECT * FROM tbl_barang WHERE id=?";
        $params = [$id];

        $stmt = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        return $row['harga'];
    }

    // proses input barang
    public function input_barang($kode, $nama, $jenis, $harga, $satuan, $minimal, $minatas, $idsub)
    {
        // $sql = "INSERT INTO tbl_barang(kode,nama,jenis,harga,satuan,jumlah_min,jumlah_min_a,tgl_buat,tgl_update,sub_dept)
        // VALUES ('$kode','$nama','$jenis','$harga','$satuan','$minimal','$minatas',now(),now(),'$idsub')";
        // $this->conn->query($sql);

        $sql = "
        INSERT INTO invqc.tbl_barang (
            kode,
            nama,
            jenis,
            harga,
            satuan,
            jumlah_min,
            jumlah_min_a,
            tgl_buat,
            tgl_update,
            sub_dept
        )
        VALUES (
            ?, ?, ?, ?, ?, ?, ?, GETDATE(), GETDATE(), ?
        )
    ";

        $params = [
            $kode,
            $nama,
            $jenis,
            $harga,
            $satuan,
            $minimal,
            $minatas,
            $idsub
        ];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }

    // tampilkan data dari tabel barang yang akan di edit
    public function edit_barang($id)
    {
        // $data = $this->conn->query("SELECT * FROM tbl_barang WHERE id='$id'");
        // while ($x = mysqli_fetch_array($data)) {
        //     $hasil[] = $x;
        // }
        // return $hasil;

        $sql = "SELECT * FROM invqc.tbl_barang WHERE id = ?";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $hasil = [];
        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
            $hasil[] = $row;
        }

        return $hasil;
    }

    // proses update data Barang
    public function update_barang($id, $nama, $jenis, $harga, $satuan, $minimal, $minatas)
    {
        // $this->conn->query("UPDATE tbl_barang SET
        // nama='$nama',
        // jenis='$jenis',
        // harga='$harga',
        // satuan='$satuan',
        // jumlah_min='$minimal',
        // jumlah_min_a='$minatas',
        // tgl_update=now()
        // WHERE id='$id'");

        $sql = "
        UPDATE invqc.tbl_barang
        SET
            nama = ?,
            jenis = ?,
            harga = ?,
            satuan = ?,
            jumlah_min = ?,
            jumlah_min_a = ?,
            tgl_update = GETDATE()
        WHERE id = ?
    ";

        $params = [
            $nama,
            $jenis,
            $harga,
            $satuan,
            $minimal,
            $minatas,
            $id
        ];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
    // proses delete data barang
    public function hapus_barang($id)
    {
        // $this->conn->query("DELETE FROM tbl_barang where id='$id'");
        $sql = "DELETE FROM invqc.tbl_barang WHERE id = ?";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }

    // tampilkan data dari tabel barang
    public function tampil_data($idsub, $min)
    {
        // if ($min == "minimal") {
        //     $where = " AND a.jumlah <= a.jumlah_min_a ";
        // } else {
        //     $where = " ";
        // }
        // $data = $this->conn->query("SELECT
        // 	a.*,b.id as idb
        // 		FROM
        // 	tbl_barang a
        // 		LEFT JOIN tbl_barang_in b ON a.id=b.id_barang WHERE a.sub_dept='$idsub' and `status`='1' $where
        // 		GROUP BY a.id
        // 		ORDER BY
        // 	kode ASC");
        // while ($d = mysqli_fetch_array($data)) {
        //     $result[] = $d;
        // }
        // return $result;

        $where = "";
        $params = [$idsub, 1];

        if ($min == "minimal") {
            $where = " AND a.jumlah <= a.jumlah_min_a ";
        }

        $sql = "
        SELECT 
            a.*,
            bi.idb
        FROM invqc.tbl_barang a
        LEFT JOIN (
            SELECT id_barang, MIN(id) AS idb
            FROM invqc.tbl_barang_in
            GROUP BY id_barang
        ) bi ON a.id = bi.id_barang
        WHERE a.sub_dept = ?
          AND a.status = ?
          $where
        ORDER BY a.kode ASC
    ";

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $result = [];

        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }
    public function tampil_satuan()
    {
        // $query = $this->conn->query("SELECT satuan FROM tbl_satuan");
        // while ($d = mysqli_fetch_array($query)) {
        //     $result[] = $d;
        // }
        // return $result;

        $sql = "SELECT satuan FROM invqc.tbl_satuan";

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
    public function tampil_databarang($idsub)
    {
        // $query = $this->conn->query("SELECT * FROM tbl_barang WHERE sub_dept='$idsub' AND status = 1");
        // while ($d = mysqli_fetch_array($query)) {
        //     $result[] = $d;
        // }
        // return $result;

        $sql = "
        SELECT *
        FROM invqc.tbl_barang
        WHERE sub_dept = ?
          AND status = 1
    ";

        $params = [$idsub];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $result = [];
        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }

    function getSatuanByBarangId($id)
    {
        $query = $this->conn->query("SELECT * FROM tbl_barang WHERE id='$id'");
        $row = mysqli_fetch_array($query);
        return $row['satuan'];
    }
}
