<?php

/**
 * Batch that create entity
 *
 * @category  	Attila
 * @package   	Attila\Batch
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/attila/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/attila
 * @link      	https://github.com/las93
 * @since     	1.0.0
 *
 * @tutorial    You could launch this Batch in /private/
 * 				php launch.php scaffolding -p [portal]
 * 				-p [portal] => it's the name where you want add your entities and models
 * 				-r [rewrite] => if we force rewrite file
 * 					by default, it's Batch
 */
namespace Attila\Batch;

use \Attila\lib\Db as Db;
use \Attila\lib\Db\Container as DbContainer;
use \Attila\lib\Bash;

/**
 * Batch that create entity
 *
 * @category  	Attila
 * @package   	Attila\Batch
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/attila/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/attila
 * @link      	https://github.com/las93
 * @since     	1.0.0
 */
class Operation
{
    /**
     * run the batch to create entity
     *
     * @access public
     * @param  array $aOptions options of script
     * @return void
     */

    public function createDb(array $aOptions = array())
    {
        /**
         * option -a [indicated the sql json file]
         */

        if (isset($aOptions['a'])) { $sSqlJsonFile = $aOptions['a']; }
        else { $sSqlJsonFile = false; }

        /**
         * option -b [indicated the sql json]
         */

        if (isset($aOptions['b'])) { $sSqlJson = $aOptions['b']; }
        else { $sSqlJson = false; $sSqlJsonFile = str_replace('Batch', '', __DIR__).'Db.conf'; }

        /**
         * option -i [indicated the const json file to manage annotation in files]
         */

        if (isset($aOptions['i'])) { $oConstJson = json_decode(file_get_contents($aOptions['i']));}
        else { $oConstJson = '../Const.conf'; }

        if (is_object($oConstJson)) {

            foreach ($oConstJson as $sKey => $mValue) {

                if (is_string($mValue) || is_int($mValue) || is_float($mValue)) {

                    if (!defined(strtoupper($sKey))) { define(strtoupper($sKey), $mValue); }
                }
            }
        }

        if ($sSqlJsonFile !== false) { $oJson = json_decode(file_get_contents($sSqlJsonFile)); }
        else { $oJson = json_decode($sSqlJson); }

        $oConnection = $oJson->configuration;

        $oContainer = new DbContainer;

        $oContainer->setHost($oConnection->host)
            ->setName($oConnection->db)
            ->setPassword($oConnection->password)
            ->setType($oConnection->type)
            ->setUser($oConnection->user);

        $oPdo = Db::connect($oContainer);

        $oPdo->query("CREATE DATABASE ".$oConnection->db);

        echo "\n\n";
        echo Bash::setBackground("                                                                            ", 'green');
        echo Bash::setBackground("          [OK] Success                                                      ", 'green');
        echo Bash::setBackground("                                                                            ", 'green');
        echo "\n\n";
    }
}
