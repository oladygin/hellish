<?php

//
// класс БД: skillclasses
//
class MSkillClass extends CDB {
    
    // данные схемы
    var $scls_id;
    var $scls_title;
    var $scls_descr;
    var $scls_order;
    
    var $m_CategoryList;
    
    var $isActive;  // активно на экране
    
    function Get ($where = null) { return $this->Single('skillclasses', $this, is_array($where) ? $where : array('scls_id' => $where)); }
    function Create () { return $this->Insert('skillclasses', false, $this); }
    function Set () { return $this->Update('skillclasses', 'scls_id', $this); }
    
    function LoadFromRow ($row)
    {
        $this->scls_id = @$row['scls_id'];
        $this->scls_title = @$row['scls_title'];
        $this->scls_descr = @$row['scls_descr'];
        $this->scls_order = @$row['scls_order'];
    }

}

?>
