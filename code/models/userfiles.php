<?php

define('UFILTYPE_RESUME',1);
define('UFILTYPE_AVATAR',2);

//
// класс БД: userfiles
//
class MUserfile extends CDB {
    
    // данные схемы
    var $ufil_id;
    var $ufil_user_id;
    var $ufil_type;
    var $ufil_filename;
    var $ufil_name;
    var $ufil_isactive;
    var $ufil_submit_date;
    
    function Get ($where = null) { return $this->Single('userfiles', $this, is_array($where) ? $where : array('ufil_id' => $where)); }
    function Create () { return $this->Insert('userfiles', 'ufil_id', $this); }
    function Set () { return $this->Update('userfiles', 'ufil_id', $this); }
    
    function LoadFromRow ($row)
    {
        $this->ufil_id = @$row['ufil_id'];
        $this->ufil_user_id = @$row['ufil_user_id'];
        $this->ufil_type = @$row['ufil_type'];
        $this->ufil_filename = @$row['ufil_filename'];
        $this->ufil_name = @$row['ufil_name'];
        $this->ufil_submit_date = @$row['ufil_submit_date'];
    }

}

?>