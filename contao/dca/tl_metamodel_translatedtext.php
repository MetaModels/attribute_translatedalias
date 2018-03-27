<?php

/**
 * This file is part of MetaModels/attribute_translatedalias.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @author      Sven Baumann <baumann.sv@gmail.com>
 * @copyright   2012-2018 The MetaModels team.
 * @license     https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Table tl_metamodel_translatedtext
 */
$GLOBALS['TL_DCA']['tl_metamodel_translatedtext'] = array
(
    // Config
    'config' => array
    (
        'sql' => array
        (
            'keys' => array
            (
                'id'                            => 'primary',
                'att_id,value,item_id,langcode' => 'index'
            )
        )
    ),
    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => 'int(10) unsigned NOT NULL auto_increment'
        ),
        'tstamp' => array
        (
            'sql'                     => 'int(10) unsigned NOT NULL default \'0\''
        ),
        'att_id' => array
        (
            'sql'                     => 'int(10) unsigned NOT NULL default \'0\''
        ),
        'item_id' => array
        (
            'sql'                     => 'int(10) unsigned NOT NULL default \'0\''
        ),
        'langcode' => array
        (
            'sql'                     => 'varchar(5) NOT NULL default \'\''
        ),
        'value' => array
        (
            'sql'                     => 'varchar(255) NOT NULL default \'\''
        )
    )
);
