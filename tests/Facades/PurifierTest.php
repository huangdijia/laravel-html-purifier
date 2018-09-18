<?php

namespace Huangdijia\Tests\Purifier\Facades;

use GrahamCampbell\TestBenchCore\FacadeTrait;
use Huangdijia\Purifier\Facades\Purifier;
use Huangdijia\Tests\Purifier\AbstractTestCase;

class PurifierTest extends AbstractTestCase
{
    use FacadeTrait;

    /**
     * Get the facade accessor.
     *
     * @return string
     */
    protected function getFacadeAccessor()
    {
        return 'purifier';
    }
    /**
     * Get the facade class.
     *
     * @return string
     */
    protected function getFacadeClass()
    {
        return Purifier::class;
    }
    /**
     * Get the facade root.
     *
     * @return string
     */
    protected function getFacadeRoot()
    {
        return \Huangdijia\Purifier\Purifier::class;
    }
}