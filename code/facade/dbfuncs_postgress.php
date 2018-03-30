<?php

require_once ("models/skillclasses.php"); 
require_once ("models/skills.php"); 
require_once ("models/skillcategories.php"); 
require_once ("models/userskills.php"); 
require_once ("models/users.php"); 
require_once ("models/userfiles.php"); 
require_once ("models/companies.php"); 
require_once ("models/logmessages.php"); 

/**
*  тут побитно:
    бит 0 - присутствие ошибки, если 1 то ошибка, 0 все хорошо
    биты 1&2 - описания работы с данными:
        значение 0 = не указано
        значение 1 = сущность не изменена
        значение 2 = сущность изменена
        значение 3 = сущность создана
*/
define ("RESPONSE_OK",          0);        // просто выполнено 000b
define ("RESPONSE_ERROR",       1);        // ошибка           001b
define ("RESPONSE_OK_SKIP",     2);        // все хорошо, нет изменения данных 010b
define ("RESPONSE_OK_CHANGED",  4);        // все хорошо, данные немного обновлены 100b
define ("RESPONSE_OK_CREATED",  6);        // все хорошо, данные созданы/удалены 110b

// ответ во внешнюю систему
class Response
{
    var $state;
    var $message;
    
    function __construct ($state, $message = '')
    {
        $this->state = $state;
        $this->message = $message;
    }
}

// источник логирования (подсистема)
define ("LOGSOURCE_NONE",    0);        // не указан
define ("LOGSOURCE_KERNEL",  1);        // ядро системы
define ("LOGSOURCE_SYNCH",   2);        // подсистема синхронизации
define ("LOGSOURCE_MANAGER", 3);        // действия менеджера
define ("LOGSOURCE_OUTSOURCER", 4);     // действия аутсорсера

