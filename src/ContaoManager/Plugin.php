<?php

/**
 * This file is part of MetaModels/attribute_translatedalias.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedalias
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedAliasBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use MetaModels\AttributeTranslatedAliasBundle\MetaModelsAttributeTranslatedAliasBundle;
use MetaModels\CoreBundle\MetaModelsCoreBundle;

/**
 * Plugin for the Contao Manager.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(MetaModelsAttributeTranslatedAliasBundle::class)
                ->setLoadAfter(
                    [
                        ContaoCoreBundle::class,
                        MetaModelsCoreBundle::class
                    ]
                )
                ->setReplace(['metamodelsattribute_translatedalias'])
        ];
    }
}
