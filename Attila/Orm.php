<?php

/**
 * Orm Manager
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

use \Attila\lib\Db as Db;
use \Attila\lib\Db\Container as Container;
use \Attila\lib\Entity as Entity;
use \Attila\Orm\Where as Where;

/**
 * Orm Manager
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
class Orm
{
	/**
	 * default db to call
	 *
	 * @access private
	 * @var    array
	 */
	private $_sDefaultDb = '';

	/**
	 * alias to Where object of the Orm
	 *
	 * @access public
	 * @var    \Attila\Orm\Where
	 */
	public $where = null;

	/**
	 * increment to create alias
	 *
	 * @access private
	 * @var    int
	 */
	private $_iAlias = 0;

	/**
	 * select of the request
	 *
	 * @access private
	 * @var    array
	 */
	private $_aSelect = array();

	/**
	 * from of the request
	 *
	 * @access private
	 * @var    string
	 */
	private $_sFrom = '';

	/**
	 * as of from of the request
	 *
	 * @access private
	 * @var    string
	 */
	private $_sFromAs = '';

	/**
	 * join of the request
	 *
	 * @access private
	 * @var    array
	 */
	private $_aJoin = array();

	/**
	 * update of the request
	 *
	 * @access private
	 * @var    string
	 */
	private $_sUpdate = '';

	/**
	 * where of the request
	 *
	 * @access private
	 * @var    mixed
	 */
	private $_mWhere = array();

	/**
	 * having of the request
	 *
	 * @access private
	 * @var    array
	 */
	private $_aHaving = array();

	/**
	 * set of the request
	 *
	 * @access private
	 * @var    array
	 */
	private $_aSet = array();

	/**
	 * insert into of the request
	 *
	 * @access private
	 * @var    string
	 */
	private $_sInsertInto = '';

	/**
	 * values of the request
	 *
	 * @access private
	 * @var    array
	 */
	private $_aValues = array();

	/**
	 * delete of the request
	 *
	 * @access private
	 * @var    string
	 */
	private $_sDelete = '';

	/**
	 * order by of the request
	 *
	 * @access private
	 * @var    array
	 */
	private $_aOrderBy = array();

	/**
	 * group by of the request
	 *
	 * @access private
	 * @var    array
	 */
	private $_aGroupBy = array();

	/**
	 * truncate a table
	 *
	 * @access private
	 * @var    bool
	 */
	private $_sTruncate = '';

	/**
	 * limit of the result
	 *
	 * @access private
	 * @var    number
	 */
	private $_iLimit = null;

	/**
	 * limit of the result
	 *
	 * @access private
	 * @var    int
	 */
	private $_iOffset = null;
	
	/**
	 * the on duplicate key update parameter
	 * 
	 * @access private
	 * @var    array
	 */
	private $_aOnDuplicateKeyUpdate = array();

	/**
	 * constructor to create the symlink to \Attila\Orm\Where
	 *
	 * @access public
	 * @param string $sName
	 * @param string $sType
	 * @param string $sHost
	 * @param string $sUser
	 * @param string $sPassword
	 * @param string $sDbName
	 * @return \Attila\Orm
	 */
	public function __construct($sName = null, $sType = null, $sHost = null, $sUser = null, $sPassword = null, $sDbName = null) 
	{
	    if ($sName === null && $sType === null && $sHost === null && $sUser === null && $sPassword === null
	        && $sDbName === null && Db::getContainer() !== null) {

	        $this->where = new Where;
	    }
	    else if ($sName === null && $sType === null && $sHost === null && $sUser === null && $sPassword === null
	        && $sDbName === null && Db::getContainer() === null) {

	            throw new \Exception("Error: No connection define!");
	    }
	    else {
	        
	        $oContainer = new Container;
	        
	        $oContainer->setDbName($sDbName)
	                   ->setHost($sHost)
	                   ->setName($sName)
	                   ->setPassword($sPassword)
	                   ->setType($sType)
	                   ->setUser($sUser);

	        Db::setContainer($oContainer);
	        $this->setDefaultDb($sName);
            $this->where = new Where;
	    }
	}

	/**
	 * select
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function select($aSelect)
	{
		$this->_aSelect = $aSelect;
		return $this;
	}

	/**
	 * from
	 *
	 * @access public
	 * @param  string $aJoin join
	 * @return \Attila\Orm
	 *
	 * To make Join, please do that :
	 * join(array(
	 *   array(
	 *     'type' => 'left',
	 *     'table' => 'matable',
	 *     'left_field' => 'tablejoin.field1',
	 *     'right_field' => 'table2.field2'
	 *   ),
	 *   array(
	 *     'type' => 'join',
	 *     'table' => 'matable2',
	 *     'left_field' => 'tablejoin2.field1',
	 *     'right_field' => 'table3.field2'
	 *   ),
	 * ))
	 */
	public function join(array $aJoin) 
	{
		$this->_aJoin = $aJoin;
		return $this;
	}

	/**
	 * from
	 *
	 * @access public
	 * @param  string $sFrom from
	 * @return \Attila\Orm
	 */
	public function from($sFrom, $sAs = null)
	{
		$this->_sFrom = $sFrom;
		$this->_sFromAs = $sAs;
		return $this;
	}

	/**
	 * update
	 *
	 * @access public
	 * @param  string $sUpdate update
	 * @return \Attila\Orm
	 */
	public function update($sUpdate)
	{
		$this->_sUpdate = $sUpdate;
		return $this;
	}

	/**
	 * where
	 *
	 * @access public
	 * @param  mixed $mWhere where
	 * @return \Attila\Orm
	 */
	public function where($mWhere)
	{
		$this->_mWhere = $mWhere;
		return $this;
	}

	/**
	 * having
	 *
	 * @access public
	 * @param  array $aHaving having
	 * @return \Attila\Orm
	 */
	public function having($aHaving) 
	{
		$this->_aHaving = $aHaving;
		return $this;
	}

	/**
	 * Set
	 *
	 * @access public
	 * @param  array $aSet set
	 * @return \Attila\Orm
	 */
	public function set($aSet)
	{
		$this->_aSet = $aSet;
		return $this;
	}

	/**
	 * from
	 *
	 * @access public
	 * @param  string $sInsertInto insert into
	 * @return \Attila\Orm
	 */
	public function insert($sInsertInto)
	{
		$this->_sInsertInto = $sInsertInto;
		return $this;
	}

	/**
	 * where
	 *
	 * @access public
	 * @param  array $aValues values
	 * @return \Attila\Orm
	 */
	public function values($aValues)
	{
		$this->_aValues = $aValues;
		return $this;
	}

	/**
	 * delete
	 *
	 * @access public
	 * @param  string $sDelete delete from
	 * @return \Attila\Orm
	 */
	public function delete($sDelete)
	{
		$this->_sDelete = $sDelete;
		return $this;
	}

	/**
	 * order by
	 *
	 * @access public
	 * @param  array $aOrderBy order by
	 * @return \Attila\Orm
	 */
	public function orderBy($aOrderBy)
	{
		$this->_aOrderBy = $aOrderBy;
		return $this;
	}

	/**
	 * group by
	 *
	 * @access public
	 * @param  array $aGroupBy group by
	 * @return \Attila\Orm
	 */
	public function groupBy($aGroupBy)
	{
		$this->_aGroupBy = $aGroupBy;
		return $this;
	}

	/**
	 * order by
	 *
	 * @access public
	 * @param  int $iLimit number of return
	 * @param  int $iOffset offset
	 * @return \Attila\Orm
	 */
	public function limit($iLimit, $iOffset = null)
	{
		$this->_iLimit = $iLimit;
		$this->_iOffset = $iOffset;
		return $this;
	}

	/**
	 * on duplicate key update
	 *
	 * @access public
	 * @param  array $aOnDuplicateKeyUpdate on duplicate key update array
	 * @return \Attila\Orm
	 */
	public function onDuplicateKeyUpdate($aOnDuplicateKeyUpdate = array())
	{
		$this->_aOnDuplicateKeyUpdate = $aOnDuplicateKeyUpdate;
		return $this;
	}

	/**
	 * truncate
	 *
	 * @access public
	 * @param  string $sTruncate name of table
	 * @return \Attila\Orm
	 */
	public function truncate($sTruncate)
	{
		$this->_sTruncate = $sTruncate;
		return $this;
	}

	/**
	 * load
	 *
	 * @access public
	 * @param  bool $sDebug
	 * @param  string $sOtherPortail change the default portal to create the entity
	 * @return array
	 */
	public function load($bDebug = false, $sOtherPortail = null)
	{
		$sQuery = $this->_prepare();

		if ($bDebug === true) { echo $sQuery;  }

		$aResults = self::connect()->query($sQuery)->fetchAll(\PDO::FETCH_ASSOC);
		$aReturn = array();
		$i = 0;

		foreach ($aResults as $mKey => $aOneResult) {

			if (isset($aOneResult['.FOUND_ROWS()'])) {

				return $aOneResult['.FOUND_ROWS()'];
			}
			else {

				if ($this->_sFromAs) { $sPrefix = $this->_sFromAs.'.'; }
				else { $sPrefix = ''; }

				$aReturn[$i] = Entity::autoLoadEntity($this->_sFrom, $aOneResult, $sPrefix, true, $sOtherPortail);

				foreach ($this->_aJoin as $aJoin) {

					if (count($aJoin) > 0) {

						if ($aJoin['as']) { $sPrefixJoin = $aJoin['as'].'.'; }
						else { $sPrefixJoin = ''; }
 
						$sPropertieName = 'get_'.$aJoin['table'];

						if (method_exists($aReturn[$i], $sPropertieName)) {
						    
						    $sPropertieName = $aJoin['table'].'2';
						    $aReturn[$i]->$sPropertieName = Entity::autoLoadEntity($aJoin['table'], $aOneResult, $sPrefixJoin, true, $sOtherPortail);
						}
						else {
						
						    $aReturn[$i]->$aJoin['table'] = Entity::autoLoadEntity($aJoin['table'], $aOneResult, $sPrefixJoin, true, $sOtherPortail);
						}
					}
				} 

				$i++;
			}
		}

		$this->flush();

		if ($this->_mWhere instanceof Where) { $this->_mWhere->flush(); }

		/**
		 * Trigger on a model to initialize it every time you load each entity
		 */
		if (isset($aReturn[0]) && method_exists($aReturn[0], 'afterFetch')) {
		    
		    foreach ($aReturn as $iKey => $oOne) {
		    
		        $aReturn[$iKey]::afterFetch();
		    }
		}
		
		return $aReturn;
	}

	/**
	 * save
	 *
	 * @access public
	 * @return int
	 */
	public function save()
	{
		$sQuery = $this->_prepare();

		if (preg_match('/INSERT INTO/i', $sQuery)) {

			$oDb = self::connect();
			$oDb->exec($sQuery);
			$this->flush();
			return $oDb->lastInsertId();
		}
		else {

			$this->flush();

			if ($this->_mWhere instanceof Where) { $this->_mWhere->flush(); }

			return self::connect()->exec($sQuery);
		}

	}

	/**
	 * save
	 *
	 * @access public
	 * @param  string $sDefaultDb set the default db
	 * @return \Attila\Orm
	 */
	public function setDefaultDb($sDefaultDb)
	{
		$this->_sDefaultDb = $sDefaultDb;
		return $this;
	}

	/**
	 * save
	 *
	 * @access public
	 * @return string
	 */
	public function getDefaultDb($sDefaultDb)
	{
		return $this->_sDefaultDb;
	}

	/**
	 * prepare the request
	 *
	 * @access private
	 * @return string
	 */
	private function _prepare()
	{
		if (count($this->_aSelect) > 0 && $this->_aSelect[0] === 'FOUND_ROWS()' && $this->_sFrom === '') {

			$sQuery = 'SELECT FOUND_ROWS()';
		}
		else if (count($this->_aSelect) > 0 && $this->_sFrom !== '') {

			$sQuery = 'SELECT '.implode(',', $this->_aSelect).' FROM `'.$this->_sFrom.'` ';
			$sQuery = str_replace('SQL_CALC_FOUND_ROWS,', 'SQL_CALC_FOUND_ROWS ', $sQuery);

			if ($this->_sFromAs !== null) {

				$sQuery .= 'AS '.$this->_sFromAs.' ';
			}
			else {

				$sQuery .= 'AS t'.$this->_iAlias.' ';
				$this->_sFromAs = 't'.$this->_iAlias;
				$this->_iAlias++;
			}

			$sQuery .= $this->_prepareJoin();
			$sQuery .= $this->_prepareWhere();
			$sQuery .= $this->_prepareGroupBy();
			$sQuery .= $this->_prepareHaving();
			$sQuery .= $this->_prepareOrderBy();
			$sQuery .= $this->_prepareLimit();
		}
		else if ($this->_sUpdate !== '') {

			$sQuery = 'UPDATE `'.$this->_sUpdate.'` ';

			if (count($this->_aSet) > 0) {

				$sQuery .= ' SET ';

				foreach ($this->_aSet as $sKey => $sValue) {

					if ($sValue !== null) {

						$sQuery .= "`".$sKey."` = ".self::connect()->quote($sValue).",";
					}
				}
			}

			$sQuery = substr($sQuery, 0, -1);

			$sQuery .= $this->_prepareWhere();
		}
		else if ($this->_sInsertInto !== '') {

			$sQuery = 'INSERT INTO `'.$this->_sInsertInto.'` (';

			foreach ($this->_aValues as $sKey => $sValue) {

			    if (!is_array($sValue)) { $sQuery .= " `".$sKey."`,"; }
			}

			$sQuery = substr($sQuery, 0, -1);
			$sQuery .= ") VALUES (";

			foreach ($this->_aValues as $sKey => $sValue) {

				if (!is_array($sValue)) { $sQuery .= "".self::connect()->quote($sValue).","; }
			}

			$sQuery = substr($sQuery, 0, -1);
			$sQuery .= ")";
			
			if (count($this->_aOnDuplicateKeyUpdate) > 0) {
			    
			    $sQuery .= " ON DUPLICATE KEY UPDATE ";
			    
    			foreach ($this->_aOnDuplicateKeyUpdate as $sKey => $sValue) {
    			    
    			    $sQuery .= " ".$sKey." = ".self::connect()->quote($sValue).",";
    			}
    			
    			$sQuery = substr($sQuery, 0, -1);
			}
		}
		else if ($this->_sDelete !== '') {

			$sQuery = 'DELETE FROM `'.$this->_sDelete.'`';
			$sQuery .= $this->_prepareWhere();
		}
		else if ($this->_sTruncate !== '') {

			$sQuery = 'TRUNCATE `'.$this->_sTruncate.'`';
		}

		return $sQuery;
	}

	/**
	 * prepare the where
	 *
	 * @access private
	 * @return string
	 */
	private function _prepareWhere()
	{
		$sQuery = '';

		if (is_array($this->_mWhere) && count($this->_mWhere) > 0) {

			$sQuery .= ' WHERE ';

			foreach ($this->_mWhere as $sKey => $sValue) {

				$sQuery .= "".$sKey." = '".str_replace("'", "\'", $sValue)."' && ";
			}

			$sQuery = substr($sQuery, 0, -3);
		}
		else if ($this->_mWhere instanceof Where) {

			$sQuery .= ' WHERE 1 '.$this->_mWhere->get();
		}

		$sQuery = str_replace('1  &&', '', $sQuery);

		return $sQuery;
	}

	/**
	 * prepare the having
	 *
	 * @access private
	 * @return string
	 */
	private function _prepareHaving()
	{
		$sQuery = '';

		if (is_array($this->_aHaving) && count($this->_aHaving) > 0) {

			$sQuery .= ' HAVING ';

			foreach ($this->_aHaving as $sKey => $sValue) {

				$sQuery .= "".$sKey." = '".str_replace("'", "\'", $sValue)."' && ";
			}

			$sQuery = substr($sQuery, 0, -3);
		}
		else if ($this->_aHaving instanceof Where) {

			$sQuery .= ' HAVING 1 '.$this->_aHaving->get();
		}

		return $sQuery;
	}

	/**
	 * prepare the join
	 *
	 * @access private
	 * @return string
	 */
	private function _prepareJoin() 
	{
		$sQuery = '';

		if (is_array($this->_aJoin) && count($this->_aJoin) > 0) {

			foreach ($this->_aJoin as $sKey => $aValue) {

				if (isset($aValue['type']) && $aValue['type'] == 'left') {

					$sQuery .= " LEFT JOIN `".$aValue['table']."` ";
				}
				else {

					$sQuery .= " INNER JOIN `".$aValue['table']."` ";
				}

				if (isset($aValue['as']) && $aValue['as']) {

					$sQuery .= " AS ".$aValue['as']." ";
				}
				else {

					$sQuery .= " AS t".$this->_iAlias." ";
					$this->_iAlias++;
				}

				$sQuery .= " ON ".$aValue['left_field']." =  ".$aValue['right_field']." ";
			}
		}

		return $sQuery;
	}

	/**
	 * prepare the order by
	 *
	 * @access private
	 * @return string
	 */
	private function _prepareOrderBy()
	{
		$sQuery = '';

		if (is_array($this->_aOrderBy) && count($this->_aOrderBy) > 0) {

			$sQuery .= ' ORDER BY '.implode(',', $this->_aOrderBy).' ';
		}

		return $sQuery;
	}

	/**
	 * prepare the group by
	 *
	 * @access private
	 * @return string
	 */
	private function _prepareGroupBy()
	{
		$sQuery = '';

		if (is_array($this->_aGroupBy) && count($this->_aGroupBy) > 0) {

			$sQuery .= ' GROUP BY '.implode(',', $this->_aGroupBy).' ';
		}

		return $sQuery;
	}

	/**
	 * prepare the limit
	 *
	 * @access private
	 * @return string
	 */
	private function _prepareLimit()
	{
		$sQuery = '';
		$limit = (int) $this->_iLimit;
		$iOffset = $this->_iOffset;

		if($limit != 0 || $iOffset > 0) { $sQuery .= ' LIMIT '; }

		if($iOffset > 0) { $sQuery .= $iOffset.', '.$limit; }
		else if($limit != 0) { $sQuery .= $limit.' '; }

		return $sQuery;
	}

	/**
	 * get the query construct with the Orm
	 *
	 * @access public
	 * @return string
	 */
	public function getQuery()
	{
		return $this->_prepare();
	}

	/**
	 * flush the ORM
	 *
	 * @access public
	 * @return string
	 */
	public function flush()
	{
		$this->where = null;
		$this->_aSelect = array();
		$this->_sFrom = '';
		$this->_sFromAs = '';
		$this->_aJoin = array();
		$this->_sUpdate = '';
		$this->_mWhere = array();
		$this->_aSet = array();
		$this->_sInsertInto = '';
		$this->_aValues = array();
		$this->_sDelete = '';
		$this->_aOrderBy = array();
		$this->_aGroupBy = array();
		$this->_iLimit = null;
		return $this;
	}

	/**
	 * connect on DB
	 *
	 * @access public
	 * @return string
	 */
	public static function connect()
	{
	    if (Db::getContainer() instanceof Container) { return Db::connect(Db::getContainer()); }
	    else { echo "Error of connection"; }
	}

	/**
	 * begin a transaction
	 *
	 * @access public
	 * @return bool
	 */
	public function begin()
	{
	    return self::connect()->beginTransaction();
	}

	/**
	 * commit a transaction
	 *
	 * @access public
	 * @return bool
	 */
	public function commit()
	{
	    return self::connect()->commit();
	}

	/**
	 * rollback a transaction
	 *
	 * @access public
	 * @return bool
	 */
	public function rollback()
	{
	    return self::connect()->rollBack();
	}
}
