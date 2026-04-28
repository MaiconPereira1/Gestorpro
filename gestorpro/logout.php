<?php
/**
 * GestorPro - Logout
 */
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/controllers/AuthController.php';

$ctrl = new AuthController();
$ctrl->logout();
