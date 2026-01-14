<?php
class Opname extends Database
{
  private $conn;
  private $connSqlSrv;
  public function __construct()
  {
    $this->conn = $this->connectMySQLi();
    $this->connSqlSrv = $this->connectSqlServer();
  }
  public function input_opname($idsub, $awal, $akhir, $note, $userid)
  {
    //     $this->conn->query("INSERT INTO tbl_opname(tgl_awal,tgl_akhir,note,userid,tgl_buat,tgl_update,sub_dept) VALUES('$awal','$akhir','$note','$userid',now(),now(),'$idsub')");
    //     $sql = $this->conn->query("SELECT * FROM tbl_opname WHERE tgl_awal='$awal' and tgl_akhir='$akhir' and sub_dept='$idsub'");
    //     $dt = mysqli_fetch_array($sql);

    //     $data = $this->conn->query("SELECT
    //   a.id,a.kode,a.nama,a.jenis,
    //   if(ISNULL(d.stokawal),0,d.stokawal) as stokawal,
    //   IF(b.stok_in>0,b.stok_in,0)as stokin,
    //   IF(c.stok_out>0,c.stok_out,0)as stokout,
    //   (((IF(b.stok_in>0,b.stok_in,0)))-(IF(c.stok_out>0,c.stok_out,0))) + if(ISNULL(d.stokawal),0,d.stokawal) as stok_akhir,
    // a.sub_dept FROM tbl_barang a LEFT JOIN ( SELECT sum(jumlah) AS stok_in, id_barang
    // FROM tbl_barang_in WHERE tanggal BETWEEN '$awal' AND '$akhir' GROUP BY id_barang ) b ON a.id = b.id_barang
    // LEFT JOIN (	SELECT sum(jumlah) AS stok_out, id_barang FROM tbl_barang_out WHERE tanggal BETWEEN '$awal' AND '$akhir'
    // GROUP BY id_barang) c ON a.id = c.id_barang
    // LEFT JOIN (	SELECT a.sub_dept,b.stok_akhir as stokawal,b.idb from tbl_opname a INNER JOIN tbl_opname_detail b ON a.id=b.id_opname
    // WHERE a.sub_dept='$idsub' AND SUBDATE(a.tgl_akhir, INTERVAL -1 DAY)='$awal') d ON a.id = d.idb
    // WHERE a.sub_dept='$idsub'  AND a.status='1'");
    //     while ($d = mysqli_fetch_array($data)) {
    //       $this->conn->query("INSERT INTO tbl_opname_detail(id_opname,idb,kode,nama,jenis,stok_awal,stok_in,stok_out,stok_akhir)
    //     VALUES ('" . $dt['id'] . "','" . $d['id'] . "','" . $d['kode'] . "','" . $d['nama'] . "','" . $d['jenis'] . "','" . $d['stokawal'] . "','" . $d['stokin'] . "','" . $d['stokout'] . "','" . $d['stok_akhir'] . "')");
    //     }

    sqlsrv_begin_transaction($this->connSqlSrv);

    try {
      $sqlInsertOpname = "
            INSERT INTO invqc.tbl_opname
            (tgl_awal, tgl_akhir, note, userid, tgl_buat, tgl_update, sub_dept)
            VALUES (?, ?, ?, ?, GETDATE(), GETDATE(), ?);
            SELECT SCOPE_IDENTITY() AS id;
        ";

      $paramsOpname = [$awal, $akhir, $note, $userid, $idsub];

      $stmtOpname = sqlsrv_query(
        $this->connSqlSrv,
        $sqlInsertOpname,
        $paramsOpname
      );

      if ($stmtOpname === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
      }

      sqlsrv_next_result($stmtOpname);
      $rowOpname = sqlsrv_fetch_array($stmtOpname, SQLSRV_FETCH_ASSOC);
      $idOpname  = $rowOpname['id'];

      $sqlData = "
            SELECT
                a.id,
                a.kode,
                a.nama,
                a.jenis,

                ISNULL(d.stokawal, 0) AS stokawal,
                ISNULL(b.stokin, 0)   AS stokin,
                ISNULL(c.stokout, 0)  AS stokout,

                (ISNULL(d.stokawal,0)
                 + ISNULL(b.stokin,0)
                 - ISNULL(c.stokout,0)) AS stok_akhir

            FROM invqc.tbl_barang a

            LEFT JOIN (
                SELECT id_barang, SUM(jumlah) AS stokin
                FROM invqc.tbl_barang_in
                WHERE tanggal BETWEEN ? AND ?
                GROUP BY id_barang
            ) b ON a.id = b.id_barang

            LEFT JOIN (
                SELECT id_barang, SUM(jumlah) AS stokout
                FROM invqc.tbl_barang_out
                WHERE tanggal BETWEEN ? AND ?
                GROUP BY id_barang
            ) c ON a.id = c.id_barang

            LEFT JOIN (
                SELECT
                    od.idb,
                    od.stok_akhir AS stokawal
                FROM invqc.tbl_opname o
                INNER JOIN invqc.tbl_opname_detail od ON o.id = od.id_opname
                WHERE o.sub_dept = ?
                  AND o.tgl_akhir = DATEADD(DAY, -1, ?)
            ) d ON a.id = d.idb

            WHERE a.sub_dept = ?
              AND a.status = 1
        ";

      $paramsData = [
        $awal,
        $akhir,
        $awal,
        $akhir,
        $idsub,
        $awal,
        $idsub
      ];

      $stmtData = sqlsrv_query(
        $this->connSqlSrv,
        $sqlData,
        $paramsData
      );

      if ($stmtData === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
      }


      $sqlInsertDetail = "
            INSERT INTO invqc.tbl_opname_detail
            (id_opname, idb, kode, nama, jenis, stok_awal, stok_in, stok_out, stok_akhir)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

      while ($row = sqlsrv_fetch_array($stmtData, SQLSRV_FETCH_ASSOC)) {

        $paramsDetail = [
          $idOpname,
          $row['id'],
          $row['kode'],
          $row['nama'],
          $row['jenis'],
          $row['stokawal'],
          $row['stokin'],
          $row['stokout'],
          $row['stok_akhir']
        ];

        $stmtDetail = sqlsrv_query(
          $this->connSqlSrv,
          $sqlInsertDetail,
          $paramsDetail
        );

        if ($stmtDetail === false) {
          throw new Exception(print_r(sqlsrv_errors(), true));
        }
      }

      sqlsrv_commit($this->connSqlSrv);
      return true;
    } catch (Exception $e) {
      sqlsrv_rollback($this->connSqlSrv);
      die($e->getMessage());
    }
  }
  public function ambilTgl($idsub)
  {
    $sql = "
        SELECT TOP 1 tgl_akhir
        FROM invqc.tbl_opname
        WHERE sub_dept = ?
        ORDER BY id DESC
    ";

    $params = [$idsub];

    $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

    if ($query === false) {
      die(print_r(sqlsrv_errors(), true));
    }

    $row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);

