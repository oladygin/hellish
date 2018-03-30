<?php

/**
* Index page, login or not
*/
class IndexController extends \MySite
{
    protected function filter_allow_anonimous()
    {
        return true;
    }

    /**
    * First action: Index/Index
    */
    public function Index ()
    {
        // Если мы авторизованы - на свою страницу
        if($this->m_Auth->is_real())
        {
            switch($this->m_Auth->m_Me->user_type) 
            {
                case USERTYPE_ADMIN:
                    return new RedirectView('/Admin/Index/'.$this->m_Auth->m_Me->user_id);
                    break;
                case USERTYPE_MANAGER:
                case USERTYPE_OUTSOURCER:
                    return new RedirectView('/Profile/Index/'.$this->m_Auth->m_Me->user_id);
                    break;
            }
        } else {
            return new View('login',false);               
        }
    }
    
    /**
    * Logout
    */
    function Logout ()
    {
        // Если мы авторизованы - выход
        if($this->m_Auth->is_real())
        {
            $this->m_Auth->break_session();
            $this->m_Auth->clear();
        } 

         return new RedirectView('/');
    }

    /**
    * Логин пользователя
    */
    function Onlogin (string $email, string $emailget, string $password, int $isRestore)
    {
        $user = new \MUser();

        // режим восстановления пароля
        if($isRestore)
        {
            $this->m_Logger->write (HLOG_INFO, "Restore login for ($emailget)");
            
            if (!$emailget) return new ErrorView('Необходимо указать email');              
           
            if(!$user->Get(array('user_email' => trim(strtolower($emailget))))) {
                $this->m_Logger->write (HLOG_ERROR, "Email not found");
                return new ErrorView('Не найден такой email');
            }
            
            // сгенерим ключ и пароль, и отправим на почту
            return $this->do_RestoreAccess ($user);
            
        } else {
            //  обычный логин
            if ($email && $password)
            {
                $this->m_Logger->write (HLOG_INFO, "Auth for ($email)");

                // попытка авторизоваться
                if(!$user->Get(array('user_email' => trim(strtolower($email))))) {
                    $this->m_Logger->write (HLOG_ERROR, "Email not found");
                    $this->m_DB->logwrite(LOGSOURCE_KERNEL, HLOG_ERROR, null, "Email ($email) not found");
                    return new ErrorView('Неверный адрес или пароль');
                }

                // Проверим время последней попытки авторизоваться 
                $user->user_loginattemps ++;
                $distance = time() - $user->from_timevalue($user->user_logintime);
                $waittime = 5 * $user->user_loginattemps;
                $this->m_Logger->write (HLOG_DEBUG, 'Last attempt time: '.$user->user_logintime.', distance='.$distance.' seconds');
                if ($distance < $waittime) {
                    // слишком часто!
                    $this->m_Logger->write (HLOG_ERROR, 'Once too often!');
                    return new ErrorView('Слишком частые попытки авторизации, повторите чуть позже');
                } else {
                    // сохраним время попытки
                    $user->StoreLoginAttemptTime(false);
                }
                
                $hash = md5($password);
                // ИГНОРИРУЕМ ПАРОЛЬ?
                if (!$this->m_Params['params']['ignorepasswords'])
                {
                    if(! ($user->user_password && $user->user_password == $hash) ) {
                        $this->m_Logger->write (HLOG_ERROR, "Wrong password: $user->user_password == $hash");
                        $this->m_DB->logwrite(LOGSOURCE_KERNEL, HLOG_ERROR, null, "Wrong password ($password): $user->user_password == $hash");
                        return new ErrorView('Неверный адрес или пароль');
                    }
                }
                
                // успех, заодно сбросим счетчик неудачных попыток в 0                   
                $user->StoreLoginAttemptTime(true);
                $this->m_Logger->write (HLOG_INFO, "Success (userId=$user->user_id)");
                $this->m_DB->logwrite(LOGSOURCE_KERNEL, HLOG_INFO, null, "Success login for userId = $user->user_id, $user->user_email");
                $this->m_Auth->install_cookie($user->user_hash);
                
                if ($user->user_type == USERTYPE_ADMIN)
                    return new RedirectView('/Admin/Index/');
                else
                    return new RedirectView('/Profile/Index/'.$user->user_id);
            } else {
                // что-то не передали, ругаемся
                return new ErrorView('Необходимо указать пароль и email');              
            }
        }
    }
    
    
}
                                                          
?>
