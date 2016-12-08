<?php

/**
 * This file is part of MetaModels/attribute_translatedalias.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * @package     MetaModels
 * @subpackage  AttributeTranslatedAlias
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author      Stefan Heimes <stefan_heimes@hotmail.com>
 * @author      Andreas Isaak <info@andreas-isaak.de>
 * @author      Sven Baumann <baumann.sv@gmail.com>
 * @copyright   2012-2016 The MetaModels team.
 * @license     https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Table tl_metamodel_attribute
 */

/**
 * Add palette configuration.
 */
$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['translatedalias extends _complexattribute_'] = array
(
    '+advanced' => array('force_talias'),
    '+display'  => array('talias_fields after description')
);


// Get all active modules for check if attribute_translatedtext is loaded.
$activeModules = \Contao\ModuleLoader::getActive();

/**
 * Add data provider.
 */
if (!in_array('metamodelsattribute_translatedtext', $activeModules)) {
    $GLOBALS['TL_DCA']['tl_metamodel_attribute']['dca_config']['data_provider']['tl_metamodel_translatedtext'] = array
    (
        'source' => 'tl_metamodel_translatedtext'
    );
}

/**
 * Add child condition.
 */
if (!in_array('metamodelsattribute_translatedtext', $activeModules)) {
    $GLOBALS['TL_DCA']['tl_metamodel_attribute']['dca_config']['childCondition'][] = array
    (
        'from'   => 'tl_metamodel_attribute',
        'to'     => 'tl_metamodel_translatedtext',
        'setOn'  => array
        (
            array
            (
                'to_field'   => 'att_id',
                'from_field' => 'id',
            ),
        ),
        'filter' => array
        (
            array
            (
                'local'     => 'att_id',
                'remote'    => 'id',
                'operation' => '=',
            ),
        )
    );
}

/**
 * Add field configuration.
 */
$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['talias_fields'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_fields'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'columnFields' => array
        (
            'field_attribute' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['field_attribute'],
                'exclude'               => true,
                'inputType'             => 'select',
                'eval' => array
                (
                    'style'             => 'width:600px',
                    'chosen'            => 'true'
                )
            ),
        ),
    ),
);

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['force_talias'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['force_alias'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array
    (
        'tl_class' => 'cbx w50'
    ),
);
