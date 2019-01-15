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
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author      Stefan Heimes <stefan_heimes@hotmail.com>
 * @author      Andreas Isaak <info@andreas-isaak.de>
 * @author      Sven Baumann <baumann.sv@gmail.com>
 * @author      David Molineus <david.molineus@netzmacht.de>
 * @author      Ingolf Steinhardt <info@e-spin.de>
 * @author      Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/*
 * Table tl_metamodel_attribute
 */

/*
 * Add palette configuration.
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['translatedalias extends _complexattribute_'] = [
    '+advanced' => ['force_talias'],
    '+display'  => ['talias_fields after description']
];


// Get all active modules for check if attribute_translatedtext is loaded.
$activeModules = \Contao\ModuleLoader::getActive();

/*
 * Add data provider.
 */

if (!in_array('metamodelsattribute_translatedtext', $activeModules)) {
    $GLOBALS['TL_DCA']['tl_metamodel_attribute']['dca_config']['data_provider']['tl_metamodel_translatedtext'] = [
        'source' => 'tl_metamodel_translatedtext'
    ];
}

/*
 * Add child condition.
 */

if (!in_array('metamodelsattribute_translatedtext', $activeModules)) {
    $GLOBALS['TL_DCA']['tl_metamodel_attribute']['dca_config']['childCondition'][] = [
        'from'   => 'tl_metamodel_attribute',
        'to'     => 'tl_metamodel_translatedtext',
        'setOn'  => [
            [
                'to_field'   => 'att_id',
                'from_field' => 'id',
            ],
        ],
        'filter' => [
            [
                'local'     => 'att_id',
                'remote'    => 'id',
                'operation' => '=',
            ],
        ]
    ];
}

/*
 * Add field configuration.
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['talias_fields'] = [
    'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_fields'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => [
        'columnFields' => [
            'field_attribute' => [
                'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['field_attribute'],
                'exclude'               => true,
                'inputType'             => 'select',
                'reference'             => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['select_values'],
                'eval' => [
                    'style'             => 'width:600px',
                    'chosen'            => 'true'
                ]
            ],
        ],
    ],
    'sql'                     => 'blob NULL'
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['force_talias'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['force_alias'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => [
        'tl_class' => 'cbx w50'
    ],
    'sql'                     => 'char(1) NOT NULL default \'\''
];
