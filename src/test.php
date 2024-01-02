<?php 
$dt_Json = '[
{
    "pos": 1,
    "Bezeichung": "testaab",
    "Menge": 1,
    "Einheit": "sdt",
    "Steuer": 19,
    "Preis": 400,
    "Netto": 76,
    "Gesamt": 400
  },
{
    "pos": 3,
    "Bezeichung": "test3",
    "Menge": 3,
    "Einheit": "sdt",
    "Steuer": 19,
    "Preis": 500,
    "Netto": 285,
    "Gesamt": 1500
  }
]';
var_dump(json_decode($dt_Json, true));
?>