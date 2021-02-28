<?php
/*****************************************************************************
 * CodeSort
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

// ------------->ADMIN VARIABLES

$codesort['admin_username']	= 'admin';
$codesort['admin_password']	= md5('pass');

// ------------->DATABASE VARIABLES

$codesort['dbhost']		= 'localhost';
$codesort['dbuser']		= '';
$codesort['dbpass']		= '';
$codesort['dbname']		= '';

// ------------->TABLE VARIABLES

// Which script do you use for your fanlistings collective?
// Currently, the supported options are:

// Enthusiast			'e'
// Flinx Collective		'f'
// Fan Admin			'g'

// Or if you don't use one, enter 'n'

$codesort['collective_script']	= 'e';

// The name of your collective table. Here's the usual defaults:

// Enthusiast			'owned'
// Flinx Collective		'flinxcol_link'
// Fan Admin			'fa_fls'

// Or else enter your custom table name.

$codesort['collective_table']	= 'owned';

// Other tables for the script.

$codesort['codes_table']	= 'codes';
$codesort['cat_table']		= 'codes_cat';
$codesort['donors_table']	= 'codes_donors';
$codesort['sizes_table']	= 'codes_sizes';
$codesort['options_table']	= 'codes_options';

// ------------->COLLECTIVE TABLE COLUMN NAMES (optional)

// If you are using some other collective script, you can use it with CodeSort.
// IF YOU DON'T GET THESE INSTRUCTIONS, STOP!
// Just make an entry for it here, like this:
// the first index, 'x', is what you've put for $codesort['collective_script']
// the second parts are the primary ID and FL name columns of your collective table
// $coltable['x']['id'] = 'listingid';
// $coltable['x']['subject'] = 'subject';

$coltable['e']['id']		= 'listingid';
$coltable['e']['subject']	= 'subject';

$coltable['f']['id']		= 'linkID';
$coltable['f']['subject']	= 'subject';

$coltable['g']['id']		= 'fl_id';
$coltable['g']['subject']	= 'flsubject';

$coltable['n']['id']		= 'fl_id';
$coltable['n']['subject']	= 'fl_subject';
