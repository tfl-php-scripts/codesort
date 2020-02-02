<?php

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();

require_once('protect.php');

$cs->AddOptFromDb();

$clean = array();
$showForm = false;
$showLookup = false;

$cs->GetHeader('Donors');

if (isset($_GET['action'])) {

    if (isset($_GET['id'])) {

        $sql_donor_id = (int)$_GET['id'];

        $query = 'SELECT *, donor_id AS id
          FROM ' . $cs->GetOpt('donors_table') . "
          WHERE donor_id=$sql_donor_id LIMIT 1";

        $cs->db->execute($query);

        $clean = $cs->db->getRecord();

        if ($_GET['action'] == 'edit') {
            $showForm = true;
        } elseif ($_GET['action'] == 'lookup') {
            $showLookup = true;
        }
    } elseif ($_GET['action'] == 'new') {
        $showForm = true;
    }

} elseif (isset($_POST['action'])) {

    $clean = clean_input($_POST);

// ____________________________________________________________ VALIDATE INPUT

    if ($_POST['action'] == 'add' || $_POST['action'] == 'update') {

        $showForm = true;

        if (empty($clean['donor_name'])) {

            $cs->AddErr('The donor name is blank!');

        } else {

            $sql_donor_name = $cs->db->escape($clean['donor_name']);
            $sql_donor_url = $cs->db->escape($clean['donor_url']);
            $sql_donor_id = (int)$clean['id'];

            $query_check = 'SELECT COUNT(donor_id)
              FROM ' . $cs->GetOpt('donors_table') . "
              WHERE donor_name='$sql_donor_name' AND donor_id!=$sql_donor_id";

            $num_check = $cs->db->getFirstCell($query_check);

            if ($num_check > 0) {
                $cs->AddErr('The donor <strong>' . $clean['donor_name'] . '</strong> already exists! Please choose another name.');
            }
        }
    }

    if ($cs->NoErr()) {

// ____________________________________________________________ ADD QUERY

        if ($_POST['action'] == 'add') {

            $query = 'INSERT INTO ' . $cs->GetOpt('donors_table') . " (donor_name, donor_url)
              VALUES ('$sql_donor_name', '$sql_donor_url')";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('The donor <strong>' . $clean['donor_name'] . '</strong> has been <strong>added</strong> successfully.');
                $clean = array();
                $showForm = false;
            }

// ____________________________________________________________ UPDATE QUERY

        } elseif ($_POST['action'] == 'update') {

            $query = 'UPDATE ' . $cs->GetOpt('donors_table') . "
              SET donor_name='$sql_donor_name', donor_url='$sql_donor_url'
              WHERE donor_id=$sql_donor_id";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('The donor <strong>' . $clean['donor_name'] . '</strong> has been <strong>updated</strong> successfully.');
                $clean = array();
                $showForm = false;
            }

// ____________________________________________________________ DELETE QUERY

        } elseif ($_POST['action'] == 'delete') {

            $sql_donor_id = (int)$clean['id'];

            $query = 'DELETE FROM ' . $cs->GetOpt('donors_table') . " WHERE donor_id=$sql_donor_id LIMIT 1";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('Donor <strong>#' . $clean['id'] . '</strong> has been <strong>deleted</strong> successfully.');

                $query_code = 'DELETE FROM ' . $cs->GetOpt('codes_table') . " WHERE code_donor=$sql_donor_id";

                if ($cs->db->execute($query_code)) {
                    $cs->AddSuccess('All <strong>' . $cs->db->getAffectedRows() . '</strong> related code records deleted.');
                }
            }
        }
    } // end if no err
} // end if post

// _____________________________________________ REPORT SUCCESS

$cs->ReportSuccess();

// _____________________________________________ REPORT ERRORS

$cs->ReportErrors();

// ____________________________________________________________ ADD/EDIT FORM

if ($showForm) {

    if (isset($clean['id'])) { ?>
        <h2>Edit A Donor</h2>
    <?php } else { ?>
        <h2>Add A New Donor</h2>
    <?php } ?>

    <form action="add-donor.php" method="post">

        <p class="info">Fields marked with an asterisk (*) are required.</p>

        <p><label for="donor_name">* Name</label>
            <input type="text" id="donor_name" name="donor_name" size="20" maxlength="20"
                   value="<?php if (!empty($clean['donor_name'])) {
                       echo $clean['donor_name'];
                   } ?>"/></p>

        <p><label for="donor_url">* URL</label>
            <input type="text" id="donor_url" name="donor_url" size="30" maxlength="100"
                   value="<?php if (!empty($clean['donor_url'])) {
                       echo $clean['donor_url'];
                   } ?>"/></p>

        <p><label>&nbsp;</label>
            <?php if (isset($clean['id'])) { ?>
                <input type="hidden" name="id" value="<?php echo $clean['id']; ?>"/>
                <input type="submit" name="action" value="update"/>
                <input type="submit" name="action" value="delete"
                       onclick="return confirm('Are you sure you want to delete <?php echo $clean['donor_name']; ?>?');"/>
            <?php } else { ?>
                <input type="submit" name="action" value="add"/>
            <?php } ?>
            <input type="submit" value="cancel"/>
        </p>

    </form>

    <?php

}

// ____________________________________________________________ DONOR LOOKUP

