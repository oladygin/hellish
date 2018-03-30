<?php

class Html
{
    static function GetAutocomplete ($id, $values, $placeholder, $subclass, $selectedKey, $maxSelect=1, $isDisabled=false)
    {
        $s_text = '<select '.($isDisabled ? 'disabled' : '').' id="'.$id.'" data-placeholder="'.$placeholder.'" class="chosen-select notinit single '.$subclass.'">';
        
        $s_text .= '<option value=""></option>';
        foreach ($values as $k => $v)
        {
            $isSelect = ($selectedKey == $k);
            $s_text .= '<option value="'.$k.'" '.($isSelect?'selected':'').'>' . $v . '</option>';
        }
        
        $s_text .= '</select>';
        
        return ($s_text);
    }

    static function SpecializationScaleType_1 ($value)
    {
        $text = '<div class="scale type1">
                     <div class="tooltip l l1 '.($value >= 1 ? 'on' : '').'" v="1" title="Имею общие представления"></div>
                     <div class="tooltip l l2 '.($value >= 2 ? 'on' : '').'" v="2" title="Имею небольшой практический опыт"></div>
                     <div class="tooltip l l3 '.($value >= 3 ? 'on' : '').'" v="3" title="Иногда выполняю задачи в этой области/иногда использую"></div>
                     <div class="tooltip l l4 '.($value >= 4 ? 'on' : '').'" v="4" title="Регулярно выполняю задачи в этой области/регулярно использую"></div>
                     <div class="tooltip l l5 '.($value >= 5 ? 'on' : '').'" v="5" title="Я в этом профессионал/эксперт"></div>
                 </div>';
        return $text;
    }
    
    static function SpecializationScaleType_2 ($value)
    {
        $text = '<div class="scale type2">
                    <div class="tooltip d '.($value ? 'on' : '').'" value="'.($value).'" title="Есть ли опыт работы в данной компании?">
                        <div class="n no_selection">нет</div>
                        <div class="y no_selection">да</div>
                    </div>
                </div>';
        
        return $text;
    }
    
    static function Manda ()
    {
        return '<span class="manda">*</span>';
    }
    
    static function GetCheckbox ($id, $labelText, $initialState = false, $onClickAction = '', $subclass = '', $isDisabled = false)
    {
        $s_text = '<div id="'.$id.'" class="bcheck '.($initialState ? "checked" : "unchecked").($isDisabled ? " disabled" : "").'" action="'.$onClickAction.'"><div class="bim"></div>'.$labelText.'</div>';
        
        return ($s_text);
    }    
}

$Html = new Html();
  
?>
