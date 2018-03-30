<?php
  
  /*
  HELLISH точка входа
  */

require_once ("functions/functions.php"); 
require_once ("engine/logger.php"); 
require_once ("engine/random.php"); 
require_once ("engine/router.php"); 
require_once ("engine/auth.php"); 
require_once ("engine/compiller.php"); 
require_once ("engine/application.php"); 
require_once ("engine/renderer.php"); 

if(defined('HELLISH_DB'))
{
    require_once ("engine/db_impl/db_".HELLISH_DB.".php"); 
}
  
?>
