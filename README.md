# CodeSort Enhanced (v 2.2) for PHP 7

Original author is [Jenny](http://prism-perfect.net) / Original readme is [here](codesort2/readme.txt).

#### I would highly recommend not to use this script for new installations. Although some modifications were made, this script is still pretty old, not very secure, and does not have any tests, that's why please only update it if you have already installed it before.

This version requires at least PHP 7.2.

## Upgrading instructions

I'm not providing support for those who have version lower than CodeSort 2.0.

If you are using CodeSort 2.0 or CodeSort 2.1 (old version by Jenny) or 2.2.* (my version):

1. **Back up all your current CodeSort2 configurations, files, and databases first.**
2. Take note of your database information in all your `codesort2/codes-config.php` files.
3. Download [an archive of the codesort2 folder in this repository](https://gitlab.com/tfl-php-scripts/codesort/-/archive/master/codesort-master.zip?path=codesort2). Extract the archive.
4. Replace your current `codesort2/` files with the `codesort2/` files from this repository. Make sure that the only difference between your codes-config.php files and the ones from [samples folder](https://gitlab.com/tfl-php-scripts/codesort/-/archive/master/codesort-master.zip?path=samples) are credentials.

Please follow the instructions carefully. A lot of issues were related to facts that users had incorrect config files.

That's it! Should you encounter any problems, please create an issue here, and I will try and solve it as soon as possible.
