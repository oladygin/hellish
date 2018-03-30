<?php

//
// класс БД: userskills
//
class MUserSkill extends CDB {
    
    // данные схемы
    var $uskl_user_id;
    var $uskl_skil_id;
    var $uskl_value;
    var $uskl_is_expert;
    var $uskl_is_specialization;
    var $uskl_is_main_specialization;
    var $uskl_submit_date;
    
    function Create () { return $this->Insert('userskills', null, $this); }
    function Set () { $key = array('uskl_user_id' => $this->uskl_user_id, 'uskl_skil_id' => $this->uskl_skil_id); return $this->Update('userskills', $key, $this); }
    function Erase () { $key = array('uskl_user_id' => $this->uskl_user_id, 'uskl_skil_id' => $this->uskl_skil_id); return $this->Delete('userskills', $key, $this); }
    
    function LoadFromRow ($row)
    {
        $this->uskl_user_id = @$row['uskl_user_id'];
        $this->uskl_skil_id = @$row['uskl_skil_id'];
        $this->uskl_value = @$row['uskl_value'];
        $this->uskl_is_expert = @$row['uskl_is_expert'];
        $this->uskl_is_specialization = @$row['uskl_is_specialization'];
        $this->uskl_is_main_specialization = @$row['uskl_is_main_specialization'];
        $this->uskl_submit_date = @$row['uskl_submit_date'];
    }

}

?>
