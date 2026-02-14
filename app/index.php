<?php
// /opt/lampp/htdocs/Techoazis/views/index.php (példa)
require_once __DIR__ . '/../core/config.php';
http_response_code(404);
include ROOT_PATH . '/views/404.php';
exit();