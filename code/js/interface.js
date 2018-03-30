$(function () {
    $('.tooltip').poshytip({className: 'tip-yellowsimple', showTimeout: 500, alignTo: 'target', alignX: 'center', offsetY: 5, allowTipHover: true, liveEvents: true });

    // Таб на умениях пользователя
    jQuery(document).on('click', '.tabholder > .head > .item > a', this, function(event) { 
        if(!$(this.parentElement).hasClass('active')) {
            $('.item', this.parentElement.parentElement).removeClass('active');
            $(this.parentElement).addClass('active');
            var page_id = $(this).attr('pageid');
            $('.body .page', this.parentElement.parentElement.parentElement).addClass('hid');
            $('.body #tabpage-' + page_id, this.parentElement.parentElement.parentElement).removeClass('hid');
        }
        return doCancel(event);
    });
    
    // Радио
    jQuery(document).on('click', '.radioer', function(event) { 
        if(!$(this).hasClass('disabled')) {
            var block = $(this).attr('block');
            if(block) {
                $('.radioer[block='+block+']').removeClass('marked');
            }
            $(this).addClass('marked');
            var func = $(this).attr('f');
            if(func) {
                var item = this;
                func = func.replace('this','item');
                eval (func);
            }
            //on_change_spec_name(this);
        }
        return doCancel(event);
    });

    // заполнение умений по второй шкале
    jQuery(document).on('click', '.page-profile .skil .type2 > .d > div', function(event) { 
        var target = this.parentElement.parentElement.parentElement;
        if($(this).hasClass('y'))
        {
            $(target).addClass('setted');
            $(target).attr('value', 3);
        } else {
            $(target).removeClass('setted');
            $(target).attr('value', 0);
        }
    });

    // заполнение умений
    jQuery(document).on('click', '.page-profile .skil .l', function(event) { 
        do_hide_specdialog ();
        $('.l',this.parentElement).removeClass('on');
        var value = parseInt($(this).attr('v'));
        for (var i = 1 ; i <= value ; i ++) $('.l'+i,this.parentElement).addClass('on');
        $(this.parentElement.parentElement).attr('value', value).addClass('setted').removeClass('empty');
        
        if($(this.parentElement.parentElement.parentElement.parentElement).hasClass('specable')) on_spec_added(this.parentElement.parentElement);
        
        check_all_setted();
        return doCancel(event);
    });

    // установка специализации
    jQuery(document).on('click', '.page-profile .skil .spec', function(event) { 
        do_show_specdialog(this);
        return doCancel(event);
    });
    
    // установка специализации
    jQuery(document).on('click', '.page-profile .skil .slink', function(event) { 
        do_show_specdialog(this);
        return doCancel(event);
    });
    
    // закрыть специализауию
    jQuery(document).on('click', '.specdialog .closer', function(event) { 
        do_hide_specdialog();
        return doCancel(event);
    });
    
    // удаление специализации
    jQuery(document).on('click', '.page-profile .skil .matter .del', function(event) { 
        on_delete_spec(this);
        return doCancel(event);
    });
    
    // удаление умений
    jQuery(document).on('click', '.page-profile .skil > .del', function(event) { 
        do_hide_specdialog ();
        $(this.parentElement).removeClass('setted').addClass('empty').attr('value', 0).attr('specpos', 0);

        $('.l',this.parentElement).removeClass('on');
        $('.spec',this.parentElement).removeClass('on');
        
        if($(this.parentElement.parentElement.parentElement).hasClass('specable')) on_spec_erased(this.parentElement);
        
        check_all_setted();
        return doCancel(event);
    });
    
});

function on_spec_added (item)
{
    //console.log('add');
    var specpos = parseInt(parseInt($(item).attr('specpos')));
    var title = $('.title', item).text();
    var id = $(item).attr('skillid');
    var target = '#spec-place-work';
    // убиваем заглушку 
    $('.nospectext', target).addClass('hid');
    $('.yesspecwarn', target).removeClass('hid');
    // добавляем строку
    var y = $('tbody tr', target).length;
    $('tbody', target).append ('<tr name="'+title+'"><td>' + title + '</td><td>' + get_spec_radio(id, 1, y, specpos) + '</td><td>' + get_spec_radio(id, 2, y, specpos) + '</td><td>' + get_spec_radio(id, 3, y, specpos) + '</td></tr>');
}

function on_spec_erased (item)
{
    //console.log('del');
    var title = $('.title', item).text();
    var target = '#spec-place-work';
    $('tbody [name="'+title+'"]', target).remove();
    // надо показать пустой блок?
    if(!$('tbody tr', target).length) {
        $('.nospectext', target).removeClass('hid');
        $('.yesspecwarn', target).addClass('hid');
    }
}

function get_spec_radio (id, x, y, specpos)
{
    return '<div class="radioer bradio '+(x==specpos ? 'marked' : '' )+' '+(x==1 ? 'ismain' : '' )+'" x="'+x+'" y="'+y+'" f="on_spera(this)" itemname="'+id+'"><div class="dot"></div></div>';
}

