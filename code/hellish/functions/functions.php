<?php

function root()
{
    return $_SERVER['DOCUMENT_ROOT'];
}

function _textdump ($var, $level = 0, $confortView = false)
{
    $res = "";
    $type = "";
    if (is_array($var)) $type = $confortView ? ' ' : "Array[".count($var)."]";
    else if (is_object($var)) $type = "Object";
    else if (is_resource($var)) { $res .= "\"Resource\"\n"; return $res; }

    if ($type) {
        $res = " $type\n";
        for (Reset($var), $level++; list($k, $v) = each ($var);) {
            if (is_array($v) && $k==="GLOBALS") continue;
            for ($i=0; $i < $level*3; $i ++) $res .= " ";
            $res .= "<b>".HtmlSpecialChars($k)."</b> => "._textdump($v, $level, $confortView);
        }
     } else $res .= "\"".HtmlSpecialChars($var)."\".\n";
     return $res;
}

// Dump any object to string
function dump (&$Var, $confortView=false)
{
    $res = "";
    if ((is_array($Var) || is_object($Var)) && count ($Var)) 
        $res = "<pre>\n"._textdump($Var, 0, $confortView)."</pre>\n";
    else
        $res = "<tt>\n"._textdump($Var, 0, $confortView)."</tt>\n";
    return $res;
}

// decrypt token by RJ256 algorithm by two keys. Comfortable with C# RijndaelManaged algorithm
function decryptRJ256($key, $iv, $encrypted)
{
    //get all the bits
    $key = base64_decode($key);
    $iv = base64_decode($iv);
    $encrypted = base64_decode($encrypted);
    $value = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encrypted, MCRYPT_MODE_CBC, $iv);
    // unpad data
    $blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $packing = ord($value[strlen($value) - 1]);
    if ($packing && $packing < $blockSize)
    {
        for ($P = strlen($value) - 1; $P >= strlen($value) - $packing; $P--)
        {
            if(ord($value{$P}) != $packing) $packing = 0;
        }
    }
    return substr($value, 0, strlen($value) - $packing); 
}

function hextobin($hexstr) 
{ 
    $n = strlen($hexstr); 
    $sbin="";   
    $i=0; 
    while($i<$n) 
    {       
        $a =substr($hexstr,$i,2);           
        $c = pack("H*",$a); 
        if ($i==0){$sbin=$c;} 
        else {$sbin.=$c;} 
        $i+=2; 
    } 
    return $sbin; 
} 

// create array differences
function get_difference ($m_new, $s_new_key, $m_old, $s_old_key)
{
    $list_old_only = array();
    $list_both_take_new = array();
    $list_both_take_old = array();
    $list_new_only = array();
    
    // create a maps
    $m_new_map = array();
    foreach ($m_new as $m) if ($m->{$s_new_key}) $m_new_map[ $m->{$s_new_key} ] = $m;
    $m_old_map = array();
    foreach ($m_old as $m) if ($m->{$s_old_key}) $m_old_map[ $m->{$s_old_key} ] = $m;
    
    // looking for the items, present only in OLD
    foreach ($m_old as $m)
    {
        $key = $m->{$s_old_key};
        if (!isset($m_new_map[$key])) {
            $list_old_only[$key] = $m;
        } else {
            $list_both_take_old[$key] = $m;
        }
    }

    // looking for the items, present only in NEW
    foreach ($m_new as $m)
    {
        $key = $m->{$s_new_key};
        if (!isset($m_old_map[$key])) {
            $list_new_only[$key] = $m;
        } else {
            $list_both_take_new[$key] = $m;
        }
    }
    
    return array (
        'old_only' => $list_old_only,
        'take_new' => $list_both_take_new,
        'take_old' => $list_both_take_old,
        'new_only' => $list_new_only
    );
}

// return text representation of the datetime value
function dateToSmart ($date)
{
    return date('H:i', $date);
}

function xecho ($text)
{
    echo ($text);
}