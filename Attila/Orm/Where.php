<?php

/**
 * Orm Manager - Where part
 *
 * @category  	Attila
 * @package   	Attila\Orm
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/attila/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/attila
 * @link      	https://github.com/las93
 * @since     	1.0.0
 */
namespace Attila\Orm;

/**
 * Orm Manager - Where part
 *
 * @category  	Attila
 * @package   	Attila\Orm
 * @author    	Judicaël Paquet <judicael.paquet@gmail.com>
 * @copyright 	Copyright (c) 2013-2014 PAQUET Judicaël FR Inc. (https://github.com/las93)
 * @license   	https://github.com/las93/attila/blob/master/LICENSE.md Tout droit réservé à PAQUET Judicaël
 * @version   	Release: 1.0.0
 * @filesource	https://github.com/las93/attila
 * @link      	https://github.com/las93
 * @since     	1.0.0
 */
class Where 
{
	/**
	 * select of the request
	 *
	 * @access private
	 * @var    array
	 */
	private $_aElement = array();

	/**
	 * add an element && a > f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereSoundex($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, 'SOUNDEX', $mValue);
		return $this;
	}

	/**
	 * add an element && a > f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function orWhereSoundex($sElement, $mValue) 
	{
		$this->_aElement[] = array('||', $sElement, 'SOUNDEX', $mValue);
		return $this;
	}

	/**
	 * add an element && a > f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereSup($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, '>', $mValue);
		return $this;
	}

	/**
	 * add an element && a > f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereSupOrEqual($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, '>=', $mValue);
		return $this;
	}

	/**
	 * add an element && a > f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function andWhereSup($sElement, $mValue) 
	{
		return $this->whereSup($sElement, $mValue);
	}

	/**
	 * add an element && a > f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function andWhereSupOrEqual($sElement, $mValue) 
	{
		return $this->whereSupOrEqual($sElement, $mValue);
	}

	/**
	 * add an element || a > f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function orWhereSup($sElement, $mValue) 
	{
		$this->_aElement[] = array('||', $sElement, '>', $mValue);
		return $this;
	}

	/**
	 * add an element && a < f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereInf($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, '<', $mValue);
		return $this;
	}

	/**
	 * add an element && a < f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereInfOrEqual($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, '<=', $mValue);
		return $this;
	}

	/**
	 * add an element && a < f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function andWhereInf($sElement, $mValue) 
	{
		return $this->whereInf($sElement, $mValue);
	}

	/**
	 * add an element && a < f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function andWhereInfOrEqual($sElement, $mValue) 
	{
		return $this->whereInfOrEqual($sElement, $mValue);
	}

	/**
	 * add an element || a < f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function orWhereInf($sElement, $mValue) 
	{
		$this->_aElement[] = array('||', $sElement, '<', $mValue);
		return $this;
	}

	/**
	 * add an element && a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereEqual($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, '=', $mValue);
		return $this;
	}

	/**
	 * add an element && a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereLike($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, 'LIKE', '%'.$mValue.'%');
		return $this;
	}

	/**
	 * add an element && a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereLikeStartBy($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, 'LIKE', ''.$mValue.'%');
		return $this;
	}

	/**
	 * add an element && a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereRegexp($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, 'REGEXP', $mValue);
		return $this;
	}

	/**
	 * add an element && a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function andWhereEqual($sElement, $mValue) 
	{
		return $this->whereEqual($sElement, $mValue);
	}

	/**
	 * add an element && a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function andWhereLike($sElement, $mValue) 
	{
		return $this->whereLike($sElement, $mValue);
	}

	/**
	 * add an element && a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function andWhereRegexp($sElement, $mValue) 
	{
		return $this->whereRegexp($sElement, $mValue);
	}

	/**
	 * add an element || a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function orWhereEqual($sElement, $mValue) 
	{
		$this->_aElement[] = array('||', $sElement, '=', $mValue);
		return $this;
	}

	/**
	 * add an element || a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function orWhereLike($sElement, $mValue) 
	{
		$this->_aElement[] = array('||', $sElement, 'LIKE', '%'.$mValue.'%');
		return $this;
	}

	/**
	 * add an element || a = f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function orWhereRegexp($sElement, $mValue) 
	{
		$this->_aElement[] = array('||', $sElement, 'REGEXP', $mValue);
		return $this;
	}

	/**
	 * add an element && a != f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereNotEqual($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, '!=', $mValue);
		return $this;
	}

	/**
	 * add an element && a != f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function andWhereNotEqual($sElement, $mValue) 
	{
		return $this->whereNotEqual($sElement, $mValue);
	}

	/**
	 * add an element || a != f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function orWhereNotEqual($sElement, $mValue) 
	{
		$this->_aElement[] = array('||', $sElement, '!=', $mValue);
		return $this;
	}

	/**
	 * add an element && a != f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereNotLike($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, 'NOT LIKE', '%'.$mValue.'%');
		return $this;
	}

	/**
	 * add an element && a != f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function andWhereNotLike($sElement, $mValue) 
	{
		return $this->whereNotLike($sElement, $mValue);
	}

	/**
	 * add an element || a != f
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function orWhereNotLike($sElement, $mValue) 
	{
		$this->_aElement[] = array('||', $sElement, 'NOT LIKE', '%'.$mValue.'%');
		return $this;
	}

	/**
	 * where in
	 *
	 * @access public
	 * @param  array $aSelect select
	 * @return \Attila\Orm
	 */
	public function whereIn($sElement, $mValue) 
	{
		$this->_aElement[] = array('&&', $sElement, 'IN', $mValue);
		return $this;
	}

