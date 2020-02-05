<?php

namespace OlcsTest\Service\Qa;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\Preferences;
use RuntimeException;

class PreferencesTest extends MockeryTestCase
{
    public function testReturnDefaultPreferencesOnEmptyInput()
    {
        $sut = new Preferences([]);

        $expected = [
            Preferences::KEY_ANALYTICS => true,
            Preferences::KEY_SETTINGS => true
        ];

        $this->assertEquals(
            $expected,
            $sut->asArray()
        );
    }

    /**
     * @dataProvider dpExceptionOnInvalidOrMissingKey
     */
    public function testExceptionOnInvalidOrMissingKey($exceptionMessage, $preferencesArray)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Preferences($preferencesArray);
    }

    public function dpExceptionOnInvalidOrMissingKey()
    {
        return [
            [
                'Preference analytics is not present',
                [
                    'settings' => true,
                    'key87' => 'foo'
                ]
            ],
            [
                'Preference settings is not present',
                [
                    'analytics' => true,
                    'key99' => 'bar'
                ]
            ],
            [
                'Preference analytics is non-bool value',
                [
                    'analytics' => 'tree',
                    'settings' => true,
                    'key87' => 'foo'
                ]
            ],
            [
                'Preference settings is non-bool value',
                [
                    'analytics' => true,
                    'settings' => 'cat',
                    'key99' => 'bar'
                ]
            ],
        ];
    }

    /**
     * @dataProvider dpValidInput
     */
    public function testValidInput($preferencesArray)
    {
        $sut = new Preferences($preferencesArray);

        $this->assertEquals(
            $preferencesArray,
            $sut->asArray()
        );
    }

    public function dpValidInput()
    {
        return [
            [
                [
                    'analytics' => true,
                    'settings' => true,
                ]
            ],
            [
                [
                    'analytics' => true,
                    'settings' => false,
                ]
            ],
            [
                [
                    'analytics' => false,
                    'settings' => true,
                ]
            ],
            [
                [
                    'analytics' => false,
                    'settings' => false,
                ]
            ],
        ];
    }
}
