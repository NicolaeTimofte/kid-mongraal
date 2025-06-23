<?php
function isAdmin() {
    return isset($_SESSION['username']) && strtolower($_SESSION['username']) === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php?error=access_denied');
        exit();
    }
}

function hasAdminAccess() {
    return isAdmin();
}
?>