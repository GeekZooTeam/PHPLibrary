<?php

echo `/usr/bin/svn cleanup 2>&1`;
echo `export LC_CTYPE=en_US.UTF-8 && /usr/bin/svn up /var/www/geek-zoo/btv --username yinmingming --password yinmingming@geek-zoo.com 2>&1`;