<?php
/**
 * The ${NAME} file.
 */

namespace CsrDelft\view\login;

use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\WachtwoordField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;

class LoginForm extends Formulier
{

    public function __construct()
    {
        parent::__construct(null, '/login');
        $this->formId = 'loginform';

        $fields['user'] = new TextField('user', null, null);
        $fields['user']->placeholder = 'Bijnaam of lidnummer';

        $fields['pass'] = new WachtwoordField('pass', null, null);
        $fields['pass']->placeholder = 'Wachtwoord';

        if (LoginModel::instance()->hasError()) {
            $fields[] = new HtmlComment('<p class="error">' . LoginModel::instance()->getError() . '</p>');
        } else {
            $fields[] = new HtmlComment('<div class="float-left">');

            $fields['pauper'] = new CheckboxField('pauper', false, null, 'Mobiel');

            $fields[] = new HtmlComment('</div>');

            $fields['remember'] = new CheckboxField('remember', false, null, 'Blijf ingelogd');
        }

        $this->addFields($fields);
    }

    public function view()
    {
        parent::view(false);
        ?>
        <ul>
            <li>
                <a href="#" class="login-submit" onclick="document.getElementById('loginform').submit();">Inloggen</a>
                &raquo;
                &nbsp; <a href="/accountaanvragen">Account aanvragen</a> &raquo;
            </li>
            <li><a href="/wachtwoord/vergeten">Wachtwoord vergeten?</a></li>
        </ul>
        <?php
    }

}