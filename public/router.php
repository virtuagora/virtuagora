<?php
if (file_exists(__DIR__ . '/' . strtok($_SERVER["REQUEST_URI"],'?'))) {
    return false;
} else {
    include_once 'index.php';
}
