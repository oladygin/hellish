
// Поиск по странцие
var _pageSeacher = _pageSeacher || {
    timerId : 0,
    on : function (item) {
        if (this.timerId) { 
            clearTimeout(this.timerId); 
        }
        var id = item.id;
        this.timerId = setTimeout("_pageSeacher.search('" + item.id + "')", 500);
    },
    search : function (item_id) {
        var name = $('#' + item_id).val();
        if (name) {
            name = name.toLowerCase();
            // Скрываем все
            $('.pageseacher').addClass('hid');
            // Показываем те, где подходит фильтр и название
            $('.pageseacher[ltitle*="' + name + '"]').removeClass('hid');
        } else {
            // Показываем все
            $('.pageseacher').removeClass('hid');
        }
        // callback
        var action = $('#' + item_id).attr('action');
        if (action) {
            var item = $('#' + item_id);
            action = action.replace("this", "item");    // круто, да? ;(
            eval(action);
        }
        //get_userlist_count();
    }
};

(function (jQuery) {
    jQuery.fn.onn = function (eventType, selector, func) {
        $(this).off(eventType, selector).on(eventType, selector, func);
        return jQuery(this);
    },
    jQuery.fn.addRemoveClass = function (className, isAdd) {
        if(isAdd) {
            $(this).addClass(className);
        } else {
            $(this).removeClass(className);
        }
        return jQuery(this);
    },
    jQuery.fn.disabled = function (isDisabled) {
        return this.each(function () {
            if (isDisabled) 
                $(this).addClass('disabled').attr('disabled','disabled');
            else
                $(this).removeClass('disabled').attr('disabled',false);
        });
    }
})(jQuery);


$(function () {
    
    // раскрывалка элементов
    $(document).on('click', '.dehi > .dehi-title', function(event) { 
        $(this.parentElement).toggleClass('on');
        return doCancel(event);
    });
    
    $(document).on('click', '.block-dehiscent-expander', function (event) {
        // Свернул или развернут?
        var isOpen = $(this).children('.block-dehiscent-control').hasClass('minus');
        $(this).toggleClass('hidden').toggleClass('showed');
        if (isOpen) {
            // Он открыт, надо закрыть
            $(this.parentElement).children('.block-dehiscent-matter').removeClass('hid');
            $(this).children('.block-dehiscent-control').removeClass('minus').addClass('plus');
            $(this.parentElement).children('.block-dehiscent-matter').fadeOut(100);
        } else {
            $(this.parentElement).children('.block-dehiscent-matter').removeClass('hid');
            $(this).children('.block-dehiscent-control').removeClass('plus').addClass('minus');
            $(this.parentElement).children('.block-dehiscent-matter').fadeIn(100);
        }
    });
    
    $(document).on('click', '.pagefilters > .tfilters > .show', function(event) { 
        $('.show', this.parentElement).removeClass('on');
        $(this).addClass('on');
        var action = $(this.parentElement).attr('action');
        if (action) {
            var item = this;
            action = action.replace("this", "item");    // круто, да? ;(
            eval(action);
        }
        return doCancel(event);
    });
    
    $(document).on('click', '.bcheck', function(event) { 
        return on_checkbox(this);
    });    
    
    initcomponents ();
});

function initcomponents ()
{
    // chosen
    $('.chosen-select.notinit').each(function() { 
       $(this).removeClass('notinit');
       $(this).chosen({enable_split_word_search:true,search_contains:true,allow_single_deselect:true});
    });
}

function on_checkbox(item) {
    if (!$(item).hasClass('disabled')) {

        $(item).toggleClass('checked');
        $(item).removeClass('mousedown');

        var action = $(item).attr('action');
        if (action) {                                   
            action = action.replace(new RegExp("this", 'g'), "item");
            eval(action);
        }

        $(item).trigger('change');
    }
    return doCancel();
}
function is_checkbox(selector) {
    return $(selector).hasClass('checked') ? true : false;
}
function set_checkbox(selector, value) {
    $(selector).addRemoveClass('checked', value);
}
