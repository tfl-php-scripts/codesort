<?php

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();

require_once('protect.php');

$cs->AddOptFromDb();

$clean = array();
$showMain = true;
$showList = true;

$cs->GetHeader('Categories');

// ____________________________________________________________ ADD QUERY

if (isset($_POST['action'])) {

    $clean = clean_input($_POST);

    if ($_POST['action'] == 'add') {
        $showList = false;

        if ($clean['cat_fl'] == '' || !ctype_digit($clean['cat_fl'])) {
            $cs->AddErr('The fanlisting ID is invalid!');
        }

        if (empty($clean['cat_name'])) {
            $cs->AddErr('The category name is blank!');
        } else {
            $sql_cat_name = $cs->db->escape($clean['cat_name']);
            $sql_cat_fl = (int)$clean['cat_fl'];

            $query_check = 'SELECT COUNT(cat_id)
              FROM '.$cs->GetOpt('cat_table')."
              WHERE cat_name='$sql_cat_name'
              AND cat_fl=$sql_cat_fl";

            $num_check = $cs->db->getFirstCell($query_check);

            if ($num_check > 0) {
                $cs->AddErr('The category <strong>'.$clean['cat_name'].'</strong> already exists! Please choose another name.');
            }
        }

        if ($cs->NoErr()) {
            $query = 'INSERT INTO '.$cs->GetOpt('cat_table')." (cat_fl, cat_name)
              VALUES ('$sql_cat_fl', '$sql_cat_name')";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('The category <strong>'.$clean['cat_name'].'</strong> has been <strong>added</strong> successfully.');
                $clean = array();
                $showList = true;
            }
        }

// ____________________________________________________________ UPDATE QUERY

    } elseif ($_POST['action'] == 'update') {
        $success_message = 'Categories <strong>';

        $cat_count = count($clean['cat_id']);

        for ($i=0; $i<$cat_count; $i++) {

            $sql_id = (int)$clean['cat_id'][$i];
            $sql_name = $cs->db->escape($clean['cat_name'][$i]);

            $query = 'UPDATE '.$cs->GetOpt('cat_table')."
              SET cat_name='$sql_name'
              WHERE cat_id=$sql_id";

            if ($cs->db->execute($query)) {
                $success_message .= '#'.$sql_id.' ';
            }
        }

        $success_message .= 'updated</strong> successfully.';
        $cs->AddSuccess($success_message);
        $clean = array();

// ____________________________________________________________ DELETE QUERY

    } elseif ($_POST['action'] == 'delete') {

        $cat_count = count($clean['delete_cat_id']);

        for ($i=0; $i<$cat_count; $i++) {

            $sql_id = (int)$_POST['delete_cat_id'][$i];

            $query = 'DELETE FROM '.$cs->GetOpt('cat_table')." WHERE cat_id=$sql_id LIMIT 1";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('Category <strong>#'.$sql_id.'</strong> deleted.');

                $query_code = 'DELETE FROM '.$cs->GetOpt('codes_table')." WHERE code_cat=$sql_id";

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

    <h2>Add A Category</h2>

    <form action="add-cat.php" method="post">

    <p class="info">Fields marked with an asterisk (*) are required.</p>

    <fieldset>
    <legend>Category Info</legend>

    <p><label for="cat_fl">* Fanlisting</label>
    <select id="cat_fl" name="cat_fl">
    <option value="">Select a fanlisting:</option>
    <option value="0"<?php if (isset($clean['cat_fl']) && $clean['cat_fl'] == 0) { echo ' selected="selected"'; } ?>>Whole Collective</option>
    <option value="">- or -</option>
    <?php

    $query = 'SELECT '.$cs->GetOpt('col_id').' AS fl,
      '.$cs->GetOpt('col_subj').' AS subject
      FROM '.$cs->GetOpt('collective_table').'
      ORDER BY subject ASC';

    $cs->db->execute($query, null);

    while ($row = $cs->db->readRecord()) {

        echo '<option value="'.$row['fl'].'"';
        if (isset($clean['cat_fl']) && $row['fl'] == $clean['cat_fl']) { echo 'selected="selected"'; }
        echo '>'.$row['subject']."</option>\n";
    }

    $cs->db->freeResult();

    ?>
    </select></p>

    <p><label for="cat_name">* Category Name</label>
    <input type="text" id="cat_name" name="cat_name" size="20" maxlength="50" value="<?php if (!empty($clean['cat_name'])) { echo $clean['cat_name']; } ?>" /></p>

    <p><label>&nbsp;</label><input type="submit" name="action" value="add" />
    </fieldset>

    </form>

    </div>

    <?php if ($showList) { ?>

        <div class="col1">

        <h2>Categories</h2>

        <?php

        $query = 'SELECT cat_id, cat_name,
          '.$cs->GetOpt('col_subj').' AS subject, COUNT(code_id) AS num_code
          FROM '.$cs->GetOpt('cat_table').'
          LEFT JOIN '.$cs->GetOpt('codes_table').' ON cat_id=code_cat
          LEFT JOIN '.$cs->GetOpt('collective_table').' ON cat_fl='.$cs->GetOpt('col_id').'
          GROUP BY cat_id
          ORDER BY subject ASC, cat_name ASC';

        $cs->db->execute($query, 'Failed to select categories. Check that your collective_script setting is properly configured.');

        $num_cat = $cs->db->getNumRows();

        ?>
        <p>There are currently <strong><?php echo $num_cat; ?></strong> total categories.
        <?php if ($num_cat > 0) { ?><br />
            Be aware that if you delete a category, you will also be deleting all codes of that category as well.</p>

            <form action="add-cat.php" method="post">

            <table>

            <thead>
            <tr>
            <th scope="col">Fanlisting</th>
            <th scope="col">Category Name</th>
            <th scope="col">Codes</th>
            <th scope="col">Delete?</th>
            </tr>
            </thead>

            <tbody>
            <?php

// ____________________________________________________________ LIST CATEGORIES

            while ($row = $cs->db->readRecord()) {

                if (empty($row['subject'])) {
                    $row['subject'] = 'Whole Collective';
                }

                $class = (isset($class) && $class == 'odd') ? 'even' : 'odd';

                echo '<tr class="'.$class.'">';
                echo '<td>'.$row['subject'].'</td>';

                echo '<td><input type="hidden" name="cat_id[]" value="'.$row['cat_id'].'" />';
                echo '<input type="text" name="cat_name[]" size="30" maxlength="50" value="'.$row['cat_name'].'" /></td>';

                echo '<td class="number">'.$row['num_code'].'</td>';

                echo '<td><input type="checkbox" name="delete_cat_id[]" value="'.$row['cat_id'].'" /></td>';

                echo "</tr>\n";
            }

            ?>
            </tbody>

            <tfoot>
            <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="action" value="update" title="update all categories" /></td>
            <td>&nbsp;</td>
            <td><input type="submit" name="action" value="delete" title="delete checked categories"
                onclick="return confirm('Are you absolutely sure you want to delete the checked categories?');" /></td>
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
