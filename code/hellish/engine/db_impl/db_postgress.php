<?php

class HellishDB {

    var $m_Logger;
    static $p_Instance;
    
    var $dbh;           
    var $s_last_error;
    var $n_last_id;
    var $n_affected;
    var $is_transaction;
  
    function Connect () {    
        $cred = HApplication::$p_Instance->m_Params['db'];
        self::$p_Instance = $this;
        $conn_string = "host=$cred[host] port=$cred[port] dbname=$cred[db] user=$cred[login] password=$cred[password]";
        self::$p_Instance->dbh = pg_pconnect ($conn_string);
        if (!self::$p_Instance->dbh) {
            self::$p_Instance->s_last_error = 'NO_CONNECT TO DB';
            self::$p_Instance->m_Logger->write (HLOG_FATAL, "Failed connect to <b>$cred[host]:$cred[port] => $cred[db]</b>");
            return false;
        }
        self::$p_Instance->is_transaction = false;
        self::$p_Instance->s_last_error = 'CONNECTED';
        $this->m_Logger->write (HLOG_DEBUG, "Connected to <b>$cred[host]:$cred[port] => $cred[db]</b>");
        return true;                                                      
    }
    
    function Disconnect () {
        pg_close (self::$p_Instance->dbh);
    }
    
    function SetErrorText ()
    {
        self::$p_Instance->s_last_error = pg_last_error (self::$p_Instance->dbh);
        return false;
    }

    function GetAffected ()
    {
        return self::$p_Instance->n_affected;
    }

    function GetErrorText ()
    {
        $s_err = pg_last_error (self::$p_Instance->dbh);
        if ($s_err) self::$p_Instance->s_last_error = $s_err;
        return self::$p_Instance->s_last_error;
    }
    
    function Select ($s_select, $m_binds = false, $isById = false) 
    {
        self::$p_Instance->s_last_error = false;
        $m_data = array ();

        if (is_array ($m_binds)) {
            $cnt = 0;
            $m_params = array ();
            $s_bind_select = $s_select;
            foreach ($m_binds as $k => $v) {
                $cnt++;
                $s_bind_select = str_replace ($k, '$'.$cnt, $s_bind_select);
                $m_params[] = $v;
            }
            $result = pg_query_params (self::$p_Instance->dbh, $s_bind_select, $m_params);
            if (!$result) {
                HApplication::$p_Instance->m_Logger->write (HLOG_ERROR, 'Select failed: ' . $this->GetErrorText());
                $this->SetErrorText ();
                return false;
            }
        } else {
            $result = pg_query (self::$p_Instance->dbh, $s_select);
            if (!$result) {
                HApplication::$p_Instance->m_Logger->write (HLOG_ERROR, 'Select failed: ' . $this->GetErrorText());
                $this->SetErrorText ();
                return false;
            }
        }
        $rows = pg_num_rows ($result);
        if ($rows > 0) {
            for ($i = 0; $i < $rows; $i ++)  {
                $row = pg_fetch_array ($result, $i, PGSQL_ASSOC);
                if($isById===true) $m_data[$row['id']] = $row;
                else if($isById==='list') $m_data[$row['id']][] = $row;
                else $m_data[] = $row;
            }
        } 
        
        HApplication::$p_Instance->m_Logger->write (HLOG_DEBUG, 'Selected  ' . count($rows) . ' rows');
        
        return $m_data;
    }

    
    function to_timevalue($time)
    {
        $t = date ('Y-m-d H:i:s', $time);
        return $t;
    }

    function from_timevalue($timestring)
    {
        list($year, $month, $day, $h, $m, $s) = sscanf($timestring, "%d-%d-%d %d:%d:%d");
        $t = mktime($h, $m, $s, $month, $day, $year);
        return $t;
    }

    function GetLastID ()
    {
         return self::$p_Instance->n_last_id;
    }
    
