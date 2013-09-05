<?php
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<dl id="system-message">
    <dt class="message">Message</dt>
    <dd class="message">
        <ul>
            <li>
                <?php echo $this->_params->get('successnotice'); ?>
            </li>
        </ul>
    </dd>
</dl>
