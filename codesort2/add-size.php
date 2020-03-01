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

$cs->GetHeader('Sizes');

// ____________________________________________________________ ADD QUERY

if (isset($_POST['action'])) {

    $clean = clean_input($_POST);

    if ($_POST['action'] == 'add') {

        $showList = false;

        if (empty($clean['size_size'])) {
            $cs->AddErr('The size name is blank!');
        } else {

            $sql_size_size = $cs->db->escape($clean['size_size']);
            $sql_size_order = (int)$clean['size_order'];

            $query_check = 'SELECT COUNT(size_id)
              FROM '.$cs->GetOpt('sizes_table')."
              WHERE size_size='$sql_size_size'";

            $num_check = $cs->db->getFirstCell($query_check);

            if ($num_check > 0) {
                $cs->AddErr('The size <strong>'.$clean['size_size'].'</strong> already exists! Please choose another name.');
            }
        }

        if ($cs->NoErr()) {

            $query = 'INSERT INTO '.$cs->GetOpt('sizes_table')." (size_order, size_size)
              VALUES ('$sql_size_order', '$sql_size_size')";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('The size <strong>'.$clean['size_size'].'</strong> has been <strong>added</strong> successfully.');
                $clean = array();
                $showList = true;
            }
        }

// ____________________________________________________________ UPDATE QUERY

    } elseif ($_POST['action'] == 'update') {

        $success_message = 'Sizes <strong>';

        $count = count($clean['size_id']);

        for ($i=0; $i<$count; $i++) {

            $sql_id = (int)$clean['size_id'][$i];
            $sql_name = $cs->db->escape($clean['size_size'][$i]);
            $sql_order = (int)$clean['size_order'][$i];

            $query = 'UPDATE '.$cs->GetOpt('sizes_table')."
              SET size_order='$sql_order', size_size='$sql_name'
              WHERE size_id=$sql_id";

            if ($cs->db->execute($query)) {
                $success_message .= '#'.$sql_id.' ';
            }
        }

        $success_message .= 'updated</strong> successfully.';
        $cs->AddSuccess($success_message);
        $clean = array();

// ____________________________________________________________ DELETE QUERY

    } elseif ($_POST['action'] == 'delete') {

        $cat_count = count($clean['delete_size_id']);

        for ($i=0; $i<$cat_count; $i++) {

            $sql_id = (int)$_POST['delete_size_id'][$i];

            $query = 'DELETE FROM '.$cs->GetOpt('sizes_table')." WHERE size_id=$sql_id LIMIT 1";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('Size <strong>#'.$sql_id.'</strong> deleted.');

                $query_code = 'DELETE FROM '.$cs->GetOpt('codes_table')." WHERE code_size=$sql_id";

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

    <h2>Add A Size</h2>

    <form action="add-size.php" method="post">

    <p class="info">Fields marked with an asterisk (*) are required.</p>

    <fieldset>
    <legend>Size Info</legend>

    <p><label for="size_size">* Size Name</label>
    <input type="text" id="size_size" name="size_size" size="20" maxlength="20" value="<?php if (!empty($clean['size_size'])) { echo $clean['size_size']; } ?>" /></p>

    <p><label for="size_order">Order</label>
    <input type="text" id="size_order" name="size_order" size="3" maxlength="3" value="<?php if (!empty($clean['size_order'])) { echo $clean['size_order']; } ?>" /></p>

    <p><label>&nbsp;</label><input type="submit" name="action" value="add" />
    </fieldset>

    </form>

    </div>

    <?php if ($showList) { ?>

        <div class="col1">

        <h2>Sizes</h2>

        <?php

        $query = 'SELECT size_id, size_order, size_size, COUNT(code_id) AS num_code
          FROM ' .$cs->GetOpt('sizes_table'). '
          LEFT JOIN ' .$cs->GetOpt('codes_table'). ' ON size_id=code_size
          GROUP BY size_id
          ORDER BY size_order ASC, size_size ASC';
        $cs->db->execute($query);

        $num_size = $cs->db->getNumRows();

        ?>
        <p>There are currently <strong><?php echo $num_size; ?></strong> total sizes.
        <?php if ($num_size > 0) { ?><br />
            Be aware that if you delete a size, you will also be deleting all codes of that size as well.</p>

            <form action="add-size.php" method="post">

            <table>

            <thead>
            <tr>
            <th scope="col">Order</th>
            <th scope="col">Size Name</th>
            <th scope="col">Codes</th>
            <th scope="col">Delete?</th>
            </tr>
            </thead>

            <tbody>
            <?php

// ____________________________________________________________ LIST SIZES

            while ($row = $cs->db->readRecord()) {

                $class = (isset($class) && $class == 'odd') ? 'even' : 'odd';

                echo '<tr class="'.$class.'">';
                echo '<td><input type="text" name="size_order[]" size="3" maxlength="3" value="'.$row['size_order'].'" /></td>';

                echo '<td><input type="hidden" name="size_id[]" value="'.$row['size_id'].'" />';
                echo '<input type="text" name="size_size[]" size="20" maxlength="20" value="'.$row['size_size'].'" /></td>';

                echo '<td class="number">'.$row['num_code'].'</td>';

                echo '<td><input type="checkbox" name="delete_size_id[]" value="'.$row['size_id'].'" /></td>';

                echo "</tr>\n";
            }

            ?>
            </tbody>

            <tfoot>
            <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="action" value="update" title="update all sizes" /></td>
            <td>&nbsp;</td>
            <td><input type="submit" name="action" value="delete" title="delete checked sizes"
                onclick="return confirm('Are you absolutely sure you want to delete the checked sizes?');" /></td>
            </tr>
            </tfoot>

            </table>

            </form>

        <?php } else {
            echo "</p>\n";
        }

        $cs->db->freeResult();
    }

    echo "</div>\n";
}

$cs->GetFooter();
