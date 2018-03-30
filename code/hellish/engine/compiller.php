<?php
  
  class HCompiller
  {
      var $b_isResetAlways;
      var $m_Varlist;
      
      function __construct ($area, $isResetAlways=false)
      {
          $this->b_isResetAlways = $isResetAlways;
          
          if(!file_exists('appcache')) { 
              mkdir('appcache');
          }
          if(!file_exists('appcache/'.$area)) {
              mkdir('appcache/'.$area);
              mkdir('appcache/'.$area.'/controller');
              mkdir('appcache/'.$area.'/view');
          }
      }

      function execute ($filename, $classname, $method)
      {
          $filetarg = 'appcache/'.$filename;
          if (!$this->b_isResetAlways && filemtime($filename) == filemtime($filetarg)) {
              // file not shanges, include
              HApplication::$p_Instance->m_Logger->write (HLOG_INFO, 'Include unchanged file: <b>'.$filetarg.'</b>');
              include $filetarg;
              return false;
          } else {
              // file changed, compile ALL functions
              if(!include $filename)
              {
                  HApplication::$p_Instance->m_Logger->write (HLOG_FATAL, 'Cant find: <b>'.$filename.'</b>');
              } else {
                  // reset file
                  HApplication::$p_Instance->m_Logger->write (HLOG_INFO, 'Compile file: <b>'.$filename.'</b> to <b>'.$filetarg.'</b>');
                  $sourcematter = file($filename);
                  $source = file_get_contents($filename);
                  $source = "<?php namespace HellishCode; ?>".$source; 
                  file_put_contents ($filetarg, $source);
                  // compule each public method
                  $class_methods = get_class_methods($classname);
                  foreach ($class_methods as $method_name) 
                  {
                      $reflect = new ReflectionMethod($classname, $method_name);
                      if ($reflect->isPublic() && strtolower($reflect->class) ==  strtolower($classname)) 
                      {
                          HApplication::$p_Instance->m_Logger->write (HLOG_INFO, 'Compile method: <b>'.$classname.'::'.$method_name.'</b>');
                          $this->compile($sourcematter, $filetarg, $filetarg, $classname, $method_name);
                      } else {
                          //HApplication::$p_Instance->m_Logger->write (HLOG_INFO, 'Skip method: <b>'.$classname.'::'.$method_name.'</b>');
                      }
                  }
                  include $filetarg;
                  return true;
              }
          }
      }
      
      function compile ($sourcematter, $filename, $filetarg, $classname, $method)
      {
          $class = new ReflectionClass($classname);
          $class = $class->getMethod ($method);
          $filename = $filetarg; //$class->getFileName();
          $docs = $class->getDocComment();
          
          $this->m_Varlist = array();
          
          if(!empty($filename)) {
              $source = $sourcematter;
              $body = implode("", array_slice($source, $class->getStartLine()-0, $class->getEndLine() - $class->getStartLine()+0 ));
              $full = implode("", array_slice($source, $class->getStartLine()-1, $class->getEndLine() - $class->getStartLine()+1 ));
              $head = mb_substr($full, 0, mb_strlen($full) - mb_strlen($body));
              //echo $head . $docs;
              //exit (0);
              
              // change head
              $newhead = '';
              $prevtype = '';
              $funcname = '';
              $tokens = token_get_all("<?php $head ?>");
              $i = 0; 
              foreach ($tokens as $tok)
              {
                  if (is_string($tok)) {
                      $newhead .= $tok;
                      //echo 'STRING = ' . $tok.'<br>';;
                  } else {
                      switch ($tok[0])
                      {
                          case T_STRING:
                            $newhead .= $tokens[$i-2] == ',' || $tokens[$i-1] == '(' ? '' : $tok[1];
                            break;
                          case T_VARIABLE:
                            $prevtype = $tokens[$i-2][0] == T_STRING ? $tokens[$i-2][1] : null;
                            $this->m_Varlist[] = array ('name' => mb_substr($tok[1],1), 'type' => $prevtype);
                            $newhead .= $tok[1];
                            break;
                          case T_FUNCTION:
                            $funcname = $tokens[$i+2][1];
                            $newhead .= $tok[1]; 
                            break;
                          case T_WHITESPACE:
                          case T_CONSTANT_ENCAPSED_STRING;
                            $newhead .= $tok[1]; 
                            break;
                      }
                      //echo token_name($tok[0]) . ' = ' . $tok[1].'<br>';
                  }
                  $i ++;
              }
              // echo "1) $head<br>2) $newhead</br> 3) funcname = '$funcname' <br>".dump ($m_varlist);;
              
              // create new head
              $newhead = "    function $funcname ()\r\n ";
              $paramparser = '';
              $logLine = '$this->m_Logger->write (HLOG_INFO, "Function <b>'.$classname.'/'.$method.'</b> (';
              $logLineCounter = 0;
              
              // echo dump($this->m_Varlist);
              foreach ($this->m_Varlist as $var)
              {
                  $type = $var['type'];
                  if (!$type) $type = 'common';
                  $s_var  = '        $'.$var['name'].' = $this->hparam_'.$type;
                  $s_var .= "('".$var['name']."');\r\n";
                  $paramparser .= $s_var;
                  
                  $logLine .= ($logLineCounter++ ? ', ' : '').'<b>'.$var['name'] . '</b>='.($type == 'object' ? '/Object/' : '$'.$var['name']);
              }
              $logLine .= ") calling\");\r\n";
                                                                   
              // change body
              $newbody = "   {\r\n      try\r\n      {\r\n$paramparser        if (\$this->is_model_valid())\r\n        {\r\n            $logLine";
              $newbody .=  str_replace("\n", "\n    ", mb_substr(trim($body),1));
              $newbody .= "\r\n      } catch (Exception \$ex) { return new ErrorView ('Execute exception: '.\$ex->getMessage()); }\r\n    } \r\n";
              
              $source = file_get_contents($filename);
              $source = str_replace($head, $newhead, $source );
              $source = str_replace($body, $newbody, $source );
              /*$source = "<?php namespace HellishCode; ?>".$source;  */
              file_put_contents ($filetarg, $source);
              
              //include $filetarg;
          }
          
          return true;
      }
  }
?>
