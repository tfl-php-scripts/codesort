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

// DON'T CHANGE THESE (unless you really want larger/different allowed files)
$codesort['max_file_size'] = 30720; // 30kb
$codesort['file_types_array'] = array(
    'jpg',
    'png',
    'gif',
);

require_once('SqlConnection.php');

// requires SqlConnection class
class CodeSort
{
    private $config = [
        'version' => '[Robotess Fork] 1.0',
        'oldUrl' => 'http://prism-perfect.net/codesort',
        'url' => 'http://scripts.robotess.net',
    ];

    public $_colcfg = array();
    public $_errors = array();
    public $_success = array();
    public $db;

    private function __construct()
    {
        global $codesort, $coltable;

        $this->config = array_merge($this->config, $codesort);
        $this->config['col_id'] = $coltable[$this->GetOpt('collective_script', true)]['id'];
        $this->config['col_subj'] = $coltable[$this->GetOpt('collective_script', true)]['subject'];
        $this->db = SqlConnection::self($this->GetOpt('dbhost'), $this->GetOpt('dbuser'), $this->GetOpt('dbpass'), $this->GetOpt('dbname'));
    }

    /**
     * @return self
     */
    public static function GetInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $object = __CLASS__;
            $instance = new $object;
        }

        return $instance;
    }

    public function printCredits()
    {
        ?>
        Powered by <a href="<?= $this->GetOpt('url') ?>" target="_blank"
                      title="PHP Scripts: Enthusiast, Siteskin, Codesort - ported to PHP 7">CodeSort <?= $this->GetOpt('version') ?></a>
        (original author: <a href="<?= $this->GetOpt('oldUrl') ?>" target="_blank">Jenny</a>)
        <?php
    }

    public function AddOptFromDb()
    {
        $query = 'SELECT * FROM ' . $this->GetOpt('options_table');
        $this->db->execute($query);
        while ($row = $this->db->readRecord()) {
            $this->AddOpt($row['optkey'], $row['optvalue']);
        }
        $this->db->freeResult();
    }

    public function AddOpt($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function GetAllTables()
    {
        return array(
            $this->GetOpt('codes_table'),
            $this->GetOpt('sizes_table'),
            $this->GetOpt('cat_table'),
            $this->GetOpt('donors_table'),
            $this->GetOpt('options_table'),
            $this->GetOpt('collective_table'),
        );
    }

    public function GetOpt($opt_name, $returnLiteral = false)
    {
        if (isset($this->config[$opt_name])) {
            $opt = $this->config[$opt_name];
        } else {
            return false;
        }

        if (!$returnLiteral) {
            if ($opt === 0 || $opt == 'n' || $opt == 'no') {
                return false;
            }
            if ($opt === 1 || $opt == 'y' || $opt == 'yes') {
                return true;
            }
        }

        return $opt;
    }

    public function GetCodeImg($img_name)
    {
        return '<img src="' . $this->GetOpt('images_url') . '/' . $img_name . '" alt="" />';
    }

    public function GetDonorName($name, $url = '')
    {
        if (!empty($url)) {
            return '<a href="' . $url . '">' . $name . '</a>';
        }

        return $name;
    }

    public function GetCodeApproved($appr)
    {
        if ($appr === 'n') {
            return '<span class="error">No</span>';
        }

        return 'Yes';
    }

    // get rid of bad characters
    public function FixFilename($orig_name)
    {
        return strtolower(preg_replace('/[^-_.0-9a-zA-Z]/', '', $orig_name));
    }

    public function RenameImage($orig_name, $new_name)
    {
        $filename = $this->FixFilename($new_name);
        $newpath = $this->GetOpt('images_folder') . $filename;
        if (file_exists($newpath)) {
            $this->AddErr($orig_name . ' could not be renamed; file already exists.');

            return array(
                false,
                $orig_name,
            );
        }

        if (rename($this->GetOpt('images_folder') . $orig_name, $newpath)) {
            return array(
                true,
                $filename,
            );
        }

        $this->AddErr($orig_name . ' could not be renamed.');

        return array(
            false,
            $orig_name,
        );
    }

    public function UploadImage($orig_name, $temp_name, $err, $size, $old_file = null)
    {
        if ($err === UPLOAD_ERR_OK) {

            $orig_name = $this->FixFilename($orig_name);
            $newpath = $this->GetOpt('images_folder') . $orig_name;
            $filenameext = (strpos($orig_name, '.') === false) ? '' : substr(strrchr($orig_name, '.'), 1);

            // add timestamp to filename if exists
            // don't remove or new files could over-write existing ones (same names)
            if (file_exists($newpath)) {
                $filename = substr($orig_name, 0, strlen($orig_name) - strlen($filenameext)) . time() . '.' . $filenameext;
            } else {
                $filename = $orig_name;
            }
            $file_ext_allow = false;

            $allowed_ext = $this->GetOpt('file_types_array');
            foreach ($allowed_ext as $xValue) {
                if ($filenameext === $xValue) {
                    $file_ext_allow = true;
                }
            }

            if ($size > $this->GetOpt('max_file_size')) {
                $this->AddErr($orig_name . ' is too big; not uploaded.');

                return [
                    false,
                    $orig_name,
                ];
            }

            if ($file_ext_allow) {
                if (move_uploaded_file($temp_name, $this->GetOpt('images_folder') . $filename)) {
                    if (!empty($old_file)) {
                        $this->DeleteImage($old_file);
                    }
                    $this->AddSuccess($this->GetCodeImg($filename) . ' uploaded successfully.');

                    return [
                        true,
                        $filename,
                    ];
                }

                $this->AddErr($orig_name . ' not uploaded.');

                return [
                    false,
                    $orig_name,
                ];
            }

            $this->AddErr($orig_name . ' has an invalid file extension; not uploaded.');

            return array(
                false,
                $orig_name,
            );
        }

        if ($err === UPLOAD_ERR_FORM_SIZE) {
            $this->AddErr($orig_name . ' is too big; not uploaded.');

            return [
                false,
                $orig_name,
            ];
        }

        $this->AddErr($orig_name . ' not uploaded.');

        return [
            false,
            $orig_name,
            $orig_name . ' not uploaded.',
        ];
    }

    public function DeleteImage($img_name)
    {
        $path = $this->GetOpt('images_folder') . $img_name;
        return file_exists($path) && unlink($path);
    }

    public function GetHeader($pageTitle = null)
    {
        require_once('header.php');
    }

    public function GetFooter($showNav = true)
    {
        require_once('footer.php');
    }

    public function ReportErrors()
    {
        if (!empty($this->_errors)) {
            echo '<ul class="error">' . "\n";
            foreach ($this->_errors as $msg) {
                $msg = trim($msg);
                if (!empty($msg)) {
                    echo '<li>' . $msg . "</li>\n";
                }
            }
            echo "</ul>\n";
        }
    }

    public function ReportSuccess()
    {
        if (!empty($this->_success)) {
            echo '<ul class="success">' . "\n";
            foreach ($this->_success as $msg) {
                $msg = trim($msg);
                if (!empty($msg)) {
                    echo '<li>' . $msg . "</li>\n";
                }
            }
            echo "</ul>\n";
        }
    }

    public function AddErr($msg)
    {
        $this->_errors[] = $msg;
    }

    public function AddSuccess($msg)
    {
        $this->_success[] = $msg;
    }

    public function NoErr()
    {
        return !(count($this->_errors) > 0);
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        $query_opt = sprintf("SELECT * FROM %s WHERE optkey='admin_email' LIMIT 1", $this->GetOpt('options_table'));
        if (!$this->db->execute($query_opt, null)) {
            return false;
        }

        $isCodeSortInstalled = false;
        if ($this->db->getNumRows() > 0) {
            $isCodeSortInstalled = true;
        }

        $this->db->freeResult();
        return $isCodeSortInstalled;
    }

    public function printCodesFromLastQuery()
    {
        $sizedDonored = [];

        while ($row = $this->db->readRecord()) {
            if (!isset($sizedDonored[$row['size_size']])) {
                $sizedDonored[$row['size_size']] = [];
            }

            if (!isset($sizedDonored[$row['size_size']][$row['donor_id']])) {
                $sizedDonored[$row['size_size']][$row['donor_id']] = [];
            }

            $sizedDonored[$row['size_size']][$row['donor_id']][] = $row;
        }

        $lastSize = '';
        foreach ($sizedDonored as $size => $data) {
            if ($lastSize !== $size) {
                echo '<h3>' . $size . "</h3>\n";
                echo '<p>';
                $lastSize = $size;
            }

            asort($data);
            $lastDonor = '';
            foreach ($data as $donorId => $donorData) {
                if ($lastDonor !== $donorId) {
                    echo '<p>From ' . $this->GetDonorName($donorData[0]['donor_name'], $donorData[0]['donor_url']) . "<br />\n";
                    echo '<p>';
                    $lastDonor = $donorId;
                }

                foreach ($donorData as $row) {
                    echo $this->GetCodeImg($row['code_image']) . "\n";
                }
            }
        }
    }
}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
        case E_USER_ERROR:
            echo '<p class="error"><strong>ERROR:</strong> ' . $errstr . '<br />' . "\n";
            echo "  Fatal error on line $errline in file $errfile";
            echo "Aborting...</p>\n";
            exit(1);
            break;

        case E_WARNING:
        case E_USER_WARNING:
            echo '<p class="error"><strong>WARNING:</strong> ' . $errstr . '</p>' . "\n";
            break;

        case E_NOTICE:
        case E_USER_NOTICE:
            //echo '<p><strong>NOTICE:</strong> '.$errstr.'</p>'."\n";
            break;

        default:
            //echo "<p>Unknown error type: [$errno] $errstr</p>\n";
            break;
    }

    /* Don't execute PHP internal error handler */

    return true;
}

$old_error_handler = set_error_handler('myErrorHandler');

function clean_input($str, $allowtags = false)
{
    if (is_array($str)) {
        array_walk($str, 'clean_walk', $allowtags);

        return $str;
    }

    $str = stripslashes($str);

    if ($allowtags !== true) {
        $str = htmlspecialchars(strip_tags($str));
    }

    $str = trim($str);

    return $str;
}

function clean_walk(&$item, $key, $allowtags)
{
    $item = clean_input($item, $allowtags);
}

// ________________________________________ ERROR REPORTING

function validateTextOnly($input)
{
    return false !== preg_match('@^[A-Za-z0-9 ]+$@', $input);
}
