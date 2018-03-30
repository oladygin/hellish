<?php

namespace {

    class HRenderer
    {
        var $m_Logger;
        static $p_Instance = null;
        
        var $isPartial;    
        var $viewName;
        var $layoutName = 'default';
        var $modelData;
        
        function __construct()
        {
            self::$p_Instance = $this;
            //$this->p_Application = HApplication::$p_Instance;
        }
        
        function render ()
        {
            if($this->isPartial) {
                $this->m_Logger->write (HLOG_DEBUG, 'Render partial <b>'.$this->viewName.'</b>');
                $s_page = $this->render_view($this->viewName, $this->modelData);
            } else {
                $this->m_Logger->write (HLOG_DEBUG, 'Render <b>'.$this->viewName.'</b> on layout <b>'.$this->layoutName.'</b>');
                $s_page = $this->render_view($this->layoutName, null);
            }
            return $s_page;
        }

        function getDefaultView ()
        {
            return HApplication::$p_Instance->m_Router->controller . '/' . HApplication::$p_Instance->m_Router->action;
        }
        
        function get_smart_result ($mode, $text)
        {
            return 'HELRESULT-' . str_pad($mode,10) . $text;
        }
        
        function get_full_name_for_view ($view)
        {
            if (mb_strpos($view, '/') === false) {
                // only name
                $view = HApplication::$p_Instance->m_Router->controller . '/' . $view;
            } 
            return HApplication::$p_Instance->m_Router->area . '/view/' . $view;
        }
        
        function render_view ($viewname, $model)
        {
            $class = new ReflectionClass($this);
        
            $modelname = ''; 
            if(is_object($model)) $modelname = 'class '.get_class($model);
            else $modelname = gettype($model);
        
            $this->m_Logger->write (HLOG_DEBUG, 'Call <b>'.$class->getShortName().'</b>:render_view <b>'.$viewname.'</b>'.($model ? ' with model ('.$modelname.')' : ' without model'));
            ob_start();
            $templatefilename = $viewname.'.tpl';
            $templatefile = @file_get_contents($templatefilename);
            if (!$templatefile) {
                ob_get_clean();
                throw new Exception('Cant find template: '.$templatefilename);
            } else {
                $templatefile = preg_replace('/^:(.*)$/m', '<?php ${1} ?>', $templatefile);
                $templatefile = preg_replace('/\{\$(.*?)\}/', "<?php xecho ($\${1}); ?>", $templatefile);
                $templatefile = str_replace("<%=", "<?php echo ", $templatefile);
                $templatefile = str_replace("<%", "<?php", $templatefile);
                $templatefile = str_replace("%>", "?>", $templatefile);

                //$this->m_Logger->write (HLOG_DEBUG, dump($templatefile));
                
                while(true)
                {
                    $start_pos = mb_strpos($templatefile, '@{');
                    if($start_pos === false) break;
                    $end_pos = mb_strpos($templatefile, '}', $start_pos);
                    if($start_pos === false) break;
                    $name = mb_substr($templatefile, $start_pos+2, $end_pos-$start_pos-2);
                    $templatefile = mb_substr($templatefile, 0, $start_pos) . HApplication::$p_Instance->value($name) .  mb_substr($templatefile, $end_pos+1);
                }
                
                $s_funcname = str_replace("/", "_", $viewname);
                try {
                    eval ('function '.$s_funcname.' ($me, $model) { global $Html; ?>' . $templatefile . '<?php } ');
                    $me = HApplication::$p_Instance->m_Auth ? HApplication::$p_Instance->m_Auth->m_Me : null;
                    $s_funcname($me, $model);
                    $s_text = ob_get_clean();
                } catch (Exception $ex)
                {
                    echo $templatefile;
                }
                return $s_text;
            }
        }
        
    }

    function RenderBody ()
    {
        echo HRenderer::$p_Instance->render_view(HRenderer::$p_Instance->viewName, HRenderer::$p_Instance->modelData);
    }
    
    function HellishHeaders ()
    {
        return '<script src="/hellish/js/jquery/jquery-1.11.1.min.js" type="text/javascript"></script>
                <script src="/hellish/js/hellish.js" type="text/javascript"></script>';
    }
    
}

