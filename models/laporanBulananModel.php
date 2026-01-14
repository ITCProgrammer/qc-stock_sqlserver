<?php
include_once '../config/koneksi.php';
include_once '../controllers/laporanBulananNowClass.php';
// instance objek
$laporanBulananNow = new LaporanBulananNow();
$db                = new Database();
// koneksi ke MySQL via method
$db->connectMySQLi();

$page = $_GET['page'];
// ## OPNAME
if ($page == "input-laporan-bulanan") {
    $idsub  = $_POST['idsub'];
    $awal   = $_POST['awal'];
    $akhir  = $_POST['akhir'];
    $note   = str_replace("'", "''", $_POST['note']);
    $userid = $_POST['userid'];
    $laporanBulananNow->input($idsub, $awal, $akhir, $note, $userid);
    header("location:../laporan-bulanan-now");
}
// ## OPNAME-DELETE
elseif ($page == "hapus-laporan-bulanan") {
    $laporanBulananNow->hapus((int)$_GET['id']);
    header("location:../laporan-bulanan-now");
}
