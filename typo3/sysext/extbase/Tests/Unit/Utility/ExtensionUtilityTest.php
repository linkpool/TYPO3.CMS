<?php
namespace TYPO3\CMS\Extbase\Tests\Unit\Utility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for class \TYPO3\CMS\Extbase\Utility\ExtensionUtility
 */
class ExtensionUtilityTest extends UnitTestCase
{
    protected function setUp()
    {
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->tmpl = new \stdClass();
        $GLOBALS['TSFE']->tmpl->setup = [];
        $GLOBALS['TSFE']->tmpl->setup['tt_content.']['list.']['20.'] = [
            '9' => 'CASE',
            '9.' => [
                'key.' => [
                    'field' => 'layout'
                ],
                0 => '< plugin.tt_news'
            ],
            'extensionname_someplugin' => 'USER',
            'extensionname_someplugin.' => [
                'userFunc' => Bootstrap::class . '->run',
                'extensionName' => 'ExtensionName',
                'pluginName' => 'SomePlugin'
            ],
            'someotherextensionname_secondplugin' => 'USER',
            'someotherextensionname_secondplugin.' => [
                'userFunc' => Bootstrap::class . '->run',
                'extensionName' => 'SomeOtherExtensionName',
                'pluginName' => 'SecondPlugin'
            ],
            'extensionname_thirdplugin' => 'USER',
            'extensionname_thirdplugin.' => [
                'userFunc' => Bootstrap::class . '->run',
                'extensionName' => 'ExtensionName',
                'pluginName' => 'ThirdPlugin'
            ]
        ];
    }

    /**
     * @test
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin
     */
    public function configurePluginWorksForMinimalisticSetup()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.'] = [];
        ExtensionUtility::configurePlugin('MyExtension', 'Pi1', ['Blog' => 'index']);
        $staticTypoScript = $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.']['defaultContentRendering'];
        $this->assertContains('tt_content.list.20.myextension_pi1 = USER', $staticTypoScript);
        $this->assertContains('
	userFunc = TYPO3\\CMS\\Extbase\\Core\\Bootstrap->run
	extensionName = MyExtension
	pluginName = Pi1', $staticTypoScript);
        $this->assertNotContains('USER_INT', $staticTypoScript);
    }

