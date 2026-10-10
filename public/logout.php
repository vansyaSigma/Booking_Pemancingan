<?php
session_start();
$_SESSION = [];
session_destroy();
require_once __DIR__ . '/../config/helpers.php';
redirect('/Booking_Pemancingan/public/index.php');
