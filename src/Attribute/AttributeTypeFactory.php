<?php

/**
 * This file is part of MetaModels/attribute_translatedalias.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedalias
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedAliasBundle\Attribute;

use Contao\CoreBundle\Slug\Slug;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\AbstractAttributeTypeFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attribute type factory for translated alias attributes.
 */
class AttributeTypeFactory extends AbstractAttributeTypeFactory
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * The Contao slug generator.
     *
     * @var Slug
     */
    private $slug;

    /**
     * Create a new instance.
     *
     * @param Connection               $connection      Database connection.
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     * @param Slug                     $slug            The Contao slug generator.
     */
    public function __construct(Connection $connection, EventDispatcherInterface $eventDispatcher, Slug $slug)
    {
        parent::__construct();

        $this->typeName        = 'translatedalias';
        $this->typeIcon        = 'bundles/metamodelsattributetranslatedalias/alias.png';
        $this->typeClass       = TranslatedAlias::class;
        $this->connection      = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->slug            = $slug;
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new $this->typeClass($metaModel, $information, $this->connection, $this->eventDispatcher, $this->slug);
    }
}
