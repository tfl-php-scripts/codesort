<?php
/*****************************************************************************
 * CodeSort
 *
 * Copyright (c) Jenny Ferenc <jenny@prism-perfect.net>
 * Copyright (c) 2019 by Ekaterina (contributor) http://scripts.robotess.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ******************************************************************************/

if (!isset($listing)) { exit; }

echo '<div id="donate" class="codesort">'."\n";

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();
$cs->AddOptFromDb();

if ($cs->GetOpt('do_upload')) {

    $form_action = $cs->GetOpt('install_url').'/donate.php';

    if (!$cs->GetOpt('use_captcha')) {

        $form_action = clean_input($_SERVER['PHP_SELF']);

        require_once('do-donate.php');
    }

    if (!isset($_POST['action'])) {

    $query_donor = 'SELECT * FROM ' .$cs->GetOpt('donors_table'). ' ORDER BY donor_name ASC';

    $cs->db->execute($query_donor);

    $num_donor = $cs->db->getNumRows();

    if ($num_donor > 0) {
        $msg = 'If you have previously donated codes to any fanlistings that belong to the <strong>'.$cs->GetOpt('collective_name').'</strong> collective, select your name from the dropdown list below. Otherwise, enter your name and URL in the new donor form.';
    } else {
        $msg = 'Enter your name and URL below.';
    }

    ?>

    <p><?php echo $msg; ?> Select the number of codes you would like to donate and click continue.</p>

    <p class="info">Fields marked with an asterisk (*) are required.</p>

    <form action="<?php echo $form_action; ?>" method="post">
    <input type="hidden" name="listing" value="<?php echo $listing; ?>" />
    <input type="hidden" name="returnto" value="http://<?php echo $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].clean_input($_SERVER['PHP_SELF']); ?>" />

    <?php if ($num_donor > 0) { ?>
    <p><label for="code_donor">* Returning Donor name</label>
    <select id="code_donor" name="code_donor">
    <option value="0">Select your name:</option>
    <?php
    while ($row = $cs->db->readRecord()) {
        echo '<option value="'.$row['donor_id'].'">'.$row['donor_name']."</option>\n";
    }
    ?>
    </select> OR</p>
    <?php } $cs->db->freeResult(); ?>

    <p><label for="new_donor_name">* New donor name</label>
    <input type="text" id="new_donor_name" name="new_donor_name" size="20" maxlength="20" /></p>

    <p><label for="new_donor_url">New donor URL</label>
    <input type="text" id="new_donor_url" name="new_donor_url" size="20" maxlength="100" /></p>

    <p><label for="new_num_of_uploads">* Number of codes</label>
    <select id="new_num_of_uploads" name="num_of_uploads">
    <option>1</option>
    <option>2</option>
    <option>3</option>
    <option>4</option>
    <option>5</option>
    <option>6</option>
    <option>7</option>
    <option>8</option>
    <option>9</option>
    <option>10</option>
    </select></p>

    <?php if ($cs->GetOpt('use_captcha')) { ?>
    <p><label>&nbsp;</label>
    <img src="<?php echo $cs->GetOpt('install_url'); ?>/captcha.php" alt="captcha" /></p>
    <p><label for="captcha">Captcha</label>
    <input type="text" id="captcha" name="captcha" /></p>
    <?php } ?>

    <p><label>Action</label>
    <input type="submit" name="action" value="continue" /></p>

    </form>

    <?php

    }

} else {
    echo '<p>Uploading disabled.</p>';
}

$cs->printCredits();

echo "</div><!-- END #donate .codesort -->\n";

// restore old error handler
restore_error_handler();
