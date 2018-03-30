<?php

  define ("HLOG_FATAL",   1);
  define ("HLOG_ERROR",   2);
  define ("HLOG_WARNING", 3);
  define ("HLOG_INFO",    4);
  define ("HLOG_DEBUG",   5);
  define ("HLOG_TRACE",   6);
  
  class HLogger
  {
      var $p_Application;
      var $n_logLevel;
      var $b_isLogEnabled;
      var $s_Filepath;
      var $s_Filename;
      var $h_Handle;

      function __construct ($app, $isLogEnabled, $logLevel)
      {
          if(!isset($_GET['route'])) $_GET['route'] = '';
          if (mb_substr($_GET['route'], 0, 7) == 'hellish' ||  mb_stripos($_GET['route'], '.') !== false) $isLogEnabled = false;
          
          $this->n_logLevel = $logLevel;
          $this->b_isLogEnabled = $isLogEnabled;
          $this->s_Filepath = 'logs';
          $this->s_Filename = 'general.log';
          
          if ($this->b_isLogEnabled)
          {
              if(!file_exists($this->s_Filepath)) mkdir($this->s_Filepath);
              
              $filename = $this->s_Filepath . '/' . $this->s_Filename;
              if(time() - filemtime($filename) < 2) {
                  $this->h_Handle = fopen($filename, 'a+');
                  fwrite($this->h_Handle, "<div class='hellish log'><h1>Append close request</h1><table>\r\n");
              } else {
                  $this->h_Handle = fopen($filename, 'w+');
                  fwrite($this->h_Handle, "<div class='hellish log'><h1>Hellish log</h1><table>\r\n");
              }
              $this->write(HLOG_INFO, "Start");
          }
      }
      
      function __destruct ()
      {
          if ($this->b_isLogEnabled)
          {
              fwrite($this->h_Handle, '</table></div>');
              fflush($this->h_Handle);
              fclose($this->h_Handle);
          }
      }
      
      function write ($logLevel, $s_message, $m_object = null)
      {
          if ($this->b_isLogEnabled)
          {
              $m_levels = array(
                  HLOG_FATAL => 'Fatal',
                  HLOG_ERROR => 'Error',
                  HLOG_WARNING => 'Warning',
                  HLOG_INFO => 'Info',
                  HLOG_DEBUG => 'Debug',
              );
          
              //$func = mb_substr(__FILE__, mb_strlen($_SERVER['DOCUMENT_ROOT'])+1);
              //echo dump($trace);
              $trace = debug_backtrace (DEBUG_BACKTRACE_IGNORE_ARGS);
              $line =  count($trace) > 1 ? $trace[1] : $trace[0];
              $linenum = isset($line['line']) ? $line['line'] : $trace[0]['line'];
              $func = @$line['class'] . @$line['type'] . @$line['function'] .' [' . $linenum .']';
              $func = str_replace('HellishCode\\', '', $func);
              
              $s_text = '<tr class=l'.$logLevel.'><td class="t">'.date('H:i', time()).'</td>';
              $s_text .= '<td class="l">'.$m_levels[$logLevel].'</td>';
              $s_text .= '<td class="f">'.$func.'</td>';
              if ($m_object) $s_message .= '<div class="dump">'.dump($m_object).'</div>';
              $s_text .= '<td class="m">'.$s_message."</td></tr>\r\n";

              fwrite($this->h_Handle, $s_text);
              fflush($this->h_Handle);
          }
      }
  }   
