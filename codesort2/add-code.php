<?php

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();

require_once('protect.php');

$cs->AddOptFromDb();

$clean = array();
$show = 'main';

$cs->GetHeader('Codes');

if (isset($_GET['action'])) {

    if ($_GET['action'] == 'new') {
        $show = 'form';
        $num = (int)$_GET['num'];

    } elseif ($_GET['action'] == 'list') {
        $show = 'list';
    } elseif ($_GET['action'] == 'snippet') {
        $show = 'snippet';
    }

    if (isset($_GET['fl'])) {
        $fl = (int)$_GET['fl'];
    }

} elseif (isset($_POST['action'])) {

    $clean = clean_input($_POST);
    $fl = (int)$clean['fl'];

    // ____________________________________________________________ GET DATA TO EDIT

    if ($_POST['action'] == 'edit') {

        $num = 0;

        foreach ($_POST['id'] as $key => $value) {

            $sql_code_id = (int)$value;

            $query = "SELECT *, code_id AS id, code_fl as fl
              FROM " . $cs->GetOpt('codes_table') . "
              WHERE code_id=$sql_code_id LIMIT 1";

            $cs->db->execute($query);

            $clean[] = $cs->db->getRecord();
            $num++;
        }

        $fl = $clean[$num - 1]['fl'];
        $show = 'form';

        // ____________________________________________________________ ADD QUERY

    } elseif ($_POST['action'] == 'add') {


        foreach ($clean['code_size'] as $key => $value) {

            $result = false;
            if ($cs->GetOpt('do_upload')) {
                if (!empty($_FILES['file']['name'][$key])) {
                    $origfilename = $_FILES['file']['name'][$key];
                    list($result, $filename) = $cs->UploadImage($_FILES['file']['name'][$key], $_FILES['file']['tmp_name'][$key], $_FILES['file']['error'][$key], $_FILES['file']['size'][$key]);
                }
            } else if (!empty($clean['code_img'][$key])) {
                $filename = $clean['code_img'][$key];
                $result = true;
            }

            if ($result) {

                $code_image = $cs->db->escape($filename);
                $code_cat = (int)$clean['code_cat'][$key];
                $code_size = (int)$clean['code_size'][$key];
                $code_donor = (int)$clean['code_donor'][$key];
                $code_approved = (!empty($clean['code_approved'][$key])) ? $cs->db->escape($clean['code_approved'][$key]) : 'n';

                $query = "INSERT INTO " . $cs->GetOpt('codes_table') . "
                  SET code_fl=$fl, code_cat=$code_cat, code_size=$code_size,
                  code_donor=$code_donor, code_approved='$code_approved', code_image='$code_image'";

                if ($cs->db->execute($query)) {
                    $cs->AddSuccess('Code <strong>' . $filename . '</strong> has been <strong>added</strong>.');
                }
            }
        }

        $show = 'list';

        // ____________________________________________________________ UPDATE QUERY

    } elseif ($_POST['action'] == 'update') {

        foreach ($clean['code_size'] as $key => $value) {

            $result = false;
            if ($cs->GetOpt('do_upload')) {
                if (!empty($_FILES['file']['name'][$key])) {
                    $origfilename = $_FILES['file']['name'][$key];
                    list($result, $filename) = $cs->UploadImage($_FILES['file']['name'][$key], $_FILES['file']['tmp_name'][$key], $_FILES['file']['error'][$key], $_FILES['file']['size'][$key], $clean['code_oldimg'][$key]);
                } elseif (!empty($clean['code_image_rename'][$key]) && $clean['code_image_rename'][$key] != $clean['code_oldimg'][$key]) {
                    list($result, $filename) = $cs->RenameImage($clean['code_oldimg'][$key], $clean['code_image_rename'][$key]);
                } else {
                    $filename = $clean['code_oldimg'][$key];
                    $result = true;
                }
            } else if (!empty($clean['code_img'][$key])) {
                $filename = $clean['code_img'][$key];
                $result = true;
            }

            if ($result) {

                $code_image = $cs->db->escape($filename);
                $code_cat = (int)$clean['code_cat'][$key];
                $code_size = (int)$clean['code_size'][$key];
                $code_donor = (int)$clean['code_donor'][$key];
                $code_approved = (!empty($clean['code_approved'][$key])) ? $cs->db->escape($clean['code_approved'][$key]) : 'n';
                $id = (int)$clean['id'][$key];

                $query = "UPDATE " . $cs->GetOpt('codes_table') . "
                  SET code_cat=$code_cat, code_size=$code_size,
                  code_donor=$code_donor, code_approved='$code_approved', code_image='$code_image'
                  WHERE code_id=$id";

                if ($cs->db->execute($query)) {
                    $cs->AddSuccess('Code <strong>' . $filename . '</strong> has been <strong>updated</strong>.');
                }
            }
        }

        $show = 'list';

        // ____________________________________________________________ DELETE QUERY

    } elseif ($_POST['action'] == 'delete') {

        foreach ($clean['id'] as $key => $value) {

            if ($cs->GetOpt('do_upload') && !empty($clean['code_oldimg'][$key])) {
                $cs->DeleteImage($clean['code_oldimg'][$key]);
            }

            $id = (int)$value;

            $query = "DELETE FROM " . $cs->GetOpt('codes_table') . "
              WHERE code_id=$id LIMIT 1";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('Code <strong>#' . $id . '</strong> has been <strong>deleted</strong>.');
            }
        }

        $show = 'list';
    }

} // end if post

