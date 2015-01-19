<?php 

/**
 * index.php to run the scaffolding
 *
 * @category  	Attila
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/venus2/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/venus2
 * @link      	https://github.com/las93
 * @since     	1.0
 * 
 * @tutorial    php index.php -p Demo -a Db.conf
 */

namespace Attila;

use \Attila\Batch\Entity as Entity;

include('../../../autoload.php');

if (isset($_SERVER['argv'])) { $aArguments = $_SERVER['argv']; }
else { $aArguments = $argv; }

$sBatchName = $aArguments[1];
array_shift($aArguments);

$aOptions = array();

while (count($aArguments) > 0) {

    if (preg_match('/^-[a-z]/', $aArguments[0])) {

        $sOptionName = str_replace('-', '', $aArguments[0]);

        if (isset($aArguments[1])) { $sOptionValue = $aArguments[1]; }
        else { $sOptionValue = ''; }

        if (isset(Entity::$aOptionsBatch[$sOptionName]) && Entity::$aOptionsBatch[$sOptionName] === false) {

            $aOptions[$sOptionName] = true;
            array_shift($aArguments);
        }
        else if (isset(Entity::$aOptionsBatch[$sOptionName]) && Entity::$aOptionsBatch[$sOptionName] === 'string') {

            $aOptions[$sOptionName] = $sOptionValue;
            array_shift($aArguments);
            array_shift($aArguments);
        }
        else {

            array_shift($aArguments);
        }
    }
    else {

        array_shift($aArguments);
    }
}

$oBatch = new Entity;
$oBatch->runScaffolding($aOptions);
