
<div class="top-pane">
    <h1>Лог системы</h1>

    <div class="pagefilters">
        <input style="width: 625px;" class="onenter" id="generalfilter" placeholder="Поиск тексту" onenter="_pageSeacher.on(this);" onescape="_pageSeacher.on(this);">
        <div class="tfilters" action="on_log_filters(this);">
            <div class="text">Показать:</div>
            <div class="show on" value="100"><span>все</span></div>
            <div class="show " value="2"><span>ошибки</span></div>
            <div class="show " value="4"><span>информация</span></div>
            <div class="show " value="5"><span>отладочные</span></div>
        </div>
    </div>
</div>

<div class="main-pane">

    <div class="logmessages">

:   foreach($model as $date => $logmessages) {
    
    <div class="block-dehiscent gray2" id="{$date}">
        <div class="block-dehiscent-expander showed">
            <div class="block-dehiscent-title comptitle">{$date}</div>
            <div class="block-dehiscent-control minus">
                <span class="i"></span>
                <span class="m">Свернуть</span>
                <span class="p">Развернуть</span>
            </div>
        </div>
        <div class="block-dehiscent-matter">    
    
        <div class="list">
            <table>
:           foreach($logmessages as $log) {
            <tr class="pageseacher filterrow lvl-{$log->logm_level}" value="{$log->logm_level}" ltitle="{$log->GetSearchTitle()}">
                <td class="logo">
                   <img src="/images/logs/level-{$log->logm_level}.png"/>
                </td>
                <td class="time">
                   <%=date('H:i:s',$log->d_logm_submit_date)%>
                </td>
                <td class="user">
:                   if($log->m_user) {
                        <a href="/Profile/Index/{$log->m_user->user_id}">{$log->m_user->GetName()}</a>
:                   } 
                </td>
                <td class="func">
                    {$log->GetFunction()}
                </td>
                <td class="matter">
                    {$log->logm_message}
                </td>
             </tr>
:           }
            </table>
        </div>

        </div>
    <div class="block-dehiscent-end"></div>
:   }
       
    </div>

</div>    

<script>

    function on_log_filters (item)
    {
        $('.pageseacher').removeClass('hid2');
        var v = parseInt($(item).attr('value'));
        $('.pageseacher').each(function(){ 
            var t = parseInt($(this).attr('value'));
            if(t > v) $(this).addClass('hid2');
        });
    }
    
</script>   
 

   