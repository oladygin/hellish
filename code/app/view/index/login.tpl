<div class="page-login data-login">

    <div class="banner"></div>
    <div class="line"></div>
    
    <form autocomplete="on" method="post">
        <table class="lo">
            <tr>
                <th><h1>Получить доступ</h1></th>
                <th><h1>Авторизация</h1></th>
            </tr>
            <tr>
                <td><input class="formval" name="emailget" placeholder="Электронная почта*"/></td>
                <td><input class="formval" name="email" placeholder="Электронная почта*"/></td>
            </tr>
            <tr>
                <td class="rem" style="font-size:11px;">Чтобы получить или восстановить доступ, укажите e-mail, по которому вы зарегистрированы в компании "Петер-Сервис".</td>
                <td><input class="formval" name="password" placeholder="Пароль*" type="password"/></td>
            </tr>
            <tr>
                <td><button onclick="return on_login(event,this,1)">Получить доступ</button></td>
                <td><button onclick="return on_login(event,this,0)">Войти</button></td>
            </tr>
        </table>
    </form>

    <div id="place-error"></div>

    <div class="help">Что-то пошло не так? &nbsp; <a href="mailto:subcontract@billing.ru">Напишите нам</a></div>    
</div>
