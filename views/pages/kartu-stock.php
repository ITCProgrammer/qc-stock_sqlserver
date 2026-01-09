<?php
    error_reporting(0);
    session_start();

    $barangNow = new BarangNow();
    $db        = new Database();
    $idsub     = $_SESSION['subQC'];

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Kartu Stock</title>
  </head>

  <body>
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"> Filter Data Kartu Stock</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
      </div>
      <!-- /.box-header -->
      <!-- form start -->
      <form name="form1" class="form-horizontal" id="form1">
        <div class="box-body">
          <input type="hidden" name="idsub" value="<?php echo $_SESSION['subQC']; ?>">
          <input type="hidden" name="userid" value="<?php echo $_SESSION['userQC']; ?>">

          <div class="form-group">
            <div class="col-sm-3">
                <?php
                    $barangNow->renderBarangSelect();
                ?>
            </div>
          </div>

          <div class="form-group">
            <div class="col-sm-3">
              <div class="input-group date">
                <div class="input-group-addon"> <i class="fa fa-calendar"></i> </div>
                <input name="tanggal_awal" type="text" class="form-control pull-right" id="datepicker" placeholder="Tanggal Awal"  autocomplete="off" required/>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <div class="input-group date">
                <div class="input-group-addon"> <i class="fa fa-calendar"></i> </div>
                <input name="tanggal_akhir" type="text" class="form-control pull-right" id="datepicker1" placeholder="Tanggal Akhir"  autocomplete="off" required/>
              </div>
            </div>
          </div>
          <div class="form-group">
          <div class="col-sm-3">
            <button type="submit" class="btn btn-info" onclick="submitForm()">Cari</button>
          </div>
        </div>
      </div>
      </form>
    </div>
  </body>
</html>

<script>
    function submitForm() {
        var idBarang = document.getElementById("nama_barang").value;
        var tanggalAwal = document.getElementById("datepicker").value;
        var tanggalAkhir = document.getElementById("datepicker1").value;

        if (tanggalAwal === "") {
            alert("Tanggal awal tidak boleh kosong!");
            return;
        }
        if (tanggalAkhir === "") {
            alert("Tanggal akhir tidak boleh kosong!");
            return;
        }

        if (tanggalAkhir < tanggalAwal) {
            alert("Tanggal akhir tidak boleh lebih kecil dari tanggal awal!");
            return;
        }

        var actionPage = (idBarang === "all") ? "views/pages/cetak/cetak_laporan_stock_all.php" : "views/pages/cetak/cetak_kartu_stock.php";
        var url = actionPage + "?id_barang=" + idBarang + "&tanggal_awal=" + tanggalAwal + "&tanggal_akhir=" + tanggalAkhir;

        window.open(url, '_blank');
    }
</script>
