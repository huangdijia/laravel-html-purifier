<?php

namespace Huangdijia\Tests\Purifier;

use HTMLPurifier;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Huangdijia\Purifier\Purifier;

class PurifierTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        // Clean Purifier Definitions cache
        array_map('unlink', glob($this->getBasePath() . '/storage/app/purifier/HTML/*'));
        array_map('unlink', glob($this->getBasePath() . '/storage/app/purifier/CSS/*'));
        array_map('unlink', glob($this->getBasePath() . '/storage/app/purifier/URI/*'));
    }

    public function testConstruct()
    {
        $purifier = $this->app->make('purifier');
        $this->assertInstanceOf(Purifier::class, $purifier);
        $this->assertAttributeInstanceOf(HTMLPurifier::class, 'purifier', $purifier);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Configuration parameters not loaded!
     */
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Configuration parameters not loaded!
     */
    public function testExpectionIsThrownWhenConfigIsBad()
    {
        new Purifier(new Filesystem(), new Repository());
    }

    public function testGetConfigMethod()
    {
        $purifier = $this->app->make('purifier');
        /** @var \HTMLPurifier_Config $config */
        $config = $this->invokeMethod($purifier, 'getConfig', [null]);

        $this->assertEquals('utf-8', $config->get('Core.Encoding'));
        $this->assertEquals(storage_path('app/purifier'), $config->get('Cache.SerializerPath'));
        $this->assertEquals(493, $config->get('Cache.SerializerPermissions'));
        $this->assertEquals('HTML 4.01 Transitional', $config->get('HTML.Doctype'));
        $this->assertEquals('div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]', $config->get('HTML.Allowed'));
        $this->assertEquals(explode(',', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align'), array_keys($config->get('CSS.AllowedProperties')));
        $this->assertEquals(true, $config->get('AutoFormat.AutoParagraph'));
        $this->assertEquals(true, $config->get('AutoFormat.RemoveEmpty'));

        $config = $this->invokeMethod($purifier, 'getConfig', ['Core.Encoding']);

        $this->assertEquals('utf-8', $config->get('Core.Encoding'));
        $this->assertEquals(storage_path('app/purifier'), $config->get('Cache.SerializerPath'));
        $this->assertEquals(493, $config->get('Cache.SerializerPermissions'));
    }

    public function testGetInstanceMethod()
    {
        $purifier = $this->app->make('purifier');
        $this->assertInstanceOf(HTMLPurifier::class, $purifier->getInstance());
    }

    public function testCleaning()
    {
        $purifier = $this->app->make('purifier');
        $html = '<b>Simple and short';
        $pureHtml = $purifier->clean($html);
        $this->assertSame('<p><b>Simple and short</b></p>', $pureHtml);

        $html = [
            '<script>alert(\'XSS\');</script>',
            '<b>Simple and short',
        ];
        $pureHtml = $purifier->clean($html);
        $this->assertSame(['', '<p><b>Simple and short</b></p>'], $pureHtml);
    }

    public function testCleaningWithCustomConfig()
    {
        $purifier = $this->app->make('purifier');

        $html = '<span id="some-id">This is my H1 title';
        $pureHtml = $purifier->clean($html, ['Attr.EnableID' => true]);
        $this->assertSame('<span id="some-id">This is my H1 title</span>', $pureHtml);

        $html = '<span id="some-id">This is my H1 title';
        $pureHtml = $purifier->clean($html, 'test');
        $this->assertSame('<span id="some-id">This is my H1 title</span>', $pureHtml);
    }

    public function testCustomDefinitions()
    {
        /** @var HTMLPurifier $purifier */
        $purifier = $this->app->make('purifier');

        $html = '<u>custom element';
        $pureHtml = $purifier->clean($html);

        $this->assertSame('<p><u>custom element</u></p>', $pureHtml);

        // Test custom element from definition
        $html = '<section>HTML5 section tag';
        $pureHtml = $purifier->clean($html, ['HTML.Allowed' => 'section']);

        $this->assertSame('<section>HTML5 section tag</section>', $pureHtml);

        $html = '<section class="test-class">HTML5 section tag';
        $pureHtml = $purifier->clean($html, ['HTML.Allowed' => 'section[class]']);

        $this->assertSame('<section class="test-class">HTML5 section tag</section>', $pureHtml);

        // Test that we can use allowed target attribute values
        $html = '<a href="#" target="_blank">test link</a>';
        $pureHtml = $purifier->clean($html, ['HTML.Allowed' => 'a[href|target]']);

        $this->assertSame('<a href="#" target="_blank" rel="noreferrer noopener">test link</a>', $pureHtml);

        // And can not use any other
        $html = '<a href="#" target="_forbidden">test link</a>';
        $pureHtml = $purifier->clean($html, ['HTML.Allowed' => 'a[href|target]']);

        $this->assertSame('<a href="#">test link</a>', $pureHtml);
    }
}