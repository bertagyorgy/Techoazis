<?php
require_once __DIR__ . '/../config.php';
// Az auth_check azonnal kidob, ha nem vagy admin, így nem látják a fájllistát sem
require_once ROOT_PATH . '/app/auth_check.php'; 

// Ha admin vagy, mehetünk a főpanelre
header("Location: " . BASE_URL . "/admin/admin");
exit();