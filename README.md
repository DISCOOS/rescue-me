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
```bash
php rescueme.phar install --install-dir=/path/to/www/rescueme
```

Follow the instructions.

Build from source
-----------------

1. **<a href="https://github.com/DISCOOS/rescue-me/archive/master.zip">Download latest source</a> and extract it, or**

    ```bash
    git clone https://github.com/DISCOOS/rescue-me.git
    ```

2. **Goto RescueMe root folder and**

    *Linux*

    ```php
    ./compile package -v <string>
    ```

    *Any OS*

    ```php
    php compile.php package -v <string>
    ```
    which outputs `dist/rescueme-<string>.phar` 


3. **Install RescueMe**

   (assumes Apache is already installed and configured)

    ```php
    php dist/rescueme-<version>.phar install --install-dir=/path/to/www/rescueme
    ```
    Follow the instructions.
    
Troubleshooting
---------------

1. Windows user and command line is fighting you? [Read this](http://php.net/manual/en/install.windows.commandline.php).

Dependencies
------------

* PHP >= 5.3
* [Minify 2.1.5](https://minify.googlecode.com/files/minify-2.1.5.zip)
