<?php

if ($cs->GetOpt('do_upload')) {

    $clean = [];

// ____________________________________________________________ UPLOAD FORM

    if (isset($_POST['action'])) {

        $clean = clean_input($_POST);

        if ($_POST['action'] === 'continue') {

            // check captcha
            if ($cs->GetOpt('use_captcha')) {
                if (!isset($_SESSION['security_code']) || $_POST['captcha'] !== $_SESSION['security_code']) {
                    $_SESSION = array();
                    session_destroy();
                    $cs->AddErr('Sorry, that is not the correct CAPTCHA code. Please go back and try again.');
                }
            }

            if ($cs->NoErr()) {
                if (!empty($clean['new_donor_name'])) {
                    if (validateTextOnly($clean['new_donor_name'])) {

                        $new_donor_name = $cs->db->escape($clean['new_donor_name']);
                        $new_donor_url = $cs->db->escape($clean['new_donor_url']);

                        $query = 'INSERT INTO ' . $cs->GetOpt('donors_table') . " (donor_name, donor_url)
                      VALUES ('$new_donor_name', '$new_donor_url')";

                        if ($cs->db->execute($query)) {
                            $donor_id = $cs->db->getLastSequence();
                            $cs->AddSuccess('Thank you, <strong>' . $clean['new_donor_name'] . '</strong>. You have been added to the donor\'s list. Please upload the code(s) you would like to donate with the form below.');
                        } else {
                            $cs->AddErr('There was an error adding your name to the donor list. Perhaps you should let the site owner know about this problem?');
                        }
                    } else {
                        $cs->AddErr('You must enter a valid name (only alphanumeric characters). Please go back and try again.');
                    }
                } else {
                    $donor_id = (int)$_POST['code_donor'];
                    if (!empty($donor_id)) {
                        $cs->AddSuccess('Please upload the code(s) you would like to donate with the form below.');
                    } else {
                        $cs->AddErr('You must either select your name from the list of previous code donors, or enter your name. Please go back and try again.');
                    }
                }
            }

            // _____________________________________________ REPORT SUCCESS

            $cs->ReportSuccess();

            // _____________________________________________ REPORT ERRORS

            $cs->ReportErrors();

            if ($cs->NoErr()) {

                if ($cs->GetOpt('use_captcha')) {
                    $token = md5(uniqid(mt_rand(), TRUE));
                    $_SESSION['token'] = $token;
                    $_SESSION['token_time'] = time();
                }

                ?>

                <form action="<?php echo $form_action; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $cs->GetOpt('max_file_size'); ?>"/>
                    <input type="hidden" name="listing" value="<?php echo $clean['listing']; ?>"/>
                    <input type="hidden" name="returnto" value="<?php echo $clean['returnto']; ?>"/>
                    <input type="hidden" name="donor_id" value="<?php echo $donor_id; ?>"/>
                    <?php if ($cs->GetOpt('use_captcha')) { ?><input type="hidden" name="token"
                                                                     value="<?php echo $_SESSION['token']; ?>" /><?php } ?>

                    <p>The maximum file size is <?php echo round($cs->GetOpt('max_file_size') / 1024, 1); ?> KB. The
                        only allowable file types are <?php echo implode(', ', $cs->GetOpt('file_types_array')); ?>.</p>

                    <?php for ($x = 0; $x < $_POST['num_of_uploads']; $x++) {
                        echo '<p><input type="file" name="file[]" /></p>' . "\n";
                    } ?>

                    <p><input type="submit" name="action" value="upload"/></p>

                </form>

                <?php

            }
// ____________________________________________________________ PROCESS SUBMISSIONS

        } elseif ($_POST['action'] === 'upload') {

            $yay_codes = false;

            $code_donor = (int)$clean['donor_id'];
            $listing = (int)$clean['listing'];

            if (empty($listing) && $listing !== 0) {
                $cs->AddErr('Invalid site ID. Please try again from the beginning, if you are a legitimate submitter.');
            }
            if (empty($code_donor)) {
                $cs->AddErr('Invalid donor ID. Please try again from the beginning, if you are a legitimate submitter.');
            }

            if ($cs->GetOpt('use_captcha') && $_POST['token'] !== $_SESSION['token']) {
                $cs->AddErr('Invalid session. Please try again from the beginning, if you are a legitimate submitter.');
            }

            if ($cs->NoErr()) {

                $query_size = 'SELECT size_id FROM ' . $cs->GetOpt('sizes_table') . ' ORDER BY size_order ASC LIMIT 1';

                $cs->db->execute($query_size);

                $size_id = $cs->db->getFirstCell();

                foreach ($_FILES['file']['error'] as $key => $value) {

                    $result = false;
                    if (!empty($_FILES['file']['name'][$key])) {
                        $origfilename = $_FILES['file']['name'][$key];
                        list($result, $filename) = $cs->UploadImage($_FILES['file']['name'][$key], $_FILES['file']['tmp_name'][$key], $_FILES['file']['error'][$key], $_FILES['file']['size'][$key]);
                    }

                    if ($result) {

                        $code_image = $cs->db->escape($filename);

                        $query = "INSERT INTO " . $cs->GetOpt('codes_table') . "
                      SET code_fl=$listing, code_size=$size_id,
                      code_donor=$code_donor, code_approved='n', code_image='$code_image'";

                        if ($cs->db->execute($query)) {
                            $yay_codes = true;
                        }
                    }
                }
            }

            // _____________________________________________ REPORT SUCCESS

            $cs->ReportSuccess();

            // _____________________________________________ REPORT ERRORS

            $cs->ReportErrors();

            if ($yay_codes) {

                $msg = "You have newly donated codes waiting to be approved.\nPlease login to your admin panel:\n\n";
                $msg .= $cs->GetOpt('install_url') . "/\n\n";
                $msg .= "- CodeSort notifier -\n";

                $recipient = $cs->GetOpt('admin_email');

                $subject = "CodeSort: Code donation";

                $mailheaders = "From:CodeSort <" . $cs->GetOpt('admin_email') . ">\n";

                @mail($recipient, $subject, $msg, $mailheaders);

                echo '<p>Thank you for your submission! Once these codes have been approved and categorized by the owner, they will be added to the codes page with a link back to your website if you have one.</p>';

            }
        }

        echo '<p><a href="' . $clean['returnto'] . '">Go back?</a></p>';

    } // end if post action

} // end if do_upload
