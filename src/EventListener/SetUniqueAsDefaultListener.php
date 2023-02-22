<?php

/**
 * This file is part of MetaModels/attribute_translatedalias.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedalias
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedAliasBundle\EventListener;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;

/**
 * This class provides the attribute unique as default.
 */
class SetUniqueAsDefaultListener
{
    /**
     * Request scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator
    ) {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * Enable widget isunique at create.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildWidget(BuildWidgetEvent $event): void
    {
        $model = $event->getModel();

        if (false === $this->scopeDeterminator->currentScopeIsBackend()
            || null !== $model->getId()
            || 'translatedalias' !== $model->getProperty('type')
            || 'tl_metamodel_attribute' !== $event->getEnvironment()->getDataDefinition()->getName()
            || 'isunique' !== $event->getProperty()->getName()
        ) {
            return;
        }

        $model->setProperty('isunique', true);
    }
}