class CDB extends HellishDB 
{
    // записать что-то в лог
    function logwrite ($source, $level, $user_id, $message, $data = null)
    {
        if($level > 5) return;
    
        // получим имы вызванной функции
        $trace = debug_backtrace (DEBUG_BACKTRACE_IGNORE_ARGS);
        $line =  count($trace) > 1 ? $trace[1] : $trace[0];
        $func = @$line['class'] . @$line['type'] . @$line['function'];
        $func = str_replace('HellishCode\\', '', $func);
        $func = mb_substr($func, 0, 50);
        
        $message = mb_substr($message, 0, 255);
        
        if(is_object($data)) $data = dump($data);
        else if(is_array($data)) $data = dump($data);
        
        $m_binds = array(
            ':p_source' => $source,
            ':p_level' => $level,
            ':p_user_id' => $user_id,
            ':p_function' => $func,
            ':p_message' => $message,
            ':p_data' => $data
        );
        
        $this->Execute ("INSERT INTO logmessages (logm_id, logm_source, logm_level, logm_submit_date, logm_user_id, logm_function, logm_message, logm_data) 
                                          VALUES (nextval('logm_id_seq'), :p_source, :p_level, CURRENT_TIMESTAMP, :p_user_id, :p_function, :p_message, :p_data)", $m_binds, true);
    }


    /**
    * Зарегистрировать, обновить компанию
    */
    function registerCompany ($comp)
    {
        if ($comp && isset($comp->comp_id)) 
        {
            $m_binds = array(':p_comp_id' => $comp->comp_id, ':p_title' => $comp->comp_title);
            // есть ли такая компания? нет? добавляем, иначе обновляем
            $this->Execute ('UPDATE companies SET comp_title=:p_title WHERE comp_id=:p_comp_id',$m_binds);
            if(!$this->GetAffected()) {
                $this->Execute ('INSERT INTO companies (comp_id, comp_title) VALUES (:p_comp_id,:p_title)',$m_binds);
            }
        }
    }
    
    /**
    * Установить контактного менеджера для аутсорсера
    */
    function SetUserManager ($userId, $managerId)
    {
        $m_binds = array(':p_boss_id' => $managerId, ':p_user_id' => $userId);
        $this->Execute ('UPDATE users SET user_boss_id=:p_boss_id WHERE user_id=:p_user_id',$m_binds);
    }    
    
    // Вернуть всех пользователей вообще
    function GetAllUsers ()
    {
        $m_data = $this->Select ('select u.*, 
                                         b.user_eid as userboss_eid, b.user_id as userboss_id, b.user_firstname as userboss_firstname, b.user_lastname as userboss_lastname
                                    from users u
                                    left join users b on b.user_id=u.user_boss_id'); // where u.user_activestate = 0
        $m_result = array();                    
        foreach($m_data as $row)
        {
            //$user = $this->Fill('MUser', $row);
            //$m_result[] = $user;
            
            $m_user = $this->Fill('MUser', $row);
            $m_user->userboss_eid = @$row['userboss_eid'];
            $m_user->userboss_id = @$row['userboss_id'];
            $m_user->userboss_firstname = @$row['userboss_firstname'];
            $m_user->userboss_lastname = @$row['userboss_lastname'];
            $m_result[] = $m_user;            
        }
                                
        return $m_result;
    }
    
    
    // Получить список аутсорсеров конторы
    function GetOutSourcers ($company_id = null, $user_type = 2)
    {
        $m_binds = array(':p_type' => $user_type);
        if ($company_id) $m_binds[':p_comp_id'] = $company_id;
    
        $m_data = $this->Select ('select u.*, 
                                         b.user_eid as userboss_eid, b.user_id as userboss_id, b.user_firstname as userboss_firstname, b.user_lastname as userboss_lastname, 
                                         c.*
                                    from users u
                                    left join users b on b.user_id=u.user_boss_id
                                    left join companies c on c.comp_id=u.user_comp_id
                                    where '.($company_id ? 'u.user_comp_id=:p_comp_id and ' : '').'u.user_type = :p_type and u.user_activestate = 0
                                    order by u.user_lastname, u.user_firstname, u.user_email
                                ', $m_binds);
        $m_result = array();                    
        foreach($m_data as $row)
        {
            if(!$row['user_state']) $row['user_state'] = 0;
            //$m_result[] = $this->Fill('MUser', $row);
            $m_user = $this->Fill('MUser', $row);
            $m_user->LoadFromRow($row);
            $m_result[] = $m_user;
        }
                                
        return $m_result;
    }

    
    // вернуть логи
    function GetLogMessages ($days=7)
    {
        $time = date('Y-m-d', strtotime("-$days days"));
    
        $m_data = $this->Select ('select l.logm_id, l.logm_source, l.logm_level, l.logm_submit_date, l.logm_user_id, l.logm_function, l.logm_message,
                                         u.user_id, u.user_lastname, u.user_firstname, u.user_eid, u.user_type, u.user_state 
                                    from logmessages l
                                    left join users u on l.logm_user_id = u.user_id
                                    where l.logm_submit_date > :p_date and l.logm_level < 6
                                    order by l.logm_id desc
                                  ', array(':p_date' => $time));
        $m_result = array();                    
        foreach($m_data as $row)
        {
            $m_logm = new MLogMessage();
            $m_logm->LoadFromRow($row);
            $m_result[] = $m_logm;
        }
                                
        return $m_result;
    }

    // Вернуть все компании с пользователями
    function GetAllCompaniesWithUsers ()
    {
        $m_data = $this->Select ('select c.*, u.* 
                                  from users u 
                                  left join companies c on c.comp_id=u.user_comp_id
                                  where u.user_activestate = 0  
                                  order by c.comp_title, u.user_lastname, u.user_firstname
                                  ');
        $m_result = array();                    
        foreach($m_data as $row)
        {
            $comp_id = @$row['comp_id'];
            if(!$comp_id) $comp_id = 0;
            
            if(!isset($m_result[$comp_id]))
            {
                // добавляем новую контору, если еще не было
                $m_comp = new MCompany();
                $m_comp->LoadFromRow($row);
                $m_result[$comp_id] = $m_comp;
            } 
            
            $m_user = new MUser();
            $m_user->LoadFromRow($row);
            $m_result[$comp_id]->m_Userlist[$m_user->user_id] = $m_user;
        }
                                
        return $m_result;
    }

    // проверить уникальность почты
    function CheckEmailUnicum ($email, $except_user_id, $except_user_eid = 0)
    {
        if($except_user_eid)
        {
            $m_data = $this->Select ('select u.* from users u where u.user_email=:p_email and u.user_eid != :p_user_id',
                array(':p_email' => $email, ':p_user_id' => $except_user_eid));
        } else {
            $m_data = $this->Select ('select u.* from users u where u.user_email=:p_email and u.user_id != :p_user_id',
                array(':p_email' => $email, ':p_user_id' => $except_user_id));
        }
            
        //$d = array(':p_email' => $email, ':p_user_id' => $except_user_id);
        //HApplication::$p_Instance->m_Logger->write (HLOG_ERROR, "Execute failed: ".dump($d));
            
        return (count($m_data) == 0);
    }

    // удалить всех пользователей, которые одлжны быть удалены
    function EraseInactiveUsers ()
    {
        $this->Execute ('delete from userfiles where ufil_user_id in (select user_id from users where user_activestate = 1)');
        $this->Execute ('delete from userskills where uskl_user_id in (select user_id from users where user_activestate = 1)');
        $this->Execute ('delete from users where user_id in (select user_id from users where user_activestate = 1)');
    }
    
    // Получить список аутсорсеров всех контор, которые опубликоваи саои навыки (для регистрации в скилбере)
    function GetPublishedOutsourcers ()
    {
        $m_data = $this->Select ('select u.*, b.user_eid as userboss_eid, 
                                        f1.ufil_filename as filename_type1, f1.ufil_id as filename_type1_id, 
                                        f2.ufil_filename as filename_type2, f2.ufil_id as filename_type2_id
                                    from users u
                                    left join users b on b.user_id=u.user_boss_id
                                    left join userfiles f1 on f1.ufil_user_id = u.user_id and f1.ufil_isactive = 1 and f1.ufil_type = 1
                                    left join userfiles f2 on f2.ufil_user_id = u.user_id and f2.ufil_isactive = 1 and f2.ufil_type = 2
                                    where u.user_type = 2 and u.user_state in (1,5)
                                    order by u.user_lastname, u.user_firstname
                                ');
        $m_result = array();                    
        foreach($m_data as $row)
        {
            $user = $this->Fill('MUser', $row);
            $user->userboss_eid = @$row['userboss_eid'];
            $user->filename_type1 = @$row['filename_type1'];
            $user->filename_type2 = @$row['filename_type2'];
            $user->filename_type1_id = @$row['filename_type1_id'];
            $user->filename_type2_id = @$row['filename_type2_id'];
            $m_result[] = $user;
        }
                                
        return $m_result;
    }

    // Получить список умений пользователя + всех
    function GetUserSkills ($user_id)
    {
        $m_data = $this->Select ('select * 
                                    from skillcategoryskills ss, skillcategories k, skillclasses sk, skills s
                                    left join userskills us on us.uskl_skil_id = s.skil_id and us.uskl_user_id = :p_user_id
                                    where s.skil_id = ss.skil_id
                                          and sk.scls_id = s.skil_scls_id
                                          and ss.scat_id = k.scat_id
                                    order by sk.scls_order, s.skil_scls_id, k.scat_title, s.skil_title
                                ', array(':p_user_id' => $user_id));
        $m_result = array();                    
        foreach($m_data as $row)
        {
            //$m_result[] = $this->Fill('MSkill', $row);
            $skil = new MSkill ();
            $skil->LoadFromRow($row);
            $m_result[] = $skil;
        }
                                
        return $m_result;
    }

    // Сбросить все специализации у пользователяs
    function ResetUserSpecialization ($user_id)
    {
        return $this->Execute ('update userskills set uskl_is_specialization=0, uskl_is_main_specialization=0 where uskl_user_id=:p_user_id', array(':p_user_id' => $user_id));
    }
    
    // Сбросить активность для всех файлов такого типа, ауказанный сделать активным
    function SetActiveFileByType ($user_id, $ufil_type, $filename)
    {
        return $this->Execute ('update userfiles set ufil_isactive = 0 where ufil_user_id=:p_user_id and ufil_type=:p_type;', 
                                array(':p_user_id' => $user_id, ':p_type' => $ufil_type)) &&
               $this->Execute ('update userfiles set ufil_isactive = 1 where ufil_user_id=:p_user_id and ufil_type=:p_type and ufil_name=:p_name;', 
                                array(':p_user_id' => $user_id, ':p_type' => $ufil_type, ':p_name' => $filename));
    }
    
    // Установить специализации у пользователяs
    function SetUserSpecialization ($user_id, $skil_id, $isMain)
    {
        $this->Execute ('update userskills set uskl_is_specialization=1, uskl_is_main_specialization=:p_ismain
                                where uskl_user_id=:p_user_id
                                      and uskl_skil_id=:p_skil_id', 
                                      array(':p_user_id' => $user_id, ':p_skil_id' => $skil_id, ':p_ismain' => $isMain ? 1:0, ));
        return $this->GetAffected();
    }
    
    // Сбросить все специализации у пользователяs
    function SetLoginAttemptTime ($user_id, $attemps)
    {
        return $this->Execute ('update users set user_logintime=CURRENT_TIMESTAMP, user_loginattemps=:p_cnt where user_id=:p_user_id', array(':p_user_id' => $user_id, ':p_cnt' => $attemps), true);
    }
    
    
}
?>
