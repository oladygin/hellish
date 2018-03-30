<?php
    //error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    error_reporting(E_ALL);
    header('Content-Type: text/html; charset=utf-8');

    date_default_timezone_set('Europe/Moscow');
    define('ROOT',dirname(__FILE__).'/');
    define('HELLISH_DB','postgress');
    include '/hellish/hellish.php';
    
    include '/app/helpers/common.php';
   
    // Load own facade
    require_once ('facade/dbfuncs_'.HELLISH_DB.'.php');
    
    class MySite extends HApplication
    {
        // создать слуайный пароль
        function generatePassword ($length = 10)
        {
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz";
            $password = '';
            while ($length--)
            {
                $password .= $chars[mt_rand(1, strlen($chars)-1)];
            }
            return $password;
        }

        //        
        function send_email ($to, $subject, $message)
        {
            $this->m_Logger->write (HLOG_DEBUG, "send_email: to '$to' ($subject)");

            $mail_message = '<!DOCTYPE HTML><html><head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                                    <style type="text/css">
                                        HTML, BODY { color: #333; font: normal 12px Tahoma; font-size:14px;}
                                        p          { margin:12px 0; color: #333; font-size:14px;}
                                        a          { color:#45903E;text-decoration:none; }
                                        h1         { color:#F15A29; font-size:20px; magrin:4px 0; }
                                        h2         { color:#F15A29; font-size:15px; magrin:12px 0; }
                                        h3         { color:#00A79D; font-size:16px; magrin:4px 0; }
                                        .rem       { color:#999; font-size:12px; padding:24px 0 12px 0; }
                                        .comment   { color:#333; font-size:12px; padding-left:8px; border-left: 6px solid #F15A29; }
                                    </style>            
                                   </head>
                                   <body>'.$message.'</body>
                             </html>';
            
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset="uft-8"' . "\r\n";
            //$headers .= 'To: ' . $to . "\r\n";
            if($this->m_Params['debug'] || true)
            {
                $headers .= "Bcc: oladygin@gmail.com\r\n";
            }
            $headers .= $this->m_Params['params']['isrelease'] ? 
                            'From: subcontract-info@billing.ru' . "\r\n" :
                            'From: unknownsender-noreply@billing.ru' . "\r\n";

            return mail($to, $subject, $mail_message, $headers);
        }

    }

    $host = $_SERVER['HTTP_HOST'];
    $isProd = ($host == 'hsomehostname.ru');

    $config = array(
        'db' => array (
                    'host' => $isProd ? '127.0.0.1' : 'localhost',
                    'port' => $isProd ? 5432 : 5433,
                    'db' => 'dbname',
                    'login' => 'postgres',
                    'password' => $isProd ? 'prodPassword' : 'localPassword'
                ),
        'debug' => !$isProd, 
        'params' => array(
                    'isrelease' => $isProd,
                    'hostname' => $host,
                    'ignorepasswords' => false  //!$isProd 
                ),
        'auth' => array(
                'allowanonymous' => false,
                'authredirect'   => '/',
                'sessionname'    => 'SUBC_SESS',
                'cookiename'     => 'SUBC_COOK',
                'usermodel'      => '\MUser',        // должен поддерживать метод LoadByHash и GetLogName и isReal
                'creteanonymous' => false
                ),
        'defaultPath' => array (
                'area' => 'app',
                'controller' => 'index',
                'action' => 'index'
                ),
        'keys' => array(
                'cryptKey' => "Base64Key=",   
                'cryptIV'  => "Base64Iv="     
                )
    );

    // Execute
    $my_app = new MySite($config);

    if(($result = $my_app->start()) !== true)
    {
        echo "ENGINE START ERROR: " . $result;
    }
    
?>