namespace HellishCode
{
    /**
    * Base view with Layout
    */
    class View extends \HRenderer
    {
        function __construct ($view = null, $model = null)
        {
            parent::__construct();
            
            if($view!==null && $model!==null) {
                $this->viewName = parent::get_full_name_for_view($view);
                $this->modelData = $model;
            } else if ($view) {
                $this->viewName = parent::getDefaultView();
                $this->modelData =  parent::get_full_name_for_view($view);
            } else {
                $this->viewName = parent::getDefaultView();
            }
            $this->isPartial = false;
        }

        function render ()
        {
            if(isset(\HApplication::$p_Instance->router()->vars['target'])) {
                $target = \HApplication::$p_Instance->router()->vars['target'];
                $this->m_Logger->write (HLOG_DEBUG, 'Render view <b>'.$this->viewName.'</b> as partial on target <b>'.$target.'</b>');
                $s_page = $this->render_view($this->viewName, $this->modelData);
            } else {
                $this->m_Logger->write (HLOG_DEBUG, 'Render <b>'.$this->viewName.'</b> on layout <b>'.$this->layoutName.'</b>');
                $s_page = $this->render_view($this->layoutName, null);
            }
            return $s_page;
        }
    }

    /**
    * Base view without Layout
    */
    class PartialView extends \HRenderer
    {
        function __construct ($view = null, $model = null)
        {
            parent::__construct();
            
            if($view!==null && $model!==null) {
                $this->viewName = parent::get_full_name_for_view($view);
                $this->modelData = $model;
            } else if ($view) {
                $this->viewName = parent::getDefaultView();
                $this->modelData =  parent::get_full_name_for_view($view);
            } else {
                $this->viewName = parent::getDefaultView();
            }

            $this->isPartial = true;
        }
    }

    
    /**
    * General eror
    */
    class ErrorView extends \HRenderer
    {
        function __construct ($errorMessage)
        {
            parent::__construct();
            $this->modelData = $errorMessage;
            $this->isPartial = true;
        }
        function render ()
        {
            $this->m_Logger->write (HLOG_DEBUG, 'Render <b>error</b> view ('.$this->modelData.')');
            return $this->get_smart_result ('ERROR', $this->modelData);
        }
    }          

    /**
    * Console view - write somedata to browser console
    */
    class ConsoleView extends \HRenderer
    {
        function __construct ($message)
        {
            parent::__construct();
            $this->modelData = $message;
            $this->isPartial = true;
        }
        function render ()
        {
            $this->m_Logger->write (HLOG_DEBUG, 'Render <b>to browser console</b> some data: '.$this->modelData.'');
            return $this->get_smart_result ('CONSOLE', $this->modelData);
        }
    }

    /**
    * Alert view - send alert to browser 
    */
    class AlertView extends \HRenderer
    {
        function __construct ($message)
        {
            parent::__construct();
            $this->modelData = $message;
            $this->isPartial = true;
        }
        function render ()
        {
            $this->m_Logger->write (HLOG_DEBUG, 'Render <b>alert to browser</b>: '.$this->modelData.'');
            return $this->get_smart_result ('ALERT', $this->modelData);
        }
    }

    /**
    * Execute view - execute code
    */
    class ExecuteView extends \HRenderer
    {
        function __construct ($message)
        {
            parent::__construct();
            $this->modelData = $message;
            $this->isPartial = true;
        }
        function render ()
        {
            $this->m_Logger->write (HLOG_DEBUG, 'Render <b>execute code</b>: '.$this->modelData.'');
            return $this->get_smart_result ('EXEC', $this->modelData);
        }
    }

    /**
    * Redirect view - goto another url
    */
    class RedirectView extends \HRenderer
    {
        function __construct ($url)
        {
            parent::__construct();
            $this->modelData = $url;
            $this->isPartial = false;
        }
        function render ()
        {
            $this->m_Logger->write (HLOG_DEBUG, 'Redirect to <b>'.$this->modelData.'</b>');
            if (\HApplication::$p_Instance->m_Router->is_ajax_request)
                return $this->get_smart_result ('EXEC', 'window.location.href="'.$this->modelData.'";');
            else {
                return '<script>window.location.href="'.$this->modelData.'";</script>';
            }
        }
    }
    
    /**
    * Output any string
    */
    class StringView extends \HRenderer
    {
        function __construct ($message)
        {
            parent::__construct();
            $this->modelData = $message;
            $this->isPartial = true;
        }
        function render ()
        {
            $this->m_Logger->write (HLOG_DEBUG, 'Render <b>string</b> view ('.$this->modelData.')');
            return $this->modelData;
        }
    }
}  


?>
