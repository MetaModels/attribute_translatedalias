<?php

/**
 * This file is part of MetaModels/attribute_translatedalias.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeTranslatedAlias
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute\TranslatedAlias;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReplaceInsertTagsEvent;
use MetaModels\Attribute\TranslatedReference;

/**
 * This is the MetaModelAttribute class for handling translated text fields.
 */
class TranslatedAlias extends TranslatedReference
{
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
        return array_merge(
            parent::getAttributeSettingNames(),
            array(
                'talias_fields',
                'isunique',
                'force_talias',
                'alwaysSave'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDefinition($arrOverrides = array())
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

        $arrAlias = array();
        foreach (deserialize($this->get('talias_fields')) as $strAttribute) {
            if ($this->isMetaField($strAttribute['field_attribute'])) {
                $strField   = $strAttribute['field_attribute'];
                $arrAlias[] = $objItem->get($strField);
            } else {
                $arrValues  = $objItem->parseAttribute($strAttribute['field_attribute'], 'text', null);
                $arrAlias[] = $arrValues['text'];
            }
        }

        $dispatcher   = $this->getMetaModel()->getServiceContainer()->getEventDispatcher();
        $replaceEvent = new ReplaceInsertTagsEvent(implode('-', $arrAlias));
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_REPLACE_INSERT_TAGS, $replaceEvent);

        // Implode with '-', replace inserttags and strip HTML elements.
        $strAlias = standardize(strip_tags($replaceEvent->getBuffer()));

        // We need to fetch the attribute values for all attributes in the alias_fields and update the database
        // and the model accordingly.
        if ($this->get('isunique')) {
            // Ensure uniqueness.
            $strLanguage  = $this->getMetaModel()->getActiveLanguage();
            $strBaseAlias = $strAlias;
            $arrIds       = array($objItem->get('id'));
            $intCount     = 2;
            while (array_diff($this->searchForInLanguages($strAlias, array($strLanguage)), $arrIds)) {
                $strAlias = $strBaseAlias . '-' . ($intCount++);
            }
        }

        $arrData = $this->widgetToValue($strAlias, $objItem->get('id'));

        $this->setTranslatedDataFor(
            array
            (
                $objItem->get('id') => $arrData
            ),
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
     * Check if we have a meta field from metamodels.
     *
     * @param string $strField The selected value.
     *
     * @return boolean True => Yes we have | False => nope.
     */
    protected function isMetaField($strField)
    {
        $strField = trim($strField);

        if (in_array($strField, $this->getMetaModelsSystemColumns())) {
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
