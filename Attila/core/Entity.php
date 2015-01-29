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

use \Attila\lib\Entity as LibEntity;
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
     * Cache to know if a model was initialize or not because we must initialize it just one time by script
     * 
     * @access private
     * @var    array
     */
    private static $_aInitialize = array();
	
    /**
     * Array to stock all join
     * 
     * @access private
     * @var    array
     */
    private $_aJoins = array();
	
    /**
     * Array to stock all foreign key
     * 
     * @access private
     * @var    array
     */
    private $_aForeignKey = array();
    
    /**
     * Cascade action on the foreign key
     * @var int
     */
    const CASCADE = 1;

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
		
		/**
		 * Trigger on a model to initialize it. You could fill entity with it.
		 */
		if (method_exists(get_called_class(), 'initialize')) {
		    
		    if (!isset(self::$_aInitialize[get_called_class()])) { 
		        
		        static::initialize();
		        self::$_aInitialize[get_called_class()] = true;
		    }
		}
		
		/**
		 * Trigger on a model to initialize it every time you construct it
		 */
		if (method_exists(get_called_class(), 'onConstruct')) { static::onConstruct(); }
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
	    /**
	     * Trigger on an entity to initialize it before the save
	     */
	    if (method_exists(get_called_class(), 'beforeSave')) { static::beforeSave(); }
	    
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
		
		/**
		 * check if the virtual foreign key in this model is respected
		 */
		if (count($this->_aForeignKey) > 0) {

		    foreach ($this->_aForeignKey as $sName => $aForeignKey) {

		        if ($aForeignKey['has_one'] == 1) {
		        
		            $sMethodPrimaryKey = 'get_'.$aForeignKey['foreign_key'];
				    $mFIeld = $this->$sMethodPrimaryKey();
				    
				    if ($mFIeld) {
				        
				        $oOrm = new Orm;
				        	
				        $iResults = $oOrm->select(array('*'))
				                         ->from($aForeignKey['entity_join_name']);
				        
				        $oWhere = new Where;
				        
				        $oWhere->whereEqual($aForeignKey['primary_key_name'], $mFIeld);

				        $aResults = $oOrm->where($oWhere)
				                         ->load();
			
			            if (count($aResults) < 1) {
			            	
			                if ($aForeignKey['foreign_key_options']['message']) {
			                    
			                    throw new \Exception($aForeignKey['foreign_key_options']['message']);
			                }
			                else {
			                    
			                    throw new \Exception('Foreign Key not respected!');
			                }
			            }
				    }
		        }
    		}
		}
		
		/**
		 * check if the virtual foreign key in the others models are respected
		 */
		$oReflectionClass  = new \ReflectionClass(get_called_class());
		$oReflectionProperties = $oReflectionClass->getProperties();
		
		foreach($oReflectionProperties as $mKey => $aOne) {

		    $sCommentPhpDoc = $aOne->getDocComment();
		    
		    if (strstr($sCommentPhpDoc, '@join')) {
		        
		        $sClassName = $aOne->class;
		        $oClass = new $sClassName;
		        
		        if (count($oClass->getForeignKey()) > 0) {
		        
		            foreach ($oClass->getForeignKey() as $sName => $aForeignKey) {
		        
		                if ($aForeignKey['has_many'] == 1) {
		        
		                    $sMethodPrimaryKey = 'get_'.$aForeignKey['foreign_key'];
		                    $mFIeld = $this->$sMethodPrimaryKey();
		        
		                    if ($mFIeld) {
		        
		                        $oOrm = new Orm;
		                         
		                        $iResults = $oOrm->select(array('*'))
		                        ->from($aForeignKey['entity_join_name']);
		        
		                        $oWhere = new Where;
		        
		                        $oWhere->whereEqual($aForeignKey['primary_key_name'], $mFIeld);
		        
		                        $aResults = $oOrm->where($oWhere)
		                                         ->load();
		                        	
		                        if (count($aResults) < 1) {
		        
		                            if ($aForeignKey['foreign_key_options']['message']) {
		                                 
		                                throw new \Exception($aForeignKey['foreign_key_options']['message']);
		                            }
		                            else {
		                                 
		                                throw new \Exception('Foreign Key not respected!');
		                            }
		                        }
		                    }
		                }
		            }
		        }
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
		
		/**
		 * check if the virtual foreign key in this model is respected
		 */
		if (count($this->_aForeignKey) > 0) {

		    foreach ($this->_aForeignKey as $sName => $aForeignKey) {

		        if ($aForeignKey['has_one'] == 1 && isset($aForeignKey['foreign_key_options']['action'])
		            && $aForeignKey['foreign_key_options']['action'] == self::CASCADE) {
		        
		            $sMethodPrimaryKey = 'get_'.$aForeignKey['foreign_key'];
				    $mFIeld = $this->$sMethodPrimaryKey();
				    
				    if ($mFIeld) {
				        
				        $oOrm = new Orm;
				        	
				        $iResults = $oOrm->select(array('*'))
				                         ->from($aForeignKey['entity_join_name']);
				        
				        $oWhere = new Where;
				        
				        $oWhere->whereEqual($aForeignKey['primary_key_name'], $mFIeld);

				        $aResults = $oOrm->where($oWhere)
				                         ->load();
			
			            if (count($aResults) > 0) {
			            	
			                $oOrm = new Orm;
				        	
    				        $oOrm->delete($aForeignKey['entity_join_name'])
			                     ->where($oWhere)
			                     ->save();
			            }
				    }
		        }
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
		else if (preg_match('/^get_/', $sName) && $this->_aJoins[preg_replace('/^get_/', '', $sName)]) {

			return $this->_aJoins[preg_replace('/^get_/', '', $sName)]();
		}
	}

	/**
	 * create a join in one to many
	 *
	 * @access public
	 * @param string $sPrimaryKeyName
	 * @param string $sEntityJoinName
	 * @param string $sForeignKeyName
	 * @param string $sNamespaceEntity
	 * @param array $aOptions
	 * @return array
	 */
	public function hasMany($sPrimaryKeyName, $sEntityJoinName, $sForeignKeyName, $sNamespaceEntity, array $aOptions = array())
	{
	    $this->_aJoins[$sEntityJoinName] = function($mParameters = null) use ($sPrimaryKeyName, $sEntityJoinName, $sForeignKeyName, $sNamespaceEntity)
	    {
	        if (!isset($this->$sEntityJoinName)) {
	        
	            $oOrm = new Orm;
	        
	            $oOrm->select(array('*'))
	                 ->from($sEntityJoinName);

	            if ($mParameters) { 
	                
	                $aWhere[$sForeignKeyName] = $mParameters; 
	            }
	            else { 
	                
	                $sMethodName = 'get_'.$sPrimaryKeyName;
	                $aWhere[$sForeignKeyName] = $this->$sMethodName(); 
	            }
	            	
	            	
	            $this->$sEntityJoinName = $oOrm->where($aWhere)
	                                           ->load(false, $sNamespaceEntity.'\\');
	        }
	        
	        return $this->$sEntityJoinName;
	    };
	    
	    if (isset($aOptions['foreignKey']) && !isset($this->_aForeignKey[$sEntityJoinName])) {
	    
	        $this->_aForeignKey[$sEntityJoinName] = array(
	            'primary_key' => $sPrimaryKeyName, 
	            'entity_join_name' => $sEntityJoinName, 
	            'foreign_key_name' => $sForeignKeyName,
	            'foreign_key_options' => $aOptions['foreignKey'],
	            'has_one' => 0
	        );
	    }
	}

	/**
	 * create a join in one to one
	 *
	 * @access public
	 * @param string $sPrimaryKeyName
	 * @param string $sEntityJoinName
	 * @param string $sForeignKeyName
	 * @param string $sNamespaceEntity
	 * @param array $aOptions
	 * @return object
	 */
	public function hasOne($sPrimaryKeyName, $sEntityJoinName, $sForeignKeyName, $sNamespaceEntity, array $aOptions = array())
	{
	    $this->_aJoins[$sEntityJoinName] = function($mParameters = null) use ($sPrimaryKeyName, $sEntityJoinName, $sForeignKeyName, $sNamespaceEntity)
	    {
	        if (!isset($this->$sEntityJoinName)) {
	        
	            $oOrm = new Orm;
	        
	            $oOrm->select(array('*'))
	                 ->from($sEntityJoinName);

	            if ($mParameters) { 
	                
	                $aWhere[$sForeignKeyName] = $mParameters; 
	            }
	            else { 
	                
	                $sMethodName = 'get_'.$sPrimaryKeyName;
	                $aWhere[$sForeignKeyName] = $this->$sMethodName(); 
	            }
	            		            	
	            $this->$sEntityJoinName = $oOrm->where($aWhere)
	                                           ->load(false, $sNamespaceEntity.'\\');
	        }

	        return $this->{$sEntityJoinName}[0];
	    };
	    
	    if (isset($aOptions['foreignKey']) && !isset($this->_aForeignKey[$sEntityJoinName])) {
	    
	        $this->_aForeignKey[$sEntityJoinName] = array(
	            'foreign_key' => $sPrimaryKeyName, 
	            'entity_join_name' => $sEntityJoinName, 
	            'primary_key_name' => $sForeignKeyName,
	            'foreign_key_options' => $aOptions['foreignKey'],
	            'has_one' => 1
	        );
	    }
	}

	/**
	 * create a join in one to one
	 *
	 * @access public
	 * @param string $sEntityJoinName
	 * @param mixed $mParameters
	 * @return mixed
	 */
	public function getRelated($sEntityJoinName, $mParameters)
	{
	    return $this->_aJoins[$sEntityJoinName]($mParameters);
	}

	/**
	 * get foreign key declared
	 *
	 * @access public
	 * @return array
	 */
	public function getForeignKey()
	{
	    return $this->_aForeignKey;
	}
}
