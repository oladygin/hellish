<?php

//
// класс БД: skills
//
class MSkill extends CDB {
    
    // данные схемы
    var $skil_id;
    var $skil_scls_id;
    var $skil_title;
    var $skil_descr;
    var $skil_scaletype;
    var $skil_is_specialization;
    
    // дополнительно, не схема
    var $category;
    var $userskill;
    var $class;
    
    function Get ($where = null) { return $this->Single('skills', $this, is_array($where) ? $where : array('skil_id' => $where)); }
    function Create () { return $this->Insert('skills', null, $this); }
    function Set () { return $this->Update('skills', 'skil_id', $this); }
    
    function LoadFromRow ($row)
    {
        $this->skil_id = @$row['skil_id'];
        $this->skil_scls_id = @$row['skil_scls_id'];
        $this->skil_title = @$row['skil_title'];
        $this->skil_descr = @$row['skil_descr'];
        $this->skil_scaletype = @$row['skil_scaletype'];
        $this->skil_is_specialization = @$row['skil_is_specialization'];
        
        $this->category = array(    
            'id' => @$row['scat_id'],
            'title' => @$row['scat_title']
        );
        $this->class = array(    
            'id' => @$row['scls_id'],
            'title' => @$row['scls_title']
        );
        $this->userskill = array(    
            'value' => @$row['uskl_value'],
            'is_specialization' => @$row['uskl_is_specialization'],
            'is_main_specialization' => @$row['uskl_is_main_specialization'],
            'is_any_spec' => (@$row['uskl_is_specialization'] || @$row['uskl_is_main_specialization']),
            'specpos' => 0
        );
        if($this->userskill['is_main_specialization']) $this->userskill['specpos'] = 1;
        else if($this->userskill['is_specialization']) $this->userskill['specpos'] = $this->userskill['is_specialization'];
    }

}

?>
