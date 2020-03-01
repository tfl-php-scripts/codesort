<?php
/*****************************************************************************
 * CodeSort
 *
 * Copyright (c) Jenny Ferenc <jenny@prism-perfect.net>
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
?><ul id="nav">
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
