<?php

//
// класс БД: companies
//
class MCompany extends CDB {
    
    // данные схемы
    var $comp_id;
    var $comp_title;
    
    // дополнительно, не схема
    var $m_Userlist;
    
    function Get ($where = null) { return $this->Single('companies', $this, is_array($where) ? $where : array('comp_id' => $where)); }
    function Create () { return $this->Insert('companies', null, $this); }
    function Set () { return $this->Update('companies', 'comp_id', $this); }
    
    function LoadFromRow ($row)
    {
        $this->comp_id = @$row['comp_id'];
        $this->comp_title = @$row['comp_title'];

        $this->m_Userlist = array();
    }

}

?>
