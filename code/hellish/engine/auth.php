<?php

    class HAuth
    {
        var $s_Hash;      
        var $isCookieLogin;  
        var $isAuthUser;           
        var $m_Me;               
    
        function __construct ()
        {
            session_name (HApplication::$p_Instance->m_Params['auth']['sessionname']);
            session_start ();
            if (isset($_SESSION[HApplication::$p_Instance->m_Params['auth']['cookiename']])) {
                // кривого пользователя сразу сбросим
                if (!$this->get_user_by_hash()) $this->break_session();
            }
        } 
        
        function is_real()
        {
            return $this->m_Me && $this->m_Me->is_real();
        }
        
        function break_session ()
        {
            setcookie(HApplication::$p_Instance->m_Params['auth']['cookiename'], 0, time()-1, '/', false, 0); 
            unset ($_SESSION[HApplication::$p_Instance->m_Params['auth']['cookiename']]);
            $this->clear ();
            session_unset ();
        }

        function clear () 
        {
            $this->m_Me = null;
            $this->s_Hash = false;
            $this->isCookieLogin = false;
            $this->isAuthUser = false;
        }
        
        function install_cookie ($hash)
        {
            $this->s_Hash = $hash;
            $coockietime = 60*60*24*365;    
            setcookie(HApplication::$p_Instance->m_Params['auth']['cookiename'], $this->s_Hash, time()+$coockietime, '/', false, 0); 
            $this->isCookieLogin = true;
            HApplication::$p_Instance->m_Logger->write(HLOG_INFO, "Install cookie by hash: ".$this->s_Hash);
        }
        
        function create_hash ($value)
        {
            $random_compat = new PHP\Random(true);
            $soul = $random_compat->int(getrandmax());
            $hash = md5($value.$soul.time());
            return $hash;
        }
        
        function get_user_by_hash ($hash)
        {
            $this->m_Me = new HApplication::$p_Instance->m_Params['auth']['usermodel']();
            $this->s_Hash = $hash;
            return $this->m_Me->LoadByHash($hash);
        }
        
        function do_auth ()
        {
            // Есть ли авторизация по куки?
            if (isSet ($_COOKIE[HApplication::$p_Instance->m_Params['auth']['cookiename']])) {
                if ($_COOKIE[HApplication::$p_Instance->m_Params['auth']['cookiename']]) {
                    $this->isCookieLogin = true;
                    HApplication::$p_Instance->m_Logger->write(HLOG_DEBUG, "Try hash: ".$_COOKIE[HApplication::$p_Instance->m_Params['auth']['cookiename']]);
                    if ($this->get_user_by_hash($_COOKIE[HApplication::$p_Instance->m_Params['auth']['cookiename']])) {
                        // Получили пользователя по хэшу
                        $this->isAuthUser = true;
                        HApplication::$p_Instance->m_Logger->write(HLOG_INFO, "Get user by cookie: ".$this->m_Me->GetLogName());
                        return true;
                    } else {
                        // Это неавторизованный пользователь, но он уже был
                        $ret = HApplication::$p_Instance->m_Params['auth']['allowanonymous'];
                        HApplication::$p_Instance->m_Logger->write(HLOG_INFO, "Anonimous user with cookie, return " . ($ret ? 'True' : 'False'));
                        return ($ret);
                    }
                }
            }
            // Разрешено ли создавать анонимных пользователей?
            if (!HApplication::$p_Instance->m_Params['auth']['creteanonymous'])
            {
                // запращено, не создаем, просто болванка пустая
                HApplication::$p_Instance->m_Logger->write(HLOG_INFO, "Anonimous user first time, set cookie");
                $this->m_Me = new HApplication::$p_Instance->m_Params['auth']['usermodel']();
                $this->install_cookie("anonymous");
                return (HApplication::$p_Instance->m_Params['auth']['allowanonymous']);
            } else {
                // разрешено, создаем в БД (пока не реализованоs)
                $this->m_Me = new HApplication::$p_Instance->m_Params['auth']['usermodel']();
                return true;   
            }
        }
    }
    
?>
