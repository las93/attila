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
class Entity
{
    /**
     * options of the batch
     * 
     * @access public
     * @var    array
     */
    public static $aOptionsBatch = array(
        "p" => "string", 
        "r" => false, 
        "c" => false, 
        "e" => false, 
        "d" => false, 
        "f" => false,
        "a" => "string",
        "g" => "string",
        "h" => "string",
        "i" => "string",
        "v" => false
    );
    
	/**
	 * run the batch to create entity
	 * @tutorial launch.php scaffolding
	 *
	 * @access public
	 * @param  array $aOptions options of script
	 * @return void
	 */

	public function runScaffolding(array $aOptions = array())
	{
        /**
         * option -v [if you want the script tell you - dump of sql]
         */

        if (isset($aOptions['v'])) { $bDumpSql = true;}
        else { $bDumpSql = false; }

		/**
		 * option -p [portail]
		 */

		if (isset($aOptions['p'])) { 
		    
		    $sPortal = $aOptions['p'];
		}
		else { 
		    
		    echo 'Error: you must indicated the Entity Path';
		    exit;
		}

		/**
		 * option -r [yes/no]
		 */

		if (isset($aOptions['r']) && $aOptions['r'] === 'yes') { $sRewrite = $aOptions['r']; }
		else { $sRewrite = 'no'; }

		/**
		 * option -c [create table]
		 */

		if (isset($aOptions['c'])) { $bCreate = true; }
		else { $bCreate = false; }

		/**
		 * option -e [create entity and models]
		 */

		if (isset($aOptions['e'])) { $bCreateEntity = true; }
		else { $bCreateEntity = false; }

		/**
		 * option -f [create models if not exists]
		 */

		if (isset($aOptions['f'])) { 
			
			$bCreateModelIfNotExists = true;
			$bCreateEntity = true;
		}
		else { 
			
			$bCreateModelIfNotExists = false;
		}

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
		 * option -d [drop table]
		 */

		if (isset($aOptions['d'])) { $bDropTable = true; }
		else { $bDropTable = false; }

		/**
		 * option -g [indicate the Entities directory]
		 */

	    if (isset($aOptions['g'])) { $sEntitiesPath = $aOptions['g']; }
		else { $sEntitiesPath = ''; }

		/**
		 * option -h [indicate the Models directory]
		 */

	    if (isset($aOptions['h'])) { $sModelsPath = $aOptions['h']; }
		else { $sModelsPath = ''; }

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

		    if (!defined('SQL_FIELD_NAME_SEPARATOR')) {
		        
    		    if ($oConnection->type == 'mysql') {
    
    		        define('SQL_FIELD_NAME_SEPARATOR', '`');
    		    }
    		    else {
    		        
    		        define('SQL_FIELD_NAME_SEPARATOR', '');
    		    }
		    }
		    
			/**
			 * scaffolding of the database
			 */

			if ($bCreate === true) {

			    $oContainer = new DbContainer;

        		$oContainer->setDbName($oConnection->db)
        		           ->setHost($oConnection->host)
        		           ->setName($oConnection->db)
        		           ->setPassword($oConnection->password)
        		           ->setType($oConnection->type)
        		           ->setUser($oConnection->user);
			    
				$oPdo = Db::connect($oContainer);
				
				foreach ($oConnection->tables as $sTableName => $oOneTable) {

    				foreach ($oOneTable->fields as $sFieldName => $oOneField) {
    				
    				    if (isset($oOneField->many_to_many)) {
    				        
    				        if (!isset($oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many})) {

    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many} = new \stdClass();    				            
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields = new \stdClass();

    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$sTableName} = new \stdClass();
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$sTableName}->type = $oOneField->type;
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$sTableName}->key = 'primary';

    				            if (isset($oOneField->null)) {
    				                
    				                $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$sTableName}->null = $oOneField->null;
    				            }
    				            
