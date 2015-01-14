<?php

/**
 * Db Manager
 *
 * @category  	Attila
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/venus2/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/venus2
 * @link      	https://github.com/las93
 * @since     	1.0
 */
namespace Attila;

use \Venus\core\Config as Config;

/**
 * Db Manager
 *
 * @category  	Attila
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/venus2/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/venus2
 * @link      	https://github.com/las93
 * @since     	1.0
 */
class Db 
{
	/**
	 * object Db
	 *
	 * @access private
	 * @var    array
	 */
	private static $_oPdo = null;

	/**
	 * get instance of Pdo
	 *
	 * @access public
	 * @param  string sName name of the configuration
	 * @return void
	 */
	public static function connect($sName)
	{
		if (!isset(self::$_oPdo[$sName])) {

			$oDbConf = Config::get('Db')->configuration;

			if ($oDbConf->{$sName}->type == 'mysql') {

				try {

					self::$_oPdo[$sName] = new \PDO('mysql:host='.$oDbConf->{$sName}->host.';dbname='.$oDbConf->{$sName}->db, $oDbConf->{$sName}->user, $oDbConf->{$sName}->password, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
					self::$_oPdo[$sName]->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, 1);
					self::$_oPdo[$sName]->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
				}
				catch (\Exception $oException) {

					echo $oException->getMessage();
				}
			}
			else if ($oDbConf->{$sName}->type == 'mssql') {

				self::$_oPdo[$sName] = new \PDO('mssql:host='.$oDbConf->{$sName}->host.';dbname='.$oDbConf->{$sName}->db, $oDbConf->{$sName}->user, $oDbConf->{$sName}->password);
				self::$_oPdo[$sName]->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, 1);
				self::$_oPdo[$sName]->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
			else if ($oDbConf->{$sName}->type == 'sqlite') {

				self::$_oPdo[$sName] = new \PDO('sqlite:'.$oDbConf->{$sName}->path);
				self::$_oPdo[$sName]->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, 1);
				self::$_oPdo[$sName]->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
		}

		return self::$_oPdo[$sName];
	}
}
