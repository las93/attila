<?php

/**
 * autoload of Attila
 * use the PSR-0
 *
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/apollina/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 2.0.0.0
 * @filesource	https://github.com/las93/apollina
 * @link      	https://github.com/las93
 * @since     	2.0.0.0
 *
 * new version with SPL to have the capacity to add external autoload
 */
spl_autoload_register(function ($sClassName)
{
    $sClassName = ltrim($sClassName, '\\');
    $sFileName  = '';
    $sNamespace = '';

    if ($iLastNsPos = strrpos($sClassName, '\\')) {

        $sNamespace = substr($sClassName, 0, $iLastNsPos);
        $sClassName = substr($sClassName, $iLastNsPos + 1);
		$sFileName  = str_replace('\\', DIRECTORY_SEPARATOR, $sNamespace).DIRECTORY_SEPARATOR;
    }

    $sFileName = str_replace('/', '\\', $sFileName);
    
    $sFileName .= $sClassName.'.php';

    if (strstr($sFileName, 'Attila\\') && file_exists(__DIR__.DIRECTORY_SEPARATOR.str_replace('Attila\\', '', $sFileName))) {

    	require __DIR__.DIRECTORY_SEPARATOR.str_replace('Attila\\', '', $sFileName);
    }
});
