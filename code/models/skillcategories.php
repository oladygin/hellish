<?php

//
// класс БД: skillcategories
//
class MSkillCategory extends CDB {
    
    // данные схемы
    var $scat_id;
    var $scat_title;
    var $scat_descr;
    
    function Get ($where = null) { return $this->Single('skillcategories', $this, is_array($where) ? $where : array('scat_id' => $where)); }
    function Create () { return $this->Insert('skillcategories', null, $this); }
    function Set () { return $this->Update('skillcategories', 'scat_id', $this); }
    
    function LoadFromRow ($row)
    {
        $this->scat_id = @$row['scat_id'];
        $this->scat_title = @$row['scat_title'];
        $this->scat_descr = @$row['scat_descr'];
    }

}

?>
