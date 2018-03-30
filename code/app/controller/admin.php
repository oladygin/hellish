<?php

/**
* Admin page
*/
class AdminController extends \MySite
{
    protected function filter_allow_anonimous()
    {
        return false;
    }

    protected function filter_access()
    {
        return ($this->m_Auth->is_real() && $this->m_Auth->m_Me->user_type == USERTYPE_ADMIN);
    }

    /**
    * First action: Admin/Index
    */
    public function Index ()
    {
        if (!$this->filter_access()) return new RedirectView ('/');
    
        $user = $this->m_Auth->m_Me;

        // Читаем всех пользователей
        $user->m_Companies = $this->m_DB->GetAllCompaniesWithUsers();
    
        return new View ('index', $user);
    }
    
    /**
    * Log page: Admin/Log
    */
    public function Loghistory (string $days)
    {
        if (!$this->filter_access()) return new RedirectView ('/');

        // Читаем последние логи
        $logMessages =  $this->m_DB->GetLogMessages($days ? $days : 7);
        
        // группируме по дням
        $model = array();
        foreach($logMessages as $log)
        {
            $date = date ('Y-m-d', $log->d_logm_submit_date);
            $model[$date][] = $log;
        }
    
        return new View ('loghistory', $model);
    }
    
    
}
                                                          
?>
