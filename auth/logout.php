<?php
include_once __DIR__ . "/../includes/session.php";

session_unset();
session_destroy();

header("Location: ../public/login.php");
exit;
