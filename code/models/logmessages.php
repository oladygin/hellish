<?php

//
// класс БД: logmessages
//
class MLogMessage extends CDB {
    
    // данные схемы
    var $logm_id;
    var $logm_source;
    var $logm_level;
    var $logm_submit_date;
    var $logm_user_id;
    var $logm_function;
    var $logm_message;
    var $logm_data;
    
    var $d_logm_submit_date;
    
    // дополнительно, не схема
    var $m_user;
    
    function Get ($where = null) { return $this->Single('logmessages', $this, is_array($where) ? $where : array('logm_id' => $where)); }
    function Create () { return $this->Insert('logmessages', null, $this); }
    function Set () { return $this->Update('logmessages', 'logm_id', $this); }
    
    function LoadFromRow ($row)
    {
        $this->logm_id = @$row['logm_id'];
        $this->logm_source = @$row['logm_source'];
        $this->logm_level = @$row['logm_level'];
        $this->logm_submit_date = @$row['logm_submit_date'];
        $this->logm_user_id = @$row['logm_user_id'];
        $this->logm_function = @$row['logm_function'];
        $this->logm_message = @$row['logm_message'];
        $this->logm_data = @$row['logm_data'];

        $this->d_logm_submit_date = $this->from_timevalue($this->logm_submit_date);
        
        if($this->logm_user_id) {
            $this->m_user = new MUser();
            $this->m_user->LoadFromRow($row);
        }
    }

    function GetFunction ()
    {
        $f = str_replace('Controller->', '::', $this->logm_function);
        return $f;
    }
    
    function GetSearchTitle ()
    {
        $name = $this->logm_message . ' ';
        if($this->m_user) $name .= $this->m_user->GetName();
        
        $name = mb_strtolower($name);
        
        return $name;
    }


}

?>
