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

namespace MetaModels\AttributeTranslatedAliasBundle\Test;

use MetaModels\AttributeTranslatedAliasBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeTranslatedAliasBundle\Attribute\TranslatedAlias;
use PHPUnit\Framework\TestCase;

/**
 * This class tests if the deprecated autoloader works.
 *
 * @covers \MetaModels\AttributeTranslatedAliasBundle\Attribute\TranslatedAlias
 * @covers \MetaModels\AttributeTranslatedAliasBundle\Attribute\AttributeTypeFactory
 */
class DeprecatedAutoloaderTest extends TestCase
{
    /**
     * TranslatedAliases of old classes to the new one.
     *
     * @var array
     */
    private static $classes = [
        'MetaModels\Attribute\TranslatedAlias\TranslatedAlias'       => TranslatedAlias::class,
        'MetaModels\Attribute\TranslatedAlias\AttributeTypeFactory' => AttributeTypeFactory::class
    ];

    /**
     * Provide the alias class map.
     *
     * @return array
     */
    public function provideAliasClassMap()
    {
        $values = [];

        foreach (static::$classes as $translatedAlias => $class) {
            $values[] = [$translatedAlias, $class];
        }

        return $values;
    }

    /**
     * Test if the deprecated classes are aliased to the new one.
     *
     * @param string $oldClass Old class name.
     * @param string $newClass New class name.
     *
     * @dataProvider provideAliasClassMap
     */
    public function testDeprecatedClassesAreAliased($oldClass, $newClass)
    {
        self::assertTrue(\class_exists($oldClass), \sprintf('Class TranslatedAlias "%s" is not found.', $oldClass));

        $oldClassReflection = new \ReflectionClass($oldClass);
        $newClassReflection = new \ReflectionClass($newClass);

        self::assertSame($newClassReflection->getFileName(), $oldClassReflection->getFileName());
    }
}
