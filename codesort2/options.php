<?php

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();

require_once('protect.php');

$cs->AddOptFromDb();

$cs->GetHeader('Options');

$clean = array();

?>

<h2>Options</h2>

<?php

// _____________________________________________ UPDATE QUERY

if (isset($_POST['action']) && $_POST['action'] == 'update') {

    foreach ($_POST['key'] as $id => $key) {

        $key = $cs->db->escape(clean_input($key));
        $value = $cs->db->escape(clean_input($_POST['value'][$id]));

        $query = "UPDATE ".$cs->GetOpt('options_table')." SET optvalue='$value' WHERE optkey='$key'";

        if (! $cs->db->execute($query)) {
            $cs->AddErr('Failed to update <strong>'.$key.'</strong>');
        } else {
            $cs->AddSuccess('Set <strong>'.$key.'</strong> = '.$value);
        }
    }
}

// _____________________________________________ REPORT SUCCESS

$cs->ReportSuccess();

// _____________________________________________ REPORT ERRORS

$cs->ReportErrors();

// _____________________________________________ OPTIONS FORM

?>

<p>Be aware, that if you change the <strong>install_folder</strong> you will need to generate new code snippets for inclusion in your fanlistings.</p>

<form action="options.php" method="post">

<?php

$query = "SELECT * FROM ".$cs->GetOpt('options_table');
$cs->db->execute($query);

while ($row = $cs->db->readRecord()) {
    $class = (isset($class) && $class == 'odd') ? 'even' : 'odd';
    ?>
<div class="clearfix <?php echo $class; ?>">
<div class="col1">
<p><label for="<?php echo $row['optkey']; ?>"><?php echo $row['optkey']; ?></label>
<input type="text" id="<?php echo $row['optkey']; ?>" name="value[]" size="60" maxlength="255" value="<?php echo $row['optvalue']; ?>" />
<input type="hidden" name="key[]" value="<?php echo $row['optkey']; ?>" /></p>
</div>
<div class="col2">
<p class="footnote"><?php echo $row['optdesc']; ?></p>
</div>
</div>
<?php

}

$cs->db->freeResult();

?>

<div class="col1">
<p><label>&nbsp;</label>
<input type="submit" name="action" value="update" /></p>
</div>

</form>

<?php $cs->GetFooter();
