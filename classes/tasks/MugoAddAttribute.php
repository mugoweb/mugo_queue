<?php

class MugoAddAttribute extends MugoTask
{
	
	private $attributeParams = array();
	private $attributeId;
	
	public function __construct()
	{
		$params = array();
		$params['classIdentifier']          = 'folder';
		
		$params['identifier']               = 'fips';
		
		// Name of attribute
		$params['name']                     = 'Fips Test';
		$params['language']                 = 'eng-US';
		// Name of datatype -- check kernel/classes/datatypes for a list of datatypes
		$params['data_type_string']         = 'ezstring';
		// No default value for most attributes
		$params['default_value']            = 'fips default';
		// Usually can translate
		$params['can_translate']            = true;
		// Yes, required
		$params['is_required']              = false;
		// Yes, searchable
		$params['is_searchable']            = true;
		// Not actually sure what this does
		$params['content']                  = '';
		// That's the order of the attribute
		// Leave blank to place it at the bottom
		$params['placement']                = false;
		// No, not info collector
		$params['is_information_collector'] = false;
		
		// objectrelation specific values
		// 1 is dropdown list
		//$params['selection_type']           = 1;
		//$params['fuzzy_match']              = false;
		// Also works with a node ID
		//$params['default_selection_node']   = 'Media/Sources';
		
		// ezmatrix specific values
		$params['matrix'] = array();
		$params['matrix']['type'] = 'Type';
		$params['matrix']['path'] = 'Path';
		$params['matrix']['title'] = 'Title';
		$params['matrix']['site_name'] = 'Site Name';
		$params['default_row_count'] = 0;
		
		$this->attributeParams = $params;
	}
		
	public function create( $parameters )
	{
		$return = array();

		/*
		 * Change class definition
		 */
		$contentClass = eZContentClass::fetchByIdentifier( $this->attributeParams[ 'classIdentifier' ] );
		
		if( $contentClass )
		{
			$classAttributeID = $this->addClassAttribute( $contentClass, $this->attributeParams );

			/*
			 * Queue up all objects that need update
			 */
			if( $classAttributeID )
			{
				$objects = eZContentObject::fetchSameClassList( $contentClass->attribute( 'id' ), false );
				
				foreach( $objects as $object )
        		{
        			$return[] = $object[ 'id' ];
        		}
			}			
		}
		else
		{
			$this->log( 'Class does not exists: ' . $this->attributeParams[ 'classIdentifier' ] );
		}
		
		return $return;
	}
	
	public function execute( $task_id, $parameters = null )
	{
		$return = false;
		
		if( ! $this->attribute_id )
		{
			$class = eZContentClass::fetchByIdentifier( $this->attributeParams[ 'classIdentifier' ] );
			$classDataMap = $class->attribute('data_map' );
		
			if( isset( $classDataMap[ $this->attributeParams[ 'identifier' ] ] ) )
			{
				$this->attribute_id = $classDataMap[ $this->attributeParams[ 'identifier' ] ]->attribute( 'id' );
			}
		}
		

		if( $this->attribute_id )
		{
			$object = eZContentObject::fetch( $task_id );
            if ( $object )
            {
                $contentobjectID = $object->attribute( 'id' );
                $objectVersions = $object->versions();
                foreach( $objectVersions as $objectVersion )
                {
                    $translations = $objectVersion->translations( false );
                    $version = $objectVersion->attribute( 'version' );
                    $dataMap = $objectVersion->attribute( 'data_map' );              
                    if( $this->attributeParams[ 'identifier' ] && isset( $dataMap[ $this->attributeParams[ 'identifier' ] ] ) )
                    {
                    	$return = true;
                    }
                    else
                    {
                        foreach( $translations as $translation )
                        {
                            $objectAttribute = eZContentObjectAttribute::create( $this->attribute_id, $contentobjectID, $version );
                            $objectAttribute->setAttribute( 'language_code', $translation );
                            $objectAttribute->initialize();
                            $objectAttribute->store();
                            $objectAttribute->postInitialize();
                            
                            $return = true;
                        }
                    }
                }
            }
		}

		return $return;
	}
	
