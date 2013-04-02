<?php
/*
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('configs/config.php');
require_once('base/App.php');

App::get()->application_start();