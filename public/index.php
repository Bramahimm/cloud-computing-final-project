<?php
// FILE: public/index.php
// Redirector utama

require_once __DIR__ . '/../config/app.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}