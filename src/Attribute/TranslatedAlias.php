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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedAliasBundle\Attribute;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReplaceInsertTagsEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\TranslatedReference;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the MetaModelAttribute class for handling translated text fields.
 */
class TranslatedAlias extends TranslatedReference
{
    /**
     * The alias.
     *
     * @var string
     */
    private $alias;

    /**
     * The integer prefix.
     *
     * @var string
     */
    private $integerPrefix = 'id-';

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel               $objMetaModel    The MetaModel instance this attribute belongs to.
     *
     * @param array                    $arrData         The information array, for attribute information, refer to
     *                                                  documentation of table tl_metamodel_attribute and documentation
     *                                                  of the certain attribute classes for information what values are
     *                                                  understood.
     *
     * @param Connection               $connection      Database connection.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     */
    public function __construct(
        IMetaModel $objMetaModel,
        array $arrData = [],
        Connection $connection = null,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        parent::__construct($objMetaModel, $arrData, $connection);

        if (null === $eventDispatcher) {
            // @codingStandardsIgnoreStart Silencing errors is discouraged
            @\trigger_error(
                'Event dispatcher is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $eventDispatcher = System::getContainer()->get('event_dispatcher');
        }

        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValueTable()
    {
        return 'tl_metamodel_translatedtext';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSettingNames()
    {
        return \array_merge(
            parent::getAttributeSettingNames(),
            [
                'talias_fields',
                'isunique',
                'force_talias',
                'alwaysSave',
                'validAliasCharacters',
                'skipIntegerPrefix',
                'prepostfix_fields'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDefinition($arrOverrides = [])
    {
        $arrFieldDef = parent::getFieldDefinition($arrOverrides);

        $arrFieldDef['inputType'] = 'text';

        // We do not need to set mandatory, as we will automatically update our value when isunique is given.
        if ($this->get('isunique')) {
            $arrFieldDef['eval']['mandatory'] = false;
        }

        // If "force_alias" is true set alwaysSave and readonly to true.
        if ($this->get('force_talias')) {
            $arrFieldDef['eval']['alwaysSave'] = true;
            $arrFieldDef['eval']['readonly']   = true;
        }

        return $arrFieldDef;
    }

    /**
     * {@inheritdoc}
     */
    public function modelSaved($objItem)
    {
        $arrValue = $objItem->get($this->getColName());
        // Alias already defined and no update forced, get out!
        if ($arrValue && !empty($arrValue['value']) && (!$this->get('force_talias'))) {
            return;
        }

        // Generate alias string.
        $this->alias = $this->generateAlias($objItem);

        // Convert alias with Contao standardize or slug.
        if ($this->get('validAliasCharacters')) {
            $this->convertAliasBySlug();
        } else {
            $this->convertAliasByStandardize();
        }

        // We need to fetch the attribute values for all attributes in the alias_fields and update the database
        // and the model accordingly.
        if ($this->get('isunique')) {
            // Ensure uniqueness.
            $strLanguage  = $this->getMetaModel()->getActiveLanguage();
            $strBaseAlias = $this->alias;
            $arrIds       = [$objItem->get('id')];
            $intCount     = 2;
            while (\array_diff($this->searchForInLanguages($this->alias, [$strLanguage]), $arrIds)) {
                $this->alias = $strBaseAlias . '-' . ($intCount++);
            }
        }

        $arrData = $this->widgetToValue($this->alias, $objItem->get('id'));

        $this->setTranslatedDataFor(
            [
                $objItem->get('id') => $arrData
            ],
            $this->getMetaModel()->getActiveLanguage()
        );
        $objItem->set($this->getColName(), $arrData);
    }

    /**
     * {@inheritdoc}
     */
    public function get($strKey)
    {
        if ($strKey == 'force_alias') {
            $strKey = 'force_talias';
        }
        return parent::get($strKey);
    }

    /**
     * Convert alias by standardize.
     */
    private function convertAliasByStandardize()
    {
        // Implode with '-', replace inserttags and strip HTML elements.
        $replaceEvent = new ReplaceInsertTagsEvent($this->alias);
        $this->eventDispatcher->dispatch(ContaoEvents::CONTROLLER_REPLACE_INSERT_TAGS, $replaceEvent);
        $baseAlias   = $this->alias;
        $this->alias = StringUtil::standardize(\strip_tags($replaceEvent->getBuffer()));

        // Check skip integer prefix.
        if (!$this->get('skipIntegerPrefix')) {
            return;
        }

        // Skip integer prefix is added by standardize.
        if ($this->integerPrefix !== substr($baseAlias, 0, strlen($this->integerPrefix))
            && $this->integerPrefix === substr($this->alias, 0, strlen($this->integerPrefix))) {
            $this->alias = substr($this->alias, strlen($this->integerPrefix));
        }

        return;
    }

    /**
     * Convert alias by slug.
     */
    private function convertAliasBySlug()
    {
        $slugOptions = [
            'locale'     => $this->getMetaModel()->getActiveLanguage(),
            'validChars' => $this->get('validAliasCharacters')
        ];

        $slugGenerator = new SlugGenerator();
        $baseAlias     = StringUtil::prepareSlug($this->alias);
        $this->alias   = $slugGenerator->generate($baseAlias, $slugOptions);

        // Add integer prefix.
        if (!$this->get('skipIntegerPrefix') && preg_match('/^[1-9]\d*$/', $this->alias)) {
            $this->alias = $this->integerPrefix . $this->alias;
        }

        return;
    }

    /**
     * Generate the alias.
     *
     * @param IItem $objItem The item.
     *
     * @return string
     */
    private function generateAlias(IItem $objItem)
    {
        $activeLanguage = $this->getMetaModel()->getActiveLanguage();
        $arrAlias       = [];

        if (!empty($this->get('prepostfix_fields')[$activeLanguage]['talias_prefix'])) {
            $arrAlias[] = $this->get('prepostfix_fields')[$activeLanguage]['talias_prefix'];
        }

        foreach (\deserialize($this->get('talias_fields')) as $strAttribute) {
            if ($this->isMetaField($strAttribute['field_attribute'])) {
                $strField   = $strAttribute['field_attribute'];
                $arrAlias[] = $objItem->get($strField);
            } else {
                $arrValues  = $objItem->parseAttribute($strAttribute['field_attribute'], 'text', null);
                $arrAlias[] = $arrValues['text'];
            }
        }

        if (!empty($this->get('prepostfix_fields')[$activeLanguage]['talias_postfix'])) {
            $arrAlias[] = $this->get('prepostfix_fields')[$activeLanguage]['talias_postfix'];
        }

        return \implode('-', $arrAlias);
    }


    /**
     * Check if we have a meta field from metamodels.
     *
     * @param string $strField The selected value.
     *
     * @return boolean True => Yes we have | False => nope.
     */
    protected function isMetaField($strField)
    {
        $strField = \trim($strField);

        if (\in_array($strField, $this->getMetaModelsSystemColumns())) {
            return true;
        }

        return false;
    }

    /**
     * Returns the global MetaModels System Columns (replacement for super global access).
     *
     * @return mixed Global MetaModels System Columns
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getMetaModelsSystemColumns()
    {
        return $GLOBALS['METAMODELS_SYSTEM_COLUMNS'];
    }
}
