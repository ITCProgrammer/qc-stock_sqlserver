<?php
class BarangMasuk extends Database
{
    private $conn;
    private $connSqlSrv;

    public function __construct()
    {
        $this->conn = $this->connectMySQLi();
        $this->connSqlSrv = $this->connectSqlServer();
    }
    public function jmlMasuk($idsub)
    {
        $sql = "SELECT count(*) as jml from invqc.tbl_barang_in WHERE sub_dept=?";
        $params = [$idsub];

        $stmt = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        return $row['jml'];
    }
    public function cektgl($tgl1, $tgl2, $idsub)
    {
        //         $data = $this->conn->query("SELECT a.kode,a.nama,a.jenis,a.harga,a.satuan,b.tanggal,b.jumlah,b.note,b.userid from tbl_barang a
        // INNER JOIN tbl_barang_in b ON a.id=b.id_barang
        // WHERE b.tanggal BETWEEN '$tgl1' AND '$tgl2' AND a.sub_dept='$idsub'
        // ORDER BY a.kode ASC");
        //         while ($d = mysqli_fetch_array($data)) {
        //             $result[] = $d;
        //         }
        //         return $result;

        $sql = "
        SELECT
            a.kode,
            a.nama,
            a.jenis,
            a.harga,
            a.satuan,
            b.tanggal,
            b.jumlah,
            b.note,
            b.userid
        FROM invqc.tbl_barang a
        INNER JOIN invqc.tbl_barang_in b 
            ON a.id = b.id_barang
        WHERE b.tanggal BETWEEN ? AND ?
          AND a.sub_dept = ?
        ORDER BY a.kode ASC
    ";

        $params = [
            $tgl1, // pastikan format: 'Y-m-d H:i:s' atau DateTime
            $tgl2,
            $idsub
        ];

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
    // tampilkan data dari tabel barang dan tabel barang-in
    public function tampil_data_in($idsub)
    {
        //         $data = $this->conn->query("SELECT a.id as idb,a.kode,a.nama,a.jenis,a.harga,a.satuan,b.id,b.tanggal,b.jumlah,b.note,b.userid from tbl_barang a
        //   INNER JOIN tbl_barang_in b ON a.id=b.id_barang  WHERE a.sub_dept='$idsub' AND a.status = '1'
        //   ORDER BY b.id DESC LIMIT 5000");
        //         while ($d = mysqli_fetch_array($data)) {
        //             $result[] = $d;
        //         }
        //         return $result;

        $sql = "
        SELECT TOP 5000
            a.id AS idb,
            a.kode,
            a.nama,
            a.jenis,
            a.harga,
            a.satuan,
            b.id,
            b.tanggal,
            b.jumlah,
            b.note,
            b.userid
        FROM invqc.tbl_barang a
        INNER JOIN invqc.tbl_barang_in b 
            ON a.id = b.id_barang
        WHERE a.sub_dept = ?
          AND a.status = 1
        ORDER BY b.id DESC
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
    // tampilkan data dari tabel barang dan tabel barang-in berdasarkan range tgl masuk
    public function tampildatain_tgl($tgl1, $tgl2, $idsub)
    {
        //         $data = $this->conn->query("SELECT a.kode,a.nama,a.jenis,a.harga,a.satuan,b.tanggal,b.jumlah,b.note,b.userid from tbl_barang a
        //   INNER JOIN tbl_barang_in b ON a.id=b.id_barang
        //   WHERE b.tanggal BETWEEN '$tgl1' AND '$tgl2' AND a.sub_dept='$idsub'
        //   ORDER BY a.kode ASC");
        //         while ($d = mysqli_fetch_array($data)) {
        //             $result[] = $d;
        //         }
        //         return $result;

        $sql = "
        SELECT
            a.kode,
            a.nama,
            a.jenis,
            a.harga,
            a.satuan,
            b.tanggal,
            b.jumlah,
            b.note,
            b.userid
        FROM invqc.tbl_barang a
        INNER JOIN invqc.tbl_barang_in b 
            ON a.id = b.id_barang
        WHERE b.tanggal BETWEEN ? AND ?
          AND a.sub_dept = ?
        ORDER BY a.kode ASC
    ";

        $params = [
            $tgl1,
            $tgl2,
            $idsub
        ];

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
    // proses input barang
    public function input_barang_in($id, $jumlah, $userid, $note, $idsub)
    {
        // $sql = "INSERT INTO tbl_barang_in(id_barang,tanggal,jumlah,userid,note,sub_dept)
        // 		VALUES ('$id',now(),'$jumlah','$userid','$note','$idsub')";
        // $this->conn->query($sql);
        // $this->conn->query("UPDATE tbl_barang SET
        // jumlah=jumlah+'$jumlah'
        // WHERE id='$id'");

        // mulai transaction
        sqlsrv_begin_transaction($this->connSqlSrv);

        try {

            $sqlInsert = "
            INSERT INTO invqc.tbl_barang_in (
                id_barang,
                tanggal,
                jumlah,
                userid,
                note,
                sub_dept
            )
            VALUES (
                ?, GETDATE(), ?, ?, ?, ?
            )
        ";

            $paramsInsert = [
                $id,
                $jumlah,
                $userid,
                $note,
                $idsub
            ];

            $stmtInsert = sqlsrv_query(
                $this->connSqlSrv,
                $sqlInsert,
                $paramsInsert
            );

            if ($stmtInsert === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            $sqlUpdate = "
            UPDATE invqc.tbl_barang
            SET jumlah = jumlah + ?
            WHERE id = ?
        ";

            $paramsUpdate = [
                $jumlah,
                $id
            ];

            $stmtUpdate = sqlsrv_query(
                $this->connSqlSrv,
                $sqlUpdate,
                $paramsUpdate
            );

            if ($stmtUpdate === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            // commit jika semua sukses
            sqlsrv_commit($this->connSqlSrv);

            return true;
        } catch (Exception $e) {
            // rollback jika ada error
            sqlsrv_rollback($this->connSqlSrv);
            die($e->getMessage());
        }
    }
    // tampilkan data dari tabel barang yang akan di edit
    public function edit_barang_in($id)
    {
        // $data = $this->conn->query("SELECT * FROM tbl_barang_in WHERE id='$id'");
        // while ($x = mysqli_fetch_array($data)) {
        //     $hasil[] = $x;
        // }
        // return $hasil;

        $sql = "SELECT * FROM invqc.tbl_barang_in WHERE id = ?";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC) ?: null;
    }

    // proses update data Barang
    public function update_barang_in($id, $jumlah, $note, $idb, $selisih)
    {
        //         $this->conn->query("UPDATE tbl_barang_in SET
        //   jumlah='$jumlah',
        //   note='$note',
        //   tgl_update=now()
        //   WHERE id='$id'");
        //         $this->conn->query("UPDATE tbl_barang SET
        //   jumlah=jumlah-'$selisih'
        //   WHERE id='$idb'");

        sqlsrv_begin_transaction($this->connSqlSrv);

        try {
            $sqlUpdateIn = "
            UPDATE invqc.tbl_barang_in
            SET
                jumlah = ?,
                note = ?,
                tgl_update = GETDATE()
            WHERE id = ?
        ";

            $paramsUpdateIn = [
                $jumlah,
                $note,
                $id
            ];

            $stmt1 = sqlsrv_query(
                $this->connSqlSrv,
                $sqlUpdateIn,
                $paramsUpdateIn
            );

            if ($stmt1 === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            $sqlUpdateBarang = "
            UPDATE invqc.tbl_barang
            SET jumlah = jumlah - ?
            WHERE id = ?
        ";

            $paramsUpdateBarang = [
                $selisih,
                $idb
            ];

            $stmt2 = sqlsrv_query(
                $this->connSqlSrv,
                $sqlUpdateBarang,
                $paramsUpdateBarang
            );

            if ($stmt2 === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            sqlsrv_commit($this->connSqlSrv);
            return true;
        } catch (Exception $e) {
            sqlsrv_rollback($this->connSqlSrv);
            die($e->getMessage());
        }
    }
    // proses delete data barang-in
    public function hapus_barang_in($id, $idb, $jumlah)
    {
        // $this->conn->query("DELETE FROM tbl_barang_in where id='$id'");
        // $this->conn->query("UPDATE tbl_barang SET
        // jumlah=jumlah-'$jumlah'
        // WHERE id='$idb'");

        sqlsrv_begin_transaction($this->connSqlSrv);

        try {
            $sqlDelete = "DELETE FROM invqc.tbl_barang_in WHERE id = ?";

            $paramsDelete = [$id];

            $stmtDelete = sqlsrv_query(
                $this->connSqlSrv,
                $sqlDelete,
                $paramsDelete
            );

            if ($stmtDelete === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            $sqlUpdate = "
            UPDATE invqc.tbl_barang
            SET jumlah = jumlah - ?
            WHERE id = ?
        ";

            $paramsUpdate = [
                $jumlah,
                $idb
            ];

            $stmtUpdate = sqlsrv_query(
                $this->connSqlSrv,
                $sqlUpdate,
                $paramsUpdate
            );

            if ($stmtUpdate === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            sqlsrv_commit($this->connSqlSrv);

            return true;
        } catch (Exception $e) {
            sqlsrv_rollback($this->connSqlSrv);
            die($e->getMessage());
        }
    }
    public function show_data_inid($id)
    {
        //         $query = $this->conn->query("SELECT a.id,b.jumlah from tbl_barang a
        //   INNER JOIN tbl_barang_in b ON a.id=b.id_barang
        //   WHERE b.id='$id' ORDER BY a.kode ASC");
        //         $d = mysqli_fetch_array($query);
        //         return $d['id'];

        $sql = "
        SELECT a.id
        FROM invqc.tbl_barang a
        INNER JOIN invqc.tbl_barang_in b 
            ON a.id = b.id_barang
        WHERE b.id = ?
        ORDER BY a.kode ASC
    ";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

        return $row['id'] ?? null;
    }
    public function show_data_injml($id)
    {
        //         $query = $this->conn->query("SELECT a.id,b.jumlah from tbl_barang a
        //   INNER JOIN tbl_barang_in b ON a.id=b.id_barang
        //   WHERE b.id='$id' ORDER BY a.kode ASC");
        //         $d = mysqli_fetch_array($query);
        //         return $d['jumlah'];

        $sql = "
        SELECT b.jumlah
        FROM invqc.tbl_barang a
        INNER JOIN invqc.tbl_barang_in b 
            ON a.id = b.id_barang
        WHERE b.id = ?
        ORDER BY a.kode ASC
        ";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

        return $row['jumlah'] ?? null;
    }
    public function show_detail($id)
    {
        //         $query = $this->conn->query("SELECT a.*,b.tanggal,b.jumlah as jml,b.note,b.userid from tbl_barang a
        //   INNER JOIN tbl_barang_in b ON a.id=b.id_barang
        //   WHERE b.id_barang='$id' ORDER BY a.kode ASC");
        //         while ($x = mysqli_fetch_array($query)) {
        //             $hasil[] = $x;
        //         }
        //         return $hasil;

        $sql = "
        SELECT 
            a.*,
            b.tanggal,
            b.jumlah AS jml,
            b.note,
            b.userid
        FROM invqc.tbl_barang a
        INNER JOIN invqc.tbl_barang_in b 
            ON a.id = b.id_barang
        WHERE b.id_barang = ?
        ORDER BY a.kode ASC
    ";

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
}
