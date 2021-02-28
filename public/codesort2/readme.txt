/*
 * CodeSort version 2.0
 * Author: Jenny Ferenc
 * Copyright: 2007 Jenny Ferenc
 * Date: 2005-04-01
 * Updated: 2007-02-13
 * Requirements: PHP 4, MySQL 4
 * Link: http://prism-perfect.net/codesort
 * 
 * This program is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation; either version 2 
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
 * GNU General Public License for more details: 
 * http://www.gnu.org/licenses/gpl.html
 * 
 */

It would be nice if you kept the link to http://prism-perfect.net/codesort 
or at least added it to a site credits page, but that is not required. 
Merely keep any credit notices intact in the code. Note that this 
is a change from previous versions.

CodeSort may work with versions of MySQL earlier than 4, but I have 
no way to test that. Let me know if you try it and it works. 
You may also be able to use it with a different database, if you 
change all of the mysql_ functions in SqlConnection.php to those 
for other PHP database functions, but I have also not tested this.

// ____________________________________________________________

Follow the instructions below to install CodeSort version 2.0. 
If you are upgrading from version 1.5 or 1.6, you will need to 
enter your current config settings into the NEW codes-config.php 
file; there are some important changes to that and the old one 
won't work. (There is currently no upgrade script for version 1.0, 
because no one seems to be using it anymore. If you need it, 
please contact jenny@prism-perfect.net )

// ____________________________________________________________

INSTALLATION INSTRUCTIONS / VERSION 1.6 OR 1.5 UPGRADE INSTRUCTIONS:

1. Edit the config file:
Open the file codes-config.php in a plain text editor. Edit the variables 
for your site. You MUST change the DATABASE VARIABLES for your MySQL database 
and the ADMIN VARIABLES for your username and password. The TABLE VARIABLES 
only need to be changed if you want to use CodeSort in conjunction with one 
of the supported collective management scripts (Enthusiast 3, Flinx Collective 
or Fan Admin). If so, you must install CodeSort in THE SAME database as 
said management script.

2. Upload CodeSort files:
Upload all of the CodeSort files to an http-accessible directory on your webserver. 
(Overwrite your current files if you are upgrading.)

3. Create images folder: (if upgrading, you've already got this. skip to step 4)
You will need to create a folder for your images/codes -- the default is as a 
direct sub-directory of your CodeSort directory (ie CodeSort/images). The install 
script will determine the server path and URL setting for this location, so you 
should use that if you have no reason not to.

4. Run install script:
Run the install.php script by going to http://yoursite.com/CODESORT_FOLDER/install.php 
You'll need to set some more options for your site here, including the location 
of your images folder. Then submit the form to create or update the CodeSort 
database tables.

5. Once you have successfully run the installation script, be sure to DELETE it 
from you webserver (you won't need it anymore). Then you can begin managing codes 
from the admin panel. If you are using the script stand-alone you'll first need 
to add the names of your fanlistings in the 'Fanlistings' tab (you won't have 
this option if you're accessing your list of fanlistings from another 
collective management script).

6. Try uploading a code/image. If you get errors about permission denied to move 
uploaded file, see the note below about folder permissions:

Important: a security note on CHMOD 777
Folder permissions of 777 may be needed to allow PHP to upload files 
(and there is really no simple alternative), but it is also a very big security risk 
-- it can allow malicious people to upload any files they like into that folder 
because it is world writable. If your server requires 777 for your own uploads to work 
(e.g., you get permisision denied errors when trying to upload a file with CodeSort), 
the safest thing you can do is to NOT use CodeSort to upload your images 
(set do_upload = n in the install options and use your preferred FTP client) 
and just give CodeSort the file name. You could also manually change the permissions 
to 777 (via your FTP client), use the CodSort admin panel with do_upload = y, 
and then change permissions back to 755 when you are done.

// ____________________________________________________________

DISPLAYING CODES:

To display your codes for a single fanlisting or for your entire collective, 
visit the admin panel home page and click the buttons for 'get snippet.' 
This will generate a code snippet that you can copy-and-paste into any .php page 
to display your codes (and donation upload form, if you want to use it).

Please note: This snippet is not compatible with NL-ConvertToPHP/dynamic includes pages! 
This means you can't put the inclusion snippet within any PHP coding similar to 
<?php if ($_SERVER['QUERY_STRING'] == 'codes') { ?>
If you're not sure, the best bet is to put the snippet for CodeSort in its own .php page, 
along with whatever other content you want on your codes page. You CAN put the codes 
and donation form snippets on the same page, if you like.

I think that's about it -- please contact me if you find any bugs 
or if these instructions are unclear. Plus suggestions and ideas are always welcome!

Jenny Ferenc
jenny@prism-perfect.net
http://prism-perfect.net/