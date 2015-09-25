<?php
require 'KooKoo-PHP/kookoophp/response.php';//include response.php into your code

$r = new response();
$r->setFiller(true);
$r->addPlayText("I Love Koo Koo"); // Play any text to play
$r->addHangup();
$r->send();

?>