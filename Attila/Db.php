<?php

/**
 * Db Manager
 *
 * @category  	Attila
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/attila/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/attila
 * @link      	https://github.com/las93
 * @since     	1.0.0
 */
namespace Attila;

/**
 * Db Manager
 *
 * @category  	Attila
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/attila/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/attila
 * @link      	https://github.com/las93
 * @since     	1.0.0
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
	 * @param  string $sType kind of database
	 * @param  string $sHost host of the connection (path for sqlite)
	 * @param  string $sUser user of the connection
	 * @param  string $sPassword password of the connection
	 * @param  string $sDbName name of the connection
	 * @return void
	 */
	public static function connect($sName, $sType = 'mysql', $sHost = 'localhost', $sUser = 'root', $sPassword = '', $sDbName = 'demo')
	{
		if (!isset(self::$_oPdo[$sName])) {

			if ($sType == 'mysql') {

				try {

					self::$_oPdo[$sName] = new \PDO('mysql:host='.$sHost.';dbname='.$sDbName, $sUser, $sPassword, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
					self::$_oPdo[$sName]->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, 1);
					self::$_oPdo[$sName]->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
				}
				catch (\Exception $oException) {

					echo $oException->getMessage();
				}
			}
			else if ($oDbConf->{$sName}->type == 'mssql') {

				self::$_oPdo[$sName] = new \PDO('mssql:host='.$sHost.';dbname='.$sDbName, $sUser, $sPassword);
				self::$_oPdo[$sName]->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, 1);
				self::$_oPdo[$sName]->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
			else if ($oDbConf->{$sName}->type == 'sqlite') {

				self::$_oPdo[$sName] = new \PDO('sqlite:'.$sHost);
				self::$_oPdo[$sName]->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, 1);
				self::$_oPdo[$sName]->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
		}

		return self::$_oPdo[$sName];
	}
}
