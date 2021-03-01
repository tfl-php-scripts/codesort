<?php
/*****************************************************************************
 * CodeSort
 *
 * Copyright (c) 2021 by Ekaterina http://scripts.robotess.net
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

if (!isset($listing)) {
    exit;
}

echo '<div id="codes" class="codesort">' . "\n";

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();
$cs->AddOptFromDb();

if (!empty($_GET['cat'])) {

    $cat = (int)$_GET['cat'];

    $query_cat = 'SELECT cat_name
      FROM ' . $cs->GetOpt('cat_table') . "
      WHERE cat_id=$cat LIMIT 1";

    $cs->db->execute($query_cat);

    if ($row_cat = $cs->db->getRecord()) {

        echo '<h2>' . $row_cat['cat_name'] . "</h2>\n";

        $query_codes = 'SELECT code_image, donor_id, donor_name, donor_url, size_size, code_fl,code_approved,code_cat
          FROM ' . $cs->GetOpt('codes_table') . '
          JOIN ' . $cs->GetOpt('sizes_table') . ' ON code_size=size_id
          LEFT JOIN ' . $cs->GetOpt('donors_table') . " ON code_donor=donor_id
          WHERE code_fl=$listing AND code_cat=$cat AND code_approved='y'
          ORDER BY size_order ASC, code_id " . $cs->GetOpt('sort_order');

        $cs->db->execute($query_codes);

        $cs->printAllCatsLimited(99999);

        $cs->db->freeResult();

        echo '<p><a href="' . clean_input($_SERVER['PHP_SELF']) . '">&laquo; Back</a></p>' . "\n";

    } else {
        $cs->AddErr('Null category.');
    }

// ____________________________________________________________ GET ALL

} else {

    echo "<h2>All Codes</h2>\n";

    $query_codes = 'SELECT code_image, donor_id, donor_name, donor_url, cat_name, code_fl,code_approved, cat_id
      FROM ' . $cs->GetOpt('codes_table') . '
      JOIN ' . $cs->GetOpt('cat_table') . ' ON cat_id=code_cat
      LEFT JOIN ' . $cs->GetOpt('donors_table') . " ON code_donor=donor_id
      WHERE code_fl=$listing AND code_approved='y'
      ORDER BY cat_name ASC, code_id " . $cs->GetOpt('sort_order');

    $cs->db->execute($query_codes);

    $cs->printAllCatsLimited($limit ?? 5);

    $cs->db->freeResult();
}

$cs->ReportErrors();

$cs->printCredits();

echo "</div><!-- END #code .codesort -->\n";

// restore old error handler
restore_error_handler();
