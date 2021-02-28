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

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();

require_once('protect.php');

$cs->AddOptFromDb();

$clean = array();
$showMain = true;
$showList = true;

$cs->GetHeader('Fanlistings');

if (isset($_POST['action'])) {

    $clean = clean_input($_POST);

// ____________________________________________________________ ADD QUERY

    if ($_POST['action'] == 'add') {

        $showList = false;

        if (empty($clean['fl_subject'])) {
            $cs->AddErr('The fanlisting name is blank!');
        } else {

            $sql_fl_subject = $cs->db->escape($clean['fl_subject']);

            $query_check = 'SELECT COUNT('.$cs->GetOpt('col_id').')
              FROM '.$cs->GetOpt('collective_table').'
              WHERE '.$cs->GetOpt('col_subj')."='$sql_fl_subject'";

            $num_check = $cs->db->getFirstCell($query_check);

            if ($num_check > 0) {
                $cs->AddErr('The fanlisting <strong>'.$clean['fl_subject'].'</strong> already exists! Please choose another name.');
            }
        }

        if ($cs->NoErr()) {

            $query = 'INSERT INTO '.$cs->GetOpt('collective_table').' ('.$cs->GetOpt('col_subj').")
              VALUES ('$sql_fl_subject')";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('The fanlisting <strong>'.$clean['fl_subject'].'</strong> has been <strong>added</strong> successfully.');
                $clean = array();
                $showList = true;
            }
        }

// ____________________________________________________________ UPDATE QUERY

    } elseif ($_POST['action'] == 'update') {

        $success_message = 'Fanlistings <strong>';

        $count = count($clean['fl_id']);

        for ($i=0; $i<$count; $i++) {

            $sql_id = (int)$clean['fl_id'][$i];
            $sql_name = $cs->db->escape($clean['fl_subject'][$i]);

            $query = 'UPDATE '.$cs->GetOpt('collective_table').'
              SET '.$cs->GetOpt('col_subj')."='$sql_name'
              WHERE ".$cs->GetOpt('col_id')."=$sql_id";

            if ($cs->db->execute($query)) {
                $success_message .= '#'.$sql_id.' ';
            }
        }

        $success_message .= 'updated</strong> successfully.';
        $cs->AddSuccess($success_message);
        $clean = array();

// ____________________________________________________________ DELETE QUERY

    } elseif ($_POST['action'] == 'delete') {

        $cat_count = count($clean['delete_fl_id']);

        for ($i=0; $i<$cat_count; $i++) {

            $sql_id = (int)$_POST['delete_fl_id'][$i];

            $query = 'DELETE FROM '.$cs->GetOpt('collective_table').'
              WHERE '.$cs->GetOpt('col_id')."=$sql_id LIMIT 1";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('Fanlisting <strong>#'.$sql_id.'</strong> deleted.');

                $query_cat = 'DELETE FROM '.$cs->GetOpt('cat_table')." WHERE cat_fl=$sql_id";

                if ($cs->db->execute($query_cat)) {
                    $cs->AddSuccess('All <strong>'.$cs->db->getAffectedRows().'</strong> related categories deleted.');
                }

                $query_code = 'DELETE FROM '.$cs->GetOpt('codes_table')." WHERE code_fl=$sql_id";

                if ($cs->db->execute($query_code)) {
                    $cs->AddSuccess('All <strong>'.$cs->db->getAffectedRows().'</strong> related code records deleted.');
                }
            }
        }
        $clean = array();
    }
} // end if $_POST['action']

// _____________________________________________ REPORT SUCCESS

$cs->ReportSuccess();

// _____________________________________________ REPORT ERRORS

$cs->ReportErrors();

// ____________________________________________________________ ADD FORM

if ($showMain) {

    ?>

    <div class="col2 sidebox">

    <h2>Add A Fanlisting</h2>

    <form action="add-fl.php" method="post">

    <p class="info">Fields marked with an asterisk (*) are required.</p>

    <fieldset>
    <legend>Fanlisting Info</legend>

    <p><label for="fl_subject">* Fanlisting Name</label>
    <input type="text" id="fl_subject" name="fl_subject" size="20" maxlength="50" value="<?php if (!empty($clean['fl_subject'])) { echo $clean['fl_subject']; } ?>" /></p>

    <p><label>&nbsp;</label><input type="submit" name="action" value="add" />
    </fieldset>

    </form>

    </div>

    <?php if ($showList) { ?>

        <div class="col1">

        <h2>Fanlistings</h2>

        <?php

        $query = 'SELECT '.$cs->GetOpt('col_id').' AS fl,
          '.$cs->GetOpt('col_subj').' AS subject, COUNT(code_id) AS num_code
          FROM '.$cs->GetOpt('collective_table').'
          LEFT JOIN '.$cs->GetOpt('codes_table').' ON '.$cs->GetOpt('col_id').'=code_fl
          GROUP BY fl
          ORDER BY subject ASC';

        $cs->db->execute($query, 'Failed to select fanlistings. Check that your collective_script setting is properly configured.');

        $num_size = $cs->db->getNumRows();

        ?>
        <p>There are currently <strong><?php echo $num_size; ?></strong> total sizes.
        <?php if ($num_size > 0) { ?><br />
            Be aware that if you delete a fanlisting, you will also be deleting all codes of that fanlisting as well.</p>

            <form action="add-fl.php" method="post">

            <table>

            <thead>
            <tr>
            <th scope="col">Fanlisting</th>
            <th scope="col">Codes</th>
            <th scope="col">Delete?</th>
            </tr>
            </thead>

            <tbody>
            <?php

// ____________________________________________________________ LIST FLS

            while ($row = $cs->db->readRecord()) {

                $class = (isset($class) && $class == 'odd') ? 'even' : 'odd';

                echo '<tr class="'.$class.'">';

                echo '<td><input type="hidden" name="fl_id[]" value="'.$row['fl'].'" />';
                echo '<input type="text" name="fl_subject[]" size="30" maxlength="50" value="'.$row['subject'].'" /></td>';

                echo '<td class="number">'.$row['num_code'].'</td>';

                echo '<td><input type="checkbox" name="delete_fl_id[]" value="'.$row['fl'].'" /></td>';

                echo "</tr>\n";
            }

            ?>
            </tbody>

            <tfoot>
            <tr>
            <td><input type="submit" name="action" value="update" title="update all fanlistings" /></td>
            <td>&nbsp;</td>
            <td><input type="submit" name="action" value="delete" title="delete checked fanlistings"
                onclick="return confirm('Are you absolutely sure you want to delete the checked fanlistings?');" /></td>
            </tr>
            </tfoot>

            </table>

            </form>

        <?php } else {
            echo "</p>\n";
        }

        $cs->db->freeResult();
        echo "</div>\n";
    }
}

$cs->GetFooter();
