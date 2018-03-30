
<div class="top-pane">
    <h1>Специалисты</h1>

    <div class="pagefilters">
        <input style="width: 625px;" class="onenter" id="generalfilter" placeholder="Поиск по имени или фамилии" onenter="_pageSeacher.on(this);" onescape="_pageSeacher.on(this);">
        <div class="tfilters" action="on_manager_filters(this);">
            <div class="text">Показать:</div>
            <div class="show on" value=""><span>все</span></div>
            <div class="show " value="st-0"><span>не заполненные</span></div>
            <div class="show " value="st-1"><span>на рассмотрении</span></div>
            <div class="show " value="st-3"><span>отклоненные</span></div>
            <div class="show " value="st-2"><span>принятые</span></div>
        </div>
    </div>
</div>

<div class="main-pane">

    <div class="page-profile center-pane">

:   foreach($model->m_Companies as $comp) {
    
    <div class="block-dehiscent gray2" id="skillclass_{$comp->comp_id}">
        <div class="block-dehiscent-expander showed">
            <div class="block-dehiscent-title comptitle">{$comp->comp_title} <span>(всего <%=count($comp->m_Userlist)%>)</span></div>
            <div class="block-dehiscent-control minus">
                <span class="i"></span>
                <span class="m">Свернуть</span>
                <span class="p">Развернуть</span>
            </div>
        </div>
        <div class="block-dehiscent-matter">    
    
:      if(count($comp->m_Userlist)) {
            <div class="list">
:           foreach($comp->m_Userlist as $u) {
                 <div class="pageseacher filterrow st-{$u->user_state}" ltitle="{$u->GetName(false,true)}">
                    <div class="left">
                        <div class="logo">
                            <img src="{$u->GetIconUrl()}"/>
                        </div>
                    </div>
                    <div class="matter">
:                       if($u->user_type == USERTYPE_MANAGER) {
                            <span class="title">{$u->GetName()}</span>
                            <span class="usertype">(Менеджер)</span>
:                       } else if($u->user_type == USERTYPE_ADMIN) {
                            <span class="title" >{$u->GetName()}</span>
                            <span class="usertype">(Администратор)</span>
:                       } else {
                            <a class="title" href="{$u->GetUrl()}">{$u->GetName()}</a>
:                       }
                        <div class="descr">{$u->user_title}</div>
:                       if($u->user_comment && $u->user_state==3) {                    
                        <div class="reject">
                            Комментарий: <i>{$u->user_comment}</i>
                        </div>
:                       }
                        <div class="bottom">
                            <div class="createdate">Создан <%=date('Y-m-d',$u->user_registration_date)%></div>
:                           if($u->user_type == USERTYPE_OUTSOURCER) {
                            <div class="workerstate">{$u->GetAnketaState()}</div>
:                           }
                        </div>
                        <div class="clear"></div>
                        <div class="actions">
                        </div>
                    </div>
                 </div>
:           }
            </div>
:       } else {
            <p>Специалистов не найдено</p>
:       }

        </div>
    <div class="block-dehiscent-end"></div>
:   }
       
    </div>

</div>    

<script>
    $(function () {
        $('#right-info').draggable();
    });

    function on_manager_filters (item)
    {
        var v = $(item).attr('value');
        if(v) {
            $('.filterrow').hide();
            $('.filterrow').each(function() { 
                if($(this).hasClass(v)) $(this).show();
            });
        } else {
            $('.filterrow').show();
        }
    }
</script>   
 

   