<?php

use Tafhyseni\PhpMultiTenancy\Tenancy;


require 'vendor/autoload.php';

$db_config = array(
    'hostname' => '127.0.0.1',
    'username' => 'root',
    'password' => '',
    'database' => 'test',
    'tenancy_hostname' => '127.0.0.1',
    'tenancy_username' => 'root',
    'tenancy_password' => ''
);
$query = "SELECT * FROM new_table";
    $taf = new Tenancy(
        $db_config
    );
    $taf->index();