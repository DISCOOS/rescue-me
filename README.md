RescueMe
========

Light-weight and minimalistic system for push-based location requests.

Download RescueMe
-----------------

Run this in your terminal to get the latest RescueMe version:

```bash
curl -sS http://rescueme.discoos.org/installer | php
```

or if you don't have curl (windows):

```bash
php -r "eval('?>'.file_get_contents('http://rescueme.discoos.org/installer'));"
```    
This script will check some php.ini settings, warn you if they are set incorrectly, 
and then download the latest rescueme.phar in the current directory. 

Install RescueMe
----------------

Run this in your terminal to install RescueMe:

(assumes Apache is already installed and configured)

```bash
php rescueme.phar install --install-dir=/path/to/rescueme
```

Follow the instructions.

Build from source
-----------------

2. **<a href="https://github.com/DISCOOS/rescue-me/archive/master.zip">Download latest source</a> and extract it to /path/to/rescume/src, or**

    ```bash
    git clone https://github.com/DISCOOS/rescue-me.git /path/to/rescume/src
    ```

3. **Download latest Composer version into /path/to/rescume/src**

    ```bash
    curl -sS https://getcomposer.org/installer | php
    ```
    
    or if you don't have curl (windows):
    
    ```bash
    php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
    ```    

3. **Install dependencies and configure RescueMe**

    ```php
    php composer.phar install
    ```
    
    Follow the instructions.

Developers
----------

Remember to set correct newline behavior before commiting changes to this repo 
(see [Git help](https://help.github.com/articles/dealing-with-line-endings)). The repos 
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

**Database changes**

If you change the database structure, remember to perform
```bash
php compile.php prepare
```
and commit + push changes made to `src/rescueme.sql`. Developers can update the local 
database using
```bash
php compile.php update
```
which will import `src/rescueme.sql` analyzing it for changes, adding any new tables or columns. 

Troubleshooting
---------------

1. Windows user and command line is fighting you? [Read this](http://php.net/manual/en/install.windows.commandline.php).

Dependencies
------------

* PHP >= 5.3
* [Minify 2.1.7](https://minify.googlecode.com/files/minify-2.1.7.zip)
