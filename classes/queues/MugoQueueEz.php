<?php

class MugoQueueEz extends MugoQueue
{
	protected $batchSize = 500;

	public function add_tasks( $task_type_id, $task_ids, $unique = false )
	{
		if( $unique )
		{
			foreach( $task_ids as $index => $id )
			{
				$sql =
					'INSERT INTO `ezpending_actions` ( action, created, param )
					SELECT "' . eZDB::instance()->escapeString( $task_type_id ) . '", ' . time() . ', "' . eZDB::instance()->escapeString( $id ) . '" FROM DUAL
					WHERE NOT EXISTS (SELECT * FROM `ezpending_actions`
						  WHERE action="' . eZDB::instance()->escapeString( $task_type_id ) . '" AND param="' . eZDB::instance()->escapeString( $id ) . '" )
					LIMIT 1';

				$db = eZDB::instance();
				$db->query( $sql );
			}
		}
		else
		{
			// Batch handling of INSERTs for best performance
			$sql_inserts = array();
			foreach( $task_ids as $index => $id )
			{
				if( $id !== '' )
				{
					$sql_inserts[] = '( "'. eZDB::instance()->escapeString( $task_type_id ) .'", '. time() . ', "'. eZDB::instance()->escapeString( $id ) . '")';
				}

				if( !( count( $sql_inserts ) % $this->batchSize ) )
				{
					$this->insertIntoDB( $sql_inserts );
					$sql_inserts = array();
				}
			}

			if( !empty( $sql_inserts ) )
			{
				$this->insertIntoDB( $sql_inserts );
			}
		}

		return true;
	}

	public function get_tasks( $task_type_id = null, $limit = false, $offset = null )
	{
		$return = array();
		
		$db = eZDB::instance();
		
		$sql = 'SELECT action AS type, param AS id FROM ezpending_actions ';
		
		$sql_page = '';
		if( (int) $limit > 0 )
		{
			$sql_page .= 'LIMIT ' . $limit . ' ';
			$sql_page .= 'OFFSET ' . (int) $offset . ' ';
		}

		$sql_where = 'WHERE ';
		if( $task_type_id )
		{
			$sql_where .= 'action = "'. $db->escapeString( $task_type_id ) .'" AND ';
		}
		$sql_where .= '1 ';
		
		$sql .= $sql_where . $sql_page;

		$result = $db->arrayQuery( $sql );
		
		if( !empty( $result ) )
		{
			//TODO: put it back to just return a list of task ids
			$return = $result;
		}
		
		return $return;
	}
	
	public function remove_tasks( $task_type_id = null, $taskIds = null )
	{
		$db = eZDB::instance();
		$db->begin();

		$sql_where = 'WHERE ';
		if( $task_type_id )
		{
			$sql_where .= 'action = "'. $db->escapeString( $task_type_id ) .'" AND ';

			if( !empty( $taskIds ) )
			{
				foreach( $taskIds as $index => $taskId )
				{
					if( is_string( $taskId ) )
					{
						$taskIds[ $index ] = '"'. $db->escapeString( $taskId ) .'"';
					}
				}

				$sql_where .= 'param IN ('. implode( ',', $taskIds ) . ') AND ';
			}
		}

		$sql_where .= '1 ';
		
		$sql = 'DELETE FROM ezpending_actions ' . $sql_where;
		//echo $sql;

		$db->query( $sql );
		$db->commit();
	}

	/**
	 * @param string $task_type_id
	 * @return int|bool
	 */
	public function get_tasks_count( $task_type_id = '' )
	{
		$return = false;
		
		$db = eZDB::instance();
				
		$sql = 'SELECT count(*) AS total FROM ezpending_actions ';

		$sql_where = 'WHERE ';
		if( $task_type_id )
		{
			$sql_where .= 'action = "'. $db->escapeString( $task_type_id ) .'" AND ';
		}
		$sql_where .= '1 ';

		$sql .= $sql_where;

		$result = $db->arrayQuery( $sql );

		if( !empty( $result ) )
		{
			$return = $result[0][ 'total' ];
		}
		
		return $return;
	}

	/**
	 *
	 * @param array $taskTypeIds
	 * @return array|bool
	 */
	public function getTaskTypeIdsWithCounts( $taskTypeIds = array() )
	{
		$return = false;

		$db = eZDB::instance();

		$sqlInStatement = '1=1';
		if( !empty( $taskTypeIds ) )
		{
			$sqlInStatement = $this->arrayToSqlIn( $taskTypeIds, 'action' );
		}

		$sql = 'SELECT action AS task_type_id, count(*) AS total FROM ezpending_actions WHERE '. $sqlInStatement .' GROUP BY action';

		$result = $db->arrayQuery( $sql );

		if( !empty( $result ) )
		{
			$return = $result;
		}

		return $return;
	}

	/**
	 * @param integer $count
	 * @param array $taskTypeIds
	 * @return array
	 */
	public function getRandomTaskIds( $count, $taskTypeIds = array() )
	{
		$return = array();
		
		$db = eZDB::instance();

		$sqlInStatement = '1=1';
		if( !empty( $taskTypeIds ) )
		{
			$sqlInStatement = $this->arrayToSqlIn( $taskTypeIds, 'action' );
		}

		// get random index (offset)
		$countSql = 'SELECT count(*) AS total FROM ezpending_actions WHERE ' . $sqlInStatement;

		$countResult = $db->arrayQuery( $countSql );

		if( !empty( $countResult ) )
		{
			$total = $countResult[0][ 'total' ];
		}
		$rand_offset = floor( $total * ( rand(0, 999) / 1000 ) );

		// get task data
		$sql = 'SELECT action AS type, param AS id FROM ezpending_actions WHERE '. $sqlInStatement .' LIMIT '. $rand_offset . ', 1';
		
		//echo $sql;
		
		$result = $db->arrayQuery( $sql );
		
		if( !empty( $result ) )
		{
			$return = $result;
		}
		
		return $return;
	}

	/**
	 * @param array $elements
	 * @param string $dbField
	 * @return string
	 */
	protected function arrayToSqlIn( $elements, $dbField )
	{
		$db = eZDB::instance();
		return $dbField . ' IN ( "' . implode('","', array_map( array( $db, 'escapeString' ), $elements ) ) . '" )';
	}

	private function insertIntoDB( $sql_inserts )
	{
		$db = eZDB::instance();
		$db->begin();
		{
			$result = $db->query(
				'INSERT INTO ezpending_actions ( action, created, param ) VALUES ' . implode( ',',  $sql_inserts )
			);
		}
		$db->commit();

		return $result;
	}

}