if (!empty($fl) > 0) {

    $query = "SELECT " . $cs->GetOpt('col_id') . " AS fl, " . $cs->GetOpt('col_subj') . " AS subject
      FROM " . $cs->GetOpt('collective_table') . "
      WHERE " . $cs->GetOpt('col_id') . "=$fl LIMIT 1";

    $cs->db->execute($query);

    $row = $cs->db->getRecord();
    $fl = $row['fl'];
    $subject = $row['subject'];
} else {
    $fl = 0;
    $subject = 'Whole Collective';
}

// _____________________________________________ REPORT SUCCESS

$cs->ReportSuccess();

// _____________________________________________ REPORT ERRORS

$cs->ReportErrors();

// ____________________________________________________________ ADD/EDIT FORM

if ($show == 'form') {

    $query = "SELECT * FROM " . $cs->GetOpt('sizes_table') . " ORDER BY size_order ASC";

    $cs->db->execute($query);

    while ($row = $cs->db->readRecord()) {
        $size_id[] = $row['size_id'];
        $size_size[] = $row['size_size'];
    }

    $cs->db->freeResult();

    if ($cs->GetOpt('use_cat')) {

        $query = "SELECT * FROM " . $cs->GetOpt('cat_table') . " ORDER BY cat_name ASC";

        $cs->db->execute($query);

        while ($row = $cs->db->readRecord()) {
            $cat_id[] = $row['cat_id'];
            $cat_fl[] = $row['cat_fl'];
            $cat_name[] = $row['cat_name'];
        }

        $cs->db->freeResult();

    }

    $query = "SELECT * FROM " . $cs->GetOpt('donors_table') . " ORDER BY donor_name ASC";

    $cs->db->execute($query);

    $donor_id[] = 0;
    $donor_name[] = 'None';
    while ($row = $cs->db->readRecord()) {
        $donor_id[] = $row['donor_id'];
        $donor_name[] = $row['donor_name'];
    }

    $cs->db->freeResult();

    if (empty($num)) {
        $num = 1;
    }

    if (isset($_POST['action']) && $_POST['action'] == 'edit') { ?>
        <h2>Edit Codes</h2>
    <?php } else { ?>
        <h2>Add Codes</h2>
    <?php } ?>

    <form action="add-code.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $cs->GetOpt('max_file_size'); ?>"/>

        <?php if ($num > 1) { ?>
            <div class="col2 sidebox">
                <h3>Master Controls</h3>
                <p><input type="checkbox" id="toggle" name="toggle" value="y" onclick="toggleAllExtra()"/> Show/hide
                    non-master controls</p>

                <p><label for="code_size_master">Size</label>
                    <select id="code_size_master" name="code_size_master" onchange="setAllSize(this.value)">
                        <?php
                        foreach ($size_id as $key => $value) {
                            echo '<option value="' . $value . '">' . $size_size[$key] . "</option>\n";
                        }
                        ?>
                    </select></p>

                <?php if ($cs->GetOpt('use_cat')) { ?>
                    <p><label for="code_cat_master">Category</label>
                        <select id="code_cat_master" name="code_cat_master" onchange="setAllCat(this.value)">
                            <option value="0">None</option>
                            <?php
                            foreach ($cat_id as $key => $value) {
                                if ($fl == $cat_fl[$key]) {
                                    echo '<option value="' . $value . '">' . $cat_name[$key] . "</option>\n";
                                }
                            }
                            ?>
                        </select></p>
                <?php } ?>

                <p><label for="code_donor_master">Donor</label>
                    <select id="code_donor_master" name="code_donor_master" onchange="setAllDonor(this.value)">
                        <?php
                        foreach ($donor_id as $key => $value) {
                            echo '<option value="' . $value . '">' . $donor_name[$key] . "</option>\n";
                        }
                        ?>
                    </select></p>

                <p><label for="code_approved_master">Approved?</label>
                    <input type="checkbox" id="code_approved_master" name="code_approved_master" value="y"
                           onclick="setAllAppr(this.checked)"/> Yes</p>

                <p>Use these controls to mass-select the settings for <strong>all</strong> the records to the left.</p>
            </div>
        <?php } ?>

        <div class="col1">

            <p class="info">Fields marked with an asterisk (*) are required.</p>

            <?php

            for ($i = 0; $i < $num; $i++) {

                if (!isset($clean[$i]['code_fl'])) {
                    $clean[$i]['code_fl'] = $fl;
                }

                $class = (isset($class) && $class == 'even') ? 'odd' : 'even';

                ?>
                <div class="<?php echo $class; ?>">

                    <?php if (!empty($clean[$i]['code_image'])) { ?>
                        <p><label>Current Image</label>
                            <input type="hidden" name="code_oldimg[]" value="<?php echo $clean[$i]['code_image']; ?>"/>
                            <input type="hidden" name="id[]" value="<?php echo $clean[$i]['id']; ?>"/>
                            <?php echo $cs->GetCodeImg($clean[$i]['code_image']); ?></p>
                        <?php if ($cs->GetOpt('do_upload')) { ?>
                            <p><label for="code_image_rename<?php echo $i; ?>">Rename Image</label>
                                <input type="input" id="code_image_rename<?php echo $i; ?>" name="code_image_rename[]"
                                       size="30" value="<?php echo $clean[$i]['code_image']; ?>"/></p>
                        <?php } ?>
                    <?php } ?>

                    <?php if ($cs->GetOpt('do_upload')) { ?>
                        <p><label for="code_image<?php echo $i; ?>">* New Image</label>
                            <input type="file" id="code_image<?php echo $i; ?>" name="file[]"/></p>
                    <?php } else { ?>
                        <p><label for="code_image<?php echo $i; ?>">* Image Name</label>
                            <input type="input" id="code_image<?php echo $i; ?>" name="code_image[]" size="30"
                                   value="<?php if (!empty($clean[$i]['code_image'])) {
                                       echo $clean[$i]['code_image'];
                                   } ?>"/><br/>
                            Make sure this file has already been uploaded to <?php echo $cs->GetOpt('images_folder'); ?>
                        </p>
                    <?php } ?>

                    <div id="toggle<?php echo $i; ?>" class="toggleExtra">

                        <p><label for="code_size<?php echo $i; ?>">* Size</label>
                            <select class="setSize" id="code_size<?php echo $i; ?>" name="code_size[]">
                                <?php
                                foreach ($size_id as $key => $value) {
                                    echo '<option value="' . $value . '"';
                                    if (isset($clean[$i]['code_size']) && $value == $clean[$i]['code_size']) {
                                        echo ' selected="selected"';
                                    }
                                    echo '>' . $size_size[$key] . "</option>\n";
                                }
                                ?>
                            </select></p>

                        <?php if ($cs->GetOpt('use_cat')) { ?>
                            <p><label for="code_cat<?php echo $i; ?>">Category</label>
                                <select class="setCat" id="code_cat<?php echo $i; ?>" name="code_cat[]">
                                    <option value="0">None</option>
                                    <?php
                                    foreach ($cat_id as $key => $value) {
                                        if ($clean[$i]['code_fl'] == $cat_fl[$key]) {
                                            echo '<option value="' . $value . '"';
                                            if (isset($clean[$i]['code_cat']) && $value == $clean[$i]['code_cat']) {
                                                echo ' selected="selected"';
                                            }
                                            echo '>' . $cat_name[$key] . "</option>\n";
                                        }
                                    }
                                    ?>
                                </select></p>
                        <?php } else { ?>
                            <input type="hidden" name="code_cat[]" value="<?php echo $clean[$i]['code_cat']; ?>"/>
                        <?php } ?>

                        <p><label for="code_donor<?php echo $i; ?>">Donor</label>
                            <select class="setDonor" id="code_donor<?php echo $i; ?>" name="code_donor[]">
                                <?php
                                foreach ($donor_id as $key => $value) {
                                    echo '<option value="' . $value . '"';
                                    if (isset($clean[$i]['code_donor']) && $value == $clean[$i]['code_donor']) {
                                        echo ' selected="selected"';
                                    }
                                    echo '>' . $donor_name[$key] . "</option>\n";
                                }
                                ?>
                            </select></p>

                        <p><label for="code_approved<?php echo $i; ?>">Approved?</label>
                            <input class="setAppr" type="checkbox" id="code_approved<?php echo $i; ?>"
                                   name="code_approved[]"
                                   value="y"<?php if (!isset($clean[$i]['code_approved']) || $clean[$i]['code_approved'] == 'y') {
                                echo ' checked="checked"';
                            } ?> /> Yes</p>
                    </div><!-- END .toggleExtra -->
                </div>
            <?php } ?>

            <p><label>&nbsp;</label>
                <input type="hidden" name="fl" value="<?php echo $fl; ?>"/>
                <?php if (isset($_POST['action']) && $_POST['action'] == 'edit') { ?>
                    <input type="submit" name="action" value="update"/>
                <?php } else { ?>
                    <input type="submit" name="action" value="add"/>
                <?php } ?>
                <input type="submit" value="cancel"/>
            </p>

        </div>

    </form>

    <?php

