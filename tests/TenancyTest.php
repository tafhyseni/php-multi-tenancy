<?php

namespace Tafhyseni\PhpMultiTenancy\Tests;

use PHPUnit\Framework\TestCase;
use Tafhyseni\PhpMultiTenancy\Tenancy;

class TenancyTest extends TestCase
{

    public $tenancy;

    /** @test */
    public function setUp(): void
    {
        $this->tenancy = new Tenancy(
            array(
                'hostname' => '127.0.0.1',
                'username' => 'root',
                'password' => '',
                'database' => 'test',
                'tenancy_hostname' => '127.0.0.1',
                'tenancy_username' => 'root',
                'tenancy_password' => ''
            ),
            true  
        );
        self::assertTrue(true);
    }

    /** @test */
    public function tearDown(): void
    {
        $this->tenancy = NULL;
    }

    /** @test */
    public function auto_generate_name()
    {
        $tenancy_name = $this->tenancy->auto_name();
        $this->assertIsString('string', $tenancy_name);
    }

}