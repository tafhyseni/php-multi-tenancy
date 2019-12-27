<?php

/**
 * Configuration of PHP Multi Tenancy
 */

return [
    /*
    |--------------------------------------------------------------------------
    | CREATE TABLE LIKE
    |--------------------------------------------------------------------------
    |
    | Creates a mirror table with structure and indexes based on Main Database
    |
     */
    'CREATE' => 'CREATE TABLE {table} LIKE {database_name}.{table}',

    /*
    |--------------------------------------------------------------------------
    | INSERT INTO
    |--------------------------------------------------------------------------
    |
    | Insert all data from Main Database table to Tenancy Table
    |
     */

    'INSERT' => 'INSERT INTO {tenancy_name}.{table} SELECT * FROM {database_name}.{table}'
];
