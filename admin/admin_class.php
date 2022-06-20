<?php
if(!isset($_SESSION)) 
{ 
   session_start(); 
}  
ini_set('display_errors', 1);
class Action
{
    private $db;

    public function __construct()
    {
        ob_start();
        include '../dbConfig.php';

        $this->db = $db;
    }
    function __destruct()
    {
        $this
            ->db
            ->close();
        ob_end_flush();
    }

    function login()
    {
        extract($_POST);
        $qry = $this
            ->db
            ->query("SELECT * FROM users where username = '" . $username . "' and password = '" . md5($password) . "' ");
        if ($qry->num_rows > 0)
        {
            foreach ($qry->fetch_array() as $key => $value)
            { 
                if ($key != 'passwors' && !is_numeric($key)) $_SESSION['login_' . $key] = $value;
                $userdata[$key] = $value;
            }
            if($userdata['type'] == 3){
                return 2;
            }
            return 1;
        }
        else
        {
            return 3;
        }
    }
    function logout()
    {
        session_destroy();
        foreach ($_SESSION as $key => $value)
        {
            unset($_SESSION[$key]);
        }
        header("location:login.php");
    }

    function save_user()
    {
        extract($_POST);
        $data = " name = '$name' ";
        $data .= ", username = '$username' ";
        if (!empty($password)) $data .= ", password = '" . md5($password) . "' ";
        $data .= ", type = '$type' ";
        $data .= ", social_account_type = '$social_account_type' ";
        $chk = $this
            ->db
            ->query("Select * from users where username = '$username' and id !='$id' ")->num_rows;
        if ($chk > 0)
        {
            return 2;
            exit;
        }
        if (empty($id))
        {
            $save = $this
                ->db
                ->query("INSERT INTO users set " . $data);
        }
        else
        {
            $save = $this
                ->db
                ->query("UPDATE users set " . $data . " where id = " . $id);
        }
        if ($save)
        {
            return 1;
        }
    }
    function delete_user()
    { 
        extract($_POST);
        $delete = $this
            ->db
            ->query("DELETE FROM users where id = " . $id);
        if ($delete) return 1;
    }


    function signup()
    {
        extract($_POST);
        $data = " name = '$name' ";
        $data .= ", contact = '$contact' ";
        $data .= ", address = '$address' ";
        $data .= ", username = '$email' ";
        $data .= ", password = '" . md5($password) . "' ";
        $data .= ", type = 3";
        $chk = $this
            ->db
            ->query("SELECT * FROM users where username = '$email' ")->num_rows;
        if ($chk > 0)
        {
            return 2;
            exit;
        }
        $save = $this
            ->db
            ->query("INSERT INTO users set " . $data);
        if ($save)
        {
            $qry = $this
                ->db
                ->query("SELECT * FROM users where username = '" . $email . "' and password = '" . md5($password) . "' ");
            if ($qry->num_rows > 0)
            {
                foreach ($qry->fetch_array() as $key => $value)
                {
                    if ($key != 'passwors' && !is_numeric($key)) $_SESSION['login_' . $key] = $value;
                }
            }
            return 1;
        }
    }

    function save_timeslot(){
        extract($_POST);
        $data =  " time_diff = '$time_diff' ";
        
        // echo "INSERT INTO system_settings set ".$data;
        $chk = $this->db->query("SELECT * FROM timeslot");
        if($chk->num_rows > 0){
            $save = $this->db->query("UPDATE timeslot set ".$data);
        }else{
            $save = $this->db->query("INSERT INTO timeslot set ".$data);
        }
        if($save){
        $query = $this->db->query("SELECT * FROM timeslot limit 1")->fetch_array();
        foreach ($query as $key => $value) {
            if(!is_numeric($key))
                $_SESSION['setting_'.$key] = $value;
        }

            return 1;
                }
    }

}

