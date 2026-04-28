<?php
// cfg/index.php - Main entry point
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/constants.php';

// Check session timeout
checkSessionTimeout();

// If logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/modules/dashboard/');
} else {
    redirect('/modules/auth/login.php');
}