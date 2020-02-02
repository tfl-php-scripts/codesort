<?php

require_once('codes-config.php');
require_once('functions.php');

$cs = CodeSort::GetInstance();

require_once('protect.php');

$cs->AddOptFromDb();

$cs->GetHeader();

?>

    <div class="col1">

        <p>Welcome to the CodeSort admin area.</p>

        <?php if ($cs->GetOpt('do_upload')) { ?>
            <p><a href="img-cleanup.php">Cleanup unused images?</a></p>
            <?php

        }

        // ____________________________________________________________ LIST UNAPPROVED CODES

        $query = "SELECT code_id, code_image, " . $cs->GetOpt('col_subj') . " AS subject, donor_name, donor_url
  FROM " . $cs->GetOpt('codes_table') . "
  LEFT JOIN " . $cs->GetOpt('collective_table') . " ON code_fl=" . $cs->GetOpt('col_id') . "
  LEFT JOIN " . $cs->GetOpt('donors_table') . " ON code_donor=donor_id
  WHERE code_approved='n'
  ORDER BY code_id " . $cs->GetOpt('sort_order');

        $cs->db->execute($query, 'Failed to select unapproved codes. Check that your collective_script setting is properly configured.');
        $num_unapproved = $cs->db->getNumRows();

        if ($num_unapproved > 0) {

            ?>

            <h2>Codes Awaiting Approval</h2>

            <p>There are currently <strong><?php echo $num_unapproved; ?></strong> unapproved codes. To approve a code,
                select &#8216;edit&#8217; to choose the right size and category for it. Otherwise select &#8216;delete&#8217;
                to reject it.</p>

            <form action="add-code.php" method="post">
                <table>

                    <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Fanlisting</th>
                        <th scope="col">Donor</th>
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
                        echo '<td>' . $cs->GetDonorName($row['donor_name'], $row['donor_url']) . '</td>';
                        echo '<td><input type="checkbox" name="id[' . $i . ']" value="' . $row['code_id'] . '" />
<input type="hidden" name="code_oldimg[' . $i . ']" value="' . $row['code_image'] . '" /></td>';
                        echo "</tr>\n";
                        $i++;
                    }

                    ?>
                    </tbody>

                    <tfoot>
                    <tr>
                        <td colspan="3" class="number"><a href="#" onclick="checkAll(false); return false;">Uncheck
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

        ?>

    </div>

    <div class="col2 sidebox">

        <h2>Stats</h2>

        <?php

        $query_count = 'SELECT COUNT(code_id) FROM ' . $cs->GetOpt('codes_table');
        $num_code = $cs->db->getFirstCell($query_count);

        ?>

        <p>You have <strong><?php echo $num_code; ?></strong> codes in total.</p>

    </div>

    <div class="col2 sidebox">

        <h2>Feed</h2>

        <?php

        function printFeed()
        {
            $updatesFeedUrl = 'https://scripts.robotess.net/projects/codesort/atom.xml';
            $posts = '';

            try {
                $doc = new DOMDocument();
                $success = @$doc->load($updatesFeedUrl);
                if (!$success) {
                    throw new Exception('Was not able to retrieve updates from remote server');
                }

                $domChannel = $doc->getElementsByTagName('channel');
                if ($domChannel->length !== 1) {
                    echo '<p class="success">Feed is empty</p>';
                }

                if ($domChannel->item(0)->getElementsByTagName('item')->length === 0) {
                    echo '<p class="success">Feed is empty</p>';
                }

                /** @var DOMElement $node */
                foreach ($domChannel->item(0)->getElementsByTagName('item') as $node) {
                    $title = $node->getElementsByTagName('title')->item(0)->nodeValue;
                    $link = $node->getElementsByTagName('link')->item(0)->nodeValue;
                    $pubdate = $node->getElementsByTagName('pubDate')->item(0)->nodeValue;
                    $description = $node->getElementsByTagName('description')->item(0)->nodeValue;

                    $timestamp = strtotime($pubdate);
                    $daylong = date('l', $timestamp);
                    $monlong = date('F', $timestamp);
                    $yyyy = date('Y', $timestamp);
                    $dth = date('jS', $timestamp);
                    $min = date('i', $timestamp);
                    $_24hh = date('H', $timestamp);

                    $posts .= <<<MARKUP
                <h4>{$title}<br />
                <small>{$daylong}, {$dth} {$monlong} {$yyyy}, {$_24hh}:{$min} &bull; <a href="{$link}" target="_blank">permalink</a></small></h4>
                <blockquote>{$description}</blockquote>
MARKUP;
                }
            } catch (Exception $e) {
                echo '<p class="error">Was not able to connect to feed: ' . $e->getMessage() . '</p>';
            }

            echo $posts;
        }

        printFeed();
        ?>

    </div>

<?php $cs->GetFooter();
