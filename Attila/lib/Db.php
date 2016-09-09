<?php

/**
 * Db Manager
 *
 * @category  	Attila
 * @package  	Attila\lib
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/attila/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/attila
 * @link      	https://github.com/las93
 * @since     	1.0.0
 */
namespace Attila\lib;

use \Attila\lib\Db\Container as Container;

/**
 * Db Manager
 *
 * @category  	Attila
 * @package  	Attila\lib
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
	 * the container of connection datas
	 * 
	 * @access private
	 * @var    \Attila\lib\Db\Container
	 */
	private static $_oContainerConnection = null;

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
	public static function connect(Container $oContainerConnection)
	{
	    if (self::getContainer() === null) { self::setContainer($oContainerConnection); }
	    
		if (!isset(self::$_oPdo[$oContainerConnection->getName()])) {

			if ($oContainerConnection->getType() == 'mysql') {

				try {
                    if ($oContainerConnection->getDbName()) { $dbText = ";dbname=".$oContainerConnection->getDbName(); } else { $dbText = ""; }
					self::$_oPdo[$oContainerConnection->getName()] = new \PDO('mysql:host='.$oContainerConnection->getHost().$dbText, $oContainerConnection->getUser(), $oContainerConnection->getPassword(), array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
					self::$_oPdo[$oContainerConnection->getName()]->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, 1);
					self::$_oPdo[$oContainerConnection->getName()]->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
				}
				catch (\Exception $oException) {

					echo $oException->getMessage();
				}
			}
			else if ($oContainerConnection->getType() == 'mssql') {

                if ($oContainerConnection->getDbName()) { $dbText = ";dbname=".$oContainerConnection->getDbName(); } else { $dbText = ""; }
                self::$_oPdo[$oContainerConnection->getName()] = new \PDO('mssql:host='.$oContainerConnection->getHost().$dbText, $oContainerConnection->getUser(), $oContainerConnection->getPassword());
				self::$_oPdo[$oContainerConnection->getName()]->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, 1);
				self::$_oPdo[$oContainerConnection->getName()]->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
			else if ($oContainerConnection->getType() == 'sqlite') {

				self::$_oPdo[$oContainerConnection->getName()] = new \PDO('sqlite:'.$oContainerConnection->getHost());
				self::$_oPdo[$oContainerConnection->getName()]->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, 1);
				self::$_oPdo[$oContainerConnection->getName()]->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
		}

		return self::$_oPdo[$oContainerConnection->getName()];
	}

	/**
	 * set container of the database connection
	 *
	 * @access public
	 * @param Container $oContainer
	 * @return string
	 */
	public static function setContainer(Container $oContainer)
	{
		self::$_oContainerConnection = $oContainer;
	}

	/**
	 * get container of the database connection
	 *
	 * @access public
	 * @return string
	 */
	public static function getContainer()
	{
		return self::$_oContainerConnection;
	}
}
