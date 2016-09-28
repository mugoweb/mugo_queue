 <?php

class MugoQueueEz extends MugoQueue
{
	protected $batchSize = 500;

	public function add_tasks( $task_type_id, $task_ids )
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

		return true;
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
	 * @return array|bool
	 */
	public function getTaskTypeIdsWithCounts()
	{
		$return = false;

		$db = eZDB::instance();

		$sql = 'SELECT action AS task_type_id, count(*) AS total FROM ezpending_actions GROUP BY action';

		$result = $db->arrayQuery( $sql );

		if( !empty( $result ) )
		{
			$return = $result;
		}

		return $return;
	}

	public function get_random_tasks()
	{
		$return = array();
		
		$db = eZDB::instance();
		
		$total = $this->get_tasks_count();
		$rand_offset = floor( $total * ( rand(0, 999) / 1000 ) );
		
		$sql = 'SELECT SUBSTR( action, 12 ) AS type, param AS id FROM ezpending_actions LIMIT '. $rand_offset . ', 1';
		
		//echo $sql;
		
		$result = $db->arrayQuery( $sql );
		
		if( !empty( $result ) )
		{
			$return = $result;
		}
		
		return $return;
	}
	
}
