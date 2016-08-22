<?php

/**
 * Model Manager
 *
 * @category  	Attila
 * @package   	Attila\core
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/attila/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 2.0.0
 * @filesource	https://github.com/las93/attila
 * @link      	https://github.com/las93
 * @since     	2.0.0
 */
namespace Attila\core;

use \Attila\Orm as Orm;
use \Attila\lib\Entity as LibEntity;
use \Attila\Orm\Where as Where;

/**
 * Model Manager
 *
 * @category  	Attila
 * @package   	Attila\core
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/attila/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 2.0.0
 * @filesource	https://github.com/las93/attila
 * @link      	https://github.com/las93
 * @since     	2.0.0
 */
abstract class Model extends Mother
{
    /**
     * Callback to filter the results
     * 
     * @access private
     * @var    callable
     */
    private $_cFilterCallback;
	
    /**
     * Array to stock all join
     * 
     * @access private
     * @var    array
     */
    private $_aHasOne = array();
	
    /**
     * Array to stock all join
     * 
     * @access private
     * @var    array
     */
    private $_aHasMany = array();
	
    /**
     * Cache to know if a model was initialize or not because we must initialize it just one time by script
     * 
     * @access private
     * @var    array
     */
    private static $_aInitialize = array();
    
	/**
	 * Constructor
	 *
	 * @access public
	 * @param  object $oDbConfig
	 * @return object
	 */
	public function __construct($oDbConfig = null)
	{
		$aClass = explode('\\', get_called_class());
		$sClassName = $aClass[count($aClass) - 1];
		$sNamespaceName = str_replace('\\'.$aClass[count($aClass) - 1], '', get_called_class());

		if (isset($sClassName)) {

			$sNamespaceBaseName = str_replace('\Model', '', $sNamespaceName);
			$defaultEntity = $sNamespaceBaseName.'\Entity\\'.$sClassName;

			$this->_sTableName = $sClassName;

			$this->entity = function() use ($defaultEntity) { return new $defaultEntity; };

			$this->orm = function() use ($oDbConfig)
			{
			    if ($oDbConfig === null) {
			        
			        $oDbConfig = json_decode(file_get_contents(__DIR__ . '/../Db.conf'))->configuration;
			    }
			    
			    return new Orm($oDbConfig->db, $oDbConfig->type, $oDbConfig->host, $oDbConfig->user, $oDbConfig->password, 
			        $oDbConfig->db);
			};
			
			$this->where = function() { return new Where; };
		}
		
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
	 * classic method to find an entity
	 *
	 * @access public
	 * @param  object $oEntityCriteria
	 * @return array
	 */
	public function find($oEntityCriteria)
	{
		$this->_checkEntity($oEntityCriteria);
		$aEntity = get_object_vars(LibEntity::getRealEntity($oEntityCriteria));

		$sPrimaryKeyName = LibEntity::getPrimaryKeyName($oEntityCriteria);

		$aResults = $this->orm
			 			 ->select(array('*'))
			 			 ->from($this->_sTableName)
			 			 ->where(array($sPrimaryKeyName => $aEntity[$sPrimaryKeyName]))
						 ->load();

		if ($aResults) { return $aResults[0]; }

		if ($this->_isFilter()) {
		    
		    foreach ($aResults as $iKey => $oValue) {
		        
		        $aResults[$iKey] = $this->_applyFilter($oValue);
		    }
		}

		return $aResults;
	}

	/**
	 * magic method to create dynamically each methods
	 *
	 * @access public
	 * @param  string $sName
	 * @param  array $aArguments
	 * @return mixed
	 */
	public function __call($sName, $aArguments) 
	{
		/**
		 * @example	$oModel->findOneByid(12);
		 * 			$oModel->findByfirstname('george');
		 *
		 * @example	$oModel->findOneOrderByid();
		 * 			$oModel->findOrderByfirstname();
		 * 			$oModel->findOneOrderByidDesc();
		 * 			$oModel->findOrderByfirstnameDesc();
		 */

        if (preg_match('/^findOneBy([a-zA-Z_]+)$/', $sName, $aMatchs)) {
        	
        	$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
        	
        	$aResults = $this->orm
        					 ->select(array('*'))
        					 ->from($this->_sTableName)
        					 ->where(array($aMatchs[1] => $aArguments[0]))
        					 ->limit(1)
        					 ->load(false, $sEntityNamespace);

        	if (isset($aResults[0]) && $this->_isFilter()) { $aResults[0] = $this->_applyFilter($aResults[0]); }
        	
        	if (isset($aResults[0])) { return $aResults[0]; }
        	else { return array(); }
        }
        else if (preg_match('/^findBy([a-zA-Z_]+)$/', $sName, $aMatchs)) {

        	$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
        	 
        	$aResults = $this->orm
        					 ->select(array('*'))
        					 ->from($this->_sTableName)
        					 ->where(array($aMatchs[1] => $aArguments[0]))
        					 ->load(false, $sEntityNamespace);

        	if ($this->_isFilter()) {
        	
        	    foreach ($aResults as $iKey => $oValue) {
        	
        	        $aResults[$iKey] = $this->_applyFilter($oValue);
        	    }
        	}
        	
        	return $aResults;
        }
        else if (preg_match('/^findOneOrderBy([a-zA-Z_]+)$/', $sName, $aMatchs)) {

        	$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
        	 
        	$aMatchs[1] = preg_replace('/^(.+)(Desc)$/', '$1 $2', $aMatchs[1]);
        	$aMatchs[1] = preg_replace('/^(.+)(Asc)$/', '$1 $2', $aMatchs[1]);

        	$aResults = $this->orm
        					 ->select(array('*'))
        					 ->from($this->_sTableName)
        					 ->orderBy(array($aMatchs[1]))
        					 ->limit(1)
        					 ->load(false, $sEntityNamespace);

        	if (isset($aResults[0]) && $this->_isFilter()) { $aResults[0] = $this->_applyFilter($aResults[0]); }
        	
        	if (isset($aResults[0])) { return $aResults[0]; }
        	else { return array(); }
        }
        else if (preg_match('/^findOrderBy([a-zA-Z_]+)$/', $sName, $aMatchs)) {

        	$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
        	 
        	$aMatchs[1] = preg_replace('/^(.+)(Desc)$/', '$1 $2', $aMatchs[1]);
        	$aMatchs[1] = preg_replace('/^(.+)(Asc)$/', '$1 $2', $aMatchs[1]);

        	$aResults = $this->orm
        					 ->select(array('*'))
        					 ->from($this->_sTableName)
        					 ->orderBy(array($aMatchs[1]))
        					 ->load(false, $sEntityNamespace);

        	if ($this->_isFilter()) {
        	
        	    foreach ($aResults as $iKey => $oValue) {
        	
        	        $aResults[$iKey] = $this->_applyFilter($oValue);
        	    }
        	}
        	
        	return $aResults;
        }
    }


    /**
     * get all line of the tables
     *
     * @access public
     * @return array
     */
    public function findAll() 
    {
    	$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
    	
    	$aResults = $this->orm
    					 ->select(array('*'))
    					 ->from($this->_sTableName)
    					 ->load(false, $sEntityNamespace);
    	
    	if ($this->_isFilter()) {
    	     
    	    foreach ($aResults as $iKey => $oValue) {
    	         
    	        $aResults[$iKey] = $this->_applyFilter($oValue);
    	    }
    	}

    	return $aResults;
    }

    /**
     * return an entity that it is found with the arguments
     *
     * @access public
     * @param  array $aArguments
     * @return object
     *
     * @example	$oModel->findOneBy(array('id' => 12);
     */
    public function findOneBy(array $aArguments)
    {
    	$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
    	
    	$aResults = $this->orm
    					 ->select(array('*'))
    					 ->from($this->_sTableName)
    					 ->where($aArguments)
    					 ->limit(1)
    					 ->load(false, $sEntityNamespace);

    	if (isset($aResults[0]) && $this->_isFilter()) { $aResults[0] = $this->_applyFilter($aResults[0]); }
    	
    	if (isset($aResults[0])) { return $aResults[0]; }
    	else { return false; }
    }

    /**
     * return list of entities that they are found with the arguments
     *
     * @access public
     * @param  array $aArguments
     * @return array
     *
     * @example	$oModel->findBy(array('id' => 12);
     */
    public function findBy(array $aArguments)
    {
    	$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
    	
    	$aResults = $this->orm
    					 ->select(array('*'))
    					 ->from($this->_sTableName)
    					 ->where($aArguments)
    					 ->load(false, $sEntityNamespace);
    	
    	if ($this->_isFilter()) {
    	     
    	    foreach ($aResults as $iKey => $oValue) {
    	         
    	        $aResults[$iKey] = $this->_applyFilter($oValue);
    	    }
    	}

    	return $aResults;
    }

    /**
     * return an entity that it is found with the arguments
     *
     * @access public
     * @param  array $aArguments
     * @return object
     *
     * @example	$oModel->findOneBy(array('id' => 12);
     */
    public function findOneOrderBy(array $aArguments)
    {
    	$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
    	 
    	$aResults = $this->orm
    					 ->select(array('*'))
    					 ->from($this->_sTableName)
    					 ->orderBy($aArguments)
    					 ->limit(1)
    					 ->load(false, $sEntityNamespace);

    	if (isset($aResults[0]) && $this->_isFilter()) { $aResults[0] = $this->_applyFilter($aResults[0]); }

    	return $aResults[0];
    }

    /**
     * return list of entities that they are found with the arguments
     *
     * @access public
     * @param  array $aArguments
     * @return array
     *
     * @example	$oModel->findOrderBy(array('id DESC');
     */
    public function findOrderBy(array $aArguments)
    {
    	$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
    	
    	$aResults = $this->orm
    					 ->select(array('*'))
    					 ->from($this->_sTableName)
    					 ->orderBy($aArguments)
    					 ->load(false, $sEntityNamespace);
    	
    	if ($this->_isFilter()) {
    	     
    	    foreach ($aResults as $iKey => $oValue) {
    	         
    	        $aResults[$iKey] = $this->_applyFilter($oValue);
    	    }
    	}

    	return $aResults;
    }

	/**
	 * classic method to get a list of entities
	 *
	 * @access public
	 * @param  object $oEntityCriteria
	 * @return array
	 */
	public function get($oEntityCriteria = null)
	{
		$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
		
		if ($oEntityCriteria !== null) {

			$this->_checkEntity($oEntityCriteria);
			$aEntityTmp = get_object_vars(LibEntity::getRealEntity($oEntityCriteria));
			$aEntity = array();

			foreach ($aEntityTmp as $sKey => $mField) {

				if ($mField !== null) {

					$aEntity[$sKey] = $mField;
				}
			}
		}
		else {

			$aEntity = array();
		}

		$aResults = $this->orm
			 			 ->select(array('*'))
			 			 ->from($this->_sTableName)
			 			 ->where($aEntity)
    					 ->load(false, $sEntityNamespace);
    	
    	if ($this->_isFilter()) {
    	     
    	    foreach ($aResults as $iKey => $oValue) {
    	         
    	        $aResults[$iKey] = $this->_applyFilter($oValue);
    	    }
    	}

		return $aResults;
	}

	/**
	 * classic method to get a list of entities
	 *
	 * @access public
	 * @param  object $oEntityCriteria
	 * @return int
	 */
	public function update($oEntityCriteria)
	{
		$this->_checkEntity($oEntityCriteria);

		if ($oEntityCriteria !== null) {

			$aEntity = get_object_vars(LibEntity::getRealEntity($oEntityCriteria));
		}
		else {

			$aEntity = array();
		}

		$sPrimaryKeyName = LibEntity::getPrimaryKeyName($oEntityCriteria);

		if (is_array($sPrimaryKeyName)) {

			$aPrimaryKeys = array();

			foreach ($sPrimaryKeyName as $sOne) {

				$aPrimaryKeys[$sOne] = $aEntity[$sOne];
			}

			$iResult = $this->orm
					    	->update($this->_sTableName)
							->set($aEntity)
							->where($aPrimaryKeys)
							->save();
		}
		else {

			$iResult = $this->orm
				 			->update($this->_sTableName)
				 			->set($aEntity)
				 			->where(array($sPrimaryKeyName => $aEntity[$sPrimaryKeyName]))
							->save();
		}

		return $iResult;
	}

	/**
	 * classic method to get a list of entities
	 *
	 * @access public
	 * @param  object $oEntityCriteria
	 * @return int
	 */
	public function insert($oEntity)
	{
		$this->_checkEntity($oEntity);

		$iResult = $this->orm
						->insert($this->_sTableName)
						->values(LibEntity::getAllEntity($oEntity))
						->save();

		return $iResult;
	}

	/**
	 * get last row
	 *
	 * @access public
	 * @return object
	 */

	public function getLastRow()
	{
		$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
		
		$aResults = $this->orm
					     ->select(array('*'))
			 		     ->from($this->_sTableName)
			 		     ->orderBy(array(LibEntity::getPrimaryKeyName($this->entity) => 'DESC'))
			 		     ->limit(1)
    				     ->load(false, $sEntityNamespace);

    	if (isset($aResults[0]) && $this->_isFilter()) { $aResults[0] = $this->_applyFilter($aResults[0]); }

		return $aResults[0];
	}

	/**
	 * save Entity and get it
	 *
	 * @access public
	 * @param  object $oEntity
	 * @return int|object
	 */
	public function insertAndGet($oEntity)
	{
		$iResult = $this->insert($oEntity);
		
		if ($iResult) { return $this->getLastRow(); }

		return $iResult;
	}

	/**
	 * update Entity and get it
	 *
	 * @access public
	 * @param  object $oEntityCriteria
	 * @return mixed
	 */
	public function updateAndGet($oEntity)
	{
		$sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
		
		$mResult = $this->update($oEntity);

		if ($result) {
			
		    $aEntity = get_object_vars(LibEntity::getRealEntity($oEntity));
			$mPrimaryKey = LibEntity::getPrimaryKeyName($aEntity);
			
			$mResult = $this->orm
					        ->select(array('*'))
			 		        ->from($this->_sTableName)
			 		        ->where(array($mPrimaryKey => $aEntity[$mPrimaryKey]))
    				        ->load(false, $sEntityNamespace);
    	
        	if ($this->_isFilter()) {
        	     
        	    foreach ($mResult as $iKey => $oValue) {
        	         
        	        $mResult[$iKey] = $this->_applyFilter($oValue);
        	    }
        	}
		}

		return $mResult;
	}

	/**
	 * classic method to delete one entities
	 *
	 * @access public
	 * @param  object $oEntityCriteria
	 * @return object
	 */
	public function delete($oEntityCriteria)
	{
		$this->_checkEntity($oEntityCriteria);

		$aEntity = LibEntity::getAllEntity($oEntityCriteria, true);

		$this->orm
		 	 ->delete($this->_sTableName)
		 	 ->where($aEntity)
		 	 ->save();
		
		return $this;
	}

	/**
	 * classic method to truncate a table
	 *
	 * @access public
	 * @return object
	 */
	public function truncate()
	{    
		$aClass = explode('\\', get_called_class());
		$sClassName = $aClass[count($aClass) - 1];
	
	    $this->orm
	         ->truncate($sClassName)
	         ->save();
		
		return $this;
	}

	/**
	 * add a filter on the results
	 *
	 * @access public
	 * @param  callable $cCallback callback to do the filter on the results
	 * @return object
	 */
	public function filter(callable $cCallback)
	{    
	    $this->_cFilterCallback = $cCallback;
	    return $this;
	}

	/**
	 * check if the entity passed is good
	 *
	 * @access private
	 * @param  object $oEntityCriteria
	 * @return void
	 */
	private function _checkEntity($oEntityCriteria)
	{
		$sClassName = get_called_class();
		$sClassName = str_replace('Model', 'Entity', $sClassName);

		if (!is_object($oEntityCriteria) || !$oEntityCriteria instanceof $sClassName) {

			throw new \Exception('You must passed '.$sClassName.' like Entity!');
		}
	}

	/**
	 * apply the filter on the result and add the join
	 *
	 * @access private
	 * @param  object $oResult result to apply filter
	 * @return object
	 */
	private function _applyFilter($oResult)
	{    
	    $oResult = $this->_cFilterCallback($oResult);
	    
	    foreach($this->_aHasOne as $sKey => $aParam) {
	        
	        $oResult->hasOne($aParam[0], $aParam[1], $aParam[2], $aParam[3], $aParam[4]);
	    }
	    
	    foreach($this->_aHasMany as $sKey => $aParam) {
	        
	        $oResult->hasMany($aParam[0], $aParam[1], $aParam[2], $aParam[3], $aParam[4]);
	    }
	    
	    return $this->_cFilterCallback($oResults);
	}

	/**
	 * apply the filter on the results
	 *
	 * @access private
	 * @param  object $oResults result to apply filter
	 * @return object
	 */
	private function _isFilter()
	{    
	    if (is_callable($this->_cFilterCallback)) { return true; }
	    else { return false; }
	}

	/**
	 * create a join in many to one
	 *
	 * @access public
	 * @param string $sPrimaryKeyName
	 * @param string $sEntityJoinName
	 * @param string $sForeignKeyName
	 * @param string $sNamespaceEntity
	 * @param array $aOptions
	 * @return object
	 */
	public function belongsTo($sPrimaryKeyName, $sEntityJoinName, $sForeignKeyName, $sNamespaceEntity, array $aOptions = array())
	{
	    $this->_aHasOne[$sEntityJoinName] = array($sPrimaryKeyName, $sEntityJoinName, $sForeignKeyName, $sNamespaceEntity);
	}

	/**
	 * create a join in many to many
	 *
	 * @access public
	 * @param string $sPrimaryKeyName
	 * @param string $sEntityJoinName
	 * @param string $sForeignKeyName
	 * @param string $sNamespaceEntity
	 * @param unknown $sManyToManyKeyName
	 * @param unknown $sManyToManyTableName
	 * @param array $aOptions
	 * @return object
	 */
	public function hasManyToMany($sPrimaryKeyName, $sEntityJoinName, $sForeignKeyName, $sNamespaceEntity, $sManyToManyKeyName, $sManyToManyTableName, array $aOptions = array())
	{
	    $this->_ahasMany[$sEntityJoinName] = function($mParameters = null) use ($sPrimaryKeyName, $sEntityJoinName, $sForeignKeyName, $sManyToManyKeyName, $sManyToManyTableName, $sNamespaceEntity)
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
	        
	        $aResults = array();
	        
	        foreach ($this->$sEntityJoinName as $iKey => $oOne) {
	            
	            $oOrm = new Orm;
	            
	            $oOrm->select(array('*'))
	                 ->from($sManyToManyTableName);
	             
	            if ($mParameters) {
	            
	                $aWhere[$sManyToManyKeyName] = $mParameters;
	            }
	            else {
	            
	                $sMethodName = 'get_'.$sManyToManyKeyName;
	                $aWhere[$sManyToManyKeyName] = $this->$sMethodName();
	            }
	             
	             
	            $aResults[] = $oOrm->where($aWhere)
	                               ->load(false, $sNamespaceEntity.'\\');
	        }
	         
	        return $aResults;
	    };
	}

