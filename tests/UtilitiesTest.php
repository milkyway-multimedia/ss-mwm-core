<?php namespace Milkyway\SS\Tests;

/**
 * Milkyway Multimedia
 * UtilitiesTest.php
 *
 * @package 2.x-installer
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\Utilities;
use Cookie;
use CookieJar;
use Injector;
use ArrayData;
use Config;
use Session;
use i18n;

class UtilitiesTest extends \SapphireTest {
    protected $extraDataObjects = [
        'DataObjectTest\NamespacedClass',
        'DataObjectTest\RelationClass',
    ];

    public function testSetCookie() {
        $this->assertEmpty(Cookie::get('testCookie'));
        Utilities::set_cookie('testCookie', 'is yum');
        $this->assertEquals('is yum', Cookie::get('testCookie'));
    }

    public function testForceCookieExpiry() {
        Injector::inst()->registerService(new CookieJar([
            'testCookie' => 'not so yum',
        ]), 'Cookie_Backend');

        $this->assertEquals('not so yum', Cookie::get('testCookie'));

        Utilities::force_cookie_expiry('testCookie');

        $this->assertEmpty(Cookie::get('testCookie'));
    }

    public function testParseRecordFields() {
        $content = 'Hi $Name, I am testing the application named $App';
        $variables = [
            'Name' => 'Tester',
            'App' => 'MWM Utilities',
        ];

        $parsed = (string)Utilities::parse_record_fields($content, $variables);

        // Check if variables are parsed properly in content
        $this->assertEquals('Hi Tester, I am testing the application named MWM Utilities', $parsed);

        $record = ArrayData::create($variables);

        $parsed = (string)Utilities::parse_record_fields($content, [], $record);

        // Check if record is parsed properly in content
        $this->assertEquals('Hi Tester, I am testing the application named MWM Utilities', $parsed);

        $parsed = (string)Utilities::parse_record_fields($content, [
            'Name' => 'Tester 2',
        ], $record);

        // Check if record variables are overridden with array in content
        $this->assertEquals('Hi Tester 2, I am testing the application named MWM Utilities', $parsed);

        $parsed = (string)Utilities::parse_record_fields($content, [
            'Name' => 'Tester 2',
        ], $record, ['App']);

        // Check if ignored variables work
        $this->assertEquals('Hi Tester 2, I am testing the application named ', $parsed);

        $content = '<b>Hi $Name</b>, I am testing the application named $App';

        $parsed = (string)Utilities::parse_record_fields($content, $variables);

        // Check if HTML stays as long as it is allowed
        $this->assertEquals('<b>Hi Tester</b>, I am testing the application named MWM Utilities', $parsed);

        $parsed = (string)Utilities::parse_record_fields($content, $variables, null, [], false);

        // Check if HTML is encoded if not allowed
        $this->assertEquals('&lt;b&gt;Hi Tester&lt;/b&gt;, I am testing the application named MWM Utilities', $parsed);
    }

    public function testSaveFromMap() {
        $child = new \DataObjectTest\RelationClass;
        $child->ParentClass = 'DataObjectTest\NamespacedClass';
        $child->{'Parent[Name]'} = 'Test Object';

        Utilities::save_from_map($child, 'Parent', '', false);

        $this->assertEquals('Test Object', $child->Parent()->Name);

        $child->{'Other[Name]'} = 'Test Object 2';

        Utilities::save_from_map($child, 'Parent', 'Other', false);

        $this->assertEquals('Test Object 2', $child->Parent()->Name);
    }

    public function testGetOpengraphMetatag() {
        $this->assertEquals(
            '<meta property="test" content="value" />',
            Utilities::get_opengraph_metatag('test', 'value')
        );

        $this->assertEquals(
            '<meta property="mwm:test" content="value" />',
            Utilities::get_opengraph_metatag('test', 'value', 'mwm')
        );
    }

    public function testAdminEmail() {
        $oldBaseUrl = Config::inst()->get('Director', 'alternate_base_url');
        Config::inst()->update('Director', 'alternate_base_url', 'http://test.com');

        $oldEmail = Config::inst()->get('Email', 'admin_email');
        Config::inst()->update('Email', 'admin_email', 'test@mwm.com');

        $this->assertEquals('test@mwm.com', Utilities::adminEmail());
        $this->assertEquals('noreply+test@mwm.com', Utilities::adminEmail('noreply'));

        Config::inst()->update('Email', 'admin_email', '');

        $this->assertEquals('no-reply@test.com', Utilities::adminEmail());
        $this->assertEquals('mwm+no-reply@test.com', Utilities::adminEmail('mwm'));

        Config::inst()->update('Director', 'alternate_base_url', $oldBaseUrl);
        Config::inst()->update('Email', 'admin_email', $oldEmail);
    }

    public function testGetVisitorCountry() {
        $details = [
            'IP' => '8.8.8.8',
            'Code' => 'US',
            'Name' => 'United States',
        ];

        $oldServerVars = $_SERVER;
        $_SERVER = [];
        $oldCountry = Session::get('Milkyway.UserInfo.Country');

        // Check defaults
        $country = Utilities::get_visitor_country();

        $this->assertEquals('AU', $country->Code);
        $this->assertEquals('Australia', $country->Name);

        // Check IP for country
        $_SERVER['HTTP_CLIENT_IP'] = $details['IP'];
        Session::set('Milkyway.UserInfo.Country', null);

        $country = Utilities::get_visitor_country(false);

        $this->assertEquals($details['Code'], $country->Code);
        $this->assertEquals($details['Name'], $country->Name);

        $_SERVER = $oldServerVars;
        Session::set('Milkyway.UserInfo.Country', $oldCountry);
    }

    public function testGetFullCountryName() {
        $this->assertEquals('Australia', Utilities::get_full_country_name('AU', 'en_US'));
    }

    public function testMapArrayToI18n() {
        $data = ['MyProperty' => 'Test'];

        $map = Utilities::map_array_to_i18n($data);
        $this->assertEquals('Test', $map['MyProperty']);

        i18n::get_translator('core')->getAdapter()->addTranslation([
            '_MilkywayUtilitiesTest.MyProperty' => 'MyProperty'
        ], 'en_US');

        $map = Utilities::map_array_to_i18n($data, '_MilkywayUtilitiesTest');
        $this->assertEquals('MyProperty', $map['MyProperty']);

        i18n::get_translator('core')->getAdapter()->addTranslation([
            '_MilkywayUtilitiesTest.MyProperty' => 'MyProperty is {description}'
        ], 'en_US');

        $map = Utilities::map_array_to_i18n($data, '_MilkywayUtilitiesTest', [
            'description' => 'awesome'
        ]);
        $this->assertEquals('MyProperty is awesome', $map['MyProperty']);
    }

    public function testCleanCacheKey() {
        $this->assertEquals('Test_', Utilities::clean_cache_key('Test#&^%$'));
        $this->assertEquals('Test_isawesome', Utilities::clean_cache_key('Test#&^%$', ['is' => 'awesome']));
        $this->assertEquals('Test_isawesome_andmore', Utilities::clean_cache_key('Test#&^%$', ['is' => 'awesome', 'and' => 'more']));
    }
}