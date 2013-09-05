<?php

defined('_JEXEC') or die('Restricted access');

class modContactFormHelper {

    private $_params;
    private $_email;
    private $_emailsubject;
    private $_table;
    public $showCAPTCHA = FALSE;
    public $data = array();
    public $fields = array(
        'fname' => array(
            'type' => 'text'
            , 'size' => 42
        )
        , 'lname' => array(
            'type' => 'text'
            , 'size' => 42
        )
        , 'title' => array(
            'type' => 'text'
            , 'size' => 42
        )
        , 'org' => array(
            'type' => 'text'
            , 'size' => 42
        )
        , 'address' => array(
            'type' => 'text'
            , 'size' => 42
        )
        , 'address2' => array(
            'type' => 'text'
            , 'size' => 42
        )
        , 'city' => array(
            'type' => 'text'
            , 'size' => 42
        )
        , 'state' => array(
            'type' => 'select'
            , 'values' => array(
                "AL" => "Alabama",
                "AK" => "Alaska",
                "AZ" => "Arizona",
                "AR" => "Arkansas",
                "CA" => "California",
                "CO" => "Colorado",
                "CT" => "Connecticut",
                "DE" => "Delaware",
                "DC" => "Dist of Columbia",
                "FL" => "Florida",
                "GA" => "Georgia",
                "HI" => "Hawaii",
                "ID" => "Idaho",
                "IL" => "Illinois",
                "IN" => "Indiana",
                "IA" => "Iowa",
                "KS" => "Kansas",
                "KY" => "Kentucky",
                "LA" => "Louisiana",
                "ME" => "Maine",
                "MD" => "Maryland",
                "MA" => "Massachusetts",
                "MI" => "Michigan",
                "MN" => "Minnesota",
                "MS" => "Mississippi",
                "MO" => "Missouri",
                "MT" => "Montana",
                "NE" => "Nebraska",
                "NV" => "Nevada",
                "NH" => "New Hampshire",
                "NJ" => "New Jersey",
                "NM" => "New Mexico",
                "NY" => "New York",
                "NC" => "North Carolina",
                "ND" => "North Dakota",
                "OH" => "Ohio",
                "OK" => "Oklahoma",
                "OR" => "Oregon",
                "PA" => "Pennsylvania",
                "RI" => "Rhode Island",
                "SC" => "South Carolina",
                "SD" => "South Dakota",
                "TN" => "Tennessee",
                "TX" => "Texas",
                "UT" => "Utah",
                "VT" => "Vermont",
                "VA" => "Virginia",
                "WA" => "Washington",
                "WV" => "West Virginia",
                "WI" => "Wisconsin",
                "WY" => "Wyoming")
        )
        , 'zip' => array(
            'type' => 'text'
            , 'size' => 42
            , 'validation' => 'zip'
        )
        , 'phone' => array(
            'type' => 'text'
            , 'size' => 42
            , 'validation' => 'phone'
            , 'mask' => '999-999-9999? x99999'
        )
        , 'email' => array(
            'type' => 'text'
            , 'size' => 42
            , 'validation' => 'email'
        )
        , 'comments' => array(
            'type' => 'textarea'
            , 'cols' => 42
            , 'rows' => 4
        )
        , 'custom1' => array()
        , 'custom2' => array()
        , 'custom3' => array()
        , 'custom4' => array()
        , 'custom5' => array()
    );

    function __construct(&$params) {
        $this->_params = $params;

        //build the array of fields
        $this->_getFields();

        if ($params->get('table'))
            $this->_table = $params->get('table');

        if ($params->get('sendemail'))
            $this->_email = $params->get('sendemail');
        
        if ($params->get('emailsubject'))
            $this->_emailsubject = $params->get('emailsubject');

        if ($params->get('captcha') == 'show')
            $this->showCAPTCHA = TRUE;
    }