    function Commit ()
    {
        self::$p_Instance->s_last_error = false;
        self::$p_Instance->is_transaction = false;
        if (!pg_query (self::$p_Instance->dbh, "COMMIT")) {
            $this->SetErrorText();
            HApplication::$p_Instance->m_Logger->write (HLOG_ERROR, "Commit failed: ".$this->GetErrorText());
        } else {
            HApplication::$p_Instance->m_Logger->write (HLOG_DEBUG, "Commited");
            return true;
        }
    }
                                                                                                                   
    function Execute ($s_select, $m_binds = false, $b_autocommit=false) 
    {
        self::$p_Instance->n_last_id = 0;
        self::$p_Instance->s_last_error = false;
        if (!self::$p_Instance->is_transaction) pg_query (self::$p_Instance->dbh, "BEGIN WORK "); 
        self::$p_Instance->is_transaction = true;
        {
            $cnt = 0;
            $m_params = array ();
            $s_bind_select = $s_select;
            if (is_array ($m_binds)) {
                foreach ($m_binds as $k => $v) {
                    $cnt++;
                    $s_bind_select = str_replace ($k, '$'.$cnt, $s_bind_select);
                    $m_params[] = $v;
                }
            }
            $result = @pg_prepare (self::$p_Instance->dbh, null, $s_bind_select);
            if (!$result) {
                //self::$p_Instance->SetErrorText();
                //self::$p_Instance->m_Logger->write (HLOG_ERROR, "Execute failed: ".$this->GetErrorText());
                HApplication::$p_Instance->m_Logger->write (HLOG_ERROR, "Execute failed: ".$this->GetErrorText());
                return false;
            }
            $result = pg_execute(self::$p_Instance->dbh, null, $m_params);
            if (!$result) {
                HApplication::$p_Instance->m_DB->SetErrorText();
                HApplication::$p_Instance->m_Logger->write (HLOG_ERROR, "Execute failed: ".$this->GetErrorText());
                return false;
            }
            $insert_row = pg_fetch_row ($result);
            self::$p_Instance->n_affected = pg_affected_rows($result);
            self::$p_Instance->n_last_id = $insert_row[0];            
        }
        //$self->n_last_id = $self->dbh->lastInsertId ();
        if ($b_autocommit) {
            return $this->Commit();
        }
        HApplication::$p_Instance->m_Logger->write (HLOG_DEBUG, "Execute done");
        return true;
    }
    
    // Выбрать 1 запись прямо в класс
    public function Single ($s_tablename, &$m_object, $where = null)
    {
        $m_results = $this->loadDataFromDB ($s_tablename, $where);
        if (count ($m_results) >= 1) 
        {
            $m_object = $this->Fill($m_object, $m_results[0]);
            /*
            $row = $m_results[0];
            foreach ($m_object as $poco_key => $cur_value) 
            {
                // Ставим обычное свойство
                if($this->isPOCO($poco_key)) $m_object->{$poco_key} = $row[$poco_key];
            }
            */
            return true;
        }
        return false;
    }
    
    // Выбрать таблицу прямо в классы
    public function Table ($s_tablename, $class_name, $key = null, $where = null, $orders = null)
    {
        $m_results = $this->loadDataFromDB ($s_tablename, $where, $orders);
        $m_object_list = array();
        foreach($m_results as $row)
        {
            //$m_object = new $class_name();
            $m_object = $this->Fill($class_name, $row);
            if ($key) $m_object_list[$m_object->{$key}] = $m_object;
                 else $m_object_list[] = $m_object;
        }
        return $m_object_list;
    }

    // заполнить свойства объекта данным из БД (передается готовый объект или его строковое назвнаиеs)
    public function Fill ($m_object, $db_row)
    {
        if (is_string($m_object)) $m_object = new $m_object();
        
        foreach ($m_object as $poco_key => $cur_value) 
        {
            // Ставим обычное свойство
            if($this->isPOCO($poco_key)) $m_object->{$poco_key} = $db_row[$poco_key];
        }
        
        return $m_object;
    }
    
    // Является для свойство объекта POCO_свойством
    public function isPOCO ($varname, $key_name = false)
    {
        if(!is_array($key_name))
        {
            if ($key_name)
                return (mb_substr($varname, 4, 1) == '_' && mb_substr($varname, 0, 4) == mb_substr($key_name, 0, 4));
            else 
                return (mb_substr($varname, 4, 1) == '_');
        } 
        return (mb_substr($varname, 4, 1) == '_');
    }
    
