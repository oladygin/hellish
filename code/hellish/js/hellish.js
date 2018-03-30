$j = jQuery.noConflict();

var IsMobile = 0;

$j(document).ready ( function() {
    
    setTimeout("scrollToTop_create()", 1000);
    doInitPopupLoad();
                
    $j(document).on('keyup', '.onenter', function (e) {
        if (e.keyCode == 27) {
            $j(this).val('');
            eval($j(this).attr('onescape'));
        }
        if (e.keyCode == 13) {
            eval($j(this).attr('onenter'));
        }
    });

    $j(document).on('keyup', '.onchange', function (e) {
        var act = $j(this).attr('onchange');
        if(!act) act = $j(this).attr('onenter');                               
        if(act) eval($j(this).attr('onenter'));
    });

    $j(document).keyup(function (e) { if (e.keyCode == 27) { kill_bubbles(); popup_close(); } });

    $j('body').on('click', '.bubble .closer', function (ev) {
        kill_bubbles(); 
    });
    
    $j('body').on('click', '.actor', function (ev) {
        var func = $j(this).attr('function');
        if(func) {
            var item = this;
            var func = func.replace("this", "item");
            eval(func);
            return doCancel(ev);
        }
    });

    $j(document).on('click', '#longpopupwindow .closer', function () { popup_close() });
    /*
    $j(document).on('mouseenter', '.closer', function () { $j(this).addClass('rotor'); });
    $j(document).on('mouseleave', '.closer', function () { $j(this).removeClass('rotor'); });
    */

});

$j.fn.extend({
    bubble: function (className, message) {
        return this.each(function () {
            $j(this).html('<div class="bubble '+className+'"><div class="icon"></div><span class="closer">X</span>'+message+'</div>');
        });
    },
    disabled: function (isDisabled) {
        return this.each(function () {
            if (isDisabled) 
                $j(this).addClass('disabled').attr('disabled','disabled');
            else
                $j(this).removeClass('disabled').attr('disabled',false);
        });
    },
    scrollto: function () {
        return this.each(function () {
            $j('html, body').animate({ scrollTop: $(this).offset().top }, '200', 'swing');
        });
    },
    isonscreen: function () {
        var value = 1;
        this.each(function () {
            var $elem = $(this);
            var $window = $(window);
            var docViewTop = $window.scrollTop();
            var docViewBottom = docViewTop + $window.height();
            var elemTop = $elem.offset().top;
            var elemBottom = elemTop + $elem.height();
            value =  value & ( ((elemBottom <= docViewBottom) && (elemTop >= docViewTop) ? 1 : 0) );
        });
        return value;
    }
});    


function scrollToTop_create() {
    $j('#totopcontrol_place').hide().attr('id', 'totopcontrol');
    $j('#totopcontrol').html('<img class="totopcontrol" src="/css/images/toup.png">').click(function () {
        $j('html, body').animate({ scrollTop: 0 }, 'fast');
    });
    $j(window).scroll(function () {
        scrollToTop_method();
    });
    scrollToTop_method();
}
function scrollToTop_method ()
{
    if ($j(window).scrollTop() == "0") {
        $j('#totopcontrol').fadeOut("slow").css('cursor', 'default');
    } else {
        $j('#totopcontrol').fadeIn("slow").css('cursor', 'pointer');
    }
    if ($j('.lazybutton').length && $('.lazybutton').offset().top - 50 < $(window).scrollTop() + $(window).height()) {
        // Lazy load here
        $j('.lazybutton:visible').trigger('click');
    }
    if (typeof on_page_private_scroll == 'function') {
        try { on_page_private_scroll(); } catch (e) { }
    }
}

function main_onload (_isMobile) {
    IsMobile = _isMobile;
    width=document.body.clientWidth; // ширина  
    height=document.body.clientHeight; // высота  
}

function do_ajax (url, m_params)
{
    $j.ajax({
      url: url,
      type: 'POST',
      contentType: 'application/json; charset=utf-8',
      data: JSON.stringify(m_params),
      dataType: 'json',
      error: function (data) {
          on_ajax_response(data.responseText, m_params);
      },
      success: function (data) {
          on_ajax_response($j.trim(data), m_params);
      }
    });
    return false ;
}             

