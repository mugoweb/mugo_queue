<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/*
 * Wrapper around a queue solution
 */
class MugoQueueRabbitMq extends MugoQueue
{
	
	public function add_tasks( $task_type_id, $task_ids )
	{
		$exchange = 'router';
		$queue = 'msgs';
		
		$conn = new AMQPConnection( 'localhost', 5672, 'guest', 'guest', '/' );
		$ch = $conn->channel();

		/*
		    name: $queue
		    passive: false
		    durable: true // the queue will survive server restarts
		    exclusive: false // the queue can be accessed in other channels
		    auto_delete: false //the queue won't be deleted once the channel is closed.
		*/
		$ch->queue_declare($queue, false, true, false, false);
		
		/*
		    name: $exchange
		    type: direct
		    passive: false
		    durable: true // the exchange will survive server restarts
		    auto_delete: false //the exchange won't be deleted once the channel is closed.
		*/
		
		$ch->exchange_declare($exchange, 'direct', false, true, false);
		
		$ch->queue_bind($queue, $exchange);
		
		$msg_body = 'Fips here';
		$msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
		$ch->basic_publish($msg, $exchange);
		
		$ch->close();
		$conn->close();
	}

	//TODO: put it back to just return a list of task ids
	public function get_tasks( $task_type_id = null, $limit = false )
	{
		echo 'Not supported' . "\n";
	}
	
	public function remove_tasks( $task_type_id, $object_ids = null )
	{
		echo 'Not supported' . "\n";
	}
	
	public function get_tasks_count( $task_type_id = null )
	{
		echo 'Not supported' . "\n";
	}
	
	public function get_random_tasks()
	{
		echo 'Not supported' . "\n";
	}
	
}

?>