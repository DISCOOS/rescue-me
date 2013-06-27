RescueMe
========

Light-weight and minimalistic system for push-based location requests.

Build from source
=================

1. [Download latest source](/DISCOOS/rescue-me/archive/master.zip) and extract it, or

    ```bash
    git clone https://github.com/DISCOOS/rescue-me.git
    ```

2. Goto RescueMe root folder and

    *Linux*


    ```php

    ./build.sh package --version=<string>

    ```

    *Any OS*

    ```php

    php build.php package version=<string>

    ```

    which outputs `dist/rescueme-<string>.phar` 


3. Install RescueMe 

   (assumes Apache is already installed and configured)

    ```php

    php dist/rescueme-<version>.phar install --install-dir=/path/web/document/root

    ```
    Follow the instructions.

Dependencies
============

* [Minify 2.1.5](https://minify.googlecode.com/files/minify-2.1.5.zip)
