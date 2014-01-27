<?php

class JSON
{

  public static function output($response, $compat = true)
  {
    if ($compat) {
      header('Content-type: text/html; charset="utf-8"');
    } else {
      header('Content-type: application/json; charset="utf-8"');
    }
    echo json_encode($response);
  }

}