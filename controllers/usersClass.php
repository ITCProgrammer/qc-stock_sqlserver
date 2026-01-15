<?php
session_start();
class User extends Database
{
    private $conn;
    private $connSqlSrv;
    public function __construct()
    {
        // $this->conn = $this->connectMySQLi();
        $this->connSqlSrv = $this->connectSQLServer();
    }
    // Proses Login
    public function cek_login($username, $password, $sub)
    {
        $password = md5($password);
        $sql = ("SELECT * FROM invqc.tbl_user WHERE username= ? AND password=?");
        $params = array($username, $password);
        $stmt = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // Ambil satu baris data user
        $user_data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        // Hitung jumlah baris
        $no_rows = ($user_data !== null) ? 1 : 0;
        if ($user_data['level'] == 1 or $user_data['sub_dept'] == $sub) {
            $role = 1;
        } else {
            $role = 0;
        }

        if ($no_rows == 1 and $role == 1) {
            $_SESSION['loginQC']       = true;
            $_SESSION['idQC']           = $user_data['id'];
            $_SESSION['userQC']        = $username;
            $_SESSION['passQC']        = $password;
            $_SESSION['fotoQC']        = $user_data['foto'];
            $_SESSION['jabatanQC']    = $user_data['jabatan'];
            $_SESSION['mamberQC']      = $user_data['mamber'];
            $_SESSION['lvlQC']      = $user_data['level'];
            $_SESSION['subQC']      = $sub;
            return true;
        } else {
            return false;
        }
    }

    // Ambil Sesi
    public function get_sesi()
    {
        return $_SESSION['loginQC'];
    }


    // Logout
    public function user_logout()
    {
        $_SESSION['loginQC'] = false;
        session_destroy();
    }

    // ambil nama
    public function ambilNama($id)
    {
        $sql =  "SELECT * FROM invqc.tbl_user WHERE id=?";
        $params = array($id);
        $row = sqlsrv_query($this->connSqlSrv, $sql, $params);
        $user = sqlsrv_fetch_array($row, SQLSRV_FETCH_ASSOC);
        echo ucwords($user['username']);
    }

    // tampilkan data dari tabel users
    public function tampil_data()
    {
        $sql = "SELECT * FROM invqc.tbl_user";
        $query = sqlsrv_query($this->connSqlSrv, $sql);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $result = [];

        while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }

    // proses input data user
    public function input_user($username, $pwd, $level, $status, $mamber, $jabatan, $idsub)
    {
        // $sql     = "INSERT INTO tbl_user (username,password,level,status,mamber,jabatan,foto,sub_dept)
        // VALUES ('$username','$pwd','$level','$status','$mamber','$jabatan','avatar.png','$idsub')";
        // $this->conn->query($sql);

        $sql = "
        INSERT INTO invqc.tbl_user (
            username,
            password,
            level,
            status,
            mamber,
            jabatan,
            foto,
            sub_dept
        )
        VALUES (
            ?, ?, ?, ?, ?, ?, 'avatar.png', ?
        )
    ";

        $params = [
            $username,
            $pwd,
            $level,
            $status,
            $mamber,
            $jabatan,
            $idsub
        ];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }

    // tampilkan data dari tabel users yang akan di edit
    public function edit_user($id)
    {
        // $data = $this->conn->query("SELECT * FROM tbl_user WHERE id='$id'");
        // while ($x = mysqli_fetch_array($data)) {
        //     $hasil[] = $x;
        // }
        // return $hasil;

        $sql = "SELECT * FROM invqc.tbl_user WHERE id = ?";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC) ?: null;
    }

    // proses update data user
    public function update_user($id, $username, $pwd, $level, $status, $mamber, $jabatan, $idsub)
    {
        // $this->conn->query("UPDATE tbl_user SET
        // username='$username',
        // password='$pwd',
        // level='$level',
        // status='$status',
        // mamber='$mamber',
        // jabatan='$jabatan',
        // sub_dept='$idsub'
        // WHERE id='$id'");

        $sql = "
        UPDATE invqc.tbl_user
        SET
            username = ?,
            password = ?,
            level    = ?,
            status   = ?,
            mamber   = ?,
            jabatan  = ?,
            sub_dept = ?
        WHERE id = ?
    ";

        $params = [
            $username,
            $pwd,
            $level,
            $status,
            $mamber,
            $jabatan,
            $idsub,
            $id
        ];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
    // proses delete data project
    public function hapus_user($id)
    {
        // $this->conn->query("DELETE FROM tbl_user where id='$id'");

        $sql = "DELETE FROM invqc.tbl_user WHERE id = ?";

        $params = [$id];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
    // proses change password
    public function change_password($id, $pwd)
    {
        // $this->conn->query("UPDATE tbl_user SET
        // password='$pwd'
        // WHERE id='$id'");

        $sql = "
        UPDATE invqc.tbl_user
        SET password = ?
        WHERE id = ?
    ";

        $params = [
            $pwd, 
            $id
        ];

        $query = sqlsrv_query($this->connSqlSrv, $sql, $params);

        if ($query === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return true;
    }
}