	private function addClassAttribute( $class, $params )
	{
		$classDataMap = $class->attribute('data_map' );
		
		if( isset( $classDataMap[ $params[ 'identifier' ] ] ) )
		{
			$this->log( 'Attribute already exists.' );
			return $classDataMap[ $params[ 'identifier' ] ]->attribute( 'id' );
		}
		
		
		$classID = $class->attribute( 'id' );

		$classAttributeIdentifier = $params['identifier'];
		// This is a very precise way that eZ Publish stores this
		$classAttributeName = serialize( array( $params['language'] => $params['name'], 'always-available' => $params['language'] ) );

		$datatype = $params['data_type_string'];
		$defaultValue = isset( $params['default_value'] ) ? $params['default_value'] : false;
		$canTranslate = isset( $params['can_translate'] ) ? $params['can_translate'] : 0;
		$isRequired   = isset( $params['is_required']   ) ? $params['is_required']   : 0;
		$isSearchable = isset( $params['is_searchable'] ) ? $params['is_searchable'] : 0;
		$attrContent  = isset( $params['content'] )	   ? $params['content']	   : false;

		$attrCreateInfo = array( 'identifier' => $classAttributeIdentifier,
	                             'serialized_name_list' => $classAttributeName,
		                         'can_translate' => $canTranslate,
		                         'is_required' => $isRequired,
		                         'is_searchable' => $isSearchable );
    	$newAttribute = eZContentClassAttribute::create( $classID, $datatype, $attrCreateInfo  );

		$dataType = $newAttribute->dataType();
		if ( !$dataType )
		{
			$this->log( "\t\tUnknown datatype: '$datatype'", 'error' );
			return false;
		}
		
		$dataType->initializeClassAttribute( $newAttribute );
		$newAttribute->store();
		
		$this->updateParameters( $newAttribute, $params );
		//PK: What's sync?
		$newAttribute->sync();


		// not all datatype can have 'default_value'. do check here.
		// PK: Put the default value into function 'updateParameters'
		if( $defaultValue !== false  )
		{
			switch( $datatype )
			{
				case 'ezboolean':
				{
					$newAttribute->setAttribute( 'data_int3', $defaultValue );
				}
				break;

				default:
					break;
			}
		}

		if( $attrContent )
			$newAttribute->setContent( $attrContent );

		// store attribute, update placement, etc...
		$attributes = $class->fetchAttributes();
		$attributes[] = $newAttribute;

		// remove temporary version
		if ( $newAttribute->attribute( 'id' ) !== null )
		{
			$newAttribute->remove();
		}

		$newAttribute->setAttribute( 'version', eZContentClass::VERSION_STATUS_DEFINED );
		$newAttribute->setAttribute( 'placement', count( $attributes ) );

		$class->adjustAttributePlacements( $attributes );
		foreach( $attributes as $attribute )
		{
			$attribute->storeDefined();
		}
		$classAttributeID = $newAttribute->attribute( 'id' );
		
		$this->log( "Attribute with ID $classAttributeID added" );
		return $classAttributeID;
	}

	private function updateParameters( $classAttribute, $params )
    {
        $content = $classAttribute->content();
        
        switch( $classAttribute->DataTypeString )
        {
        	case 'ezobjectrelation':
        	{
		        $content['selection_type'] = 0;
		        if ( isset( $params[ 'selection_type' ] ) )
		        {
		            $content['selection_type'] = $params[ 'selection_type' ];
		        }
		        $content['fuzzy_match'] = false;
		        if ( isset( $params[ 'fuzzy_match' ] ) )
		        {
		            $content['fuzzy_match'] = $params[ 'fuzzy_match' ];
		        }
		        $content['default_selection_node'] = false;
		        if ( isset( $params[ 'default_selection_node' ] ) )
		        {
		            if( is_numeric( $params[ 'default_selection_node' ] ) )
		            {
		                $content['default_selection_node'] = $params[ 'default_selection_node' ];
		            }
		            else
		            {
		                $node = eZContentObjectTreeNode::fetchByURLPath( $params[ 'default_selection_node' ] );
		                if( $node )
		                {
		                    $content['default_selection_node'] = $node->attribute( 'node_id' );
		                }
		            }
		        }
		        $classAttribute->setContent( $content );
		        $classAttribute->store();
        	} break;
            case 'ezmatrix':
            {
                $matrix = new eZMatrixDefinition();
                if( !empty( $params['matrix'] ) )
                {
                    foreach( $params['matrix'] as $identifier => $name )
                    {
                        $matrix->addColumn( $name, $identifier );
                    }
                }
                $classAttribute->setContent( $matrix );
                $classAttribute->setAttribute( 'data_int1', $params['default_row_count'] );
		        $classAttribute->store();
            } break;
        	
        	default:
        }
    }
}