function on_spera (item)
{
    $('.nospecwarn').addClass('hid');

    var target = item.parentElement.parentElement.parentElement.parentElement;
    var x = $(item).attr('x');
    var y = $(item).attr('y');
    //console.log(x + ' = ' + y);
    $('.radioer[x='+x+']', target).removeClass('marked')
    $('.radioer[y='+y+']', target).removeClass('marked')
    $(item).addClass('marked')
    
    check_all_setted();
}

function on_change_spec_name(item)
{                           
    return;          
    var spectarget = $('.activedialog')[0].parentElement.parentElement;
    var skillid = $(spectarget).attr('skillid');
    var name   = $('.title', spectarget).text();
    var target = item.parentElement.parentElement;
    var default_title = $('.label', target).html();
    $(target).attr('skillid', skillid).attr('default', default_title);
}

function do_hide_specdialog()
{
    $('.activespec').removeClass('activespec');
    $('#spec-holder .activedialog').remove();
}

function on_delete_spec (item)
{
    var target = item.parentElement.parentElement;
    var default_title = $(target).attr('default');
    $(target).removeClass('setted');
    $('.label', target).html(default_title);
    $('.radioer', target).disabled(false);
}

function do_show_specdialog(item)
{
    do_hide_specdialog ();

    // покажем шаблон диалога
    var level = $(item.parentElement.parentElement).attr('value');
    var dialog = $('#spec-template').html();
    var scat_block = item.parentElement.parentElement.parentElement.parentElement; 
    $(item.parentElement).append(dialog);
    $(item.parentElement.parentElement).addClass('activespec');
    $('.specdialog', item.parentElement).addClass('activedialog').addClass(level > 0 ? '' : 'null');
    // загрузим в него названия элементов от данной категории
    var scat_id = $(scat_block).attr('scat_id');
    // наберем выбранные умения
    var next_position = 1;
    $('.spec.on', scat_block).each(function() { 
        var title = $('.title', this.parentElement.parentElement).html();
        var skil_id = $(this.parentElement.parentElement).attr('skillid');
        // если главная - на первое место, иначе на следующее
        var position = 0;
        if ($(this).hasClass('main')) position = 0;
            else position = next_position++;
        var line = $('.activedialog .spec'+position)[0];
        var default_title = $('.label', line).html();
        $(line.parentElement).attr('skillid', skil_id);    
        $('.label', line).html(title);
        $('.radioer', line).disabled(true);
        $(line.parentElement).addClass('setted').attr('default', default_title);
    });
}

function on_save_spec_dialog(ev,item)
{
    // сбросим все специализации в этом блоке
    var block = item.parentElement.parentElement.parentElement.parentElement;
    $('.spec', block).removeClass('on').removeClass('main');
    $('.skil', block).each(function() { 
        if(!$(this).attr('value')) $(this).removeClass('setted');
    });
    
    // по всем эелементам
    $('.activedialog .radioer').each(function(){
        var skil_id = $(this.parentElement.parentElement).attr('skillid');    
        if(skil_id) {
            var target = $('.skills > .skil[skillid=' + skil_id + ']')[0];
            $(target).removeClass('empty').addClass('setted');
            if($(this).hasClass('marked') || ($(this).hasClass('disabled') &&  $(this.parentElement.parentElement).hasClass('setted') ))
            {
                // такие умение выбрано, установмс его
                $('.spec', target).addClass('on');
                if($(this.parentElement).hasClass('spec0')) $('.spec', target).addClass('main');
            } else {
                // оно сброшено, снимем
                $('.spec', target).removeClass('on').removeClass('main');
            }
        }
    });
    
    do_hide_specdialog ();
    check_all_setted();
    return doCancel(ev);
}

function on_login (ev,item,isRestore)
{
    var params = get_form_data('.data-login');
    params.isRestore = isRestore;
    do_ajax('/index/onlogin', params );
    return doCancel(ev);
}

function on_add_worker (ev)
{
    popup_call ('/manager/addworker');
}

function on_edit_worker (ev, userId)
{
    popup_call ('/manager/addworker', {userId : userId});
}

function on_save_worker (ev,item)
{
    var params = get_form_data('#add-worker-form');
    popup_call ('/manager/saveworker', params );
}

function on_skill_filter (item)
{
    var target = item.parentElement;
    var text = $(item).val();
    if(text) {
        $('.skil',target).addClass('hid');
        $('.categ',target).addClass('notfound');
        // все фильтруем
        $('.skil > .title',target).each(function() { 
            var oldname = $(this).text();
            var index = oldname.toLowerCase().indexOf(text.toLowerCase());
            if(index >= 0)
            {
                $(this.parentElement).removeClass('hid');
                var newname = oldname.substring(0,index) + '<span>' + text + '</span>' + oldname.substring(index+text.length);
                $(this).html(newname);
                $(this.parentElement.parentElement.parentElement).removeClass('notfound').removeClass('hid').addClass('on');
            } else {
            }
        })
        // пройдем по каждому блоку - есть там найденное - остави его, иначе уберем с экрана
        $('.categ.notfound',target).addClass('hid');
    } else {
        // снимем все выделение
        $('.skil',target).removeClass('hid');
        $('.categ',target).removeClass('hid');
        $('.skil > .title',target).each(function() { 
            $(this).html($(this).text());
        });
    }
}

function do_send_command ()
{
    do_ajax ('/admin/command', {message : $('#message-holder').val(), target: '#result-holder'});
}