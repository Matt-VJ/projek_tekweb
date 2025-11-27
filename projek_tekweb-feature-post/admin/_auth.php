<?php
session_start();
// Simple admin protection for Tophers admin pages.
// Change this password if you want. This is intentionally minimal.
define('TOPHER_ADMIN_PASSWORD', 'topher123');

function require_admin(){
    if (!isset($_SESSION['topher_admin']) || $_SESSION['topher_admin'] !== true){
        header('Location: login.php');
        exit;
    }
}

function admin_logout(){
    unset($_SESSION['topher_admin']);
    session_destroy();
}

?>
