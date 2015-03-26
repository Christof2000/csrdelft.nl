<?php

# -------------------------------------------------------------------
# defines.include.php
# -------------------------------------------------------------------
# allerlei paden enzo goed zetten.
# -------------------------------------------------------------------
#
# database automatisch controleren
define('DB_CHECK', true); # zie PersistentEntity::checkTable()
#
# database automatisch bijwerken
define('DB_MODIFY', false); # heb je een backup gemaakt?
#
# database automatisch droppen
define('DB_DROP', false); # heb je een backup gemaakt?
#
# debug modus
define('DEBUG', false);
#
# measure time
define('TIME_MEASURE', false);
#
# redirect to https
define('FORCE_HTTPS', false);

# wordt gebruikt om pagina's alleen op Confide te laten zien
#define('CONFIDE_IP', '80.112.180.173');

# wordt gebruikt voor secure cookies
define('CSR_DOMAIN', 'alpha.csrdelft.nl:8080');

# urls ZONDER trailing slash
define('CSR_ROOT', 'http://' . CSR_DOMAIN);

# paden MET trailing slash
define('BASE_PATH', realpath(dirname(__FILE__)) . "/../");
define('ETC_PATH', BASE_PATH . 'etc/');
define('DATA_PATH', BASE_PATH . 'data/');
define('SESSION_PATH', BASE_PATH . 'sessie/');
define('TMP_PATH', BASE_PATH . 'tmp/');
define('LIB_PATH', BASE_PATH . 'lib/');
define('HTDOCS_PATH', BASE_PATH . 'htdocs/');
define('PICS_PATH', HTDOCS_PATH . 'plaetjes/');
define('ICON_PATH', PICS_PATH . 'famfamfam/');
define('PUBLIC_FTP', '/srv/ftp/incoming/csrdelft/');

# smarty template engine
define('SMARTY_DIR', LIB_PATH . 'smarty/libs/');
define('SMARTY_TEMPLATE_DIR', LIB_PATH . 'templates/');
define('SMARTY_COMPILE_DIR', DATA_PATH . 'smarty/compiled/');
define('SMARTY_CACHE_DIR', DATA_PATH . 'smarty/cache/');

# ImageMagick
define('IMAGEMAGICK_PATH', '/usr/bin/');
