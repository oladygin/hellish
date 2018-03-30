<?php


/**
* User profile controller
*/
class ProfileController extends \MySite
{
    protected function filter_allow_anonimous()
    {
        return false;
    }

    /**
    * First action: Profile/Index - визитка пользователя
    */
    public function Index (int $id)
    {
        // грузим указанного пользователя
        $user = new \MUser();
        if(!$user->Get($id)) return new RedirectView('/');

        // Если я менеджер - свой профиль или просмотр профиля аутсорсера
        if ($this->m_Auth->m_Me->user_type == USERTYPE_MANAGER) 
        {
            // смотрим профиль аутсорсера?
            if($user->user_type == USERTYPE_OUTSOURCER) 
            {
                return $this->Index_Outsourcer_View($user);
            } else {
                // иначе свой профиль
                return $this->Index_Manager($this->m_Auth->m_Me);
            }
        } else if ($this->m_Auth->m_Me->user_type == USERTYPE_ADMIN) {
            return $this->Index_Outsourcer_View($user);
        } else {
            // я аутсорсер - только свой профиль на редактирование или чтение
            if ($this->m_Auth->m_Me->user_type == USERTYPE_OUTSOURCER && 
                    ($user->user_state == USERSTATE_CREATED || 
                     $user->user_state == USERSTATE_SKILL_REJECTED || 
                     $user->user_state == USERSTATE_SKILL_UPDATING) 
               )
                return $this->Index_Outsourcer_Edit($this->m_Auth->m_Me);
            else
                return $this->Index_Outsourcer_View($this->m_Auth->m_Me);
        }
    }

    /**
    * переключить анкету пользователя в статус "обновляется"
    */
    public function Onupdate ()
    {
        $user = $this->m_Auth->m_Me;
    
        if ($user->user_type == USERTYPE_OUTSOURCER && $user->user_state == USERSTATE_SKILL_ACCEPTED)
        {
            $user->user_state = USERSTATE_SKILL_UPDATING;

            if(!($user->Set() && $user->Commit())) {
                // плохо все
                $this->m_Logger->write (HLOG_ERROR, "Error updating general profile (userId=$user->user_id)");
                return new ErrorView ('Ошибка сохранения данных');
            } else {
                $this->m_DB->logwrite(LOGSOURCE_OUTSOURCER, HLOG_INFO, $user->user_id, "Профиль переведен в статус обновления");
            }
        
            return $this->Index_Outsourcer_Edit($user);
        }
        
        return new RedirectView('/');
    }
    
}

?>
