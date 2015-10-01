<?php
require('bluesky_cli.php');
$blueskyGateway = "http://127.0.0.1:8189";
$bluesky_cli = new Bluesky_cli($blueskyGateway, "guest", "guest");
$bluesky_cli->test();
?>
