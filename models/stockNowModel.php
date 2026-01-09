<?php
include_once '../config/koneksi.php';
include_once '../controllers/StockNowClass.php';

$stockNow = new StockNow();
$db = new Database();
$db->connectMySQLi();
$page = $_GET['page'];


if ($page == "update-min-mina") {
    $stockNow->UpdateMinMina($_POST['id'], $_POST['MIN'], $_POST['MINA']);
    header("Location: ../stok-barang-now");
    exit;
}
