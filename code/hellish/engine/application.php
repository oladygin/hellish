<?php
  
  class HApplication
  {
      static $p_Instance = null;
      var $m_Router;
      var $m_Logger;
      var $m_Vars;
      var $m_Params;
      var $m_DB;
      var $m_Auth;
      var $m_viewbag = array();
      
      var $isValid = false;
      var $isAuthorized = 'undef';
      
      function __construct ($params = null)
      {
          self::$p_Instance = $this;
          if ($params) $this->m_Params = $params;

          $this->m_Logger = new HLogger($this, $this->m_Params['debug'], HLOG_DEBUG);

          $this->m_Router = new HRouter($this, $this->m_Params['defaultPath']);
          
          $this->m_DB = new CDB();
          $this->m_DB->m_Logger = $this->m_Logger;
      }
      
      function me ()    {  return $this->p_Instance; }
      function router (){  return $this->m_Router; }
      function vars ()  {  return $this->m_Vars; }
      function log ()   {  return $this->m_Logger; }
      
      function value ($name, $val=null)
      {
          if ($val!==null) $this->m_viewbag[$name] = $val;
            else return $this->m_viewbag[$name];
      }
      
      function execute ($methodName )
      {
          $this->m_Vars = $this->m_Router->vars;
          $this->m_Logger->write(HLOG_DEBUG, "Executing: $methodName");
          if( is_callable( array($this, $methodName) ) )
          {
              return call_user_func(array($this,$methodName));
          } else {
              throw new Exception('In controller '.get_called_class().' method ['.$methodName.'] not found!');
          }
      }
      
      function filter_value ($filter_name)
      {
          try {
              $value = call_user_func(array($this, 'filter_' . $filter_name));
              return $value;
          } catch (Exception $ex) {
              $this->m_Logger->write(HLOG_WARNING, "Exception: ".$ex->getMessage());
              return false;
          }
      }
      
      function raise_error ($code)
      {
          switch($code)
          {
            case 502:
                header("Location: " . $this->m_Params['auth']['authredirect']); 
                exit();
            default:
                throw new Exception('Not valid request: '.$code);
                break;
          }
      }
      
      function start()
      {
          set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
              // error was suppressed with the @-operator
              if (0 === error_reporting()) { return false; }
              HApplication::$p_Instance->m_Logger->write (HLOG_FATAL, "Fatal error: ".$errstr . ' in file: ' . $errfile . ' in line ' . $errline);
              throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
          });    

      
          $func = $this->m_Router->parse();           

          // start DB here if defined
          if($this->m_Params['db'])
          {
              if(!$this->m_DB->Connect()) return $this->m_DB->s_last_error;
          }
          
          // start Auth here if defined
          if($this->m_Params['auth'])
          {
              $this->m_Auth = new HAuth();
              $this->isAuthorized = $this->m_Auth->do_auth();
          }

          if ($this->m_Router->is_method())
          {
              $filename = $this->m_Router->get_controller_filename();
              $classname = $this->m_Router->controller.'Controller';
              $this->m_Logger->write(HLOG_INFO, "Calling: ".$this->m_Router->route);
              $this->m_Logger->write(HLOG_DEBUG, "With parameters: ".dump($this->m_Router->vars,true));
              
              $m_Compiller = new HCompiller ($this->m_Router->area, $this->m_Params['debug']);
              $m_Compiller->execute($filename, $classname, $this->m_Router->action);
              
              try {
                $classname = 'HellishCode\\'.$classname;
                $instance = new $classname();
                $instance->m_Router = $this->m_Router;
                $instance->m_Logger = $this->m_Logger;
                $instance->m_Auth = $this->m_Auth;
                $instance->m_DB = $this->m_DB;
                $instance->m_Params = $this->m_Params;
                $instance->isValid = true;
                $instance->isAuthorized = $this->isAuthorized;
                self::$p_Instance = $instance;
                $m_Render = $instance->execute($this->m_Router->action);    

                if (is_string($m_Render))
                { 
                    echo $m_Render;
                } else if (is_object($m_Render)) {
                    $m_Render->layoutName = $this->m_Router->area.'/layout/default' . ($this->m_Router->is_mobile ? '_mobile' : '');
                    $m_Render->m_Logger = $this->m_Logger;
                    $m_Render->p_Application = $this;
                    echo $m_Render->render();
                } else {
                    echo "Controller return no view";
                }
                
              } catch (Exception $ex)
              {
                  $this->log()->write(HLOG_FATAL, 'Hellish user controller fatal exception: '.$ex->getMessage());
                  echo 'HELRESULT-ALERT      Hellish caught exception: ' .  $ex->getMessage() . ', file '.$ex->getFile().', line '.$ex->getLine()."\n";
              }
          }
          
          return true;
      }
      
      function is_model_valid ()
      {
          // general invalidos
          if(!$this->isValid) {
              $this->log()->write(HLOG_WARNING, 'Model not valid');
              return false;
          }
          
          // auth check for not authorized users
          if($this->isAuthorized === false)
          {
              $this->log()->write(HLOG_WARNING, 'User not authorized');
              if($this->filter_value('allow_anonimous'))
              { 
                  // allow controller access
                  $this->log()->write(HLOG_WARNING, 'Allow anonymous access to this controller');
              } else {
                  // authorization redirect
                  $this->log()->write(HLOG_WARNING, 'Reject anonymous access, redirect to: '.$this->m_Params['auth']['authredirect']);
                  $this->raise_error (502);
              }
          } else {
               $this->log()->write(HLOG_WARNING, 'User authorized');
          }
          
          $this->log()->write(HLOG_WARNING, 'Model valid');
          return true;
      }

      function hparam_int ($name)
      {
          if (isset($this->m_Vars[$name])) return intval($this->m_Vars[$name]);
          return null;
      }

      function hparam_bool ($name)
      {
          if (isset($this->m_Vars[$name])) return $this->m_Vars[$name] ? true : false;
          return null;
      }

      function hparam_object ($name)
      {
          if (isset($this->m_Vars[$name])) return json_decode(json_encode($this->m_Vars[$name]), false);
          return null;
      }
      
      function hparam_array ($name)
      {
          if (isset($this->m_Vars[$name])) return $this->m_Vars[$name];
          return null;
      }
      
      function hparam_string ($name)
      {
          if (isset($this->m_Vars[$name])) return htmlspecialchars(strval($this->m_Vars[$name]), ENT_QUOTES);
          return null;
      }

      function hparam_common ($name)
      {
          return $this->hparam_string ($name);
      }
}
  

?>
