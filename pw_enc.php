<?php
$pw = $_GET["pw"];
echo password_hash($pw, PASSWORD_DEFAULT);
