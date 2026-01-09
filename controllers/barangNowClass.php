<?php
class BarangNow extends Database
{
    public function __construct()
    {
        $this->conn = $this->connectMySQLi();
    }

    public function getBarangList()
    {
        $query  = "SELECT id, DESCRIPTION FROM tbl_master_barang";
        $result = $this->conn->query($query);

        return $result;
    }

    public function renderBarangSelect($selectedBarangID = null)
    {
        $barangList = $this->getBarangList();
        echo '<select id="nama_barang" name="nama_barang" class="form-control" required>';
        echo '<option value="" selected>Pilih Barang</option>';

        while ($row = $barangList->fetch_assoc()) {
            $selected = ($selectedBarangID == $row['id']) ? 'selected' : '';
            echo '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['DESCRIPTION']) . '</option>';
        }

        echo '</select>';
    }
}