    public function loadDataFromDB ($s_tablename, $where, $orders = null)
    {
        $m_binds = array ();
        $s_select = 'SELECT * FROM '.$s_tablename;
        // Составляет условия выборки
        if (is_array($where)) 
        {
            $s_select .= ' WHERE ';
            $counter = 0;
            foreach ($where as $k => $v) 
            {
                if ($counter > 0) $s_select .= ' AND ';
                if (is_array($v)) {
                    $v_string = join(',', $v);
                    $s_select .= $k.' in (:p_param_'.$counter.')';
                    $m_binds[':p_param_'.$counter] = $v_string;
                } else {
                    $s_select .= $k.'=:p_param_'.$counter;
                    $m_binds[':p_param_'.$counter] = $v;
                }
                $counter ++;
            }
        }
        // orders
        if (is_array($orders)) 
        {
            $s_select .= ' ORDER BY ';
            $counter = 0;
            foreach ($orders as $k) 
            {
                if ($counter > 0) $s_select .= ', ';
                $s_select .= $k;
                $counter ++;
            }
        }
        return $this->Select ($s_select, $m_binds);
    }

    // Вставить 1 запись прямо в БД
    public function Insert ($s_tablename, $s_keyname, $m_object)
    {
        $m_binds = array ();
        if ($s_keyname) $s_select = 'INSERT INTO '.$s_tablename.' (' . $s_keyname . '';
            else $s_select = 'INSERT INTO '.$s_tablename.' (';
        // формируем список полей NOT NULL
        $s_values = '';
        $counter = 0;
        $m_binds = array (
        );
        foreach ($m_object as $poco_key => $cur_value) 
        {
            if ($poco_key != $s_keyname && $cur_value != null) 
            {
                if($this->isPOCO($poco_key, $s_keyname)) 
                {
                    $counter++;
                    if ($s_keyname) {
                        $s_select .= ', ';
                        $s_values .= ', :param'.$counter;
                    } else {
                        if ($counter > 1) {
                            $s_values .= ', ';
                            $s_select .= ', ';
                        }
                        $s_values .= ':param'.$counter;
                    }
                    $s_select .= $poco_key;
                    $m_binds [':param'.$counter] = $cur_value;
                }
            }
        }
        if ($s_keyname) 
            $s_select .= ") VALUES (nextval('".$s_keyname."_seq')".$s_values.') returning '.$s_keyname.';';
        else
            $s_select .= ") VALUES (".$s_values.');';
        // Выполняем
        $b_res = $this->Execute ($s_select,$m_binds);
        if (!$b_res) {
            HApplication::$p_Instance->m_Logger->write (HLOG_ERROR, "Insert failed: ".$this->GetErrorText());
            return false;
        }
        if ($s_keyname) $m_object->{$s_keyname} = $this->GetLastID();
        HApplication::$p_Instance->m_Logger->write (HLOG_DEBUG, "Inserted $s_keyname=".$this->GetLastID());
        return true;
    }

    // Обновить 1 запись прямо в БД
    public function Update ($s_tablename, $s_keyname, &$m_object)
    {
        $m_binds = array ();
        $s_select = 'UPDATE '.$s_tablename.' SET ';
        // формируем список полей NOT NULL
        $counter = 0;
        $m_binds = array ( );
        foreach ($m_object as $poco_key => $cur_value) 
        {
            if($this->isPOCO($poco_key,$s_keyname)) 
            {
                if ($poco_key != $s_keyname) 
                {
                    if ($counter++ > 0) {
                        $s_select .= ', ';
                    }
                    $s_select .= $poco_key.'=:param'.$counter;
                    $m_binds [':param'.$counter] = $cur_value;
                }
            }
        }
        //HApplication::$p_Instance->m_Logger->write (HLOG_DEBUG, dump($s_keyname));
        if (is_array($s_keyname))
        {
            $s_select .= ' WHERE ';
            $counter = 0;
            foreach ($s_keyname as $k => $v) 
            {
                if ($counter++ > 0) {
                    $s_select .= ' AND ';
                }
                $s_select .= ( $k.'=:p_param_'.$counter );
                $m_binds[':p_param_'.$counter] = $v;
            }
        } else if ($s_keyname) {
            $s_select .= ' WHERE '.$s_keyname.'=:paramKey ;';
            $m_binds [':paramKey'] = $m_object->{$s_keyname};
        }
        // Выполняем
        $b_res = $this->Execute ($s_select,$m_binds);
        if (!$b_res) {
            HApplication::$p_Instance->m_Logger->write (HLOG_ERROR, "Update failed: [".$s_select.'] with params: '.dump($m_binds));
            return false;
        }
        HApplication::$p_Instance->m_Logger->write (HLOG_DEBUG, "Update done");
        return true;
    }
    
