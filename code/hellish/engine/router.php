<?php

class HRouter 
{
    var $route;
    var $is_ajax_request;
    var $is_mobile;
    
    var $area;
    var $controller;
    var $action;
    var $id;

    var $vars = array();
    var $defaultPath;
    
    private $reg_paths = array(
        '([a-z0-9]+)/([a-z0-9]+)/([a-z0-9]+)/([0-9]+)' => 'area/controller/action/id',
        '([a-z0-9]+)/([a-z0-9]+)/([0-9]+)' => 'controller/action/id',
        '([a-z0-9+_\-]+)/([0-9]+)' => 'controller/id',
        '([a-z0-9]+)/([a-z0-9]+)/([a-z0-9]+)' => 'area/controller/action',
        '([a-z0-9]+)/([a-z0-9]+)' => 'controller/action',
        '([a-z0-9+_\-]+)' => 'controller',
    );
   
    function __construct ($app, $defPath)
    {
          $this->defaultPath = $defPath;
    }
   
    function parse($rewritePath = false)
    {
        $this->route = $path = (strtolower($rewritePath ? $rewritePath : $_GET['route']));
        $this->area = $this->defaultPath['area'];
        $this->controller = $this->defaultPath['controller'];
        $this->action = $this->defaultPath['action'];
        $this->id = '';

        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'undefined-agent';
        HApplication::$p_Instance->log()->write(HLOG_DEBUG, 'Router start: HTTP_USER_AGENT="'. $agent . '", from address: '.$_SERVER['REMOTE_ADDR']);
        HApplication::$p_Instance->log()->write(HLOG_DEBUG, 'Route: "'.$this->route.'"' . ($this->is_ajax_request ? ' by AJAX' : '') . ($this->is_mobile ? ' from MOBILE' : ''));
        
        $this->is_ajax_request = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        $this->is_mobile = $this->is_mobile();
        $this->id = null;
        $this->vars = array();

        HApplication::$p_Instance->log()->write(HLOG_DEBUG, 'Route: "'.$this->route.'"' . ($this->is_ajax_request ? ' by AJAX' : '') . ($this->is_mobile ? ' from MOBILE' : ''));
        
        foreach($this->reg_paths as $regxp => $keys) 
        {
            if (preg_match('#'.$regxp.'#', $path, $res)) 
            {
                $keys = explode('/',$keys);
                // action
                foreach ($keys as $i => $key) 
                {
                  $this->$key = $res[$i+1];
                }
                // id
                if ($this->id !== null)$this->vars['id'] = intval($this->id);
                // GET params
                foreach($_GET as $k => $v)
                {
                    if($k != 'route')
                    {
                        $this->vars[$k] = $v;
                    }
                }
                // POST params
                $input = file_get_contents("php://input");
                $_POST = $this->magic_parse_str($input); // need for mod_rewrite
                $smartdata = json_decode ($input,true);
                if(is_array($smartdata))
                {
                    foreach($smartdata as $k => $v)
                    {
                        $this->vars[$k] = $v;
                    }
                } else if(@key($_POST)) {
                    $postdata = json_decode (key($_POST),true);
                    if ($postdata) foreach($postdata as $k => $v)
                    {
                        $this->vars[$k] = $v;
                    } else {
                        foreach($_POST as $k => $v)
                        {
                            $this->vars[$k] = $v;
                        }
                    }
                }
                
                return $this->get_handler_name ();
            }
        }
    }
    
    /// its because of 'parse_str' manual say: 'Dots and spaces in variable names are converted to underscores.'
    function magic_parse_str ($data)
    {                                  
        $data = preg_replace_callback('/(?:^|(?<=&))[^=[]+/', function($match) {
            return bin2hex(urldecode($match[0]));
        }, $data);
        parse_str($data, $values);
        return @array_combine(array_map('hextobin', array_keys($values)), $values);
    }
    
    public function is_method()
    {
        return mb_stripos($this->route, '.') === false;
    }
    
    private function get_handler_name ()
    {
        return 'ON_'.$this->area.'_'.$this->controller.'_'.$this->action;
    }
    
    public function get_controller_filename()
    {
        return $this->area.'/controller/'.$this->controller.'.php';
    }
    
    public function is_mobile()
    {
        $useragent = @$_SERVER['HTTP_USER_AGENT'];
        return (preg_match('/(iPad|android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));
    }
  
}  
?>
