<div class="top-pane">
    
    <h1>Анкета специалиста подрядчика</h1>
    
    <div class="page-profile">
       
:      switch($model['profile']->user_state) {
:          case USERSTATE_SKILL_PUBLISHED:
                <img class="userstate" src="/images/published.png"/>
:               break;
:          case USERSTATE_SKILL_ACCEPTED:
                <img class="userstate" src="/images/accepted.png"/>
:               break;
:          case USERSTATE_SKILL_REJECTED:
                <img class="userstate" src="/images/rejected.png"/>
:               break;
:      }
       
       <div class="contactinfo">
            <div class="logo">
                <img id="cur-avatara-image" src="{$model['profile']->GetIconUrl()}"/>
            </div>
            <div class="matter">
                <form>
:                   if($model['profile']->user_state == USERSTATE_SKILL_REJECTED) {
                    <div class="state reject">
                        Ваша анкета отклонена работодателем с комментарием: 
                        <div>{$model['profile']->user_comment}</div>
                    </div>
:                   } else if($model['profile']->user_state == USERSTATE_SKILL_ACCEPTED && $model['profile']->user_id == $me->user_id) {
                    <div class="state accept">
                        Вы можете отредактировать и заново опубликовать анкету.
                        <button onclick="return do_update(event);">Редактировать</button>
                    </div>
:                   }

                    <h3>Фамилия и имя</h3>
                    <input class="formval w1" disabled name="lastname" placeholder="Ваша фамилия" value="{$model['profile']->user_lastname}"/>
                    <input class="formval w2" disabled name="firstname" placeholder="Ваше имя" value="{$model['profile']->user_firstname}"/>
                    <input class="formval w1" disabled name="patname" placeholder="Ваше отчество" value="{$model['profile']->user_nickname}"/>

                    <h3>Контакты</h3>
                    <input class="formval w1" disabled name="email" placeholder="Электронная почта" value="{$model['profile']->user_email}"/>
                    <input class="formval w2" disabled name="skype" placeholder="Скайп" value="{$model['profile']->user_skype}"/>
                    <input class="formval w0" disabled name="phone" placeholder="Телефон" value="{$model['profile']->user_phone}"/>
                    <input class="formval w0" disabled name="town"  placeholder="Город проживания" value="{$model['profile']->user_town}"/>
                    <input class="formval w0" disabled name="title" placeholder="Категория" value="{$model['profile']->user_title}"/>
                </form>
                
            </div>
            
            <hr class="wide"/>
       </div>
       
        <h1>Cпециализация</h1>
        
        <div class="skillview">
            <div class="class">
                <div class="title">Рабочая специализация</div>
                <div class="cats">
:                   if($model['specialization_text']['workspec0']) {                
                    <div class="cat">
                        <div class="skills">
                            <div class="title">Основная:</div>
                            <div class="skil" >
                                <div class="title"><%=$model['specialization_text']['workspec0']%></div>
                            </div>
                        </div>
                    </div>
:                   }                    
:                   if($model['specialization_text']['workspec1']) {                
                    <div class="cat">
                        <div class="skills">
                            <div class="title">Дополнительная №1:</div>
                            <div class="skil" >
                                <div class="title"><%=$model['specialization_text']['workspec1']%></div>
                            </div>
                        </div>
                    </div>
:                   }                    
:                   if($model['specialization_text']['workspec2']) {                
                    <div class="cat">
                        <div class="skills">
                            <div class="title">Дополнительная №2"</div>
                            <div class="skil" >
                                <div class="title"><%=$model['specialization_text']['workspec2']%></div>
                            </div>
                        </div>
                    </div>
:                   }                    
                </div>
            </div>
        </div>
        
        <table class="spec hid">
            <tr><td>
                <h3>Рабочая специализация</h3>
                <%=Html::GetAutocomplete('workspec0',$model['work_spec'],'Основная рабочая специализация','formval w0',$model['specialization']['workspec0'], 1, true)%>
                <%=Html::GetAutocomplete('workspec1',$model['work_spec'],'Дополнительная, если есть','formval w50',$model['specialization']['workspec1'], 1, true)%>
                <%=Html::GetAutocomplete('workspec2',$model['work_spec'],'Дополнительная, если есть','formval w50 right',$model['specialization']['workspec2'], 1, true)%>
            </td><td>
                <h3>Группы продуктов "Петер-Сервис"</h3>
                <%=Html::GetAutocomplete('prodspec0',$model['prod_spec'],'Основная группа продуктов','formval w0',$model['specialization']['prodspec0'], 1, true)%>
                <%=Html::GetAutocomplete('prodspec1',$model['prod_spec'],'Дополнительная, если есть','formval w50',$model['specialization']['prodspec1'], 1, true)%>
                <%=Html::GetAutocomplete('prodspec2',$model['prod_spec'],'Дополнительная, если есть','formval w50 right',$model['specialization']['prodspec2'], 1, true)%>
            </td></tr>
        </table>
        
        <h1>Умения и навыки</h1>
        
        <div class="skillview">
:               foreach($model['skillsbyname'] as $classTitle => $categories) {
                    <div class="class">
                        <div class="title">{$classTitle}</div>
                        <div class="cats">
:                           foreach($categories as $scat_title => $skills) {
                            <div class="cat">
                                <div class="skills">
                                    <div class="title">{$scat_title}:</div>
:                                   foreach($skills as $skil) {
                                        <div class="skil <%=($skil->userskill['value']>3?'expert' : '')%>" >
                                            <div class="title">{$skil->skil_title}</div>
                                        </div>
:                                   }                
                                </div>
                            </div>
:                           }                
                        </div>
                    </div>
:               }                
        </div>
        
        <hr class="wide"/>
       
        <h1>Анкета</h1>
:       if($model['resumefilename']) {
            <p>Выбранный файл-анкета:</p>
            
            <label id="file-upload-holder" for="file-upload" class="custom-file-upload">
                <input class="formval" disabled="disabled" style="width:685px;" name="resumefilename" value="{$model['resumefilename']}"/>
                <button onclick="window.location.href='/profile/getfilefile?userid={$model['profile']->user_id}&file={$model['resumefilename']}';">Открыть файл</button>
            </label>
:       } else {
            <p>При публикации анкеты файл не был приложен.</p>
:       } 
    </div>
    <div class="clear"></div>    <br><br>
        
</div>

<script>
    $(function () {

    });
    
    function do_update (ev)
    {
        window.location.href='/profile/onupdate';
        
        return doCancel(ev);
    }
</script>    


   