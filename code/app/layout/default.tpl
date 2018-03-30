<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Skillber Subcontract Space</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="x-ua-compatible" content="IE=10,9" >
    <link rel="icon" type="image/png" href="/css/images/favicon.png" />
    <link rel="shortcut icon" type="image/ico" href="/css/images/favicon.png" />

    <!-- Hellish and Base -->
    <%= HellishHeaders() %>
    <link rel="stylesheet" href="/js/chosen/chosen.css">
    <script src="/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
    <script src="/js/jquery-ui.custom.min.js" type="text/javascript"></script>
    
    <!-- Own Javascript -->
    <script src="/js/common.js" type="text/javascript"></script>
    <script src="/js/interface.js" type="text/javascript"></script>
    <script src="/js/jquery.poshytip.min.js" type="text/javascript"></script>
    <link href="/css/tooltip/tip-yellowsimple.css" rel="stylesheet" type="text/css" />

    <!-- Own CSS -->
    <link href="/css/main.css" rel="stylesheet" type="text/css"/>
    <link href="/css/interface.css" rel="stylesheet" type="text/css"/>
    <link href="/css/thirdparty.css" rel="stylesheet" type="text/css"/>
</head>
 
<body class="standart">
    <div id="totopcontrol_place"></div>

    <div class="top-shadow" id="page">
        <div class="top-light login">
          <header>  
              <div class="wrapper-full">
                <a class="logo beta" href="/">Skillber</a>    
              </div>
          </header>
          <section class="main">
              <section class="main-nav">
                    <div class="wrapper-full">
                        <nav class="profile-nav">
                            <ul>
:                           if ($me->is_real() && $me->user_type==USERTYPE_MANAGER) {
                                <li id="topmenu_profile" class="active"><a class="kabin" href="/Profile/Index/{$me->user_id}"><b></b>Кабинет</a></li>
:                           } else if ($me->is_real() && $me->user_type==USERTYPE_OUTSOURCER) {
                                <li id="topmenu_profile" class="active"><a class="anket" href="/Profile/Index/{$me->user_id}"><b></b>Анкета</a></li>
:                           } else if ($me->is_real() && $me->user_type==USERTYPE_ADMIN) {
                                <li id="topmenu_profile" class="active"><a class="anket" href="/Admin/Index/"><b></b>Админ</a></li>
:                           } else {
                                <li id="topmenu_profile" class="active "><a class="" href="/">&nbsp;</a></li>
:                           }
                            </ul>
                        </nav>
                        <nav class="main-menu">
:                           if ($me->user_type==USERTYPE_ADMIN) {
                                <ul class="menu">
                                    <li><a href="/Admin/Loghistory">Системный лог</a></li>  
                                </ul>            
                                <ul class="menu">
                                    <li><a href="/Admin/Base">База</a></li>  
                                </ul>            
                                <ul class="">
                                    <li class="">{$me->GetName()}</li>
                                    <li class="logout"><a class="" href="/Index/Logout"><b></b>Выйти</a></li>
                                </ul>            
:                           } else {
                                <div class="beta">
                                    Стенд анкетирования Skillber - <span>Beta</span>-версия
                                </div>  
                                <ul>
                                    <li class="">{$me->GetName()}</li>
                                    <li class="logout"><a class="" href="/Index/Logout"><b></b>Выйти</a></li>
                                </ul>            
:                           }
                    </div>
                </section>
                <section class="main-content">
                    <div class="wrapper-full" id="page">
                        
                        <%= RenderBody() %>

                    </div>
                </section>
              </section>
              <footer>
                <div class="wrapper-full">   
                    <div id="copyright">"PETER-SERVICE" 2016. All rights reserved.</div>
                    <a class="footer-logo" href="/"></a>
                 </div>
              </footer>
        </div>
    </div>
    <div id="general-container"></div>
</body>
</html>
