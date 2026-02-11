<?php
// ajax_search.php

// FIX: Session Start agar akses ke data user valid
session_start();

error_reporting(0);
ini_set('display_errors', 0);

// Khusus search outputnya HTML (<tr>), bukan JSON
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/actions/api/search.php';
?>