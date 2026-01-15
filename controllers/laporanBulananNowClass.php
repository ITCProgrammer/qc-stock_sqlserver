<?php
class LaporanBulananNow extends Database
{
    // private $conn;
    private $connSqlSrv;

    public function __construct()
    {
        // $this->conn = $this->connectMySQLi();
        $this->connSqlSrv = $this->connectSQLServer();
    }

    public function input($idsub, $awal, $akhir, $note, $userid)
    {
        
        // Koneksi DB2
        $hostname = "10.0.0.21";
        // $database = "NOWTEST"; // SERVER NOW 20
        $database    = "NOWPRD"; // SERVER NOW 22
        $user        = "db2admin";
        $passworddb2 = "Sunkam@24809";
        $port        = "25000";
        $conn_string = "DRIVER={IBM ODBC DB2 DRIVER}; HOSTNAME=$hostname; PORT=$port; PROTOCOL=TCPIP; UID=$user; PWD=$passworddb2; DATABASE=$database;";

        $conn1 = db2_connect($conn_string, '', '');

        if (!$conn1) {
            die('DB2 connection failed');
        }

        
        sqlsrv_begin_transaction($this->connSqlSrv);

        try {
            
            $sqlInsertHeader = "
            INSERT INTO invqc.tbl_opname_now
            (tgl_awal, tgl_akhir, note, userid, tgl_buat, tgl_update, sub_dept)
            VALUES (?, ?, ?, ?, GETDATE(), GETDATE(), ?);
            SELECT SCOPE_IDENTITY() AS id;
        ";

            $stmtHeader = sqlsrv_query(
                $this->connSqlSrv,
                $sqlInsertHeader,
                [$awal, $akhir, $note, $userid, $idsub]
            );

            if ($stmtHeader === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            sqlsrv_next_result($stmtHeader);
            $rowHeader = sqlsrv_fetch_array($stmtHeader, SQLSRV_FETCH_ASSOC);
            $id_opname = $rowHeader['id'];

           
            $stmtBarang = sqlsrv_query(
                $this->connSqlSrv,
                "SELECT * FROM invqc.tbl_master_barang"
            );

            if ($stmtBarang === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

           
            $sqlInsertDetail = "
            INSERT INTO invqc.tbl_opname_now_detail
            (id_opname, idb, kode, nama, jenis, stok_awal, stok_in, stok_out, stok_akhir)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

           
            while ($barang = sqlsrv_fetch_array($stmtBarang, SQLSRV_FETCH_ASSOC)) {

                $idb   = $barang['id'];
                $nama  = $barang['NAMA'];
                $jenis = $barang['JENIS'];

                $kode = trim(
                    $barang['ITEMTYPECODE'] . ' ' .
                        $barang['DECOSUBCODE01'] . ' ' .
                        $barang['DECOSUBCODE02'] . ' ' .
                        $barang['DECOSUBCODE03'] . ' ' .
                        $barang['DECOSUBCODE04'] . ' ' .
                        $barang['DECOSUBCODE05'] . ' ' .
                        $barang['DECOSUBCODE06']
                );

                /* ==============================
             * DB2 QUERY (MASUK & KELUAR)
             * ============================== */
                $itemtypecode  = $barang['ITEMTYPECODE'];
                $d1 = $barang['DECOSUBCODE01'];
                $d2 = $barang['DECOSUBCODE02'];
                $d3 = $barang['DECOSUBCODE03'];
                $d4 = $barang['DECOSUBCODE04'];
                $d5 = $barang['DECOSUBCODE05'];
                $d6 = $barang['DECOSUBCODE06'];

                $stock_awal_db = (float)$barang['STOCK'];

                // TOTAL MASUK
                $qMasuk = "
                SELECT SUM(USERPRIMARYQUANTITY) AS TOTAL
                FROM STOCKTRANSACTION
                WHERE TEMPLATECODE IN ('OPN','QC1','101')
                AND LOGICALWAREHOUSECODE='M301'
                AND ITEMTYPECODE='$itemtypecode'
                AND DECOSUBCODE01='$d1'
                AND DECOSUBCODE02='$d2'
                AND DECOSUBCODE03='$d3'
                AND DECOSUBCODE04='$d4'
                AND DECOSUBCODE05='$d5'
                AND DECOSUBCODE06='$d6'
                AND TRANSACTIONDATE < '$awal'
                AND CREATIONDATETIME > '2025-03-03 15:00:00'
            ";

                $resMasuk = db2_fetch_assoc(db2_exec($conn1, $qMasuk));
                $total_masuk = (float)($resMasuk['TOTAL'] ?? 0);

                // TOTAL KELUAR
                $qKeluar = str_replace(
                    "IN ('OPN','QC1','101')",
                    "= '201'",
                    $qMasuk
                );

                $resKeluar = db2_fetch_assoc(db2_exec($conn1, $qKeluar));
                $total_keluar = (float)($resKeluar['TOTAL'] ?? 0);

                // BULANAN
                $qMasukBulan = str_replace(
                    "TRANSACTIONDATE < '$awal'",
                    "TRANSACTIONDATE BETWEEN '$awal' AND '$akhir'",
                    $qMasuk
                );

                $resMasukB = db2_fetch_assoc(db2_exec($conn1, $qMasukBulan));
                $stok_in = (float)($resMasukB['TOTAL'] ?? 0);

                $qKeluarBulan = str_replace(
                    "IN ('OPN','QC1','101')",
                    "= '201'",
                    $qMasukBulan
                );

                $resKeluarB = db2_fetch_assoc(db2_exec($conn1, $qKeluarBulan));
                $stok_out = (float)($resKeluarB['TOTAL'] ?? 0);

               
                $stok_awal  = ($stock_awal_db + $total_masuk) - $total_keluar;
                $stok_akhir = ($stok_awal + $stok_in) - $stok_out;

                
                $stmtDetail = sqlsrv_query(
                    $this->connSqlSrv,
                    $sqlInsertDetail,
                    [
                        $id_opname,
                        $idb,
                        $kode,
                        $nama,
                        $jenis,
                        $stok_awal,
                        $stok_in,
                        $stok_out,
                        $stok_akhir
                    ]
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
        // $query    = $this->conn->query("SELECT tgl_akhir FROM tbl_opname_now WHERE sub_dept='$idsub' ORDER BY id DESC");
        // $row      = mysqli_fetch_array($query);
        // $tglakhir = $row['tgl_akhir'];
        // return $tglakhir;

        $sql = "
        SELECT TOP 1 tgl_akhir
        FROM invqc.tbl_opname_now
        WHERE sub_dept = ?
        ORDER BY id DESC
    ";

        $params = [$idsub];

        $stmt = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        return $row['tgl_akhir']->format('Y-m-d') ?? null;
    }

    public function tampildata($idsub)
    {
        // $query = $this->conn->query("SELECT a.id,a.tgl_awal, a.tgl_akhir,
        // sum(b.stok_awal) AS stokawal,
        // sum(b.stok_in) AS stokin,
        // sum(b.stok_out) AS stokout,
        // sum(b.stok_akhir) AS stokakhir,
        // a.note,a.userid
        // FROM tbl_opname_now a
        // INNER JOIN tbl_opname_now_detail b ON a.id=b.id_opname
        // WHERE a.sub_dept = '$idsub' GROUP BY a.id ORDER BY a.id DESC");

        // while ($d = mysqli_fetch_array($query)) {
        //     $result[] = $d;
        // }

        // return $result;

        $sql = "
        SELECT
            a.id,
            a.tgl_awal,
            a.tgl_akhir,
            SUM(b.stok_awal)  AS stokawal,
            SUM(b.stok_in)    AS stokin,
            SUM(b.stok_out)   AS stokout,
            SUM(b.stok_akhir) AS stokakhir,
            a.note,
            a.userid
        FROM invqc.tbl_opname_now a
        INNER JOIN invqc.tbl_opname_now_detail b
            ON a.id = b.id_opname
        WHERE a.sub_dept = ?
        GROUP BY
            a.id,
            a.tgl_awal,
            a.tgl_akhir,
            a.note,
            a.userid
        ORDER BY a.id DESC
    ";

        $params = [$idsub];

        $stmt = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $result = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }
        return $result;
    }

    // proses delete data project
    public function hapus($id)
    {
        // $this->conn->query("DELETE FROM tbl_opname_now where id='$id'");
        // $this->conn->query("DELETE FROM tbl_opname_now_detail where id_opname='$id'");
        sqlsrv_begin_transaction($this->connSqlSrv);

        try {
            // 1️⃣ Hapus detail dulu (FK safety)
            $sqlDetail = "DELETE FROM tbl_opname_now_detail WHERE id_opname = ?";
            $stmtDetail = sqlsrv_query($this->connSqlSrv, $sqlDetail, [$id]);

            if ($stmtDetail === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            // 2️⃣ Hapus header
            $sqlHeader = "DELETE FROM tbl_opname_now WHERE id = ?";
            $stmtHeader = sqlsrv_query($this->connSqlSrv, $sqlHeader, [$id]);

            if ($stmtHeader === false) {
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

    public function tampilreport($idsub, $awal, $akhir)
    {
        $result = [];

       
        // Koneksi DB2
        $hostname = "10.0.0.21";
        // $database = "NOWTEST"; // SERVER NOW 20
        $database    = "NOWPRD"; // SERVER NOW 22
        $user        = "db2admin";
        $passworddb2 = "Sunkam@24809";
        $port        = "25000";
        $conn_string = "DRIVER={IBM ODBC DB2 DRIVER}; HOSTNAME=$hostname; PORT=$port; PROTOCOL=TCPIP; UID=$user; PWD=$passworddb2; DATABASE=$database;";
        // $conn1 = db2_pconnect($conn_string,'', '');
        $conn1 = db2_connect($conn_string, '', '');

        if (!$conn1) {
            die('DB2 connection failed');
        }

        
        $sql = "
        SELECT
            b.idb,
            b.nama,
            b.jenis,
            b.stok_awal,
            b.stok_in,
            b.stok_out,
            b.stok_akhir
        FROM invqc.tbl_opname_now a
        INNER JOIN invqc.tbl_opname_now_detail b
            ON a.id = b.id_opname
        WHERE a.tgl_awal  = ?
          AND a.tgl_akhir = ?
          AND a.sub_dept  = ?
        ORDER BY b.id ASC
    ";

        $stmt = sqlsrv_query(
            $this->connSqlSrv,
            $sql,
            [$awal, $akhir, $idsub]
        );

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        
        while ($d = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

            $id_barang  = $d['idb'];
            $aktual     = 0;

           
            $stmtBarang = sqlsrv_query(
                $this->connSqlSrv,
                "SELECT * FROM invqc.tbl_master_barang WHERE id = ?",
                [$id_barang]
            );

            if ($stmtBarang === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            $barang = sqlsrv_fetch_array($stmtBarang, SQLSRV_FETCH_ASSOC);

            if (!$barang) {
                continue;
            }

            $itemtypecode  = $barang['ITEMTYPECODE']  ?? '';
            $d1 = $barang['DECOSUBCODE01'] ?? '';
            $d2 = $barang['DECOSUBCODE02'] ?? '';
            $d3 = $barang['DECOSUBCODE03'] ?? '';
            $d4 = $barang['DECOSUBCODE04'] ?? '';
            $d5 = $barang['DECOSUBCODE05'] ?? '';
            $d6 = $barang['DECOSUBCODE06'] ?? '';

            
            $query_balance = "
            SELECT SUM(BASEPRIMARYQUANTITYUNIT) AS TOTAL
            FROM BALANCE
            WHERE LOGICALWAREHOUSECODE = 'M301'
              AND ITEMTYPECODE  = '$itemtypecode'
              AND DECOSUBCODE01 = '$d1'
              AND DECOSUBCODE02 = '$d2'
              AND DECOSUBCODE03 = '$d3'
              AND DECOSUBCODE04 = '$d4'
              AND DECOSUBCODE05 = '$d5'
              AND DECOSUBCODE06 = '$d6'
        ";

            $exec_balance = db2_exec($conn1, $query_balance);
            $fetch_balance = db2_fetch_assoc($exec_balance);

            if ($fetch_balance) {
                $aktual = (float) ($fetch_balance['TOTAL'] ?? 0);
            }

           
            $result[] = [
                'nama'       => $d['nama'],
                'jenis'      => $d['jenis'],
                'stok_awal'  => $d['stok_awal'],
                'stok_in'    => $d['stok_in'],
                'stok_out'   => $d['stok_out'],
                'stok_akhir' => $d['stok_akhir'],
                'aktual'     => $aktual,
            ];
        }

        return $result;
    }
}
