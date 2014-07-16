<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

//prep output fields' HTML & JS for display
foreach ($reg->fields as $name => $fielddata) {
    if (strtolower($fielddata['status']) === 'show' || strtolower($fielddata['status']) === 'required') {

        //if the field is required, get the validation type, if any, and prep for inclusion
        if ($fielddata['status'] == 'required') {
            $requiredTitle = ' title="REQUIRED"';
            $requiredSpan = ' <span class="red" title="Required.">*</span>';
            if ($fielddata['validation'])
                $requiredClass = ' class="required ' . $fielddata['validation'] . '"';
            else
                $requiredClass = ' class="required"';

            //add error message
            if ($fielddata['errorMessage']) {
                if ($jsMessages)
                    $jsMessages .= ',';
                $jsMessages .= "\n            {$name}: \"{$fielddata['errorMessage']}\"";
            }
        }else
            unset($requiredTitle, $requiredSpan, $requiredClass);

        //custom fields can have helper info
        if ($fielddata['helptitle'] && $fielddata['helptext']) {
            $helpText = " <a href=\"#\" id=\"{$name}-link\">{$fielddata['helptitle']}</a>";
            $jsDialogs .= "\n    jQuery('a#{$name}-link').click(function(){
        jQuery('<div></div>')
        .html('{$fielddata['helptext']}')
        .dialog({
            autoOpen: false
            ,title: '{$fielddata['label']}'
            ,modal: true
            ,draggable: false
            ,resizable: false
        })
        .dialog('open');
        return false;
    });";
        }else
            unset($helpText); //leave $jsDialogs alone, it is a cumulative variable.

            
//some standard tags, provided here for re-use
        $openingTags = "                        <tr>
                            <td class=\"label\">
                                <label for=\"{$name}\" id=\"{$name}Label\"{$requiredTitle}>
                                    {$fielddata['label']}:{$requiredSpan}
                                </label>
                            </td>   
                            <td class=\"value\">\n                                ";
        $closingTags = "                            </td>
                        </tr>\n";

        switch ($fielddata['type']) {
            case 'select':
                $html .= $openingTags;
                $html .= "<select id=\"{$name}\" name=\"{$name}\"{$requiredTitle}{$requiredClass}>\n";
                $html .= "<option></option>";
                foreach ($fielddata['values'] as $key => $value) {
                    if (is_numeric($key)) {
                        $html .= "<option value=\"{$value}\"";
                        if ($value == $fielddata['value'])
                            $html .= ' selected="selected"';
                        $html .= ">{$value}</option>\n";
                    }else {
                        if ($name == "state" && !empty($fielddata['states']) && !in_array($key, $fielddata['states'])) {
                            //skip states not in the array.
                        } else {
                            $html .= "<option ben='{$fielddata['states']}' value=\"{$key}\"";
                            if ($key == $fielddata['value'] || $value == $fielddata['value'])
                                $html .= ' selected="selected"';
                            $html .= ">{$value}</option>\n";
                        }
                    }
                }
                $html .= "</select>";
                $html .= $helpText;
                $html .= $closingTags;
                break;
            case 'radio':
                $html .= "<tr>
                            <td colspan=\"2\" style=\"text-align:center;\"{$requiredTitle}>{$fielddata['label']}:{$requiredSpan}</td>
                        </tr><tr>
                            <td colspan=\"2\" style=\"text-align:center;\">";
                $c = 1;
                foreach ($fielddata['values'] as $value) {
                    if ($c > 1)
                        $html .= "&nbsp;&nbsp;&nbsp;";
                    $html .= "<input type=\"radio\" name=\"{$name}\" id=\"" . $name . '-' . $c . "\" value=\"{$value}\"";
                    if ($value == $fielddata['value'])
                        $html .= ' checked="checked"';
                    $html .= "{$requiredTitle}{$requiredClass} /> <label for=\"" . $name . '-' . $c . "\">{$value}</label>";
                    ++$c;
                }
                unset($c);
                $html .= '<br />' . $helpText;
                $html .= '</td></tr>';
                break;
            case 'checkbox':
                $html .= "<tr>
                            <td colspan=\"2\" style=\"text-align:center;\"{$requiredTitle}>{$fielddata['label']}:{$requiredSpan}</td>
                        </tr><tr>
                            <td colspan=\"2\" style=\"text-align:center;\">";
                $c = 1;
                foreach ($fielddata['values'] as $value) {
                    if ($c > 1)
                        $html .= "&nbsp;&nbsp;&nbsp;";
                    $html .= "<input type=\"checkbox\" name=\"{$name}[{$c}]\" id=\"{$name}-{$c}\" value=\"{$value}\"";
                    if ($value == $fielddata['value'])
                        $html .= ' checked="checked"';
                    $html .= "{$requiredTitle}{$requiredClass} /> <label for=\"{$name}-{$c}\">{$value}</label>";
                    ++$c;
                }
                unset($c);
                $html .= '<br />' . $helpText;
                $html .= '</td></tr>';
                break;
            case 'textarea':
                $html .= $openingTags;
                $html .= "<textarea id=\"{$name}\" name=\"{$name}\"";
                //not currently used, but you never know...
                if ($fielddata['rows'])
                    $html .= " rows=\"{$fielddata['rows']}\"";
                if ($fielddata['cols'])
                    $html .= " cols=\"{$fielddata['cols']}\"";
                $html .= ">{$fielddata['value']}</textarea>";
                $html .= $closingTags;
                break;
            case 'hidden':
                $html .= "<input type=\"hidden\" name=\"{$name}\" id=\"{$name}\" value=\"{$fielddata['value']}\" />\n";
                break;
            case 'text':
            case 'password':
            default:
                $html .= $openingTags;

                //collect input masks
                if ($fielddata['mask'])
                    $jsInputMasks .= "\n    jQuery(\"input#{$name}\").mask(\"{$fielddata['mask']}\");";

                $html .= "<input type=\"{$fielddata['type']}\" name=\"{$name}\" id=\"{$name}\"";
                //every reasonable option, some for future use.
                if ($fielddata['size'])
                    $html .= " size=\"{$fielddata['size']}\"";
                if ($fielddata['value'])
                    $html .= " value=\"{$fielddata['value']}\"";
                if ($fielddata['disabled'])
                    $html .= " disabled=\"disabled\"";
                if ($fielddata['readonly'])
                    $html .= " readonly=\"readonly\"";
                if ($fielddata['maxlength'])
                    $html .= " maxlength=\"{$fielddata['maxlength']}\"";
                $html .= "{$requiredTitle}{$requiredClass} />";
                $html .= $helpText;
                $html .= $closingTags;
                break;
        }
    }
}

