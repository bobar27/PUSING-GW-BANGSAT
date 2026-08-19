<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_name() {
    return $_SESSION['user_name'] ?? 'User';
}
