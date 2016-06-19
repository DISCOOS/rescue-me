RescueMe
========

Light-weight and minimalistic system for push-based location requests.

Dependencies
------------

* PHP >= 5.3.3
* MySQL >= 5.5.0
* Apache >= 2.2.0
* [Minify >= 2.1.7](https://minify.googlecode.com/files/minify-2.1.7.zip)

Download RescueMe
-----------------

Run this in your terminal to get the latest RescueMe version:

```bash
curl -sS http://rescueme.io/installer | php
```

or if you don't have curl (windows):

```bash
php -r "eval('?>'.file_get_contents('http://rescueme.io/installer'));"
```    
This script will check some php.ini settings, warn you if they are set incorrectly, 
and then download the latest `rescueme.phar` in current directory. 

Install with command-line installer
-----------------------------------

Run this in your terminal to install RescueMe:

(assumes Apache, MySQL and PHP is installed and configured correctly)

```bash
php rescueme.phar install --install-dir=/path/to/rescueme
```

Follow the instructions.

Install from source
-------------------
Developers should [use Vagrant Box for RescueMe](https://github.com/DISCOOS/rescue-me/wiki/Use%20vagrant%20box%20for%20RescueMe) as runtime environment. It will save developers time and remove the need for installing the full stack of runtime dependencies on the developer machine, with all that entails. If you still you want to install everything yourself, you should read [Create Vagrant Box for RescueMe](https://github.com/DISCOOS/rescue-me/wiki/Create-vagrant-box-for-RescueMe) which lists all runtime dependencies.

1. **<a href="https://github.com/DISCOOS/rescue-me/archive/master.zip">Download latest source</a>** and extract it to `/path/to/rescume/`, **or**
    ```bash
    git clone https://github.com/DISCOOS/rescue-me.git
    ```

2. **Log into RescueMe runtime environment**

    ```bash
    vagrant up
    vagrant ssh
    ```
3. **Goto src of RescueMe repo**

    ```bash
    cd /rescueme/src
    ```
    
4. **Download latest Composer version into /path/to/rescueme/src/**

    ```bash
    curl -sS https://getcomposer.org/installer | php
    ```
    
    or if you don't have curl (you should really use the runtime then, which have curl installed by default):
    
    ```bash
    php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
    ```    

5. **Install dependencies and configure RescueMe**

    ```php
    php composer.phar install
    ```
    
    Follow the instructions.

Developers
----------

Remember to set correct newline behavior before commiting changes to this repo 
(see [Git help](https://help.github.com/articles/dealing-with-line-endings)). The repo 
is configured to store all files with LF line endings (see .gitattributes), and correct 
behavior is best ensured by setting the correct `--global core.autocrlf` value for your OS. 

**Windows**
```bash
git config --global core.autocrlf true
```
which tells Git to auto-convert CRLF line endings into LF when you commit, and vice 
versa when it checks out code onto your filesystem.

**Mac or Linux**
```bash
git config --global core.autocrlf input
```
which tells Git to convert CRLF to LF on commit but not the other way around.

**Configuration changes**

If `config.tpl.php` or `config.minify.tpl.php` was changed by a commit, remember to perform
```bash
php rescueme.php configure
```
locally after commit is pulled. This will update local configuration files (ignored by git).

**Database changes**

If you change the database structure, remember to perform
```bash
php rescueme.php export
```
and commit + push changes made to `src/rescueme.sql`. Developers can update the local 
database using
```bash
php rescueme.php import
```
which will import `src/rescueme.sql` analyzing it for changes, adding any new tables or columns. 


Troubleshooting
---------------

1. Windows user and command line is fighting you? [Read this](http://php.net/manual/en/install.windows.commandline.php).