if ($showLookup) {

    $query = 'SELECT code_id, code_image, code_approved, size_size, cat_name,
      ' . $cs->GetOpt('col_id') . ' AS fl, ' . $cs->GetOpt('col_subj') . ' AS subject
      FROM ' . $cs->GetOpt('codes_table') . '
      JOIN ' . $cs->GetOpt('sizes_table') . ' ON code_size=size_id
      LEFT JOIN ' . $cs->GetOpt('cat_table') . ' ON code_cat=cat_id
      LEFT JOIN ' . $cs->GetOpt('collective_table') . ' ON code_fl=' . $cs->GetOpt('col_id') . '
      WHERE code_donor=' . $clean['donor_id'] . '
      GROUP BY code_id
      ORDER BY code_id ' . $cs->GetOpt('sort_order');

    $cs->db->execute($query);

    $num_codes = $cs->db->getNumRows();

    ?>

    <h2>Donor Lookup</h2>

    <p>There are currently <strong><?php echo $num_codes; ?></strong> total codes donated by
        <strong><?php echo $cs->GetDonorName($clean['donor_name'], $clean['donor_url']) ?></strong>.</p>

    <?php if ($num_codes > 0) {
        $colspan = 4; ?>
        <form action="add-code.php" method="post">
            <table>

                <thead>
                <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Fanlisting</th>
                    <th scope="col">Size</th>
                    <?php if ($cs->GetOpt('use_cat')) { ?>
                        <th scope="col">Cat</th><?php $colspan++;
                    } ?>
                    <th scope="col">Approved?</th>
                    <th scope="col">Edit? / Delete?</th>
                </tr>
                </thead>


                <tbody>
                <?php

                // ____________________________________________________________ LIST CODES

                $i = 0;
                while ($row = $cs->db->readRecord()) {

                    if (empty($row['subject'])) {
                        $row['subject'] = 'Whole Collective';
                    }

                    $class = (isset($class) && $class == 'odd') ? 'even' : 'odd';

                    echo '<tr class="' . $class . '">';
                    echo '<td>' . $cs->GetCodeImg($row['code_image']) . '</td>';
                    echo '<td>' . $row['subject'] . '</td>';
                    echo '<td>' . $row['size_size'] . '</td>';
                    if ($cs->GetOpt('use_cat')) {
                        echo '<td>' . $row['cat_name'] . '</td>';
                    }
                    echo '<td>' . $cs->GetCodeApproved($row['code_approved']) . '</td>';
                    echo '<td><input type="checkbox" name="id[' . $i . ']" value="' . $row['code_id'] . '" />
<input type="hidden" name="code_oldimg[' . $i . ']" value="' . $row['code_image'] . '" /></td>';
                    echo "</tr>\n";
                    $i++;
                }

                ?>
                </tbody>

                <tfoot>
                <tr>
                    <td colspan="<?php echo $colspan; ?>" class="number"><a href="#"
                                                                            onclick="checkAll(false); return false;">Uncheck
                            All</a> /
                        <a href="#" onclick="checkAll(true); return false;">Check All</a></td>
                    <td><input type="submit" name="action" value="edit" title="edit checked codes"/>
                        <input type="submit" name="action" value="delete" title="delete checked codes"
                               onclick="return confirm('Are you absolutely sure you want to delete the checked codes?');"/>
                    </td>
                </tr>
                </tfoot>

            </table>
            <input type="hidden" name="fl" value="<?php echo $row['fl']; ?>"/>
        </form>
        <?php

    }

    $cs->db->freeResult();
}

// ____________________________________________________________ LIST DONORS

if (!$showForm && !$showLookup) {

?>

<h2>Donors</h2>

<?php

$query = 'SELECT codes_donors.*, COUNT(code_id) AS num_code
      FROM ' . $cs->GetOpt('donors_table') . '
      LEFT JOIN ' . $cs->GetOpt('codes_table') . ' ON donor_id=code_donor
      GROUP BY donor_id
      ORDER BY donor_name ASC';

$cs->db->execute($query);

$num_donor = $cs->db->getNumRows();

echo '<p>There are currently <strong>' . $num_donor . '</strong> total donors. <a href="add-donor.php?action=new">Add a new donor?</a>';

if ($num_donor > 0) { ?><br/>
Be aware that if you delete a donor, you will also be deleting all codes for that donor as well.</p>

<table>

    <thead>
    <tr>
        <th scope="col">Name</th>
        <th scope="col">Codes</th>
        <th scope="col">Options</th>
    </tr>
    </thead>

    <tbody>
    <?php

    while ($row = $cs->db->readRecord()) {

        $class = (isset($class) && $class == 'odd') ? 'even' : 'odd';

        echo '<tr class="' . $class . '">';
        echo '<td>' . $cs->GetDonorName($row['donor_name'], $row['donor_url']) . '</td>';

        echo '<td class="number">' . $row['num_code'] . '</td>';

        echo '<td>
<form action="add-donor.php" method="get">
<input type="submit" name="action" value="lookup" />
<input type="submit" name="action" value="edit" />
<input type="hidden" name="id" value="' . $row['donor_id'] . '" />
</form>
<form action="add-donor.php" method="post">
<input type="submit" name="action" value="delete" onclick="return confirm(\'Are you sure you want to delete ' . $row['donor_name'] . '?\');" />
<input type="hidden" name="id" value="' . $row['donor_id'] . '" />
</form>
</td>';

        echo "</tr>\n";

    }

    echo "</tbody>\n";
    echo "</table>\n";

    } else {
        echo "</p>\n";
    }

    $cs->db->freeResult();
    }

    $cs->GetFooter();
