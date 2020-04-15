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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedAliasBundle\Attribute;

use Contao\CoreBundle\Slug\Slug as SlugGenerator;
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
     * The Contao slug generator.
     *
     * @var SlugGenerator
     */
    private $slugGenerator;

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
     *
     * @param SlugGenerator            $slugGenerator   The Contao slug generator.
     */
    public function __construct(
        IMetaModel $objMetaModel,
        array $arrData = [],
        Connection $connection = null,
        EventDispatcherInterface $eventDispatcher = null,
        SlugGenerator $slugGenerator = null
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

        if (null === $slugGenerator) {
            $slugGenerator = System::getContainer()->get('contao.slug');
        }

        $this->slugGenerator = $slugGenerator;
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
                'noIntegerPrefix',
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

        $itemId = $objItem->get('id');
        $alias  = $this->generateAlias($objItem);
        $slug   = $this->generateSlug($alias, $itemId);

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
     * Generate a slug from the alias.
     *
     * @param string $alias  The alias.
     *
     * @param string $itemId The item id to check for duplicates.
     *
     * @return string The generated slug.
     */
    private function generateSlug(string $alias, string $itemId): string
    {
        $replaceEvent = new ReplaceInsertTagsEvent($alias);
        $this->eventDispatcher->dispatch(ContaoEvents::CONTROLLER_REPLACE_INSERT_TAGS, $replaceEvent);

        $language    = $this->getMetaModel()->getActiveLanguage();
        $slugOptions = ['locale' => $language];

        if ($this->get('validAliasCharacters')) {
            $slugOptions += [
                'validChars' => $this->get('validAliasCharacters')
            ];
        }

        $slug = $this->slugGenerator->generate(
            $alias,
            $slugOptions,
            function (string $alias) use ($itemId, $language) {
                if (!$this->get('isunique')) {
                    return false;
                }

                return [] !== \array_diff($this->searchForInLanguages($alias, [$language]), [$itemId]);
            },
            $this->get('integerPrefix') ?? 'id-'
        );

        if (\is_numeric($slug[0]) && !$this->get('validAliasCharacters')) {
            // BC mode. In prior versions, StringUtil::standardize was used to generate the alias
            // which always added an prefix for aliases starting with a number.
            $slug = 'id-' . $slug;
        }

        return $slug;
    }

    /**
     * Generate the alias.
     *
     * @param IItem $objItem The item.
     *
     * @return string
     */
    private function generateAlias(IItem $objItem): string
    {
        $activeLanguage = $this->getMetaModel()->getActiveLanguage();
        $parts          = [];

        if (!empty($this->get('prepostfix_fields')[$activeLanguage]['talias_prefix'])) {
            $parts[] = $this->get('prepostfix_fields')[$activeLanguage]['talias_prefix'];
        }

        foreach (\deserialize($this->get('talias_fields')) as $aliasField) {
            if ($this->isMetaField($aliasField['field_attribute'])) {
                $attribute = $aliasField['field_attribute'];
                $parts[]   = $objItem->get($attribute);
            } else {
                $arrValues = $objItem->parseAttribute($aliasField['field_attribute'], 'text', null);
                $parts[]   = $arrValues['text'];
            }
        }

        if (!empty($this->get('prepostfix_fields')[$activeLanguage]['talias_postfix'])) {
            $parts[] = $this->get('prepostfix_fields')[$activeLanguage]['talias_postfix'];
        }

        return \implode('-', $parts);
    }


    /**
     * Check if we have a meta field from metamodels.
     *
     * @param string $strField The selected value.
     *
     * @return boolean True => Yes we have | False => nope.
     */
    protected function isMetaField($strField): bool
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
