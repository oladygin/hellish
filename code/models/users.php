<?php

define('USERTYPE_MANAGER',1);
define('USERTYPE_OUTSOURCER',2);
define('USERTYPE_ADMIN',3);

define('USERSTATE_CREATED',0);
define('USERSTATE_SKILL_PUBLISHED',1);
define('USERSTATE_SKILL_ACCEPTED',2);
define('USERSTATE_SKILL_REJECTED',3);
define('USERSTATE_SKILL_UPDATING',4);
define('USERSTATE_SKILL_UPDATED',5);
  
//
// класс БД: users
//
class MUser extends CDB {
    
    // данные схемы
    var $user_id;
    var $user_type;
    var $user_uid;
    var $user_eid;
    var $user_activestate;
    var $user_comp_id;
    var $user_hash;
    var $user_network;
    var $user_rights;
    var $user_photo_url;
    var $user_photo_big_url;
    var $user_profile_url;
    var $user_birthday;
    var $user_email;
    var $user_sex;
    var $user_title;
    var $user_firstname;
    var $user_lastname;
    var $user_nickname;
    var $user_registration_date;
    var $user_logintime;
    var $user_loginattemps;
    var $user_password;
    var $user_state;
    var $user_skype;
    var $user_phone;
    var $user_town;
    var $user_comment;
    
    var $m_Userlist;
    var $m_Managers;
    var $m_Companies;
    
    var $userboss_eid;
    var $userboss_id;
    var $userboss_firstname;
    var $userboss_lastname;
    
    var $filename_type1;
    var $filename_type2;
    var $filename_type1_id;
    var $filename_type2_id;

    var $resumecontent;
    var $avataracontent;
    var $resumefilename;
    
    function HasRights ($right)
    {
        return ($this->user_rights & $right) > 0 ? true : false;
    }
    
    function is_real()
    {
        return $this->user_id > 0;
    }
    
    function GetName ($isFull=false, $isLower=false)
    {
        if ($this->user_lastname) 
            $name =  $isFull ? 
                    $this->user_firstname.' '. $this->user_nickname.' '.$this->user_lastname : 
                    $this->user_lastname.' '.$this->user_firstname;
        else 
            $name =  $this->user_email;
        $name = $isLower ? mb_strtolower($name) : $name;
        
        return $name;
    }

    function GetLogName ()
    {
        return '['.$this->user_id.': '.$this->user_firstname.' '. $this->user_nickname.' '.$this->user_lastname.']';
    }

    function GetAnketaState ()
    {
        switch ($this->user_state)
        {
            case USERSTATE_CREATED:         return '<span class="created">Анкета не заполнена</span>';
            case USERSTATE_SKILL_PUBLISHED: return '<span class="published">Анкета на рассмотрении</span>';
            case USERSTATE_SKILL_ACCEPTED:  return '<span class="accepted">Анкета принята</span>';
            case USERSTATE_SKILL_REJECTED:  return '<span class="rejected">Анкета отклонена</span>';
        }
        return '<span class="unknown">Unknown state '.$this->user_state.'</span>';
    }
                                                     
    function GetIconUrl()
    {
        $logo_url = $this->user_photo_big_url ? 
                        $this->user_photo_big_url : 
                        '/images/default/'.($this->user_type==USERTYPE_MANAGER ? 'manager' : 'worker').'.png';
        return $logo_url;
    }
                                                    
    function GetUrl ()
    {
        return '/profile/index/'.$this->user_id;
    }

    function Get ($where = null) { return $this->Single('users', $this, is_array($where) ? $where : array('user_id' => $where)); }
    function Create () { return $this->Insert('users', 'user_id', $this); }
    function Set () { return $this->Update('users', 'user_id', $this); }
    
    function StoreLoginAttemptTime ($isSuccess)
    {
        return $this->SetLoginAttemptTime ($this->user_id, $isSuccess ? 0 : $this->user_loginattemps);
    }
    
    // Загркзить пользоотвалеля с данными по хэшу с веба
    function LoadByHash ($hash)
    {
        return $this->Get(array('user_hash' => $hash));
    }

    // Загркзить пользоотвалеля по ID внешней системы
    function LoadUserByExternalId ($externalUserId)
    {
        return $this->Get(array('user_eid' => $externalUserId));
    }
    
    function LoadFromRow ($row)
    {
        $this->user_id = @$row['user_id'];
        $this->user_type = @$row['user_type'];
        $this->user_comp_id = @$row['user_comp_id'];
        $this->user_uid = @$row['user_uid'];
        $this->user_activestate = @$row['user_activestate'];
        $this->user_eid = @$row['user_eid'];
        $this->user_state = @$row['user_state'] ? $row['user_state'] : 0;
        $this->user_hash = @$row['user_hash'];
        $this->user_email = @$row['user_email'];
        $this->user_network = @$row['user_network'];
        $this->user_photo_url = @$row['user_photo_url'];
        $this->user_photo_big_url = @$row['user_photo_big_url'];
        $this->user_profile_url = @$row['user_profile_url'];
        $this->user_birthday = @$row['user_birthday'];
        $this->user_sex = @$row['user_sex'];
        $this->user_firstname = @$row['user_firstname'];
        $this->user_lastname = @$row['user_lastname'];
        $this->user_nickname = @$row['user_nickname'];
        $this->user_registration_date = $this->from_timevalue(@$row['user_registration_date']);
        $this->user_rights = @$row['user_rights'];
        $this->user_logintime = $this->from_timevalue(@$row['user_logintime']);
        $this->user_loginattemps = @$row['user_loginattemps'];
        
        $this->userboss_eid = @$row['userboss_eid'];
        $this->userboss_id = @$row['userboss_id'];
        $this->userboss_firstname = @$row['userboss_firstname'];
        $this->userboss_lastname = @$row['userboss_lastname'];
    }

}

?>
