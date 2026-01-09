<?php
class LaporanBulananNow extends Database
{
    public function __construct()
    {
        $this->conn = $this->connectMySQLi();
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
        // $conn1 = db2_pconnect($conn_string,'', '');
        $conn1 = db2_connect($conn_string, '', '');

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $this->conn->query("INSERT INTO tbl_opname_now(tgl_awal,tgl_akhir,note,userid,tgl_buat,tgl_update,sub_dept)
        VALUES('$awal','$akhir','$note','$userid',now(),now(),'$idsub')");
        $sql = $this->conn->query("SELECT * FROM tbl_opname_now WHERE tgl_awal='$awal' and tgl_akhir='$akhir' and sub_dept='$idsub'");
        $dt  = mysqli_fetch_array($sql);

        $sql_master_barang = $this->conn->query("SELECT * FROM tbl_master_barang");

        while ($data_master_barang = mysqli_fetch_array($sql_master_barang)) {

            $id_opname = null;
            $idb       = null;
            $kode      = null;
            $nama      = null;
            $jenis     = null;

            $stok_awal  = 0;
            $stok_in    = 0;
            $stok_out   = 0;
            $stok_akhir = 0;

            $id_opname = $dt['id'];
            $idb       = $data_master_barang['id'];
            $kode      = $data_master_barang['ITEMTYPECODE'] . ' ' .
                $data_master_barang['DECOSUBCODE01'] . ' ' .
                $data_master_barang['DECOSUBCODE02'] . ' ' .
                $data_master_barang['DECOSUBCODE03'] . ' ' .
                $data_master_barang['DECOSUBCODE04'] . ' ' .
                $data_master_barang['DECOSUBCODE05'] . ' ' .
                $data_master_barang['DECOSUBCODE06'];

            $nama  = $data_master_barang['NAMA'];
            $jenis = $data_master_barang['JENIS'];

            // Deklarasi Awal
            $stock_awal           = 0;
            $stock_akhir          = 0;
            $stock_awal_db        = 0;
            $total_masuk          = 0;
            $total_keluar         = 0;
            $total_masuk_bulanan  = 0;
            $total_keluar_bulanan = 0;

            $stock_awal_db = $data_master_barang['STOCK'];
            $DESCRIPTION   = $data_master_barang['DESCRIPTION'];

            $itemtypecode  = (String) isset($data_master_barang['ITEMTYPECODE']) ? $data_master_barang['ITEMTYPECODE'] : '';
            $decosubcode01 = (String) isset($data_master_barang['DECOSUBCODE01']) ? $data_master_barang['DECOSUBCODE01'] : '';
            $decosubcode02 = (String) isset($data_master_barang['DECOSUBCODE02']) ? $data_master_barang['DECOSUBCODE02'] : '';
            $decosubcode03 = (String) isset($data_master_barang['DECOSUBCODE03']) ? $data_master_barang['DECOSUBCODE03'] : '';
            $decosubcode04 = (String) isset($data_master_barang['DECOSUBCODE04']) ? $data_master_barang['DECOSUBCODE04'] : '';
            $decosubcode05 = (String) isset($data_master_barang['DECOSUBCODE05']) ? $data_master_barang['DECOSUBCODE05'] : '';
            $decosubcode06 = (String) isset($data_master_barang['DECOSUBCODE06']) ? $data_master_barang['DECOSUBCODE06'] : '';

            // Total Masuk Stock
            $query_masuk = "SELECT SUM(USERPRIMARYQUANTITY) AS TOTAL
            FROM STOCKTRANSACTION
            WHERE (TEMPLATECODE ='OPN' OR TEMPLATECODE ='QC1' OR TEMPLATECODE ='101')
            AND LOGICALWAREHOUSECODE ='M301'
            AND ITEMTYPECODE ='$itemtypecode'
            AND DECOSUBCODE01 ='$decosubcode01'
            AND DECOSUBCODE02 ='$decosubcode02'
            AND DECOSUBCODE03 ='$decosubcode03'
            AND DECOSUBCODE04 ='$decosubcode04'
            AND DECOSUBCODE05 ='$decosubcode05'
            AND DECOSUBCODE06 ='$decosubcode06'
            AND TRANSACTIONDATE < '$awal'
            AND CREATIONDATETIME > '2025-03-03 15:00:00'";

            // Kondisi khusus untuk item ini cut off di tanggal 2025-03-04
            if ($itemtypecode === 'PCK' &&
                $decosubcode01 === 'TALIRAFIA' &&
                $decosubcode02 === 'LOC' &&
                $decosubcode03 === 'TALIRAFIA' &&
                $decosubcode04 === 'TALIRAFIA') {

                $query_masuk = "SELECT SUM(USERPRIMARYQUANTITY) AS TOTAL
                    FROM STOCKTRANSACTION
                    WHERE (TEMPLATECODE ='OPN' OR TEMPLATECODE ='QC1' OR TEMPLATECODE ='101')
                    AND LOGICALWAREHOUSECODE ='M301'
                    AND ITEMTYPECODE ='$itemtypecode'
                    AND DECOSUBCODE01 ='$decosubcode01'
                    AND DECOSUBCODE02 ='$decosubcode02'
                    AND DECOSUBCODE03 ='$decosubcode03'
                    AND DECOSUBCODE04 ='$decosubcode04'
                    AND DECOSUBCODE05 ='$decosubcode05'
                    AND DECOSUBCODE06 ='$decosubcode06'
                    AND TRANSACTIONDATE < '$awal'
                    AND TRANSACTIONDATE != '2025-03-04'
                    AND CREATIONDATETIME > '2025-03-03 15:00:00'";
            }

            $exec_query_masuk  = db2_exec($conn1, $query_masuk);
            $fetch_query_masuk = db2_fetch_assoc($exec_query_masuk);

            if ($fetch_query_masuk) {
                $total_masuk = (float) $fetch_query_masuk['TOTAL'];
            }

            // Total Keluar Stock
            $query_keluar = "SELECT SUM(USERPRIMARYQUANTITY) AS TOTAL
            FROM STOCKTRANSACTION
            WHERE (TEMPLATECODE ='201')
            AND LOGICALWAREHOUSECODE ='M301'
            AND ITEMTYPECODE ='$itemtypecode'
            AND DECOSUBCODE01 ='$decosubcode01'
            AND DECOSUBCODE02 ='$decosubcode02'
            AND DECOSUBCODE03 ='$decosubcode03'
            AND DECOSUBCODE04 ='$decosubcode04'
            AND DECOSUBCODE05 ='$decosubcode05'
            AND DECOSUBCODE06 ='$decosubcode06'
            AND TRANSACTIONDATE < '$awal'
            AND CREATIONDATETIME > '2025-03-03 15:00:00'";

            $exec_query_keluar  = db2_exec($conn1, $query_keluar);
            $fetch_query_keluar = db2_fetch_assoc($exec_query_keluar);

            if ($fetch_query_keluar) {
                $total_keluar = (float) ($fetch_query_keluar['TOTAL']);
            }

            // Total Masuk Bulanan
            $query_masuk_bulanan = "SELECT SUM(USERPRIMARYQUANTITY) AS TOTAL
            FROM STOCKTRANSACTION
            WHERE (TEMPLATECODE ='OPN' OR TEMPLATECODE ='QC1' OR TEMPLATECODE ='101')
            AND LOGICALWAREHOUSECODE ='M301'
            AND ITEMTYPECODE ='$itemtypecode'
            AND DECOSUBCODE01 ='$decosubcode01'
            AND DECOSUBCODE02 ='$decosubcode02'
            AND DECOSUBCODE03 ='$decosubcode03'
            AND DECOSUBCODE04 ='$decosubcode04'
            AND DECOSUBCODE05 ='$decosubcode05'
            AND DECOSUBCODE06 ='$decosubcode06'
            AND TRANSACTIONDATE BETWEEN '$awal' AND '$akhir'
            AND CREATIONDATETIME > '2025-03-03 15:00:00'";

            // Kondisi khusus untuk item ini cut off di tanggal 2025-03-04
            if ($itemtypecode === 'PCK' &&
                $decosubcode01 === 'TALIRAFIA' &&
                $decosubcode02 === 'LOC' &&
                $decosubcode03 === 'TALIRAFIA' &&
                $decosubcode04 === 'TALIRAFIA') {

                $query_masuk_bulanan = "SELECT SUM(USERPRIMARYQUANTITY) AS TOTAL
                    FROM STOCKTRANSACTION
                    WHERE (TEMPLATECODE ='OPN' OR TEMPLATECODE ='QC1' OR TEMPLATECODE ='101')
                    AND LOGICALWAREHOUSECODE ='M301'
                    AND ITEMTYPECODE ='$itemtypecode'
                    AND DECOSUBCODE01 ='$decosubcode01'
                    AND DECOSUBCODE02 ='$decosubcode02'
                    AND DECOSUBCODE03 ='$decosubcode03'
                    AND DECOSUBCODE04 ='$decosubcode04'
                    AND DECOSUBCODE05 ='$decosubcode05'
                    AND DECOSUBCODE06 ='$decosubcode06'
                    AND TRANSACTIONDATE BETWEEN '$awal' AND '$akhir'
                    AND TRANSACTIONDATE != '2025-03-04'
                    AND CREATIONDATETIME > '2025-03-03 15:00:00'";
            }

            $exec_query_masuk_bulanan  = db2_exec($conn1, $query_masuk_bulanan);
            $fetch_query_masuk_bulanan = db2_fetch_assoc($exec_query_masuk_bulanan);

            if ($fetch_query_masuk_bulanan) {
                $total_masuk_bulanan = (float) $fetch_query_masuk_bulanan['TOTAL'];
            }

            // Total Keluar Bulanan
            $query_keluar_bulanan = "SELECT SUM(USERPRIMARYQUANTITY) AS TOTAL
            FROM STOCKTRANSACTION
            WHERE (TEMPLATECODE ='201')
            AND LOGICALWAREHOUSECODE ='M301'
            AND ITEMTYPECODE ='$itemtypecode'
            AND DECOSUBCODE01 ='$decosubcode01'
            AND DECOSUBCODE02 ='$decosubcode02'
            AND DECOSUBCODE03 ='$decosubcode03'
            AND DECOSUBCODE04 ='$decosubcode04'
            AND DECOSUBCODE05 ='$decosubcode05'
            AND DECOSUBCODE06 ='$decosubcode06'
            AND TRANSACTIONDATE BETWEEN '$awal' AND '$akhir'
            AND CREATIONDATETIME > '2025-03-03 15:00:00'";

            $exec_query_keluar_bulanan  = db2_exec($conn1, $query_keluar_bulanan);
            $fetch_query_keluar_bulanan = db2_fetch_assoc($exec_query_keluar_bulanan);

            if ($fetch_query_keluar_bulanan) {
                $total_keluar_bulanan = (float) ($fetch_query_keluar_bulanan['TOTAL']);
            }

            $stok_awal  = ($stock_awal_db + $total_masuk) - $total_keluar;
            $stok_in    = $total_masuk_bulanan;
            $stok_out   = $total_keluar_bulanan;
            $stok_akhir = ($stok_awal + $stok_in) - $stok_out;

            $this->conn->query("INSERT INTO tbl_opname_now_detail(id_opname,idb,kode,nama,
            jenis,stok_awal,stok_in,stok_out,stok_akhir)
            VALUES ('" . $id_opname . "','" .
                $idb . "','" .
                $kode . "','" .
                $nama . "','" .
                $jenis . "','" .
                $stok_awal . "','" .
                $stok_in . "','" .
                $stok_out . "','" .
                $stok_akhir . "')");
        }
    }

    public function ambilTgl($idsub)
    {
        $query    = $this->conn->query("SELECT tgl_akhir FROM tbl_opname_now WHERE sub_dept='$idsub' ORDER BY id DESC");
        $row      = mysqli_fetch_array($query);
        $tglakhir = $row['tgl_akhir'];
        return $tglakhir;
    }

    public function tampildata($idsub)
    {
        $query = $this->conn->query("SELECT a.id,a.tgl_awal, a.tgl_akhir,
        sum(b.stok_awal) AS stokawal,
        sum(b.stok_in) AS stokin,
        sum(b.stok_out) AS stokout,
        sum(b.stok_akhir) AS stokakhir,
        a.note,a.userid
        FROM tbl_opname_now a
        INNER JOIN tbl_opname_now_detail b ON a.id=b.id_opname
        WHERE a.sub_dept = '$idsub' GROUP BY a.id ORDER BY a.id DESC");

        while ($d = mysqli_fetch_array($query)) {
            $result[] = $d;
        }

        return $result;
    }

    // proses delete data project
    public function hapus($id)
    {
        $this->conn->query("DELETE FROM tbl_opname_now where id='$id'");
        $this->conn->query("DELETE FROM tbl_opname_now_detail where id_opname='$id'");
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

        $query = $this->conn->query("SELECT
          b.idb,
          b.nama,
          b.jenis,
          b.stok_awal,
          b.stok_in,
          b.stok_out,
          b.stok_akhir
        FROM
          tbl_opname_now a
        INNER JOIN tbl_opname_now_detail b ON a.id = b.id_opname
        WHERE
          a.tgl_awal = '$awal'
        AND a.tgl_akhir = '$akhir'
        AND a.sub_dept = '$idsub'
        ORDER BY b.id ASC");

        while ($d = mysqli_fetch_array($query)) {
            $id_barang  = $d['idb'];
            $nama       = $d['nama'];
            $jenis      = $d['jenis'];
            $stok_awal  = $d['stok_awal'];
            $stok_in    = $d['stok_in'];
            $stok_out   = $d['stok_out'];
            $stok_akhir = $d['stok_akhir'];
            $aktual     = 0;

            $sql_master_barang  = $this->conn->query("SELECT * FROM tbl_master_barang where id='$id_barang'");
            $data_master_barang = mysqli_fetch_array($sql_master_barang);

            $itemtypecode  = (String) isset($data_master_barang['ITEMTYPECODE']) ? $data_master_barang['ITEMTYPECODE'] : '';
            $decosubcode01 = (String) isset($data_master_barang['DECOSUBCODE01']) ? $data_master_barang['DECOSUBCODE01'] : '';
            $decosubcode02 = (String) isset($data_master_barang['DECOSUBCODE02']) ? $data_master_barang['DECOSUBCODE02'] : '';
            $decosubcode03 = (String) isset($data_master_barang['DECOSUBCODE03']) ? $data_master_barang['DECOSUBCODE03'] : '';
            $decosubcode04 = (String) isset($data_master_barang['DECOSUBCODE04']) ? $data_master_barang['DECOSUBCODE04'] : '';
            $decosubcode05 = (String) isset($data_master_barang['DECOSUBCODE05']) ? $data_master_barang['DECOSUBCODE05'] : '';
            $decosubcode06 = (String) isset($data_master_barang['DECOSUBCODE06']) ? $data_master_barang['DECOSUBCODE06'] : '';

            // Total Stock Balance
            $query_balance = "SELECT SUM(BASEPRIMARYQUANTITYUNIT) AS TOTAL
            FROM BALANCE
            WHERE LOGICALWAREHOUSECODE ='M301'
            AND ITEMTYPECODE ='$itemtypecode'
            AND DECOSUBCODE01 ='$decosubcode01'
            AND DECOSUBCODE02 ='$decosubcode02'
            AND DECOSUBCODE03 ='$decosubcode03'
            AND DECOSUBCODE04 ='$decosubcode04'
            AND DECOSUBCODE05 ='$decosubcode05'
            AND DECOSUBCODE06 ='$decosubcode06'";

            $exec_query_balance  = db2_exec($conn1, $query_balance);
            $fetch_query_balance = db2_fetch_assoc($exec_query_balance);

            if ($fetch_query_balance) {
                $aktual = (float) $fetch_query_balance['TOTAL'];
            }

            $result[] = [
                'nama'       => $nama,
                'jenis'      => $jenis,
                'stok_awal'  => $stok_awal,
                'stok_in'    => $stok_in,
                'stok_out'   => $stok_out,
                'stok_akhir' => $stok_akhir,
                'aktual'     => $aktual,
            ];
        }
        return $result;
    }
}