//prep JS
$js = "";
if (!empty($jsMessages)) {
    $js .= "\n\njQuery.noConflict();
    jQuery(document).ready(function(){
        jQuery(\"#form\").validate({
            messages: {"
            . $jsMessages . "
            }
            ,errorContainer: \"#errors\"
            ,errorLabelContainer: \"#errors\"
            ,wrapper: \"p\"
        });"
            . $jsInputMasks
            . $jsDialogs . "
    });\n";
}
if ($reg->fields['zip']['status'] != 'hide') {
    $js .= 'jQuery.validator.addMethod("zip", function(value, element) {
        return this.optional(element) || /^[0-9]{5}(?:(-?)[0-9]{4})?$/.test(value);
    }, "Your zip code must be 5 or 9 digits.");';
}
if ($reg->fields['phone']['status'] != 'hide') {
    $js .= 'jQuery.validator.addMethod("phone", function(phone_number, element) {
    if( phone_number == "___-___-____ x_____" ) return true; //ignores empty input mask
    //phone_number = phone_number.replace(/\s+/g, "");
	return this.optional(element) || phone_number.length > 9 &&
		phone_number.match(/^\(?\b[0-9]{3}\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}\b(?:[\s]?[x]?[\d]{1,5})?/);
}, "Please specify a valid phone number");';
}

// show it
$moduleclass_sfx = ' ' . htmlspecialchars($params->get('moduleclass_sfx'));
$doc = & JFactory::getDocument();
$baseurl = JURI::base(true);
$doc->addStyleSheet('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/ui-lightness/jquery-ui.css');
$doc->addStyleDeclaration('.label { text-align:right; } .value { text-align:left; } .red, label.error { color:red; } #errors { display:none; } #recaptcha_widget_div { position:inherit !important; top:0 !important; }');
$doc->addScript($baseurl . '/modules/mod_contactform/js/jquery.validate.min.js', 'text/javascript', true);
$doc->addScript($baseurl . '/modules/mod_contactform/js/jquery.maskedinput.min.js', 'text/javascript', true);
$doc->addScriptDeclaration($js);

/*
 * BEGIN OUTPUT
 */

if ($reg->successnotice) {
    ?>
    <dl id="system-message">
        <dt class="message">Message</dt>
        <dd class="message">
            <ul>
                <li>
    <?php echo $reg->successnotice; ?>
                </li>
            </ul>
        </dd>
    </dl>
<?php } ?>
<div id="errors"></div>
<div class="contactform<?php echo $moduleclass_sfx; ?>">
    <form method="post" id="form" name="contactform">
        <table>
            <!-- BEGIN FOOTER -->
            <tfoot>
                <tr>
                    <td>
                        <?php echo JHTML::_('form.token'); ?>
                        <input type="submit" id="submit" value="Submit" style="width:40%;" />
                        <input type="reset" id="reset" value="Clear" style="width:40%;" />
                    </td>
                </tr>
            </tfoot>
            <!-- END FOOTER -->

            <!-- START BODY -->
            <tbody>
                <tr>
                    <td>
                        <table>
<?php echo $html; ?>
                        </table>
                    </td>
                </tr>
<?php if ($reg->showCAPTCHA) {
    echo '<tr><td align="center">'
        . recaptcha_get_html($publickey)
        . '</td></tr>';
} ?>
            </tbody>
            <!-- END BODY -->
        </table>
    </form>
</div>
