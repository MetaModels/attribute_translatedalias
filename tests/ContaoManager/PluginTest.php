<?php

/**
 * This file is part of MetaModels/attribute_translatedalias.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedalias
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedAliasBundle\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use MetaModels\AttributeTranslatedAliasBundle\ContaoManager\Plugin;
use MetaModels\CoreBundle\MetaModelsCoreBundle;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests the contao manager plugin.
 *
 * @covers \MetaModels\AttributeTranslatedAliasBundle\ContaoManager\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * Test that plugin can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $plugin = new Plugin();

        self::assertInstanceOf(Plugin::class, $plugin);
        self::assertInstanceOf(BundlePluginInterface::class, $plugin);
    }

    /**
     * Tests that the a valid bundle config is created.
     *
     * @return void
     */
    public function testBundleConfig()
    {
        $parser  = $this->getMockBuilder(ParserInterface::class)->getMock();
        $plugin  = new Plugin();
        $bundles = $plugin->getBundles($parser);

        self::assertContainsOnlyInstancesOf(BundleConfig::class, $bundles);
        self::assertCount(1, $bundles);

        /** @var BundleConfig $bundleConfig */
        $bundleConfig = $bundles[0];

        self::assertEquals($bundleConfig->getLoadAfter(), [ContaoCoreBundle::class, MetaModelsCoreBundle::class]);
        self::assertEquals($bundleConfig->getReplace(), ['metamodelsattribute_translatedalias']);
    }
}
