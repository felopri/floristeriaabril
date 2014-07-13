<?php
/**
 * @package angifw
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

defined('_AKEEBA') or die();

class ADatabaseRestoreMysqli extends ADatabaseRestore
{
	/**
	 * Overloaded constructor, allows us to set up error codes and connect to
	 * the database.
	 *
	 * @param   string  $dbkey        @see {ADatabaseRestore}
	 * @param   array   $dbiniValues  @see {ADatabaseRestore}
	 */
	public function __construct($dbkey, $dbiniValues)
	{
		parent::__construct($dbkey, $dbiniValues);

		// Set up allowed error codes
		$this->allowedErrorCodes = array(
			1262,
			1263,
			1264,
			1265,	// "Data truncated" warning
			1266,
			1287,
			1299
			// , 1406	// "Data too long" error
		);

		// Set up allowed comment delimiters
		$this->comment = array(
			'#',
			'\'-- ',
			'---',
			'/*!',
		);

		// Connect to the database
		$this->getDatabase();

		// Suppress foreign key checks
		if ($this->dbiniValues['foreignkey'])
		{
			$this->db->setQuery('SET FOREIGN_KEY_CHECKS = 0');
			try
			{
				$this->db->execute();
			}
			catch (Exception $exc)
			{
				// Do nothing if that fails. Maybe we can continue with the restoration.
			}
		}

		// Suppress auto value on zero
		if ($this->dbiniValues['noautovalue'])
		{
			$this->db->setQuery('SET NO_AUTO_VALUE_ON_ZERO = 1');
			try
			{
				$this->db->execute();
			}
			catch (Exception $exc)
			{
				// Do nothing if that fails. Maybe we can continue with the restoration.
			}
		}
	}

	/**
	 * Overloaded method which will create the database (if it doesn't exist).
	 *
	 * @return  ADatabaseDriver
	 */
	protected function getDatabase()
	{
		if (!is_object($this->db))
		{
			$db = parent::getDatabase();
			try
			{
				$db->select($this->dbiniValues['dbname']);
			}
			catch (Exception $exc)
			{
				// We couldn't connect to the database. Maybe we have to create
				// it first. Let's see...
				$options = (object)array(
					'db_name'	=> $this->dbiniValues['dbname'],
					'db_user'	=> $this->dbiniValues['dbuser'],
				);
				$db->createDatabase($options, true);
				$db->select($this->dbiniValues['dbname']);
			}

			// Try to change the database collation, if requested
			if ($this->dbiniValues['utf8db'])
			{
				try
				{
					$db->alterDbCharacterSet($this->dbiniValues['dbname']);
				}
				catch (Exception $exc)
				{
					// Ignore any errors
				}
			}
		}

		return $this->db;
	}

	/**
	 * Processes and runs the query
	 *
	 * @param   string  $query  The query to process
	 *
	 * @return  boolean  True on success
	 */
	protected function processQueryLine($query)
	{
		$db = $this->getDatabase();

		$prefix = $this->dbiniValues['prefix'];
		$existing = $this->dbiniValues['existing'];
		$forceutf8 = $this->dbiniValues['utf8tables'];
		$replacesql = $this->dbiniValues['replace'];

		$replaceAll = false;
		$changeEncoding = false;
		$useDelimiter = false;

		// CREATE TABLE query pre-processing
		// If the table has a prefix, back it up (if requested). In any case, drop
		// the table. before attempting to create it.
		if( substr($query, 0, 12) == 'CREATE TABLE')
		{
			// Yes, try to get the table name
			$restOfQuery = trim(substr($query, 12, strlen($query)-12 )); // Rest of query, after CREATE TABLE
			// Is there a backtick?
			if (substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$tableName = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the table name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$tableName = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);

			// Should I back the table up?
			if(($prefix != '') && ($existing == 'backup') && (strpos($tableName, '#__') == 0))
			{
				// It's a table with a prefix, a prefix IS specified and we are asked to back it up.
				// Start by dropping any existing backup tables
				$backupTable = str_replace('#__', 'bak_', $tableName);
				try
				{
					$db->dropTable($backupTable);
					$db->renameTable($tableName, $backupTable);
				} catch (Exception $exc) {
					// We can't rename the table. Try deleting it.
					$db->dropTable($tableName);
				}
			}
			else
			{
				// Try to drop the table anyway
				$db->dropTable($tableName);
			}

			$replaceAll = true; // When processing CREATE TABLE commands, we might have to replace SEVERAL metaprefixes.

			// Crude check: Community builder's #__comprofiler_fields includes a DEFAULT value which use a metaprefix,
			// so replaceAll must be false in that case.
			if($tableName == '#__comprofiler_fields') {
				$replaceAll = false;
			}

			$changeEncoding = $forceutf8;
		}
		// CREATE VIEW query pre-processing
		// In any case, drop the view before attempting to create it. (Views can't be renamed)
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, ' VIEW ') !== false))
		{
			// Yes, try to get the view name
			$view_pos = strpos($query, ' VIEW ');
			$restOfQuery = trim( substr($query, $view_pos + 6) ); // Rest of query, after VIEW string
			// Is there a backtick?
			if (substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$tableName = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the table name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$tableName = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);

			// Try to drop the view anyway
			$dropQuery = 'DROP VIEW IF EXISTS `'.$tableName.'`;';
			$db->setQuery(trim($dropQuery));
			$db->execute();

			$replaceAll = true; // When processing views, we might have to replace SEVERAL metaprefixes.
		}
		// CREATE PROCEDURE pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'PROCEDURE ') !== false))
		{
			// Try to get the procedure name
			$entity_keyword = ' PROCEDURE ';
			$entity_pos = strpos($query, $entity_keyword);
			$restOfQuery = trim( substr($query, $entity_pos + strlen($entity_keyword)) ); // Rest of query, after entity key string
			// Is there a backtick?
			if (substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the entity name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);

			// Try to drop the entity anyway
			$dropQuery = 'DROP' . $entity_keyword . 'IF EXISTS `'.$entity_name.'`;';
			$db->setQuery(trim($dropQuery));
			$db->execute();

			$replaceAll = true; // When processing entities, we might have to replace SEVERAL metaprefixes.
			$useDelimiter = true; // Instruct the engine to change the delimiter for this query to //
		}
		// CREATE FUNCTION pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'FUNCTION ') !== false))
		{
			// Try to get the procedure name
			$entity_keyword = ' FUNCTION ';
			$entity_pos = strpos($query, $entity_keyword);
			$restOfQuery = trim( substr($query, $entity_pos + strlen($entity_keyword)) ); // Rest of query, after entity key string
			// Is there a backtick?
			if (substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the entity name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);

			// Try to drop the entity anyway
			$dropQuery = 'DROP'.$entity_keyword.'IF EXISTS `'.$entity_name.'`;';
			$db->setQuery(trim($dropQuery));
			$db->execute();

			$replaceAll = true; // When processing entities, we might have to replace SEVERAL metaprefixes.
			$useDelimiter = true; // Instruct the engine to change the delimiter for this query to //
		}
		// CREATE TRIGGER pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'TRIGGER ') !== false))
		{
			// Try to get the procedure name
			$entity_keyword = ' TRIGGER ';
			$entity_pos = strpos($query, $entity_keyword);
			$restOfQuery = trim( substr($query, $entity_pos + strlen($entity_keyword)) ); // Rest of query, after entity key string
			// Is there a backtick?
			if(substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the entity name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);

			// Try to drop the entity anyway
			$dropQuery = 'DROP'.$entity_keyword.'IF EXISTS `'.$entity_name.'`;';
			$db->setQuery(trim($dropQuery));
			$db->execute();

			$replaceAll = true; // When processing entities, we might have to replace SEVERAL metaprefixes.
			$useDelimiter = true; // Instruct the engine to change the delimiter for this query to //
		}
		elseif( substr($query,0,6) == 'INSERT' )
		{
			if($replacesql)
			{
				// Use REPLACE instead of INSERT selected
				$query = 'REPLACE '.substr($query,7);
			}
			$replaceAll = false;
		}
		else
		{
			// Maybe a DROP statement from the extensions filter?
			$replaceAll = true;
		}

		if(!empty($query)) {
			if ($useDelimiter)
			{
				// This doesn't work from PHP
				//$this->execute('DELIMITER //');
			}
			$this->execute($query);
			if ($useDelimiter)
			{
				// This doesn't work from PHP
				//$this->execute('DELIMITER ;');
			}

			// Do we have to force UTF8 encoding?
			if($changeEncoding) {
				// Get a list of columns
				$columns = $db->getTableColumns($tableName);
				$mods = array(); // array to hold individual MODIFY COLUMN commands
				if(is_array($columns)) foreach($columns as $field => $column)
				{
					// Make sure we are redefining only columns which do support a collation
					$col = (object)$column;
					if (empty($col->Collation))
					{
						continue;
					}

					$null = $col->Null == 'YES' ? 'NULL' : 'NOT NULL';
					$default = is_null($col->Default) ? '' : "DEFAULT '".$db->escape($col->Default)."'";
					$mods[] = "MODIFY COLUMN `$field` {$col->Type} $null $default COLLATE utf8_general_ci";
				}

				// Begin the modification statement
				$sql = "ALTER TABLE `$tableName` ";

				// Add commands to modify columns
				if(!empty($mods))
				{
					$sql .= implode(', ', $mods).', ';
				}

				// Add commands to modify the table collation
				$sql .= 'DEFAULT CHARACTER SET UTF8 COLLATE utf8_general_ci;';
				$db->setQuery($sql);
				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Don't fail if the collation could not be changed
				}
			}
		}

		return true;
	}
}