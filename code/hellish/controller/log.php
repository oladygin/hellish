<?php

/**
* Hellish internal log controller
*/
class LogController extends \HApplication
{
    protected function filter_allow_anonimous()
    {                      
        // only local access allowed
        $whitelist = array(
            '127.0.0.1',
            '172.20.72.69',
            '172.20.96.135',
            '::1'
        );
                                                       
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
    }
    
    /**
    * /Log - get current log page
    */
    public function Index ()
    {
        //if($this->m_Params['debug'])
        if(true)
        {
            $s_text = "<!DOCTYPE html><html><head><title>Hellish Log</title><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/><link href='/hellish/css/hellish.css' rel='stylesheet'></head><body>";
            $s_text .= file_get_contents($this->m_Logger->s_Filepath . '/' . $this->m_Logger->s_Filename);
            $s_text .= "</html>";
        } else {
            $s_text ="<h1>Access denied</h1>";
        }
        
        return $s_text;
    }
}
  
?>
