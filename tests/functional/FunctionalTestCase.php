<?php

namespace JWebb\Unleash\Tests\Functional;

use PHPUnit\Framework\TestCase;

class FunctionalTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Mock the config() function in the current namespace
        $this->mockConfig();
    }

    protected function mockConfig()
    {
        // Define the config function only in the current namespace for testing
        if (!function_exists('\\config')) {
            // You can define global functions from any namespace using `namespace` construct
            eval('namespace {
                function config($key = null, $default = null) {
                    $mockedConfig = [
                        "unleash.enabled" => true,
                    ];

                    return $mockedConfig[$key] ?? $default;
                }
            }');
        }
    }
}