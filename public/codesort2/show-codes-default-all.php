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

if (!empty($_GET['size'])) {

    $size = (int)$_GET['size'];

    $query_size = 'SELECT size_size
      FROM ' . $cs->GetOpt('sizes_table') . "
      WHERE size_id=$size LIMIT 1";

    $cs->db->execute($query_size);

    if ($row_size = $cs->db->getRecord()) {

        echo '<h2>' . $row_size['size_size'] . "</h2>\n";

        echo '<p>';

        $query_codes = 'SELECT code_image, donor_id, donor_name, donor_url, code_fl,code_approved
          FROM ' . $cs->GetOpt('codes_table') . '
          LEFT JOIN ' . $cs->GetOpt('donors_table') . " ON code_donor=donor_id
          WHERE code_fl=$listing AND code_size=$size AND code_approved='y'
          ORDER BY code_id " . $cs->GetOpt('sort_order');

        $cs->db->execute($query_codes);

        $cs->printCodesFromLastQuery();

        $cs->db->freeResult();

        echo '<p><a href="' . clean_input($_SERVER['PHP_SELF']) . '">&laquo; Back</a></p>' . "\n";

    } else {
        $cs->AddErr('Null size.');
    }

// ____________________________________________________________ GET ALL

} else {

    echo "<h2>All Codes</h2>\n";

    $query_codes = 'SELECT code_image, donor_id, donor_name, donor_url, size_size, code_fl,code_approved, size_id
      FROM ' . $cs->GetOpt('codes_table') . '
      JOIN ' . $cs->GetOpt('sizes_table') . ' ON code_size=size_id
      LEFT JOIN ' . $cs->GetOpt('donors_table') . " ON code_donor=donor_id
      WHERE code_fl=$listing AND code_approved='y'
      ORDER BY size_order ASC, code_id " . $cs->GetOpt('sort_order');

    $cs->db->execute($query_codes);

    if(isset($limit) && (int)$limit > 0) {
        $cs->printAllSizesLimited((int)$limit);
    } else {
        $cs->printCodesFromLastQuery();
    }

    $cs->db->freeResult();
}

$cs->ReportErrors();

$query_total = 'SELECT COUNT(code_id)
  FROM ' . $cs->GetOpt('codes_table') . "
  WHERE code_fl=$listing AND code_approved='y'";

$num_total = $cs->db->getFirstCell($query_total);

$cs->printCredits();

echo "</div><!-- END #code .codesort -->\n";

// restore old error handler
restore_error_handler();
