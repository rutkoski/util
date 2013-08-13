<?php

class JSON
{

  public static function output($response)
  {
    header('Content-type: application/json; charset="utf-8"');
    echo json_encode($response);
  }

}