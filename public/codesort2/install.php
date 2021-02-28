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

$cs->GetHeader('Install');

?>

    <h2>CodeSort <?= $cs->GetOpt('version') ?> SETUP</h2>

<?php

if ($cs->isInstalled()) {

    echo '<p class="error">You\'ve previously installed CodeSort version <strong>2.0</strong>, so you <strong>do not</strong> need to run this installation script; your database is already properly configured. <strong>DELETE</strong> this file from your server and continue on <a href="index.php">to the ADMIN panel.</a></p>';

} else {

    // @todo is it needed?
    {
        $dir = __DIR__;
        // correct for weird DreamHost .name thingy
        $dir = preg_replace('#/\.([a-z]+)#', '', $dir);
        // fix Windows dir slashes
        $dir = str_replace('\\', '/', $dir);

        $row_opt['install_folder'] = $dir;
        // assume images inside dir
        $row_opt['images_folder'] = $row_opt['install_folder'] . '/images/';

        // figure absolute URL w/out trailing slash
        $dir = str_replace(array($_SERVER['DOCUMENT_ROOT'], '/' . basename($dir)), '', $dir);

        // The URL where you've uploaded this script. NO TRAILING SLASH!
        $row_opt['install_url'] = 'http://' . $_SERVER['SERVER_NAME'] . $dir;

        // The URL of your images folder. NO TRAILING SLASH!
        $row_opt['images_url'] = $row_opt['install_url'] . '/images';

    } // end if version 1.5

    if (isset($_POST['fresh_install'])) {
        // FORM is sent

        $query = 'CREATE TABLE ' . $cs->GetOpt('codes_table') . " (
      code_id int(6) unsigned NOT NULL auto_increment,
      code_fl int(2) unsigned NOT NULL default '0',
      code_cat int(6) unsigned NOT NULL default '0',
      code_size int(2) unsigned NOT NULL default '0',
      code_image varchar(100) NOT NULL default '',
      code_donor int(6) unsigned NOT NULL default '0',
      code_approved enum('y','n') NOT NULL default 'y',
      PRIMARY KEY  (code_id),
      KEY code_fl (code_fl,code_size,code_cat),
      KEY code_approved (code_approved)
    )";

        if ($cs->db->execute($query)) {
            $cs->AddSuccess('Table <strong>' . $cs->GetOpt('codes_table') . '</strong> created.');
        } else {
            $cs->AddErr('Table <strong>' . $cs->GetOpt('codes_table') . '</strong> not created!');
        }

        $query = 'CREATE TABLE ' . $cs->GetOpt('sizes_table') . " (
      size_id int(2) unsigned NOT NULL auto_increment,
      size_order tinyint(3) unsigned NOT NULL default '0',
      size_size varchar(20) NOT NULL default '',
      PRIMARY KEY  (size_id)
    )";

        if ($cs->db->execute($query)) {
            $cs->AddSuccess('Table <strong>' . $cs->GetOpt('sizes_table') . '</strong> created.');
        } else {
            $cs->AddSuccess('Table <strong>' . $cs->GetOpt('sizes_table') . '</strong> not created!');
        }

        $query = 'CREATE TABLE ' . $cs->GetOpt('cat_table') . " (
      cat_id int(6) unsigned NOT NULL auto_increment,
      cat_fl int(6) unsigned NOT NULL default '0',
      cat_name varchar(50) NOT NULL default '',
      PRIMARY KEY  (cat_id),
      KEY cat_fl (cat_fl)
    )";

        if ($cs->db->execute($query)) {
            $cs->AddSuccess('Table <strong>' . $cs->GetOpt('cat_table') . '</strong> created.');
        } else {
            $cs->AddErr('Table <strong>' . $cs->GetOpt('cat_table') . '</strong> not created!');
        }

        $query = 'CREATE TABLE ' . $cs->GetOpt('donors_table') . " (
      donor_id int(6) unsigned NOT NULL auto_increment,
      donor_name varchar(20) NOT NULL default '',
      donor_url varchar(100) NOT NULL default '',
      PRIMARY KEY  (donor_id)
    )";

        if ($cs->db->execute($query)) {
            $cs->AddSuccess('Table <strong>' . $cs->GetOpt('donors_table') . '</strong> created.');
        } else {
            $cs->AddErr('Table <strong>' . $cs->GetOpt('donors_table') . '</strong> not created!');
        }

        if (!$cs->GetOpt('collective_script')) {

            $query = 'CREATE TABLE ' . $cs->GetOpt('collective_table') . ' (
            fl_id int(8) NOT NULL auto_increment,
            fl_subject varchar(50) NOT NULL,
            PRIMARY KEY  (fl_id)
        )';

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('Table <strong>' . $cs->GetOpt('collective_table') . '</strong> created.');
            } else {
                $cs->AddSuccess('Table <strong>' . $cs->GetOpt('collective_table') . '</strong> not created!');
            }
        }

        $query = 'CREATE TABLE ' . $cs->GetOpt('options_table') . ' (
      optkey varchar(30) NOT NULL,
      optvalue varchar(255) NOT NULL,
      optdesc varchar(255) NOT NULL,
      PRIMARY KEY  (optkey)
    )';

        if ($cs->db->execute($query)) {
            $cs->AddSuccess('Table <strong>' . $cs->GetOpt('options_table') . '</strong> created.');
        } else {
            $cs->AddErr('Table <strong>' . $cs->GetOpt('options_table') . '</strong> not created!');
        }

        foreach ($_POST['key'] as $id => $key) {

            $key = $cs->db->escape(clean_input($key));
            $value = $cs->db->escape(clean_input($_POST['value'][$id]));
            $desc = $cs->db->escape(clean_input($_POST['desc'][$id]));

            $query = 'INSERT INTO ' . $cs->GetOpt('options_table') . " SET optkey='$key', optvalue='$value', optdesc='$desc'";

            if ($cs->db->execute($query)) {
                $cs->AddSuccess('Set <strong>' . $key . '</strong> = ' . $value);
            } else {
                $cs->AddErr('Failed to insert <strong>' . $key . '</strong>');
            }
        }

        $cs->ReportSuccess();

        $cs->ReportErrors();

        echo '<p>Now <strong>DELETE</strong> this file from your server and start adding codes!</p>' . "\n";
        echo '<p><a href="index.php">To the ADMIN panel.</a></p>' . "\n";

    } else {
        // SHOW FORM
        ?>

        <p>Edit these options for your site (please be careful to follow the directions about where to use trailing
            slashes!), then submit the form to install CodeSort <?= $cs->GetOpt('version') ?>.</p>

        <form action="install.php" method="post">

            <div class="clearfix odd">
                <div class="col1">
                    <p><label for="admin_email">admin_email</label>
                        <input type="text" id="admin_email" name="value[]" size="60" maxlength="255"
                               value="<?php if (!empty($row_opt['admin_email'])) {
                                   echo $row_opt['admin_email'];
                               } ?>"/>
                        <input type="hidden" name="key[]" value="admin_email"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]" readonly="readonly">Your email address, so you can be notified of new donated codes.</textarea>
                    </p>
                </div>
            </div>
            <div class="clearfix even">
                <div class="col1">
                    <p><label for="collective_name">collective_name</label>
                        <input type="text" id="collective_name" name="value[]" size="60" maxlength="255"
                               value="<?php if (!empty($row_opt['collective_name'])) {
                                   echo $row_opt['collective_name'];
                               } ?>"/>
                        <input type="hidden" name="key[]" value="collective_name"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]"
                                                  readonly="readonly">The name of your site collective.</textarea></p>
                </div>
            </div>
            <div class="clearfix odd">
                <div class="col1">
                    <p><label for="install_folder">install_folder</label>
                        <input type="text" id="install_folder" name="value[]" size="60" maxlength="255"
                               value="<?php if (!empty($row_opt['install_folder'])) {
                                   echo $row_opt['install_folder'];
                               } ?>"/>
                        <input type="hidden" name="key[]" value="install_folder"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]" readonly="readonly">The full server path to your CodeSort directory. NO trailing slash. Ex: /home/username/public_html/codesort</textarea>
                    </p>
                </div>
            </div>
            <div class="clearfix even">
                <div class="col1">
                    <p><label for="install_url">install_url</label>
                        <input type="text" id="install_url" name="value[]" size="60" maxlength="255"
                               value="<?php if (!empty($row_opt['install_url'])) {
                                   echo $row_opt['install_url'];
                               } ?>"/>
                        <input type="hidden" name="key[]" value="install_url"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]" readonly="readonly">The URL to your CodeSort directory. NO trailing slash. Ex: http://example.com/codesort</textarea>
                    </p>
                </div>
            </div>
            <div class="clearfix odd">
                <div class="col1">
                    <p><label for="images_folder">images_folder</label>
                        <input type="text" id="images_folder" name="value[]" size="60" maxlength="255"
                               value="<?php if (!empty($row_opt['images_folder'])) {
                                   echo $row_opt['images_folder'];
                               } ?>"/>
                        <input type="hidden" name="key[]" value="images_folder"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]" readonly="readonly">The full server path to your CodeSort images directory. INCLUDE the trailing slash. Ex: /home/username/public_html/codesort/images/</textarea>
                    </p>
                </div>
            </div>
            <div class="clearfix even">
                <div class="col1">
                    <p><label for="images_url">images_url</label>
                        <input type="text" id="images_url" name="value[]" size="60" maxlength="255"
                               value="<?php if (!empty($row_opt['images_url'])) {
                                   echo $row_opt['images_url'];
                               } ?>"/>
                        <input type="hidden" name="key[]" value="images_url"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]" readonly="readonly">The URL to your CodeSort images directory. NO trailing slash. Ex: http://example.com/codesort/images</textarea>
                    </p>
                </div>
            </div>
            <div class="clearfix odd">
                <div class="col1">
                    <p><label for="do_upload">do_upload</label>
                        <input type="text" id="do_upload" name="value[]" size="60" maxlength="255" value="y"/>
                        <input type="hidden" name="key[]" value="do_upload"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]" readonly="readonly">Use CodeSort to upload images directly? y or n</textarea>
                    </p>
                </div>
            </div>
            <div class="clearfix even">
                <div class="col1">
                    <p><label for="use_cat">use_cat</label>
                        <input type="text" id="use_cat" name="value[]" size="60" maxlength="255" value="y"/>
                        <input type="hidden" name="key[]" value="use_cat"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]" readonly="readonly">Use categories? y or n</textarea>
                    </p>
                </div>
            </div>
            <div class="clearfix odd">
                <div class="col1">
                    <p><label for="use_captcha">use_captcha</label>
                        <input type="text" id="use_captcha" name="value[]" size="60" maxlength="255" value="y"/>
                        <input type="hidden" name="key[]" value="use_captcha"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]"
                                                  readonly="readonly">Use CAPTCHA on donation form? y or n</textarea>
                    </p>
                </div>
            </div>
            <div class="clearfix even">
                <div class="col1">
                    <p><label for="num_per_page">num_per_page</label>
                        <input type="text" id="num_per_page" name="value[]" size="60" maxlength="255" value="20"/>
                        <input type="hidden" name="key[]" value="num_per_page"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]" readonly="readonly">Number of items displayed per page for pagination.</textarea>
                    </p>
                </div>
            </div>
            <div class="clearfix odd">
                <div class="col1">
                    <p><label for="sort_order">sort_order</label>
                        <input type="text" id="sort_order" name="value[]" size="60" maxlength="255" value="DESC"/>
                        <input type="hidden" name="key[]" value="sort_order"/></p>
                </div>
                <div class="col2">
                    <p class="footnote"><textarea name="desc[]" readonly="readonly">Display order of codes. DESC for newest first; ASC for oldest first.</textarea>
                    </p>
                </div>
            </div>

            <fieldset>
                <legend>Action</legend>
                <p>
                    <input type="submit" name="fresh_install" value="Create fresh installation"/>
                </p>
            </fieldset>

        </form>

<?php

    }
}

$cs->GetFooter(false);
