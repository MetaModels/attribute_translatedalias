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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedAliasBundle\EventListener;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\IFactory;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This handles the prefix and postfix options.
 */
class PrepostFixFieldsListener
{
    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The factory.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        TranslatorInterface $translator
    ) {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->factory           = $factory;
        $this->translator        = $translator;
    }

    /**
     * Translates the values of the prefix and postfix entries into the real array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty() !== 'prepostfix_fields')) {
            return;
        }

        $propInfo =
            $event->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getProperty('prepostfix_fields');
        $value    = StringUtil::deserialize($event->getValue(), true);
        $extra    = $propInfo->getExtra();

        $newValues = [];
        $languages = $extra['columnFields']['prepostfix_language']['options'];
        foreach (array_keys($languages) as $key) {
            if ($thisValue = ($value[$key] ?? null)) {
                $newValues[] = [
                    'prepostfix_language' => $key,
                    'talias_prefix'       => $thisValue['talias_prefix'],
                    'talias_postfix'      => $thisValue['talias_postfix']
                ];
                continue;
            }
            $newValues[] = [
                'prepostfix_language' => $key,
                'talias_prefix'       => '',
                'talias_postfix'      => ''
            ];
        }

        $event->setValue($newValues);
    }

    /**
     * Translates the values of the prefix and postfix entries into the internal array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty() !== 'prepostfix_fields')) {
            return;
        }

        $value = StringUtil::deserialize($event->getValue(), true);

        $result = [];
        foreach ($value as $k => $v) {
            $result[$v['prepostfix_language']] = [
                'talias_prefix'  => $v['talias_prefix'],
                'talias_postfix' => $v['talias_postfix']
            ];
        }

        $event->setValue(serialize($result));
    }

    /**
     * Provide options for prefix and postfix.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function buildWidget(BuildWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty()->getName() !== 'prepostfix_fields')) {
            return;
        }

        $model     = $event->getModel();
        $metaModel =
            $this->factory->getMetaModel($this->factory->translateIdToMetaModelName($model->getProperty('pid')));

        $extra = $event->getProperty()->getExtra();

        $arrLanguages = [];
        foreach ((array) $metaModel->getAvailableLanguages() as $strLangCode) {
            $arrLanguages[$strLangCode] = $this->translator
                ->trans('LNG.' . $strLangCode, [], 'contao_languages');
        }
        asort($arrLanguages);

        $extra['minCount'] = count($arrLanguages);
        $extra['maxCount'] = count($arrLanguages);

        $extra['columnFields']['prepostfix_language']['options'] = $arrLanguages;

        $event->getProperty()->setExtra($extra);
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $environment = $event->getEnvironment();
        if ('tl_metamodel_attribute' !== $environment->getDataDefinition()->getName()) {
            return false;
        }

        if ($event instanceof AbstractModelAwareEvent) {
            if ($event->getEnvironment()->getDataDefinition()->getName() !== $event->getModel()->getProviderName()) {
                return false;
            }
        }

        return true;
    }
}
