<?php
class Permohonan extends Database
{
    public function __construct()
    {
        $this->conn = $this->connectMySQLi();
        $this->connSqlSrv = $this->connectSqlServer();
    }
    // proses input permohonan
    public function input_permohonan($documentno, $tgl, $dept, $note, $idsub)
    {
        // $this->conn->query("INSERT INTO tbl_permohonan(documentno,tgl_mohon,dept,note,tgl_buat,tgl_update,sub_dept)
        // VALUES ('$documentno','$tgl','$dept','$note',now(),now(),'$idsub')");

        $sql = "
        INSERT INTO invqc.tbl_permohonan(
            documentno,
            tgl_mohon,
            dept,
            note,
            tgl_buat,
            tgl_update,
            sub_dept)
		VALUES (?,?,?,?,GETDATE(),GETDATE(),?)
        ";

        $params = [
            $documentno,
            $tgl,
            $dept,
            $note,
            $idsub,
        ];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
    // proses input detail permohonan
    public function add_detail_permohonan($id, $kode, $jumlah)
    {
        // $this->conn->query("INSERT INTO tbl_permohonan_detail(id_mohon,id_kode,jumlah,tgl_update)
        // VALUES ('$id','$kode','$jumlah',now())");

        $sql = "
        INSERT INTO invqc.tbl_permohonan_detail (
            id_mohon,
            id_kode,
            jumlah,
            tgl_update
        )
        VALUES (
            ?, ?, ?, GETDATE()
        )
    ";

        $params = [
            $id,
            $kode,
            $jumlah
        ];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
    // tampilkan data dari tabel permohonan yang akan di edit
    public function edit_permohonan($id)
    {
        // $data=$this->conn->query("SELECT * FROM tbl_permohonan WHERE id='$id'");
        // while ($x=mysqli_fetch_array($data)) {
        //     $hasil[]=$x;
        // }
        // return $hasil;

        $sql = "SELECT * FROM invqc.tbl_permohonan WHERE id=?";
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

    // proses update data permohonan
    public function update_permohonan($id, $tgl, $note)
    {
        // $this->conn->query("UPDATE tbl_permohonan SET
        // note='$note',
        // tgl_mohon='$tgl',
        // tgl_update=now()
        // WHERE id='$id'");

        $sql = "
        UPDATE invqc.tbl_permohonan
        SET
            note = ?,
            tgl_mohon = ?,
            tgl_update = GETDATE()
        WHERE id = ?
    ";

        $params = [
            $note,
            $tgl, // pastikan format: 'Y-m-d H:i:s' atau DateTime
            $id
        ];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
    // proses delete data permohonan
    public function hapus_permohonan($id)
    {
        print_r($id);
        // $this->conn->query("DELETE FROM tbl_permohonan where id='$id'");
        $sql = "DELETE FROM invqc.tbl_permohonan WHERE id = ?";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }

    // tampilkan data dari tabel permohonan
    public function tampil_data($idsub)
    {
        //         $data=$this->conn->query("SELECT
        // 	a.*,count(id_kode) as jml,b.id as idb
        // FROM
        // 	tbl_permohonan a
        // LEFT JOIN tbl_permohonan_detail b ON a.id=b.id_mohon WHERE a.sub_dept='$idsub' GROUP BY a.id
        // ORDER BY
        // 	documentno ASC");
        //         while ($d=mysqli_fetch_array($data)) {
        //             $result[]=$d;
        //         }
        //         return $result;

        $sql = "
        SELECT 
            a.id,
            a.documentno,
            a.dept,
            a.note,
            a.tgl_mohon,
            ISNULL(d.jml, 0) AS jml
        FROM invqc.tbl_permohonan a
        LEFT JOIN (
            SELECT 
                id_mohon,
                COUNT(id_kode) AS jml,
                MIN(id) AS idb
            FROM invqc.tbl_permohonan_detail
            GROUP BY id_mohon
        ) d ON a.id = d.id_mohon
        WHERE a.sub_dept = ?
        ORDER BY a.documentno ASC
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
    // tampilkan data dari tabel permohonan detail
    public function show_detail($id)
    {
        //         $query = $this->conn->query("SELECT b.jumlah as jml_mohon,c.* from tbl_permohonan a
        //   INNER JOIN tbl_permohonan_detail b ON a.id=b.id_mohon
        // 	INNER JOIN tbl_barang c ON c.id=b.id_kode
        //   WHERE a.id='$id' ORDER BY b.id ASC");
        //         while ($x = mysqli_fetch_array($query)) {
        //             $hasil[] = $x;
        //         }
        //         return $hasil;

        $sql = "
        SELECT 
            b.jumlah AS jml_mohon,
            c.*
        FROM invqc.tbl_permohonan a
        INNER JOIN invqc.tbl_permohonan_detail b 
            ON a.id = b.id_mohon
        INNER JOIN invqc.tbl_barang c 
            ON c.id = b.id_kode
        WHERE a.id = ?
        ORDER BY b.id ASC
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
    public function tampil_permohonan($id)
    {
        // $query = $this->conn->query("SELECT *, date_format(tgl_mohon, '%d %M %Y') as tglmohon FROM tbl_permohonan WHERE id='$id'");
        // while ($d = mysqli_fetch_array($query)) {
        //     $result[] = $d;
        // }
        // return $result;

        $sql = "
        SELECT *
        FROM invqc.tbl_permohonan
        WHERE id = ?
    ";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $result = [];
        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
            // format tanggal di PHP
            if ($row['tgl_mohon'] instanceof DateTimeInterface) {
                $row['tglmohon'] = $row['tgl_mohon']->format('d F Y');
            } else {
                $row['tglmohon'] = null;
            }

            $result[] = $row;
        }

        return $result;
    }
    public function tampil_permohonan_detail($id)
    {
        //         $query = $this->conn->query("SELECT
        // 	a.*,
        // 	b.kode,
        // 	b.nama,
        // 	b.satuan,
        // 	b.jumlah as stok	 
        // FROM
        // 	tbl_permohonan_detail a
        // 	INNER JOIN tbl_barang b ON a.id_kode = b.id 
        // WHERE
        // 	a.id_mohon = '$id'");
        //         while ($d = mysqli_fetch_array($query)) {
        //             $result[] = $d;
        //         }
        //         return $result;

        $sql = "
        SELECT
            a.*,
            b.kode,
            b.nama,
            b.satuan,
            b.jumlah AS stok
        FROM invqc.tbl_permohonan_detail a
        INNER JOIN invqc.tbl_barang b 
            ON a.id_kode = b.id
        WHERE a.id_mohon = ?
    ";

        $params = [$id];

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
}
