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

if (!isset($listing)) {
    exit;
}

echo '<div id="codes" class="codesort">' . "\n";

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();
$cs->AddOptFromDb();

// ____________________________________________________________ GET SIZE

/**
 * @param $titled
 * @param $donor_name
 * @param $last_size
 * @param $row
 * @param CodeSort $cs
 * @param $donor_url
 * @param $code_image
 * @return array
 */

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

    } else {
        $cs->AddErr('Null size.');
    }

// ____________________________________________________________ GET CATEGORY

} elseif (!empty($_GET['cat']) && $cs->GetOpt('use_cat')) {

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

        $cs->printCodesFromLastQuery();

        $cs->db->freeResult();

    } else {
        $cs->AddErr('Null category.');
    }

// ____________________________________________________________ GET ALL

} elseif (isset($_GET['show']) && $_GET['show'] == 'all') {

    echo "<h2>All Codes</h2>\n";

    $query_codes = 'SELECT code_image, donor_id, donor_name, donor_url, size_size, code_fl,code_approved, size_id
      FROM ' . $cs->GetOpt('codes_table') . '
      JOIN ' . $cs->GetOpt('sizes_table') . ' ON code_size=size_id
      LEFT JOIN ' . $cs->GetOpt('donors_table') . " ON code_donor=donor_id
      WHERE code_fl=$listing AND code_approved='y'
      ORDER BY size_order, code_id " . $cs->GetOpt('sort_order');

    $cs->db->execute($query_codes);

    $cs->printCodesFromLastQuery();

    $cs->db->freeResult();
}

$cs->ReportErrors();

// ____________________________________________________________ LIST SIZES/CATS

$query_size = 'SELECT size_id, size_size, COUNT(code_id) AS num_size
  FROM ' . $cs->GetOpt('sizes_table') . '
  JOIN ' . $cs->GetOpt('codes_table') . " ON size_id=code_size
  WHERE code_fl=$listing AND code_approved='y'
  GROUP BY size_id
  ORDER BY size_order ASC";

$cs->db->execute($query_size);

if ($cs->db->getNumRows() > 0) {

    echo "<p>Select a size:</p>\n";
    echo "<ul>\n";

    while ($row = $cs->db->readRecord()) {

        echo '<li><a href="' . clean_input($_SERVER['PHP_SELF']) . '?size=' . $row['size_id'] . '#codes" title="' . $row['num_size'] . ' codes">' . $row['size_size'] . "</a></li>\n";

    }

    echo "</ul>\n";
}

$cs->db->freeResult();

if ($cs->GetOpt('use_cat')) {

    $query_cat = 'SELECT cat_id, cat_name, COUNT(code_id) AS num_cat
  FROM ' . $cs->GetOpt('cat_table') . '
  JOIN ' . $cs->GetOpt('codes_table') . " ON cat_id=code_cat
  WHERE code_fl=$listing AND code_approved='y'
  GROUP BY cat_id
  ORDER BY cat_name ASC";

    $cs->db->execute($query_cat);

    if ($cs->db->getNumRows() > 0) {

        echo "<p>Or select a category:</p>\n";
        echo "<ul>\n";

        while ($row = $cs->db->readRecord()) {

            echo '<li><a href="' . clean_input($_SERVER['PHP_SELF']) . '?cat=' . $row['cat_id'] . '#codes" title="' . $row['num_cat'] . ' codes">' . $row['cat_name'] . "</a></li>\n";

        }

        echo "</ul>\n";
    }

    $cs->db->freeResult();

} // end if cat

$query_total = 'SELECT COUNT(code_id)
  FROM ' . $cs->GetOpt('codes_table') . "
  WHERE code_fl=$listing AND code_approved='y'";

$num_total = $cs->db->getFirstCell($query_total);

echo '<p>Or <a href="' . clean_input($_SERVER['PHP_SELF']) . '?show=all#codes" title="' . $num_total . ' codes">view all</a>.</p>' . "\n";

$cs->printCredits();

echo "</div><!-- END #code .codesort -->\n";

// restore old error handler
restore_error_handler();
