<?php

defined('_JEXEC') or die('Restricted access');

require_once (dirname(__FILE__) . DS . 'helper.php');
$reg = new modContactFormHelper($params);

if ($reg->showCAPTCHA && $params->get('captcha-public') && $params->get('captcha-private')) {
    require_once (dirname(__FILE__) . DS . 'recaptchalib.php');
    $publickey = $params->get('captcha-public');
    $privatekey = $params->get('captcha-private');
}

if (JRequest::get('POST')) {
    // Check for request forgeries
    JRequest::checkToken() or jexit('Invalid Token');

    if ($reg->showCAPTCHA) {
        //CAPTCHA verify
        $resp = recaptcha_check_answer($privatekey
                , $_SERVER["REMOTE_ADDR"]
                , $_POST["recaptcha_challenge_field"]
                , $_POST["recaptcha_response_field"]);

        if (!$resp->is_valid) { // What happens when the CAPTCHA was entered incorrectly
            $app = & JFactory::getApplication();
            $app->redirect(JRoute::_($_SERVER['REQUEST_URI']), "The reCAPTCHA wasn't entered correctly. Please try again.", 'error');
        }
    }

    //if CAPTCHA is successful, continue...
    $reg->data = JRequest::get('POST');

    //save data to database and/or email
    $reg->putData();
} else {
    require JModuleHelper::getLayoutPath('mod_contactform', $params->get('layout', 'default'));
}