    // Удалить 1 запись прямо в БД
    public function Delete ($s_tablename, $s_keyname, &$m_object)
    {
        $m_binds = array ();
        $s_select = 'DELETE FROM '.$s_tablename.' WHERE ';
        $m_binds = array ( );
        if (is_array($s_keyname))
        {
            $counter = 0;
            foreach ($s_keyname as $k => $v) 
            {
                if ($counter++ > 0) {
                    $s_select .= ' AND ';
                }
                $s_select .= ( $k.'=:p_param_'.$counter );
                $m_binds[':p_param_'.$counter] = $v;
            }
        } elseif ($s_keyname) {
            $s_select .= $s_keyname.'=:paramKey ;';
            $m_binds [':paramKey'] = $m_object->{$s_keyname};
        }
        // Выполняем
        $b_res = $this->Execute ($s_select,$m_binds);
        if (!$b_res) {
            HApplication::$p_Instance->m_Logger->write (HLOG_ERROR, "Delete failed: [".$s_select.'] with params: '.dump($m_binds));
            return false;
        }
        HApplication::$p_Instance->m_Logger->write (HLOG_DEBUG, "Delete done");
        return true;
    }
    
    // Удалить группу данных в БД
    public function DeleteRows ($s_tablename, $where)
    {
        $m_binds = array ();
        $s_select = 'DELETE FROM '.$s_tablename;
        // Составляет условия выборки
        if (is_array($where)) 
        {
            $s_select .= ' WHERE ';
            $counter = 0;
            foreach ($where as $k => $v) 
            {
                if ($counter > 0) $s_select .= ' AND ';
                $s_select .= $k.'=:p_param_'.$counter;
                $m_binds[':p_param_'.$counter] = $v;
                $counter ++;
            }
        }
        
        //echo "$s_select".dump($m_binds);

        // Выполняем                               
        $b_res = $this->Execute ($s_select,$m_binds);
        if (!$b_res) return false;
        return true;
    }
    
    // Обновить группу данных в БД
    public function UpdateRows ($s_tablename, $values, $where)
    {
        $m_binds = array ();
        $s_select = 'UPDATE '.$s_tablename.' SET ';
        // Составляет колонки обновления
        $counter = 0;
        if (is_array($values)) 
        {
            $counter1 = 0;
            foreach ($where as $k => $v) 
            {
                if ($counter > 0) $s_select .= ', ';
                $s_select .= $k.'=:p_param_'.$counter;
                $m_binds[':p_param_'.$counter] = $v;
                $counter ++;
                $counter1 ++;
            }
        }
        // Составляет условия выборки
        if (is_array($where)) 
        {
            $s_select .= ' WHERE ';
            $counter2 = 0;
            foreach ($where as $k => $v) 
            {
                if ($counter2 > 0) $s_select .= ' AND ';
                $s_select .= $k.'=:p_param_'.$counter;
                $m_binds[':p_param_'.$counter] = $v;
                $counter ++;
                $counter2 ++;
            }
        }
        
        //echo "$s_select".dump($m_binds);

        // Выполняем                               
        $b_res = $this->Execute ($s_select,$m_binds);
        if (!$b_res) return false;
        return true;
    }
    
    
};
?>
