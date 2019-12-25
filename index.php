<?php

use Tafhyseni\PhpMultiTenancy\Tenancy;


require 'vendor/autoload.php';

$db_config = array(
    'hostname' => '127.0.0.1',
    'username' => 'root',
    'password' => '',
    'database' => 'test'
);
$query = "SELECT * FROM new_table";
    $taf = new Tenancy(
        $query,
        $db_config
    );
    $taf->index();