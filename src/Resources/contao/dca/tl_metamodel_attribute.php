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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2020 The MetaModels team.
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
    '+display'  => ['validAliasCharacters', 'prepostfix_fields', 'noIntegerPrefix', 'talias_fields after description']
];

if (class_exists(MetaModels\AttributeTranslatedTextBundle\Attribute\TranslatedText::class)) {
    // Add data provider.
    $GLOBALS['TL_DCA']['tl_metamodel_attribute']['dca_config']['data_provider']['tl_metamodel_translatedtext'] = [
        'source' => 'tl_metamodel_translatedtext'
    ];
    // Add child condition.
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

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['validAliasCharacters'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_page']['validAliasCharacters'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => static function () {
        return Contao\System::getContainer()->get('contao.slug.valid_characters')->getOptions();
    },
    'eval'             => [
        'includeBlankOption' => true,
        'decodeEntities'     => true,
        'tl_class'           => 'w50',
        'helpwizard'         => true,
    ],
    'explanation'      => 'validAliasCharacters',
    'sql'              => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['noIntegerPrefix'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['noIntegerPrefix'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'default'   => 1,
    'sql'       => "char(1) NOT NULL default ''",
    'eval'      => [
        'tl_class' => 'clr w50'
    ],
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['prepostfix_fields'] = [
    'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['prepostfix_fields'],
    'exclude'        => true,
    'minCount'       => 1,
    'maxCount'       => 1,
    'disableSorting' => '1',
    'inputType'      => 'multiColumnWizard',
    'eval'           => [
        'dragAndDrop'  => false,
        'hideButtons'  => true,
        'tl_class'     => 'clr clx w50',
        'columnFields' => [
            'prepostfix_language' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['prepostfix_language'],
                'exclude'   => true,
                'inputType' => 'justtextoption',
                'eval'      => ['valign' => 'center']
            ],
            'talias_prefix'       => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['talias_prefix'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => ['style' => 'width:100%']
            ],
            'talias_postfix'      => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['talias_postfix'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => ['style' => 'width:100%']
            ],
        ],
    ],
    'sql'            => 'blob NULL'
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['talias_fields'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['talias_fields'],
    'exclude'   => true,
    'inputType' => 'multiColumnWizard',
    'eval'      => [
        'tl_class'     => 'clr clx w50',
        'columnFields' => [
            'field_attribute' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['field_attribute'],
                'exclude'   => true,
                'inputType' => 'select',
                'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['select_values'],
                'eval'      => [
                    'style'  => 'width:100%',
                    'chosen' => 'true'
                ]
            ],
        ],
    ],
    'sql'       => 'blob NULL'
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['force_talias'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['force_alias'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'tl_class' => 'cbx w50'
    ],
    'sql'       => 'char(1) NOT NULL default \'\''
];