// ____________________________________________________________ LIST CODES FROM FL

} elseif ($show == 'list') {

    echo '<h2>' . $subject . "</h2>\n";

    if (empty($_GET['page'])) {
        $page = 1;
    } else {
        $page = (int)$_GET['page'];
    }

    $max = $cs->GetOpt('num_per_page');
    $from = (($page * $max) - $max);

    $limit = "LIMIT $from, $max";

    $where = '';
    if (!empty($_GET['size'])) {
        $size = (int)$_GET['size'];
        $where = 'AND code_size=' . $size;
        $limit = '';
    }

    $total = $cs->db->getFirstCell("SELECT COUNT(code_id) FROM " . $cs->GetOpt('codes_table') . " WHERE code_fl=$fl $where");

    echo '<form action="add-code.php" method="get"><p>There are currently <strong>' . $total . '</strong> codes for <strong>' . $subject . '</strong>.';

    $query = "SELECT * FROM " . $cs->GetOpt('sizes_table') . " ORDER BY size_order ASC";

    $cs->db->execute($query);
    $num_size = $cs->db->getNumRows();

    if ($num_size > 0) {

        $filter_opt = '<option value="0">None</option>' . "\n";
        while ($row = $cs->db->readRecord()) {
            $filter_opt .= '<option value="' . $row['size_id'] . '">' . $row['size_size'] . "</option>\n";
        }

        ?>

        <input type="hidden" name="action" value="new"/>
        <input type="hidden" name="fl" value="<?php echo $fl; ?>"/>

        Add codes?
        <select name="num">
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
        </select>
        <input type="submit" value="Continue"/>

    <?php } else { ?>
        </p><p class="error">Before you can begin uploading codes, you need to <a href="add-size.php">add at least one
            code size</a>.
    <?php }

    echo "</p></form>\n";

    $cs->db->freeResult();

    $query = "SELECT *
      FROM " . $cs->GetOpt('codes_table') . "
      JOIN " . $cs->GetOpt('sizes_table') . " ON code_size=size_id
      LEFT JOIN " . $cs->GetOpt('cat_table') . " ON code_cat=cat_id
      LEFT JOIN " . $cs->GetOpt('donors_table') . " ON code_donor=donor_id
      WHERE code_fl=$fl $where
      ORDER BY code_id " . $cs->GetOpt('sort_order') . " $limit";

    $cs->db->execute($query);

    if ($total > 0) {

        $colspan = 4;

        ?>
        <form action="add-code.php" method="get">
            <input type="hidden" name="fl" value="<?php echo $fl; ?>"/>
            <input type="hidden" name="action" value="list"/>
        <p><label>Filter:</label>
            <select name="size">
                <?php echo $filter_opt; ?>
            </select>
            <input type="submit" value="Go"/></p>
        </form>
        <form action="add-code.php" method="post">
            <table>

                <thead>
                <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Size</th>
                    <?php if ($cs->GetOpt('use_cat')) { ?>
                        <th scope="col">Cat</th><?php $colspan++;
                    } ?>
                    <th scope="col">Donor</th>
                    <th scope="col">Approved?</th>
                    <th scope="col">Edit? / Delete?</th>
                </tr>
                </thead>


                <tbody>
                <?php

                // ____________________________________________________________ LIST CODES

                $i = 0;
                while ($row = $cs->db->readRecord()) {

                    $class = (isset($class) && $class == 'odd') ? 'even' : 'odd';

                    echo '<tr class="' . $class . '">';
                    echo '<td>' . $cs->GetCodeImg($row['code_image']) . '</td>';
                    echo '<td>' . $row['size_size'] . '</td>';
                    if ($cs->GetOpt('use_cat')) {
                        echo '<td>' . $row['cat_name'] . '</td>';
                    }
                    echo '<td>' . $cs->GetDonorName($row['donor_name'], $row['donor_url']) . '</td>';
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
                <tr>
                    <td colspan="6" class="paginate">
                        <?php

                        $total_pages = ceil($total / $max);

                        if ($page > 1) {
                            echo '<a href="add-code.php?action=list&amp;fl=' . $fl . '&amp;page=' . ($page - 1) . '">Prev</a> &middot;';
                        }

                        for ($i = 1; $i <= $total_pages; $i++) {
                            if ($i == $page) {
                                echo ' <strong>' . $i . '</strong>';
                            } else {
                                echo ' <a href="add-code.php?action=list&amp;fl=' . $fl . '&amp;page=' . $i . '">' . $i . '</a>';
                            }
                        }

                        if ($page < $total_pages) {
                            echo ' &middot; <a href="add-code.php?action=list&amp;fl=' . $fl . '&amp;page=' . ($page + 1) . '">Next</a>';
                        }

                        ?>
                    </td>
                </tr>
                </tfoot>

            </table>
            <input type="hidden" name="fl" value="<?php echo $fl; ?>"/>
        </form>
        <?php

    }

    $cs->db->freeResult();

// ____________________________________________________________ GET DISPLAY CODE

} elseif ($show == 'snippet') {

    ?>

    <h2>Codes Display</h2>

    <p>Copy an paste this snippet into the page where you'd like to display the codes for
        <strong><?php echo $subject; ?></strong>.<br/>
        Make sure your page has a .php extension.</p>

    <p><textarea cols="80" rows="10">&lt;?php

// CodeSort <?php echo $cs->GetOpt('version'); ?> codes
// subject: <?php echo $subject; ?>


$listing = <?php echo $fl; ?>;

include('<?php echo $cs->GetOpt('install_folder'); ?>/show-codes.php');

?&gt;</textarea></p>

    <?php if ($cs->GetOpt('do_upload')) { ?>

        <h2>Donation Form</h2>

        <p>Copy an paste this snippet into the page where you'd like to display the code donation form for
            <strong><?= $subject ?></strong>.<br/>
            Make sure your page has a .php extension.</p>

        <p><textarea cols="80" rows="10">&lt;?php

// CodeSort <?php echo $cs->GetOpt('version'); ?> donation form
// subject: <?php echo $subject; ?>


$listing = <?php echo $fl; ?>;

include('<?php echo $cs->GetOpt('install_folder'); ?>/show-donate.php');

?&gt;</textarea></p>

    <?php } ?>

    <h2>Customization</h2>

    <p>Everything displayed on your site is wrapped in a div with the class <strong>codesort</strong>. The best way to
        customize the appearance of the script on your site is through <abbr title="Cascading Style Sheets">CSS</abbr>.
        For example, if you add this to your site's style sheet:</p>

    <p><textarea cols="80" rows="4">.codesort li {
	display: inline;
	padding-right: 0.5em;
	}</textarea></p>

    <p>you can display the size and category links horizontally without bullets like so:</p>

    <ul>
        <li style="display:inline; padding-right: 0.5em;"><a href="#">Link 1</a></li>
        <li style="display:inline; padding-right: 0.5em;"><a href="#">Link 2</a></li>
        <li style="display:inline; padding-right: 0.5em;"><a href="#">Link 3</a></li>
    </ul>

    <p>Here are many of the elements you can customize in this way:</p>

    <p><textarea cols="80" rows="9">/* add this rule to your CSS to get the donation form to align */
.codesort label {
	float: left;
	width: 12em;
	text-align: right;
	margin: 0 0.5em 0 0;
	}

.codesort h2 { }

.codesort h3 { }

.codesort p { }

.codesort a { }

.codesort img { }

.codesort ul { }

.codesort li { }

.codesort input { }

.codesort option { }

.codesort select { }

.credit { }</textarea></p>

    <?php

// ____________________________________________________________ LIST FANLISTINGS

} else {

    ?>

    <h2>Fanlistings</h2>

    <p>Select &#8216;manage codes&#8217; to add or edit codes. Select &#8216;get snippet&#8217; for instructions to
        display codes on your website.</p>

    <table>

        <thead>
        <tr>
            <th scope="col">Fanlisting</th>
            <th scope="col">Codes</th>
            <th scope="col">Manage</th>
            <th scope="col">Snippet</th>
        </tr>
        </thead>


        <tbody>
        <?php

        $query = "SELECT COUNT(code_id) FROM " . $cs->GetOpt('codes_table') . " WHERE code_fl=0";
        $num_collective = $cs->db->getFirstCell($query);

        ?>

        <tr class="odd">
            <td>Whole Collective</td>
            <td class="number"><?php echo $num_collective; ?></td>

            <td>
                <form action="add-code.php" method="get">
                    <input type="submit" value="manage codes"/>
                    <input type="hidden" name="action" value="list"/>
                    <input type="hidden" name="fl" value="0"/>
                </form>
            </td>

            <td>
                <form action="add-code.php" method="get">
                    <input type="submit" value="get snippet"/>
                    <input type="hidden" name="action" value="snippet"/>
                    <input type="hidden" name="fl" value="0"/>
                </form>
            </td>

        </tr>

        <?php

        $query = "SELECT " . $cs->GetOpt('col_id') . " AS fl,
     " . $cs->GetOpt('col_subj') . " AS subject, COUNT(code_id) AS num_code
      FROM " . $cs->GetOpt('collective_table') . "
      LEFT JOIN " . $cs->GetOpt('codes_table') . " ON " . $cs->GetOpt('col_id') . "=code_fl
      GROUP BY fl
      ORDER BY subject ASC";

        $cs->db->execute($query, 'Failed to select fanlistings. Check that your collective_script setting is properly configured.');

        while ($row = $cs->db->readRecord()) {

            $class = (isset($class) && $class == 'even') ? 'odd' : 'even';

            ?>
            <tr class="<?php echo $class; ?>">
                <td><?php echo $row['subject'] ?></td>
                <td class="number"><?php echo $row['num_code']; ?></td>

                <td>
                    <form action="add-code.php" method="get">
                        <input type="submit" value="manage codes"/>
                        <input type="hidden" name="action" value="list"/>
                        <input type="hidden" name="fl" value="<?php echo $row['fl']; ?>"/>
                    </form>
                </td>

                <td>
                    <form action="add-code.php" method="get">
                        <input type="submit" value="get snippet"/>
                        <input type="hidden" name="action" value="snippet"/>
                        <input type="hidden" name="fl" value="<?php echo $row['fl']; ?>"/>
                    </form>
                </td>

            </tr>
            <?php

        }

        $cs->db->freeResult();

        ?>
        </tbody>
    </table>

    <?php

}

$cs->GetFooter();
