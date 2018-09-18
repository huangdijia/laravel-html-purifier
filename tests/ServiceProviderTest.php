<?php

namespace Huangdijia\Tests\Purifier;

use GrahamCampbell\TestBenchCore\ServiceProviderTrait;
use Huangdijia\Purifier\Purifier;

class ServiceProviderTest extends AbstractTestCase
{
    use ServiceProviderTrait;

    public function testPurifierIsInjectable()
    {
        $this->assertIsInjectable(Purifier::class);
    }
}