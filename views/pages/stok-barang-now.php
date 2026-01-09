<?PHP
error_reporting(0);
session_start();
// instance objek
// $barang = new Barang();
$db = new Database();
$stockNow = new StockNow();



//$idsub =$_SESSION['subQC'];
$cek = $barang->jmlStock($idsub);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Barang</title>
  <style>
    .blink_me {
      animation: blinker 1s linear infinite;
    }

    .blink_me1 {
      animation: blinker 7s linear infinite;
    }
  </style>
</head>

<body>
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <!-- <div class="box-header">
  <a href="#" data-toggle="modal" data-target="#DataStock" class="btn btn-success <?php if ($_SESSION['lvlQC'] == 3) {
                                                                                    echo "disabled";
                                                                                  } ?>"><i class="fa fa-plus-circle"></i> Add</a>
  <?php if ($cek > 0) { ?><a href="cetak/lapbarang/<?php echo $_SESSION['subQC']; ?>/" class="btn btn-danger pull-right" target="_blank">Cetak</a><?php } ?>
</div> -->
        <div class="box-body">
          <table width="100%" id="example1" class="table table-bordered table-hover">
            <thead class="btn-success">
              <tr>
                <th width="2%">No</th>
                <!-- <th width="9%">Kode</th> -->
                <th width="24%">Nama</th>
                <th width="12%">Jenis</th>
                <!-- <th width="9%">Harga</th> -->
                <th width="9%">Sisa</th>
                <th width="8%">Satuan</th>
                <th width="8%">Min</th>
                <th width="8%">Min-A</th>
                <th width="10%">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $col = 0;
              $no = 1;
              // $allQueries = [];
              foreach ($stockNow->LihatDataNow() as $rowd) {
                $bgcolor = ($col++ & 1) ? 'gainsboro' : 'antiquewhite';
                $sisa = $stockNow->getLastBalanceByBarang($rowd);
                // Pastikan $sisa numerik untuk perbandingan
                $sisaNum = is_numeric($sisa) ? floatval($sisa) : 0;
                $min = floatval($rowd['MIN']);
                $mina = floatval($rowd['MINA']);
                if ($sisaNum >= $min && $sisaNum <= $mina) {
                  $stt = "YA1";
                } else if ($sisaNum < $min) {
                  $stt = "YA";
                } else {
                  $stt = "TIDAK";
                }
                // Kumpulkan semua query
                // $allQueries[] = $stockNow->getLastBalanceByBarangQuery($rowd);
              ?>
                <tr bgcolor="<?php echo $bgcolor; ?>">
                  <td><?php echo $no; ?></td>
                  <td align="left"><?php echo $rowd['DESCRIPTION']; ?></td>
                  <td align="center"><?php echo $rowd['JENIS']; ?><?php if ($stt == "YA") { ?><br><i class='fa fa-warning text-yellow  blink_me'></i> <span class='label label-danger'>Harus Ditambah</span><?php } ?><?php if ($stt == "YA1") { ?><br><i class='fa fa-warning text-yellow  blink_me'></i> <span class='label label-warning'>Stok Hampir Habis</span><?php } ?></td>
                  <!-- <td align="right"><?php echo $rowd['harga']; ?></td> -->
                    <td align="right"><?php echo is_numeric($sisa) ? number_format($sisa, 0) : $sisa; ?></td>
                  <td align="right"><?php echo $rowd['UNITOFMEASURE']; ?></td>
                  <td align="right"><?php echo $rowd['MIN']; ?></td>
                  <td align="right"><?php echo $rowd['MINA']; ?></td>
                  <td align="center">
                    <div class="btn-group">
                      <a href="#" class="btn btn-info btn-sm" id="<?php echo $rowd['id']; ?>" data-toggle="modal" data-target="#MIN-AND-MINA-<?php echo $rowd['id']; ?>" value="<?php echo $rowd['id']; ?>"><i class="fa fa-edit"></i> </a>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="MIN-AND-MINA-<?php echo $rowd['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="minMinaLabel<?php echo $rowd['id']; ?>" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <form method="post" action="update-min-mina/" enctype="multipart/form-data">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="minMinaLabel<?php echo $rowd['id']; ?>">Edit Min & Min-A</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <input type="hidden" name="id" value="<?php echo $rowd['id']; ?>">
                              <div class="form-group">
                                <label for="min_<?php echo $rowd['id']; ?>">Min</label>
                                <input type="number" class="form-control" id="MIN<?php echo $rowd['id']; ?>" name="MIN" value="<?php echo $rowd['MIN']; ?>" required>
                              </div>
                              <div class="form-group">
                                <label for="mina_<?php echo $rowd['id']; ?>">Min-A</label>
                                <input type="number" class="form-control" id="MINA<?php echo $rowd['id']; ?>" name="MINA" value="<?php echo $rowd['MINA']; ?>" required>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                              <button type="submit" class="btn btn-primary" name="UpdateMinMina">Simpan</button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                </tr>
              <?php
                $no++;
              } ?>
            </tbody>
          </table>
          <?php
          // Tampilkan semua query di bawah tabel
          // if (!empty($allQueries)) {
          //   echo '<h4>Query yang dieksekusi untuk semua data:</h4>';
          //   echo '<pre>';
          //   foreach ($allQueries as $q) {
          //     echo htmlspecialchars($q) . "\n\n";
          //   }
          //   echo '</pre>';
          // }
          ?>


</html>