    /**
     * @test
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin
     */
    public function configurePluginCreatesCorrectDefaultTypoScriptSetup()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.'] = [];
        ExtensionUtility::configurePlugin('MyExtension', 'Pi1', ['Blog' => 'index']);
        $staticTypoScript = $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.']['defaultContentRendering'];
        $this->assertContains('tt_content.list.20.myextension_pi1 = USER', $staticTypoScript);
    }

    /**
     * @test
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin
     */
    public function configurePluginWorksForASingleControllerAction()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.'] = [];
        ExtensionUtility::configurePlugin('MyExtension', 'Pi1', [
            'FirstController' => 'index'
        ]);
        $staticTypoScript = $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.']['defaultContentRendering'];
        $this->assertContains('tt_content.list.20.myextension_pi1 = USER', $staticTypoScript);
        $this->assertContains('
	extensionName = MyExtension
	pluginName = Pi1', $staticTypoScript);
        $expectedResult = [
            'controllers' => [
                'FirstController' => [
                    'actions' => ['index']
                ]
            ],
            'pluginType' => 'list_type'
        ];
        $this->assertEquals($expectedResult, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['MyExtension']['plugins']['Pi1']);
    }

    /**
     * @test
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin
     */
    public function configurePluginThrowsExceptionIfExtensionNameIsEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1239891990);
        ExtensionUtility::configurePlugin('', 'SomePlugin', [
            'FirstController' => 'index'
        ]);
    }

    /**
     * @test
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin
     */
    public function configurePluginThrowsExceptionIfPluginNameIsEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1239891988);
        ExtensionUtility::configurePlugin('MyExtension', '', [
            'FirstController' => 'index'
        ]);
    }

    /**
     * @test
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin
     */
    public function configurePluginRespectsDefaultActionAsANonCacheableAction()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.'] = [];
        ExtensionUtility::configurePlugin('MyExtension', 'Pi1', [
            'FirstController' => 'index,show,new, create,delete,edit,update'
        ], [
            'FirstController' => 'index,show'
        ]);
        $staticTypoScript = $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.']['defaultContentRendering'];
        $this->assertContains('tt_content.list.20.myextension_pi1 = USER', $staticTypoScript);
        $this->assertContains('
	extensionName = MyExtension
	pluginName = Pi1', $staticTypoScript);
        $expectedResult = [
            'controllers' => [
                'FirstController' => [
                    'actions' => ['index', 'show', 'new', 'create', 'delete', 'edit', 'update'],
                    'nonCacheableActions' => ['index', 'show']
                ]
            ],
            'pluginType' => 'list_type'
        ];
        $this->assertEquals($expectedResult, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['MyExtension']['plugins']['Pi1']);
    }

    /**
     * @test
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin
     */
    public function configurePluginRespectsNonDefaultActionAsANonCacheableAction()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.'] = [];
        ExtensionUtility::configurePlugin('MyExtension', 'Pi1', [
            'FirstController' => 'index,show,new, create,delete,edit,update'
        ], [
            'FirstController' => 'new,show'
        ]);
        $staticTypoScript = $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.']['defaultContentRendering'];
        $this->assertContains('tt_content.list.20.myextension_pi1 = USER', $staticTypoScript);
        $this->assertContains('
	extensionName = MyExtension
	pluginName = Pi1', $staticTypoScript);
        $expectedResult = [
            'controllers' => [
                'FirstController' => [
                    'actions' => ['index', 'show', 'new', 'create', 'delete', 'edit', 'update'],
                    'nonCacheableActions' => ['new', 'show']
                ]
            ],
            'pluginType' => 'list_type'
        ];
        $this->assertEquals($expectedResult, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['MyExtension']['plugins']['Pi1']);
    }

    /**
     * @test
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin
     */
    public function configurePluginWorksForMultipleControllerActionsWithCacheableActionAsDefault()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.'] = [];
        ExtensionUtility::configurePlugin('MyExtension', 'Pi1', [
            'FirstController' => 'index,show,new,create,delete,edit,update',
            'SecondController' => 'index,show,delete',
            'ThirdController' => 'create'
        ], [
            'FirstController' => 'new,create,edit,update',
            'ThirdController' => 'create'
        ]);
        $expectedResult = [
            'controllers' => [
                'FirstController' => [
                    'actions' => ['index', 'show', 'new', 'create', 'delete', 'edit', 'update'],
                    'nonCacheableActions' => ['new', 'create', 'edit', 'update']
                ],
                'SecondController' => [
                    'actions' => ['index', 'show', 'delete']
                ],
                'ThirdController' => [
                    'actions' => ['create'],
                    'nonCacheableActions' => ['create']
                ]
            ],
            'pluginType' => 'list_type'
        ];
        $this->assertEquals($expectedResult, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['MyExtension']['plugins']['Pi1']);
    }

    /**
     * @test
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin
     */
    public function configurePluginWorksForMultipleControllerActionsWithNonCacheableActionAsDefault()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.'] = [];
        ExtensionUtility::configurePlugin('MyExtension', 'Pi1', [
            'FirstController' => 'index,show,new,create,delete,edit,update',
            'SecondController' => 'index,show,delete',
            'ThirdController' => 'create'
        ], [
            'FirstController' => 'index,new,create,edit,update',
            'SecondController' => 'delete',
            'ThirdController' => 'create'
        ]);
        $expectedResult = [
            'controllers' => [
                'FirstController' => [
                    'actions' => ['index', 'show', 'new', 'create', 'delete', 'edit', 'update'],
                    'nonCacheableActions' => ['index', 'new', 'create', 'edit', 'update']
                ],
                'SecondController' => [
                    'actions' => ['index', 'show', 'delete'],
                    'nonCacheableActions' => ['delete']
                ],
                'ThirdController' => [
                    'actions' => ['create'],
                    'nonCacheableActions' => ['create']
                ]
            ],
            'pluginType' => 'list_type'
        ];
        $this->assertEquals($expectedResult, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['MyExtension']['plugins']['Pi1']);
    }

    /**
     * Tests method combination of registerPlugin() and its dependency addPlugin() to
     * verify plugin icon path resolving works.
     *
     * @test
     */
    public function registerPluginTriggersAddPluginWhichSetsPluginIconPathIfUsingUnderscoredExtensionNameAndIconPathNotGiven()
    {
        $GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] = [];
        $GLOBALS['TYPO3_LOADED_EXT'] = [];
        $GLOBALS['TYPO3_LOADED_EXT']['indexed_search']['ext_icon'] = 'foo.gif';
        ExtensionUtility::registerPlugin(
            'indexed_search',
            'Pi2',
            'Testing'
        );
        $this->assertEquals(
            'EXT:indexed_search/foo.gif',
            $GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'][0][2]
        );
    }

    /**
     * Tests method combination of registerPlugin() and its dependency addPlugin() to
     * verify plugin icon path resolving works.
     *
     * @test
     */
    public function registerPluginTriggersAddPluginWhichSetsPluginIconPathIfUsingUpperCameCasedExtensionNameAndIconPathNotGiven()
    {
        $GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] = [];
        $GLOBALS['TYPO3_LOADED_EXT'] = [];
        $GLOBALS['TYPO3_LOADED_EXT']['indexed_search']['ext_icon'] = 'foo.gif';
        ExtensionUtility::registerPlugin(
            'IndexedSearch',
            'Pi2',
            'Testing'
        );
        $this->assertEquals(
            'EXT:indexed_search/foo.gif',
            $GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'][0][2]
        );
    }

    /**
     * Tests method combination of registerPlugin() and its dependency addPlugin() to
     * verify plugin icon path resolving works.
     *
     * @test
     */
    public function registerPluginTriggersAddPluginWhichSetsPluginIconPathIfIconPathIsGiven()
    {
        $GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] = [];
        ExtensionUtility::registerPlugin(
            'IndexedSearch',
            'Pi2',
            'Testing',
            'EXT:indexed_search/foo.gif'
        );
        $this->assertEquals(
            'EXT:indexed_search/foo.gif',
            $GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'][0][2]
        );
    }

    /**
     * A type converter added several times with the exact same class name must
     * not be added more than once to the global array.
     *
     * @test
     */
    public function sameTypeConvertersRegisteredAreAddedOnlyOnce()
    {
        $typeConverterClassName = \TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter::class;

        // the Extbase EXTCONF is not set at all at this point
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters'] = [];

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter($typeConverterClassName);

        $this->assertContains($typeConverterClassName, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters']);
        $this->assertEquals(1, count($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters']));

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter($typeConverterClassName);
        $this->assertEquals(1, count($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters']));
    }
}
