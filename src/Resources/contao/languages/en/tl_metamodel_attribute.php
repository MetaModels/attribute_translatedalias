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
 * @package    MetaModels
 * @subpackage attribute_translatedalias
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedalias/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['typeOptions']['translatedalias'] = 'Translated alias';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['noIntegerPrefix'][0]             = 'No integer prefix';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['noIntegerPrefix'][1]             =
    'Trim the "id-" prefix for alias that are numeric.';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['prepostfix_fields'][0]           = 'Alias prefix and postfix';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['prepostfix_fields'][1]           =
    'Please enter optional prefix and/or postfix.';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['prepostfix_language'][0]         = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['talias_prefix'][0]               = 'Alias prefix';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['talias_prefix'][1]               =
    'Optionally add a prefix term.';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['talias_postfix'][0]              = 'Alias postfix';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['talias_postfix'][1]              =
    'Optionally add a postfix term.';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['talias_fields'][0]               = 'Alias fields';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['talias_fields'][1]               =
    'Please select one or more attributes to combine a alias.';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['field_attribute']                = 'Attributes';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['force_talias'][0]                = 'Force alias regenerating';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['force_talias'][1]                =
    'Check this, if you want the alias to be regenerated whenever any of the dependant fields is changed. Note that ' .
    'this will invalidate old urls that are based upon the alias.';
