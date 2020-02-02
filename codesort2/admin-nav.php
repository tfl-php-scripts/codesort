<ul id="nav">
<li><a href="index.php">Index</a></li>
<li><a href="add-code.php">Codes</a></li>
<li><a href="add-size.php">Sizes</a></li>
<?php if ($this->GetOpt('use_cat')) { ?>
<li><a href="add-cat.php">Categories</a></li>
<?php } ?>
<li><a href="add-donor.php">Donors</a></li>
<?php if (!$this->GetOpt('collective_script')) { ?>
<li><a href="add-fl.php">Fanlistings</a></li>
<?php } ?>
<li><a href="options.php">Options</a></li>
<li><a href="index.php?action=logout">Logout</a></li>
</ul>