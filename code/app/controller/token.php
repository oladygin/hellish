<?php

/**
* Token controller - обработка токенов от Skillber
*/
class TokenController extends \MySite
{
    protected function filter_allow_anonimous() 
    {
        // by Address
        //$addr = strtolower(gethostbyaddr ($_SERVER['REMOTE_ADDR']));
        //$isValueAddress =  mb_substr($addr, -10) == 'billing.ru';

        // by IP
        $ip = $_SERVER['REMOTE_ADDR'];
        $isValueAddress = preg_match('/^91\.210\.[4-7]\./', $ip) > 0;   // company external IPs
        if(!$isValueAddress) $isValueAddress = preg_match('/^172\.20\.72\./', $ip) > 0;      // company internal IPs
        if(!$isValueAddress) $isValueAddress = ($ip == '172.30.0.79');    // Skillber Internal Address
        if(!$isValueAddress) $isValueAddress = ($ip == '127.0.0.1');      // local IP
        if($this->m_Params['debug'])
        {
            if(!$isValueAddress) $isValueAddress = ($ip == '172.20.74.68');      // Oleg Ladygin
        }
        
        $this->m_Logger->write (HLOG_DEBUG, 'Calling TokenController from: ' . $ip . ', address ' . ($isValueAddress ? 'valid' : 'NOT valid'));
        return $isValueAddress;
    }
    
    // Вернуть файл
    public function Getfilefile(int $userid, string $file)
    {
        $ufil = new \MUserfile();
        if($ufil->Get(array('ufil_user_id' => $userid, 'ufil_name' => $file)))
        {
            $text = file_get_contents('files/'.$userid.'/'.$ufil->ufil_filename);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($ufil->ufil_name).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            echo ($text);
            return;
        }
        
        if($ufil->Get(array('ufil_user_id' => $userid, 'ufil_filename' => $file)))
        {
            $text = file_get_contents('files/'.$userid.'/'.$ufil->ufil_filename);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($ufil->ufil_name).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            echo ($text);
            return;
        }
        
        return new ErrorView ('Файл не найден');
    }    
    
    /**
    * Обработать токен
    * Этот метов вызывает Skillber при изменении/добавлении/удалении пользователя
    * 
    * token - закодированный токен от скилбера
    * 
    */
    public function Operate (string $token)
    {
        // расшифруем токен
        $token = str_replace(' ', '+', $token);
        $token_text = decryptRJ256($this->m_Params['keys']['cryptKey'], $this->m_Params['keys']['cryptIV'], $token);
        
        // Что там внутри токена?
        $this->m_Logger->write (HLOG_DEBUG, "Get token ($token_text)");

        $data = json_decode ($token_text, false);
        $this->m_Logger->write (HLOG_DEBUG, "Parsed token (".dump($data).")");
        
        // по типу операции что-то сделаем
        if(isset($data->TokenType))
        {
            $this->m_DB->logwrite(LOGSOURCE_SYNCH, HLOG_TRACE, null, 'Получен корректный токен типа '.$data->TokenType);
            switch ($data->TokenType)
                default:
                    return new StringView (json_encode(new \Response(RESPONSE_ERROR, 'Данная версия токена несовместима с приложением')));
            }
        } else {
            $this->m_DB->logwrite(LOGSOURCE_SYNCH, HLOG_FATAL, null, 'Получен ошибочный токен, расшифровка невоможна');
        }
    }
    
}  
?>
