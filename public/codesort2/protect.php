<?php
/*****************************************************************************
 * SIMPLE LOGIN - Access Protection Script
 *
 * Copyright (c) julie@deadly-nightshade.net http://scripts.deadly-nightshade.net/
 * Copyright (c) ? by Jenny Ferenc (contributor) <jenny@prism-perfect.net>
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

session_start();
header('Cache-control: private');

/*
Modified from:
SIMPLE LOGIN - Access Protection Script
by julie@deadly-nightshade.net
http://scripts.deadly-nightshade.net/

If you find this script helpful, a link to my site is appreciated but not required.
*/

$showform = true;

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    $_SESSION = array();
    session_destroy();
}

if (isset($_POST['action']) && $_POST['action'] == 'login') {

    if (($_POST['username'] != $cs->GetOpt('admin_username')) || (md5($_POST['password']) != $cs->GetOpt('admin_password'))) {
        $showform = true;
        $cs->AddErr('Invalid login! Please try again.');
    } else {
        $showform = false;
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['password'] = md5($_POST['password']);
    }

} elseif (isset($_SESSION['username'])) {

    if ($_SESSION['username'] != $cs->GetOpt('admin_username') || $_SESSION['password'] != $cs->GetOpt('admin_password')) {
        $showform = true;
    } else {
        $showform = false;
    }
}

if ($showform) {

    $cs->GetHeader('Login');

    ?>

    <form action="index.php" method="post" id="login">

        <fieldset>
            <legend>Please Login</legend>

            <?php $cs->ReportErrors(); ?>

            <p><label for="username">Username</label>
                <input type="text" id="username" name="username"/></p>

            <p><label for="password">Password</label>
                <input type="password" id="password" name="password"/></p>

            <p id="loginButton"><input type="submit" name="action" value="login"/></p>

        </fieldset>

    </form>

    <?php

    $cs->GetFooter(false);

    exit;
}