    function putData() {
        $app = & JFactory::getApplication();

        if ($this->_table) {
            $db = & JFactory::getDBO();

            //remove unneeded data
            unset($this->data['limit']
                    , $this->data[JUtility::getToken()]
                    , $this->data['option']
                    , $this->data['layout']
                    , $this->data['view']
                    , $this->data['task']
                    , $this->data['recaptcha_challenge_field']
                    , $this->data['recaptcha_response_field']);

            //add extra data
            $this->data['created'] = date('Y-m-d H:i:s');
            $this->data['useragent'] = $_SERVER['HTTP_USER_AGENT'];
            $this->data['ip'] = $_SERVER['REMOTE_ADDR'];
            $this->data['url'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            //collate the data
			$fields = "";
			$values = "";
            foreach ($this->data as $key => $value) {
                if ($value) {
                    if (is_array($value))
                        $value = implode(',', $value);
                    if ($fields) {
                        $fields .= ", ";
                    }
                    if ($values) {
                        $values .= ", ";
                    }
                    $fields .= $db->nameQuote($key);
                    $values .= $db->quote(trim($value));
                }
            }

            //do the dew
            $query = "INSERT INTO " . $db->nameQuote($this->_params->get('table')) . " ($fields) VALUES ($values)";
            $db->setQuery($query);

            if (!$db->query()) {
                if ($db->getErrorNum() == 1054) { //unknown column
                } elseif ($db->getErrorNum() == 1146) {  //if the table doesn't exist, create it...
                    $query = "CREATE TABLE IF NOT EXISTS " . $db->nameQuote($this->_params->get('table')) . " (
                          `id` int(11) NOT NULL auto_increment,
                          `fname` text default NULL,
                          `lname` text default NULL,
                          `title` text default NULL,
                          `org` text default NULL,
                          `address` text default NULL,
                          `address2` text default NULL,
                          `city` text default NULL,
                          `state` text default NULL,
                          `zip` text default NULL,
                          `phone` text default NULL,
                          `email` text default NULL,
                          `comments` text default NULL,
                          `custom1` text default NULL,
                          `custom2` text default NULL,
                          `custom3` text default NULL,
                          `custom4` text default NULL,
                          `custom5` text default NULL,
                          `useragent` text,
                          `ip` text,
                          `url` text,
                          `created` datetime NOT NULL,
                          `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
                    $db->setQuery($query);
                    $db->query();
                    $this->putData(); //loop, to insert the data to the newly created table.
                    return;
                } else {
                    $user = & JFactory::getUser();
                    if (array_key_exists('Super Users', $user->groups)) { //super administrator, for debugging
                        $app->redirect(JRoute::_($_SERVER['REQUEST_URI']), $db->getErrorNum() . ' || ' . $db->getErrorMsg(), 'error');
                    } else { //everyone else
                        $app->redirect(JRoute::_($_SERVER['REQUEST_URI']), $this->_params->get('dberror'), 'error');
                    }
                }
            }
        }

        if (!empty($this->_email))
            $this->sendEmail();

        require(JModuleHelper::getLayoutPath('mod_contactform', 'success'));
    }

    private function _getFields() { //parse the parameter data
        foreach ($this->fields as $name => $fielddata) {
            $this->fields[$name]['status'] = $this->_params->get($name);

            if ($this->_params->get($name . 'Label'))
                $this->fields[$name]['label'] = $this->_params->get($name . 'Label');

            if ($this->_params->get($name . 'ErrorMessage'))
                $this->fields[$name]['errorMessage'] = $this->_params->get($name . 'ErrorMessage');

            if ($this->_params->get($name . 'Type'))
                $this->fields[$name]['type'] = $this->_params->get($name . 'Type');

            if ($this->_params->get($name . 'Value'))
                $this->fields[$name]['value'] = $this->_params->get($name . 'Value');

            if (substr($name, 0, 6) == 'custom') { //the custom fields are special.  as in olympics.
                if ($this->_params->get($name . 'Mask'))
                    $this->fields[$name]['mask'] = $this->_params->get($name . 'Mask');
                if ($this->_params->get($name . 'HelpTitle'))
                    $this->fields[$name]['helptitle'] = $this->_params->get($name . 'HelpTitle');
                if ($this->_params->get($name . 'HelpText'))
                    $this->fields[$name]['helptext'] = $this->_params->get($name . 'HelpText');
            }

            if ($name == "state" && $this->_params->get('states')) //allows the user to override all 50 states
                $this->fields[$name]['states'] = explode(',', $this->_params->get('states'));

            if ($this->_params->get($name . 'Values')) {
                $values = $this->_params->get($name . 'Values');

                if (strpos($values, '|'))
                    $valuesArray = explode('|', $values);
                else
                    $valuesArray = explode(',', $values);

                $this->fields[$name]['values'] = $valuesArray;
            }
        }
    }

    function sendEmail() {
        $app = & JFactory::getApplication();

        $from = $app->getCfg('mailfrom');
        $fromname = $app->getCfg('sitename');
        if (empty($this->_emailsubject)) {
            $subject = $fromname . ' -- New Submission';
        } else {
            $subject = $this->_emailsubject;
        }

        $body = "<table>";
        $body .= '<tfoot><tr><td colspan="2" style="font-size: smaller;"><hr noshade="noshade" size="3" width="100%" />This message has been brought to you by ';
        if (strtolower($fromname) !== "pdxfixit") {
            $body .= $fromname . " and ";
        }
        $body .= '<a href="http://www.pdxfixit.com/">PDXfixIT</a>.</td></tr></tfoot>';
        foreach ($this->data as $key => $value) {
            if (!empty($this->fields[$key]['label'])) { //give the user something a little prettier to look at.
                $key = $this->fields[$key]['label'];
            } else {
                switch ($key) {
                    case 'fname':
                        $key = "First Name";
                        break;
                    case 'lname':
                        $key = "Last Name";
                        break;
                    case 'org':
                        $key = "Organization";
                        break;
                    case 'address2':
                        $key = "Address 2";
                        break;
                    case 'zip':
                        $key = "Zip Code";
                        break;
                    case 'phone':
                        $key = "Phone Number";
                        break;
                    case 'email':
                        $key = "Email Address";
                        break;
                    case 'useragent':
                        $key = "Browser";
                        break;
                    case 'ip':
                        $key = "IP Address";
                        break;
                    case 'url':
                        $key = "Original URL";
                        break;
                    case 'created':
                        $key = "Time Stamp";
                        break;
                }
            }
            $body .= "<tr><td>{$key}:</td><td>{$value}</td></tr>";
        }
        $body .= "</table>";

        $mode = true;
        $cc = null;
        $bcc = null;
        $attachment = null;
        $replyto = $this->data['email'];
        $replytoname = trim($this->data['fname'] . " " . $this->data['lname']);

        foreach(explode(',', $this->_email) as $recipient) {
            JUtility::sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
        }
    }

}