function on_ajax_response(response, m_params, target)
{
    // If a smart tag first, then smart result
    // console.log('On_response: ' + response);
    var isSmart = response.substr(0,10) == 'HELRESULT-';
    if (isSmart) {
        // Get smart code
        var smartCode = $j.trim(response.substr(10,10));
        var innerData = response.substr(20);
        switch(smartCode)
        {
            case 'WRITE':
                $j('#page').html(innerData);
                break;
            case 'ALERT':
                alert(innerData);
                break;
            case 'ERROR':
                if($j('#place-error').length) {
                    $j('#place-error').bubble(smartCode.toLowerCase(), innerData);
                } else {
                    $j('#page').html(innerData);
                }
                break;
            case 'CONSOLE':
                $j('#page').html(innerData);
                break;
            case 'JSON':
                var m_vars = jQuery.parseJSON(innerData);
                if(m_vars.func) {
                    eval(m_vars.func+'(m_vars)');
                } else {
                    console.log('No JSON RESULT function defined: ' + innerData);
                }
                break;
            case 'EXEC':
                //console.log('Execute: ' + innerData);
                eval (innerData);
                break;
            default:
                alert('Undefined smartCode!');
                console.log('Undefined smartCode: ' + response);
                break;
        }
    } else if (response.substring(0,9) == '<!DOCTYPE') {
        // if a full page, then write ir
        document.open();
        document.write(response);
        document.close();
    } else if (m_params.popup) {
        $j('#place-longpopuptext').html('<div class="messagewin"><div class="closer"></div>' + response + '</div>');
    } else if (m_params.target) {
        $j(m_params.target).html(response);
    } else {
        // simple text
        alert('Engine error, see console log!');
        console.log(response);
    }
}

function doCancel(e) {
    e = e || window.event;
    if (e) {
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
        return false;
    }
}

function addRandom(url) {
    if (url.indexOf('?') > 0) url += "&r" + Math.random();
    else url += "?r" + Math.random();
    return url;
}

function dump(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;
    
    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";
    
    if(typeof(arr) == 'object') { //Array/Hashes/Objects 
        for(var item in arr) {
            var value = arr[item];
            
            if(typeof(value) == 'object') { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}

function get_form_data (selector)
{
    var m_params = {};
    $j(selector + ' .formval').each(function() { 
        var type = this.tagName;
        var name = $j(this).attr('name');
        switch(type)
        {
            case 'INPUT':
            case 'TEXTAREA':
            case 'HIDDEN':
                m_params[name] = $j(this).val();
                break;
            case 'SELECT':
                m_params[name] = $j(this).val();
                break;
        }
    });
    return m_params;
}

function kill_bubbles() {
    $j('.bubble').remove();
    $j('.comboselect').removeClass('active');
    $j('.closable').fadeOut('fast');
    $j('.eraseable').fadeOut('fast', function () { $j(this).remove(); });
}


/* -----------------------------------------------------------------------------------------------
 Большой ПОПАП
----------------------------------------------------------------------------------------------- */
var doInitPopupLoadTimer;
var doInitPopupLoadStartHash;
// Открыть форму с попапе, если она есть
function doInitPopupLoad() {
    doInitPopupLoad_Check ();
    // старт таймера для проверка урлов 
    doInitPopupLoadTimer = setInterval(function () { if (doInitPopupLoadStartHash != window.location.hash.substring(1)) { doInitPopupLoad_Check(); } }, 100);
}
function doInitPopupLoad_Check() {
    doInitPopupLoadStartHash = window.location.hash.substring(1);
    if (window.location.hash) {
        var hash = window.location.hash.substring(1);
        if (hash == 'refresh') {
            window.location.href = addRandom(window.location.href);
        } else {
            // Распакрвываем
            var params = {};
            var parts = hash.split('&');
            for (var i = 1; i < parts.length; i++) {
                var para = parts[i].split('=');
                params[para[0]] = para[1];
            }
            var action = parts[0].replace(/\-/, '/');
            //alert('on: ' + action + ', plus: ' + dump(params));
            bin_goto_longpopup('/' + action, params);
        }
    } else {
        popup_close(true);
    } 
}

function popup_create(text) {
    kill_bubbles();
    var popupItem = $j('#longpopupwindow');
    if (popupItem.length == 0) {
        // Нет, создаем
        var doc = document.documentElement, body = document.body;
        var top = (doc && doc.scrollTop || body && body.scrollTop || 0);
        $j('#page').addClass('fixed').css({'top':-top});
        $j('body').append('<div id="longpopupshadow"></div><section id="longpopupwindow"><div class="longpopupwindow" ><div class="longpopuptext" id="place-longpopuptext"></div></div></section>');
        $j('#place-longpopuptext').html(text);
    }
}
function popup_call(url,params) {
    popup_create("Loading...");
    if(!params) params = {};
    params.popup = 'long';
    do_ajax (url, params);
}
function popup_close() {
    // Закрываем, только если окно есть
    if ($j('#page').hasClass('fixed')) {
        var scrollTop = -parseInt($('#page').css('top'));
        $j('#page').css({ 'top': '' }).removeClass('fixed');
        $j('#longpopupwindow').remove();
        $j('#longpopupshadow').remove();
        //if (!ignoreBack) window.history.back();
        window.scrollTo(0, scrollTop);
    }
}

$ = jQuery.noConflict();