    				            if (isset($oOneField->unsigned)) {
    				            
    				                $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$sTableName}->unsigned = $oOneField->unsigned;
    				            }
    				            
    				            foreach ($oConnection->tables->{$oOneField->many_to_many}->fields as $sNameOfManyToManyField => $oField) {
    				            
    				                if (isset($oField->key) && $oField->key == 'primary') { $sFieldOfManyToMany = $oField; }
    				            }
    				            
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$oOneField->many_to_many} = new \stdClass();
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$oOneField->many_to_many}->type = $sFieldOfManyToMany->type;
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$oOneField->many_to_many}->key = 'primary';
    				            //@todo : attribute ne se rajoute pas en field donc erreur dans jointure
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$oOneField->many_to_many}->join = $oOneField->many_to_many;
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$oOneField->many_to_many}->join_by_field = 'id';
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$sTableName}->join = $sTableName;
    				            $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$sTableName}->join_by_field = 'id';
    				            
    				            $oConnection->tables->{$oOneField->many_to_many}->fields->{'id'}->join = array();
    				            $oConnection->tables->{$oOneField->many_to_many}->fields->{'id'}->join_by_field = array();
    				            $oConnection->tables->{$oOneField->many_to_many}->fields->{'id'}->join[] = $sTableName.'_'.$oOneField->many_to_many;
    				            $oConnection->tables->{$oOneField->many_to_many}->fields->{'id'}->join_by_field[] = 'id_'.$oOneField->many_to_many;
    				            $oConnection->tables->{$sTableName}->fields->{'id'}->join = array();
    				            $oConnection->tables->{$sTableName}->fields->{'id'}->join_by_field = array();
    				            $oConnection->tables->{$sTableName}->fields->{'id'}->join[] = $sTableName.'_'.$oOneField->many_to_many;
    				            $oConnection->tables->{$sTableName}->fields->{'id'}->join_by_field[] = 'id_'.$oOneField->many_to_many;
    				            
    				            if (isset($sFieldOfManyToMany->null)) {
    				                
    				                $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$oOneField->many_to_many}->null = $sFieldOfManyToMany->null;
    				            }
    				            
    				            if (isset($sFieldOfManyToMany->unsigned)) {
    				            
    				                $oConnection->tables->{$sTableName.'_'.$oOneField->many_to_many}->fields->{'id_'.$oOneField->many_to_many}->unsigned = $sFieldOfManyToMany->unsigned;
    				            }
    				        }
    				    }
    				    
    				    if (isset($oOneField->join)) {

    				        if (isset($oOneField->join_by_field)) { $sJoinByField = $oOneField->join_by_field; }
    				        else { $sJoinByField = $oOneField->join; }
    				        
    				        if (is_string($oOneField->join)) { $aOneFieldJoin = array($oOneField->join); }
    				        else { $aOneFieldJoin = $oOneField->join; }
    				        
    				        if (is_string($sJoinByField)) { $aJoinByField = array($sJoinByField); }
    				        else { $aJoinByField = $sJoinByField; }
    				        
    				        foreach ($aOneFieldJoin as $iKey => $sOneFieldJoin) {
    				            
    				            $sJoinByField = $aJoinByField[$iKey];
        				        
    				            if (isset($oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->key)
    				                && $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->key == 'primary'
    				                && !isset($oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join)) {
    
    				                $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join = array();
    				                $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join[0] = $sTableName;
    				                $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join_by_field[0] = $sFieldName;
    				            }
    				            else if (isset($oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->key)
    				                && $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->key == 'primary'
    				                && isset($oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join)
    				                && is_array($oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join)
    				                && !in_array($sTableName, $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join)) {
    				                
                                    $iIndex = count($oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join);
    				                $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join[$iIndex] = $sTableName;
    				                $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join_by_field[$iIndex] = $sFieldName;
    				            }
    				            else if (!isset($oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join)) {

    				                $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join = $sTableName;
    				                $oConnection->tables->{$sOneFieldJoin}->fields->{$sJoinByField}->join_by_field = $sFieldName;
    				            }
    				        }
    				    }
    				}
    			}
    			
				foreach ($oConnection->tables as $sTableName => $oOneTable) {

					if ($bDropTable === true) {
						
						$sQuery = 'DROP TABLE IF EXISTS '.SQL_FIELD_NAME_SEPARATOR.$sTableName.SQL_FIELD_NAME_SEPARATOR;
						if ($bDumpSql) { echo $sQuery."\n"; } else { $oPdo->query($sQuery); }
					}
					
					$sQuery = 'CREATE TABLE IF NOT EXISTS '.SQL_FIELD_NAME_SEPARATOR.$sTableName.SQL_FIELD_NAME_SEPARATOR.' (';

					$aIndex = array();
					$aUnique = array();
					$aPrimaryKey = array();

					foreach ($oOneTable->fields as $sFieldName => $oOneField) {

						$sQuery .= SQL_FIELD_NAME_SEPARATOR.$sFieldName.SQL_FIELD_NAME_SEPARATOR.' '.$oOneField->type;

						if (isset($oOneField->values) && $oOneField->type === 'enum' && is_array($oOneField->values)) {

							$sQuery .= '("'.implode('","', $oOneField->values).'") ';
						}
						else if (isset($oOneField->value) && (is_int($oOneField->value) || preg_match('/^[0-9,]+$/', $oOneField->value))) {

							$sQuery .= '('.$oOneField->value.') ';
						}

						if (isset($oOneField->unsigned) && $oOneField->unsigned === true) {

							$sQuery .= ' UNSIGNED ';
						}

						if (isset($oOneField->null) && $oOneField->null === true) { $sQuery .= ' NULL '; }
						else if (isset($oOneField->null) && $oOneField->null === false) { $sQuery .= ' NOT NULL '; }

						if (isset($oOneField->default) && is_string($oOneField->default)) {

							$sQuery .= ' DEFAULT "'.$oOneField->default.'" ';
						}
						else if (isset($oOneField->default)) {

							$sQuery .= ' DEFAULT '.$oOneField->default.' ';
						}

						if (isset($oOneField->autoincrement) && $oOneField->autoincrement === true) {

							$sQuery .= ' AUTO_INCREMENT ';
						}

						$sQuery .= ', ';

						if (isset($oOneField->key) && $oOneField->key === 'primary') { $aPrimaryKey[] = $sFieldName; }
						else if (isset($oOneField->key) && $oOneField->key === 'index') { $aIndex[] = $sFieldName; }
						else if (isset($oOneField->key) && $oOneField->key === 'unique') { $aUnique[] = $sFieldName; }
					
    					if (isset($oOneField->join) && is_string($oOneField->join)) {

    					    if (isset($oOneField->constraint) && is_string($oOneField->constraint)) {
    					    
    					        $sQuery .= ' CONSTRAINT '.$oOneField->constraint.' ';
    					    }
    					    
    					    $sQuery .= 'FOREIGN KEY('.$sFieldName.') REFERENCES '.$oOneField->join.'('.$oOneField->join_by_field.') ';
    					    
    					    if (isset($oOneField->join_delete) && is_string($oOneField->join_delete)) {
    					        
    					        $sQuery .= ' ON DELETE '.$oOneField->join_delete.' ';
    					    }

    					    if (isset($oOneField->join_update) && is_string($oOneField->join_update)) {
    					        	
    					        $sQuery .= ' ON UPDATE '.$oOneField->join_update.' ';
    					    }
    					    
    					    $sQuery .= ',';
    					}
					}

					if (count($aPrimaryKey) > 0) { $sQuery .= 'PRIMARY KEY('.implode(',', $aPrimaryKey).') , '; }
					
					if (count($aIndex) > 0) { $sQuery .= 'KEY('.implode(',', $aIndex).') , '; }
					
					if (count($aUnique) > 0) { $sQuery .= 'UNIQUE KEY '.$aUnique[0].' ('.implode(',', $aUnique).') , '; }

					if (isset($oOneTable->index)) {

						foreach ($oOneTable->index as $sIndexName => $aFields) {

							$sQuery .= 'KEY '.$sIndexName.' ('.implode(',', $aFields).') , ';
						}
					}

					if (isset($oOneTable->unique)) {

						foreach ($oOneTable->unique as $sIndexName => $aFields) {

							$sQuery .= 'KEY '.$sIndexName.' ('.implode(',', $aFields).') , ';
						}
					}

					$sQuery = substr($sQuery, 0, -2);
					$sQuery .= ')';
					
					if (isset($oOneTable->engine)) {  $sQuery .= ' ENGINE='.$oOneTable->engine.' '; }
					if (isset($oOneTable->auto_increment)) {  $sQuery .= ' AUTO_INCREMENT='.$oOneTable->auto_increment.' '; }
					if (isset($oOneTable->default_charset)) {  $sQuery .= ' DEFAULT CHARSET='.$oOneTable->default_charset.' '; }

                    if ($bDumpSql) {
                        echo $sQuery."\n";
                    } else  if ($oPdo->query($sQuery) === false) {
					    
					   echo "\n[ERROR SQL] ".$oPdo->errorInfo()[2]." for the table ".$sTableName."\n"; 
					   echo "\n".$sQuery."\n"; 
					}
				}
			}

			/**
			 * scaffolding of the entities
			 */

			if ($bCreateEntity) {
					
				foreach ($oConnection->tables as $sTableName => $oOneTable) {
	
					$sContentFile = '<?php
	
/**
 * Entity to '.$sTableName.'
 *
 * @category  	\\'.CATEGORY.'
 * @package   	'.ENTITY_NAMESPACE.'
 * @author    	'.AUTHOR.'
 * @copyright 	'.COPYRIGHT.'
 * @license   	'.LICENCE.'
 * @version   	Release: '.VERSION.'
 * @filesource	'.FILESOURCE.'
 * @link      	'.LINK.'
 * @since     	1.0
 */
namespace '.preg_replace('/^\\\\/', '', ENTITY_NAMESPACE).';

use \Attila\core\Entity as Entity;
use \Attila\Orm as Orm;

/**
 * Entity to '.$sTableName.'
 *
 * @category  	\\'.CATEGORY.'
 * @package   	'.ENTITY_NAMESPACE.'
 * @author    	'.AUTHOR.'
 * @copyright 	'.COPYRIGHT.'
 * @license   	'.LICENCE.'
 * @version   	Release: '.VERSION.'
 * @filesource	'.FILESOURCE.'
 * @link      	'.LINK.'
 * @since     	1.0
 */
class '.$sTableName.' extends Entity 
{';
	
					foreach ($oOneTable->fields as $sFieldName => $oField) {
	
						if ($oField->type == 'enum' || $oField->type == 'char' || $oField->type == 'varchar' || $oField->type == 'text'
							|| $oField->type == 'date' || $oField->type == 'datetime' || $oField->type == 'time' || $oField->type == 'binary'
							|| $oField->type == 'varbinary' || $oField->type == 'blob' || $oField->type == 'tinyblob'
							|| $oField->type == 'tinytext' || $oField->type == 'mediumblob' || $oField->type == 'mediumtext'
							|| $oField->type == 'longblob' || $oField->type == 'longtext' || $oField->type == 'char varying'
							|| $oField->type == 'long varbinary' || $oField->type == 'long varchar' || $oField->type == 'long') {
	
							$sType = 'string';
						}
						else if ($oField->type == 'int' || $oField->type == 'smallint' || $oField->type == 'tinyint'
							|| $oField->type == 'bigint' || $oField->type == 'mediumint' || $oField->type == 'timestamp'
							|| $oField->type == 'year' || $oField->type == 'integer' || $oField->type == 'int1' || $oField->type == 'int2'
							|| $oField->type == 'int3' || $oField->type == 'int4' || $oField->type == 'int8' || $oField->type == 'middleint') {
	
							$sType = 'int';
						}
						else if ($oField->type == 'bit' || $oField->type == 'bool' || $oField->type == 'boolean') {
	
							$sType = 'bool';
						}
						else if ($oField->type == 'float' || $oField->type == 'decimal' || $oField->type == 'double'
							|| $oField->type == 'precision' || $oField->type == 'real' || $oField->type == 'float4'
							|| $oField->type == 'float8' || $oField->type == 'numeric') {
	
							$sType = 'float';
						}
						else if ($oField->type == 'set') {
	
							$sType = 'array';
						}
	
						$sContentFile .= '
	/**
	 * '.$sFieldName.'
	 *
	 * @access private
	 * @var    '.$sType.'
	 *
';
	
						if (isset($oField->key) && $oField->key == 'primary') {
	
							$sContentFile .= '	 * @primary_key'."\n";
						}
	
						if (isset($oField->property)) {
	
							$sContentFile .= '	 * @map '.$oField->property.''."\n";
						}
	
						$sContentFile .= '	 */
    private $'.$sFieldName.' = null;
	';
						if (isset($oField->join)) {
	
						    if (!is_array($oField->join)) {
						        
						        $oField->join = array($oField->join);
						        if (isset($oField->join_alias)) { $oField->join_alias = array($oField->join_alias); }
						        if (isset($oField->join_by_field)) { $oField->join_by_field = array($oField->join_by_field); }
						    }
						    
						    for ($i = 0 ; $i < count($oField->join) ; $i++) {
						    
						        if (isset($oField->join_alias[$i])) { $sJoinUsedName = $oField->join_alias[$i]; }
							    else { $sJoinUsedName = $oField->join[$i]; }
	
								$sContentFile .= '
	/**
	 * '.$sJoinUsedName.' Entity
	 *
	 * @access private
	 * @var    '.$oField->join[$i].'
	 * @join
	 *
	 */
    private $'.$sJoinUsedName.' = null;
	
	
	';
						    }
						}
					}
	
					foreach ($oOneTable->fields as $sFieldName => $oField) {
	
						if ($oField->type == 'enum' || $oField->type == 'char' || $oField->type == 'varchar' || $oField->type == 'text'
							|| $oField->type == 'date' || $oField->type == 'datetime' || $oField->type == 'time' || $oField->type == 'binary'
							|| $oField->type == 'varbinary' || $oField->type == 'blob' || $oField->type == 'tinyblob'
							|| $oField->type == 'tinytext' || $oField->type == 'mediumblob' || $oField->type == 'mediumtext'
							|| $oField->type == 'longblob' || $oField->type == 'longtext' || $oField->type == 'char varying'
							|| $oField->type == 'long varbinary' || $oField->type == 'long varchar' || $oField->type == 'long') {
	
							$sType = 'string';
						}
						else if ($oField->type == 'int' || $oField->type == 'smallint' || $oField->type == 'tinyint'
						    || $oField->type == 'bigint' || $oField->type == 'mediumint' || $oField->type == 'timestamp'
						    || $oField->type == 'year' || $oField->type == 'integer' || $oField->type == 'int1' || $oField->type == 'int2'
						    || $oField->type == 'int3' || $oField->type == 'int4' || $oField->type == 'int8' 
							|| $oField->type == 'middleint') {
	
							$sType = 'int';
						}
						else if ($oField->type == 'bit' || $oField->type == 'bool' || $oField->type == 'boolean') {
	
							$sType = 'bool';
						}
						else if ($oField->type == 'float' || $oField->type == 'decimal' || $oField->type == 'double'
							|| $oField->type == 'precision' || $oField->type == 'real' || $oField->type == 'float4'
							|| $oField->type == 'float8' || $oField->type == 'numeric') {
	
							$sType = 'float';
						}
						else if ($oField->type == 'set') {
	
							$sType = 'array';
						}
	
						$sContentFile .= '
	/**
	 * get '.$sFieldName.' of '.$sTableName.'
	 *
	 * @access public
	 * @return '.$sType.'
	 */
	public function get_'.$sFieldName.'()
	{
		return $this->'.$sFieldName.';
	}

	/**
	 * set '.$sFieldName.' of '.$sTableName.'
	 *
	 * @access public
	 * @param  '.$sType.' $'.$sFieldName.' '.$sFieldName.' of '.$sTableName.'
	 * @return '.ENTITY_NAMESPACE.'\\'.$sTableName.'
	 */
	public function set_'.$sFieldName.'($'.$sFieldName.') 
	{
		$this->'.$sFieldName.' = $'.$sFieldName.';
		return $this;
	}
	';
						if (isset($oField->join)) {
	
							/**
							 * you could add join_by_field when you have a field name different in the join
							 * @example		ON menu1.id = menu2.parent_id
							 *
							 * if the left field and the right field have the same name, you could ignore this param.
							 */
	
						    if (!is_array($oField->join)) {
						        
						        $oField->join = array($oField->join);
						        if (isset($oField->join_alias)) { $oField->join_alias = array($oField->join_alias); }
						        if (isset($oField->join_by_field)) { $oField->join_by_field = array($oField->join_by_field); }
						    }
						    
						    for ($i = 0 ; $i < count($oField->join) ; $i++) {
	
    							if (isset($oField->join_by_field[$i])) { $sJoinByField = $oField->join_by_field[$i]; }
    							else { $sJoinByField = $sFieldName; }
    	
    							if (isset($oField->join_alias[$i])) { $sJoinUsedName = $oField->join_alias[$i]; }
    							else { $sJoinUsedName = $oField->join[$i]; }
    	
    							$sContentFile .= '
	/**
	 * get '.$sJoinUsedName.' entity join by '.$sFieldName.' of '.$sTableName.'
	 *
	 * @access public
	 * @param  array $aWhere
	 * @join
	 * @return ';
								
    							$sKey2 = '';
    							$iPrimaryKey = 0;
    							
                                if (count($oField->join) == 1) {
                                    
                                    if (isset($oConnection->tables->{$oField->join[0]}->fields->{$oField->join_by_field[0]}->key)) {
                                        
                                        $sKey2 = $oConnection->tables->{$oField->join[0]}->fields->{$oField->join_by_field[0]}->key;
                                        $iPrimaryKey = 0;
                                        
                                        foreach ($oConnection->tables->{$oField->join[0]}->fields as $iKey2 => $oField2) {
                                            
                                            if (isset($oField2->key) && $oField2->key == 'primary') {
                                                
                                                $iPrimaryKey++;
                                            }
                                        }
                                    }
                                }
                                
                                if ($sKey2 == 'primary' && $iPrimaryKey == 1) {
                                    
                                    $sContentFile .= ENTITY_NAMESPACE.'\\'.$sTableName;
                                }
    							else if (isset($oField->key) && ($oField->key == 'primary' || in_array('primary', $oField->key))) { 
    
    							    $sContentFile .= 'array';
    							}
    							else {
    								    
    							    $sContentFile .= ENTITY_NAMESPACE.'\\'.$sTableName;
    							}
    			                     
    							$sContentFile .= '
	 */
	public function get_'.$sJoinUsedName.'($aWhere = array())
	{
		if ($this->'.$sJoinUsedName.' === null) {

			$oOrm = new Orm;

			$oOrm->select(array(\'*\'))
				 ->from(\''.$oField->join[$i].'\');
												   
	        $aWhere[\''.$sJoinByField.'\'] = $this->get_'.$sFieldName.'();
											
													  ';
								
							    $sContentFile .= '
            ';
							    if ($sKey2 == 'primary' && $iPrimaryKey == 1) {
							    
							        $sContentFile .= '$aResult';
							    }
							    else if (isset($oField->key) && ($oField->key == 'primary' || in_array('primary', $oField->key))) { 
    
    							    $sContentFile .= '$this->'.$oField->join[$i].'';
    							}
    							else {
    								    
    							    $sContentFile .= '$aResult';
    							}
    			                     
    							$sContentFile .= ' = $oOrm->where($aWhere)
						           ->load(false, \''.ENTITY_NAMESPACE.'\\\\\');';

    							if ((!isset($oField->key) || (isset($oField->key) && $oField->key != 'primary' 
    							 && (is_array($oField->key) && !in_array('primary', $oField->key))))
						         || ($sKey2 == 'primary' && $iPrimaryKey == 1)) { 
    								    
    							    $sContentFile .= "\n\n".'          if (count($aResult) > 0) { $this->'.$sJoinUsedName.' = $aResult[0]; }
          else { $this->'.$sJoinUsedName.' = array(); }';
    							}
    			                     
    							$sContentFile .= '
        }

		return $this->'.$sJoinUsedName.';
	}
	
	/**
	 * set '.$sJoinUsedName.' entity join by '.$sFieldName.' of '.$sTableName.'
	 *
	 * @access public
	 * @param  '.ENTITY_NAMESPACE.'\\'.$oField->join[$i].'  $'.$sJoinUsedName.' '.$oField->join[$i].' entity
	 * @join
	 * @return ';
		 
    							if (isset($oField->key) && ($oField->key == 'primary' || (is_array($oField->key) && in_array('primary', $oField->key)))) { 
    
    							    $sContentFile .= 'array';
    							}
    							else {
    								    
    							    $sContentFile .= ENTITY_NAMESPACE.'\\'.$sTableName;
    							}
    			                     
    							$sContentFile .= '
	 */
	public function set_'.$sJoinUsedName.'(';
		 
    							if (isset($oField->key) && ($oField->key == 'primary' || (is_array($oField->key) && in_array('primary', $oField->key)))) { 
    
    							    $sContentFile .= 'array';
    							}
    							else {
    								    
    							    $sContentFile .= ENTITY_NAMESPACE.'\\'.$oField->join[$i];
    							}
			                     
							    $sContentFile .= ' $'.$sJoinUsedName.')
	{
		$this->'.$sJoinUsedName.' = $'.$sJoinUsedName.';
		return $this;
	}
';
						    }
						}	
					}
	
					$sContentFile .= '}';
	
					file_put_contents($sEntitiesPath.$sTableName.'.php', $sContentFile);
	
					if ($bCreateModelIfNotExists === false || ($bCreateModelIfNotExists === true 
						&& !file_exists($sModelsPath.$sTableName.'.php'))) {
					
						$sContentFile = '<?php
	
/**
 * Model to '.$sTableName.'
 *
 * @category  	\\'.CATEGORY.'
 * @package   	'.MODEL_NAMESPACE.'
 * @author    	'.AUTHOR.'
 * @copyright 	'.COPYRIGHT.'
 * @license   	'.LICENCE.'
 * @version   	Release: '.VERSION.'
 * @filesource	'.FILESOURCE.'
 * @link      	'.LINK.'
 * @since     	1.0
 */
namespace Venus\src\\'.$sPortal.'\Model;

use \Venus\core\Model as Model;
	
/**
 * Model to '.$sTableName.'
 *
 * @category  	\\'.CATEGORY.'
 * @package   	'.MODEL_NAMESPACE.'
 * @author    	'.AUTHOR.'
 * @copyright 	'.COPYRIGHT.'
 * @license   	'.LICENCE.'
 * @version   	Release: '.VERSION.'
 * @filesource	'.FILESOURCE.'
 * @link      	'.LINK.'
 * @since     	1.0
 */
class '.$sTableName.' extends Model 
{
}'."\n";
	
						file_put_contents($sModelsPath.$sTableName.'.php', $sContentFile);
					}
				}
			}

        echo "\n\n";
        echo Bash::setBackground("                                                                            ", 'green');
        echo Bash::setBackground("          [OK] Success                                                      ", 'green');
        echo Bash::setBackground("                                                                            ", 'green');
        echo "\n\n";
	}
}