	/**
	 * count
	 *
	 * @access public
	 * @param  array $aCriterias
	 * @return int
	 */
	public function count(array $aCriterias = array())
	{
		if (count($aCriterias) < 1) {
		    
		    return count($this->findAll());
		}
		else if (isset($aCriterias['distinct'])) {
		
		    $sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
		     
		    $aResults = $this->orm
		                     ->select(array('DISTINCT '.$aCriterias['distinct']))
		                     ->from($this->_sTableName)
		                     ->load(false, $sEntityNamespace);
		    
		    return count($aResults);
		}
		else if (isset($aCriterias['group'])) {
		
		    $sEntityNamespace = preg_replace('/^(.*)Model\\\\.+$/', '$1Entity\\', get_called_class());
		     
		    $aResults = $this->orm
		                     ->select(array('COUNT(*) AS nb, '.$aCriterias['group']))
		                     ->from($this->_sTableName)
		                     ->groupBy(array($aCriterias['group']))
		                     ->load(false, $sEntityNamespace);
		    
		    $aFinalResults = array();
		    
		    foreach ($aResults as $oOne) {
		        
		        $aFinalResults[$oOne->{'get_'.$aCriterias['group']}()] = $oOne->nb;
		    }
		    
		    return $aFinalResults;
		}
	}
}
