<?php
file_put_contents('/tmp/laravel_debug.log', date('Y-m-d H:i:s') . ' REQUEST_URI=' . (['REQUEST_URI']??'N/A') . ' SCRIPT_NAME=' . (['SCRIPT_NAME']??'N/A') . ' PHP_SELF=' . (['PHP_SELF']??'N/A') . ' REDIRECT_URL=' . (['REDIRECT_URL']??'N/A') .  \n, FILE_APPEND);
echo OK;
