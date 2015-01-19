<?php

/**
 * Entity Manager
 *
 * @category  	core
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/venus2/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 2.0.0
 * @filesource	https://github.com/las93/venus2
 * @link      	https://github.com/las93
 * @since     	2.0.0
 */
namespace Attila\core;

use \Attila\Entity as LibEntity;
use \Attila\Orm as Orm;
use \Attila\Orm\Where as Where;

/**
 * Entity Manager
 *
 * @category  	core
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/venus2/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 2.0.0
 * @filesource	https://github.com/las93/venus2
 * @link      	https://github.com/las93
 * @since     	2.0.0
 */
abstract class Entity
{
	/**
	 * name(s) of primary key
	 *
	 * @access private
	 * @var    mixed
	 */
	private $_mPrimaryKeyName;

	/**
	 * name(s) of primary key without mapping
	 *
	 * @access private
	 * @var    mixed
	 */
	private $_mPrimaryKeyNameWithoutMapping;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return object
	 */
	public function __construct()
	{
		$this->_loadPrimaryKeyName(LibEntity::getPrimaryKeyName($this));
		$this->_loadPrimaryKeyNameWithoutMapping(LibEntity::getPrimaryKeyNameWithoutMapping($this));
	}

	/**
	 * load primary key in the entity
	 *
	 * @access private
	 * @param  mixed $mName name of primary key (array if it's a multiple key)
	 * @return object
	 */
	private function _loadPrimaryKeyName($mName)
	{
	 	$this->_mPrimaryKeyName = $mName;
	 	return $this;
	}

	/**
	 * load primary key in the entity (without mapping name)
	 *
	 * @access private
	 * @param  mixed $mName name of primary key (array if it's a multiple key)
	 * @return object
	 */
	private function _loadPrimaryKeyNameWithoutMapping($mName)
	{
	 	$this->_mPrimaryKeyNameWithoutMapping = $mName;
	 	return $this;
	}

	/**
	 * save the entity
	 *
	 * @access public
	 * @param  bool $bOnDuplicateKeyUpdate (to do insert on duplicate key)
	 * @return object
	 */
	public function save($bOnDuplicateKeyUpdate = false)
	{
		$mPrimaryKeyName = $this->_mPrimaryKeyName;
		
		if ($bOnDuplicateKeyUpdate === false) { $bInsertMode = false; }
		else { $bInsertMode = true; }

		if ($mPrimaryKeyName === false) {

			throw new Exception('['.__FILE__.' (l.'.__LINE__.'] no primary key on this table!');
		}
		else if (is_string($mPrimaryKeyName)) {

			$sMethodPrimaryKey = 'get_'.$this->_mPrimaryKeyNameWithoutMapping;
			$aPrimaryKey = array($mPrimaryKeyName => $this->$sMethodPrimaryKey());
			
			if ($this->$sMethodPrimaryKey() < 1) { $bInsertMode = true; }
		}
		else {

			$aPrimaryKey = array();	

			$oOrm = new Orm;
			
			$iResults = $oOrm->select(array('*'))
							 ->from(preg_replace('/^.*\\\\([a-zA-Z0-9_]+)$/', '$1', get_called_class()));

			$oWhere = new Where;
			
			foreach($mPrimaryKeyName as $sKey => $sPrimaryKey) {

				$sMethodPrimaryKey = 'get_'.$this->_mPrimaryKeyNameWithoutMapping[$sKey];
				$aPrimaryKey[$sPrimaryKey] = $this->$sMethodPrimaryKey();

				
				$oWhere->andWhereEqual($sPrimaryKey, $aPrimaryKey[$sPrimaryKey]);
			}
			
			$aResults = $oOrm->where($oWhere)
							 ->load();
			
			if (count($aResults) < 1) { $bInsertMode = true; }
		}

		$aEntityTmp = get_object_vars(LibEntity::getRealEntity($this));
		$aEntity = array();
		
		foreach ($aEntityTmp as $sKey => $mField) {
		
			if ($mField !== null) {
		
				$aEntity[$sKey] = $mField;
			}
		}
		
		$oOrm = new Orm;
		
		if ($bInsertMode === true) {
			
			$oOrm->insert(preg_replace('/^.*\\\\([a-zA-Z0-9_]+)$/', '$1', get_called_class()))
				 ->values($aEntity);
			
			if ($bOnDuplicateKeyUpdate === true) {
			    
			    $oOrm->onDuplicateKeyUpdate($aEntity);
			}
			
			$iResults = $oOrm->save();
		}
		else {
			
			$iResults = $oOrm->update(preg_replace('/^.*\\\\([a-zA-Z0-9_]+)$/', '$1', get_called_class()))
				 			 ->set($aEntity)
				 			 ->where($aPrimaryKey)
							 ->save();
		}

		return $iResults;
	}

	/**
	 * You could remove this entity
	 *
	 * @access public
	 * @return object
	 */
	public function remove()
	{
		$mPrimaryKeyName = $this->_mPrimaryKeyName;
		$bInsertMode = false;
		
		if ($mPrimaryKeyName === false) {
		
			throw new Exception('['.__FILE__.' (l.'.__LINE__.'] no primary key on this table!');
		}
		else if (is_string($mPrimaryKeyName)) {
		
			$sMethodPrimaryKey = 'get_'.$this->_mPrimaryKeyNameWithoutMapping;
			$aPrimaryKey = array($mPrimaryKeyName => $this->$sMethodPrimaryKey());
		}
		else {
		
			$aPrimaryKey = array();
		
			foreach($mPrimaryKeyName as $sKey => $sPrimaryKey) {
		
				$sMethodPrimaryKey = 'get_'.$this->_mPrimaryKeyNameWithoutMapping[$sKey];
				$aPrimaryKey[$sPrimaryKey] = $this->$sMethodPrimaryKey();
			}
		}
		
		$oOrm = new Orm;
		
		$oOrm->delete(preg_replace('/^.*\\\\([a-zA-Z0-9_]+)$/', '$1', get_called_class()))
			 ->where($aPrimaryKey)
			 ->save();

		return $this;
	}

	/**
	 * magic method to create dynamically the link in the Entity
	 *
	 * @access public
	 * @param  string $sName
	 * @param  array $aArguments
	 * @return mixed
	 */
	public function __call($sName, $aArguments)
	{
		if (preg_match('/^get_/', $sName) && property_exists($this, preg_replace('/^get_/', '', $sName))) {

			$sPropertyName = preg_replace('/^get_/', '', $sName);
			return $this->$sPropertyName;
		}
		else if (preg_match('/^set_/', $sName) && property_exists($this, preg_replace('/^set_/', '', $sName))) {

			$sPropertyName = preg_replace('/^set_/', '', $sName);
			return $this->$sPropertyName = $aArguments[0];
		}
	}
}