	/**
	 * where in where
	 *
	 * @access public
	 * @param  \Attila\Orm\Where $oWhere increment where
	 * @return \Attila\Orm
	 */
	public function addWhere(\Attila\Orm\Where $oWhere) 
	{
		$this->_aElement[] = array('&&', $oWhere);
		return $this;
	}

	/**
	 * where in where
	 *
	 * @access public
	 * @param  \Attila\Orm\Where $oWhere increment where
	 * @return \Attila\Orm
	 */
	public function andAddWhere(\Attila\Orm\Where $oWhere) 
	{
		return $this->addWhereaddWhere($oWhere);
	}

	/**
	 * where in where
	 *
	 * @access public
	 * @param  \Attila\Orm\Where $oWhere increment where
	 * @return \Attila\Orm
	 */
	public function orAddWhere(\Attila\Orm\Where $oWhere) 
	{
		$this->_aElement[] = array('||', $oWhere);
		return $this;
	}

	/**
	 * prepare the request
	 *
	 * @access private
	 * @return string
	 */
	public function get() 
	{
		$sWhere = '';

		if (count($this->_aElement) > 0) {

			foreach ($this->_aElement as $aElement) {

				if ($aElement[1] instanceof \Attila\Orm\Where) {

					$sWhere .= ' '.$aElement[0].' ('.substr($aElement[1]->get(), 4).')';
				}
				elseif ($aElement[2] == 'SOUNDEX') {

					$sWhere .= ' '.$aElement[0].' SOUNDEX('.$aElement[1].') LIKE CONCAT(\'%\', TRIM(TRAILING \'0\' FROM  SOUNDEX(\''.$aElement[3].'\')), \'%\')';
				}
				else if (is_string($aElement[3]) && ($aElement[3] == 'NOW()' || $aElement[3] == 'CURDATE()')) {

					$sWhere .= ' '.$aElement[0].' '.$aElement[1].' '.$aElement[2]." ".$aElement[3]."";
				}
				else if (is_string($aElement[3]) && preg_match('/^\|(.*?)\|$/', $aElement[3], $aMatch)) {

					$sWhere .= ' '.$aElement[0].' '.$aElement[1].' '.$aElement[2]." ".$aMatch[1];
				}
				else if (is_string($aElement[3])) {

					$sWhere .= ' '.$aElement[0].' '.$aElement[1].' '.$aElement[2]." '".str_replace("'", "\'", $aElement[3])."'";
				}
				elseif (is_int($aElement[3]) || is_float($aElement[3])) {

					$sWhere .= ' '.$aElement[0].' '.$aElement[1].' '.$aElement[2].' '.$aElement[3];
				}
				elseif (is_array($aElement[3]) && $aElement[2] == 'IN') {

					$sWhere .= ' '.$aElement[0].' '.$aElement[1].' '.$aElement[2]." (".implode(',', $aElement[3]).")";
				}
			}
		}

		return $sWhere;
	}


	/**
	 * flush this method
	 *
	 * @access public
	 * @return \Attila\Orm
	 */
	public function flush() 
	{
		$this->_aElement = array();
		return $this;
	}
}
