<?php

  require 'autoloader.php';
  require '../conf/local.php';

  $path = explode('/', $_GET['path']);

  if (stristr($path[0], 'Favicon.ico')) {
    exit;
  }

  if ($path[0] == '') {
    $module = 'Index';
  }
  else {
    $module = ucwords(strtolower($path[0]));
  }

  if (preg_match("/(\d+)/", $path[1], $matches)) {
    $record = $matches[1];
    $action = $path[2];
  }
  else {
    $action = $path[1];
  }

  Alpine::render($module, $record, $action);

?>
