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

$cs->GetHeader('Images Cleanup');

if (isset($_POST['action']) && $_POST['action'] == 'delete') {

    $clean = clean_input($_POST);

    foreach ($clean['file'] as $goodbye) {
        if ($cs->DeleteImage($goodbye)) {
            $cs->AddSuccess($goodbye.' deleted.');
        } else {
            $cs->AddErr($goodbye.' could not be deleted.');
        }
    }

    // _____________________________________________ REPORT SUCCESS

    $cs->ReportSuccess();

    // _____________________________________________ REPORT ERRORS

    $cs->ReportErrors();

} else {

if ($handle = opendir($cs->GetOpt('images_folder'))) {
    while (false !== ($file = readdir($handle))) {
        if ($file != '.' && $file != '..' && $file != 'index.php') {
            $files[] = $file;
        }
    }
    closedir($handle);
}

$query = 'SELECT code_image FROM ' .$cs->GetOpt('codes_table');
$cs->db->execute($query);
while ($row = $cs->db->readRecord()) {
    $imgs[] = $row['code_image'];
}

$cs->db->freeResult();

$old = array_diff($files, $imgs);

?>

<h2>Image Cleanup</h2>

<p>The below images are no longer used by CodeSort, and you are free to delete them if you so choose.</p>

<?php

if (!empty($old)) {

    ?>
<form action="img-cleanup.php" method="post">

<table>

<thead>
<tr>
<th scope="col">Image</th>
<th scope="col">Delete?</th>
</tr>
</thead>

<tbody>
<?php

    natcasesort($old);

    foreach ($old as $file) {

        $class = (isset($class) && $class == 'odd') ? 'even' : 'odd';

        ?>
        <tr class="<?php echo $class; ?>">
        <td><?php echo $cs->GetCodeImg($file); ?></td>
        <td><input type="checkbox" name="file[]" value="<?php echo $file; ?>" /></td>
        </tr>
        <?php

    }

?>
</tbody>

<tfoot>
<tr>
<td class="number"><a href="#" onclick="checkAll(false); return false;">Uncheck All</a> /
<a href="#" onclick="checkAll(true); return false;">Check All</a></td>
<td><input type="submit" name="action" value="delete" onclick="return confirm('Delete all checked images?');" /></td>
</tr>
</tfoot>

</table>

</form>
<?php

} else {
    echo '<p>No unused images found. Yay!</p>';
}

}

$cs->GetFooter();
