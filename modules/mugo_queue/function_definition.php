<?php
$FunctionList = array();

$FunctionList[ 'get_pagination_steps' ] = array(
		'name' => 'Get pagination steps',
		'call_method' => array(
				'class' => 'MugoQueueFetchFunctions',
				'method' => 'getPaginationSteps' ),
		'parameter_type' => 'standard',
		'parameters' => array(
				array(  'name'     => 'current_page',
						'type'     => 'integer',
						'required' => true
				),
				array(  'name'     => 'page_count',
					'type'     => 'integer',
					'required' => true
				),
		)
);
