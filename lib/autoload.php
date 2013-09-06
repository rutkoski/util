<?php

if (! class_exists('SplAutoload')) {
  require_once('SplAutoload.php');
}

sy_autoload_register(array('SplAutoload', 'autoload'));

SplAutoload::registerPath(dirname(__file__));
