<?php
foreach(['REQUEST_URI','SCRIPT_NAME','PHP_SELF','PATH_INFO','REDIRECT_URL','ORIG_PATH_INFO'] as $k) {
    echo "$k: " . ($_SERVER[$k]??'N/A') . "\n";
}
