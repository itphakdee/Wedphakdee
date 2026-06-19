<?php
$input = file_get_contents("php://https://webhook.site/5a41bf91-46f0-48d4-b991-c0a856356dba");
$data = json_decode($input, true);

file_put_contents("log.txt", print_r($data, true), FILE_APPEND);