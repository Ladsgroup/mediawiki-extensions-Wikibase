<?php

namespace Wikibase\Lib;

use InvalidArgumentException;

/**
 * Service that manages entity type definition. This is a registry that provides access to factory
 * functions for various services associated with entity types, such as serializers.
 *
 * EntityTypeDefinitions provides a one-stop interface for defining entity types.
 * Each entity type is defined using a "entity type definition" array.
 * A definition array has the following fields:
 * - serializer-factory-callback: a callback for creating a serializer for entities of this type
 *   (requires a SerializerFactory to be passed to it)
 * - deserializer-factory-callback: a callback for creating a deserializer for entities of this type
 *   (requires a DeserializerFactory to be passed to it)
 *
 * @see docs/entitytypes.wiki
 *
 * @licence GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class EntityTypeDefinitions {

	/**
	 * @var array[]
	 */
	private $entityTypeDefinitions;

	/**
	 * @param array[] $entityTypeDefinitions Map from entity types to entity definitions
	 *        See class level documentation for details
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( array $entityTypeDefinitions ) {
		foreach ( $entityTypeDefinitions as $id => $def ) {
			if ( !is_string( $id ) || !is_array( $def ) ) {
				throw new InvalidArgumentException( '$entityTypeDefinitions must be a map from string to arrays' );
			}
		}

		$this->entityTypeDefinitions = $entityTypeDefinitions;
	}

	/**
	 * @param string $field
	 *
	 * @return array
	 */
	private function getMapForDefinitionField( $field ) {
		$fieldValues = array();

		foreach ( $this->entityTypeDefinitions as $id => $def ) {
			if ( isset( $def[$field] ) ) {
				$fieldValues[$id] = $def[$field];
			}
		}

		return $fieldValues;
	}

	/**
	 * @return callable[]
	 */
	public function getSerializerFactoryCallbacks() {
		return $this->getMapForDefinitionField( 'serializer-factory-callback' );
	}

	/**
	 * @return callable[]
	 */
	public function getDeserializerFactoryCallbacks() {
		return $this->getMapForDefinitionField( 'deserializer-factory-callback' );
	}

	/**
	 * @return callable[]
	 */
	public function getChangeFactoryCallbacks() {
		return $this->getMapForDefinitionField( 'change-factory-callback' );
	}

}