    return $row['tgl_akhir'] ?? null;
  }
  public function tampildata($idsub)
  {
    //     $query = $this->conn->query("SELECT a.id,a.tgl_awal, a.tgl_akhir,
    // 	sum(b.stok_awal) AS stokawal,
    // 	sum(b.stok_in) AS stokin,
    // 	sum(b.stok_out) AS stokout,
    //   sum(b.stok_akhir) AS stokakhir,
    //   a.note,a.userid
    // FROM tbl_opname a
    // INNER JOIN tbl_opname_detail b ON a.id=b.id_opname
    // WHERE a.sub_dept = '$idsub' GROUP BY a.id ORDER BY a.id DESC");
    //     while ($d = mysqli_fetch_array($query)) {
    //       $result[] = $d;
    //     }
    //     return $result;

    $sql = "
        SELECT
            a.id,
            a.tgl_awal,
            a.tgl_akhir,
            ISNULL(d.stokawal, 0)  AS stokawal,
            ISNULL(d.stokin, 0)    AS stokin,
            ISNULL(d.stokout, 0)   AS stokout,
            ISNULL(d.stokakhir, 0) AS stokakhir,
            a.note,
            a.userid
        FROM invqc.tbl_opname a
        INNER JOIN (
            SELECT
                id_opname,
                SUM(stok_awal)  AS stokawal,
                SUM(stok_in)    AS stokin,
                SUM(stok_out)   AS stokout,
                SUM(stok_akhir) AS stokakhir
            FROM invqc.tbl_opname_detail
            GROUP BY id_opname
        ) d ON a.id = d.id_opname
        WHERE a.sub_dept = ?
        ORDER BY a.id DESC
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
  // proses delete data project
  public function hapus_opname($id)
  {
    $this->conn->query("DELETE FROM tbl_opname where id='$id'");
    $this->conn->query("DELETE FROM tbl_opname_detail where id_opname='$id'");

    sqlsrv_begin_transaction($this->connSqlSrv);

    try {
      $params = [$id];
      $sqlDeleteOpname = "DELETE FROM invqc.tbl_opname where id=?";

      $stmtOpname = sqlsrv_query($this->connSqlSrv, $sqlDeleteOpname, $params);

      if ($stmtOpname === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
      }

      $sqlDeleteOpnameDetail = "DELETE FROM invqc.tbl_opname_detail where id_opname=?";

      $stmtOpnameDetail = sqlsrv_query($this->connSqlSrv, $sqlDeleteOpnameDetail, $params);

      if ($stmtOpnameDetail === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
      }

      sqlsrv_commit($this->connSqlSrv);

      return true;
    } catch (Exception $e) {
      sqlsrv_rollback($this->connSqlSrv);
      die($e->getMessage());
    }
  }
  public function tampilreport($idsub, $awal, $akhir)
  {
    //     $query = $this->conn->query("SELECT
    // 	b.nama,
    // 	b.jenis,
    // 	b.stok_awal,
    // 	b.stok_in,
    // 	b.stok_out,
    // 	b.stok_akhir,
    // 	c.jumlah AS aktual
    // FROM
    // 	tbl_opname a
    // INNER JOIN tbl_opname_detail b ON a.id = b.id_opname
    // LEFT JOIN (
    // 	SELECT
    // 		id,
    // 		jumlah
    // 	FROM
    // 		tbl_barang
    // 	WHERE
    // 		sub_dept = '$idsub'
    // 		and `status` = '1'
    // ) c ON b.idb = c.id
    // WHERE
    // 	a.tgl_awal = '$awal'
    // AND a.tgl_akhir = '$akhir'
    // AND a.sub_dept = '$idsub'
    // ORDER BY b.id ASC");
    //     while ($d = mysqli_fetch_array($query)) {
    //       $result[] = $d;
    //     }
    //     return $result;

    $sql = "
        SELECT
            b.nama,
            b.jenis,
            b.stok_awal,
            b.stok_in,
            b.stok_out,
            b.stok_akhir,
            c.jumlah AS aktual
        FROM invqc.tbl_opname a
        INNER JOIN invqc.tbl_opname_detail b 
            ON a.id = b.id_opname
        LEFT JOIN (
            SELECT
                id,
                jumlah
            FROM invqc.tbl_barang
            WHERE sub_dept = ?
              AND status = 1
        ) c ON b.idb = c.id
        WHERE a.tgl_awal = ?
          AND a.tgl_akhir = ?
          AND a.sub_dept = ?
        ORDER BY b.id ASC
    ";

    $params = [
      $idsub,   // untuk subquery tbl_barang
      $awal,    // tgl_awal
      $akhir,   // tgl_akhir
      $idsub    // sub_dept tbl_opname
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
}
