<?php

namespace Wikibase\DataAccess;

use Wikibase\Client\WikibaseClient;
use Wikibase\DataModel\Services\EntityId\PrefixMappingEntityIdParserFactory;
use Wikibase\DataModel\Services\Lookup\UnknownForeignRepositoryException;
use Wikibase\Lib\EntityIdComposer;
use Wikibase\Lib\Serialization\RepositorySpecificDataValueDeserializerFactory;

/**
 * A factory providing PerRepositoryServiceContainer objects configured for given repository.
 * PerRepositoryServiceContainers are initialized using wiring files provided in the constructor.
 *
 * @license GPL-2.0+
 */
class PerRepositoryServiceContainerFactory {

	/**
	 * @var PrefixMappingEntityIdParserFactory
	 */
	private $idParserFactory;

	/**
	 * @var EntityIdComposer
	 */
	private $idComposer;

	/**
	 * @var RepositorySpecificDataValueDeserializerFactory
	 */
	private $dataValueDeserializerFactory;

	/**
	 * Associative array mapping repository names to database names (string or false)
	 *
	 * @var array
	 */
	private $databaseNames;

	/**
	 * @var string[]
	 */
	private $wiringFiles;

	/**
	 * @var WikibaseClient
	 */
	private $client;

	/**
	 * FIXME: injecting of the top-level factory (WikibaseClient) here is only a temporary solution.
	 * The instance of the top-level factory is being passed to instantiators of services
	 * stored in the PerRepositoryServiceContainer so they can get the service they depend on.
	 *
	 * This approach is not clean, the class should not depend on the top-level factory.
	 * This should be changed after some refactoring: Instantiators in PerRepositoryServiceWiring should
	 * rather be getting some other service container, not the whole top-level factory.
	 *
	 * @param PrefixMappingEntityIdParserFactory $idParserFactory
	 * @param EntityIdComposer $idComposer
	 * @param RepositorySpecificDataValueDeserializerFactory $dataValueDeserializerFactory
	 * @param array $repositoryDatabaseNames
	 * @param string[] $wiringFiles
	 * @param WikibaseClient $client
	 */
	public function __construct(
		PrefixMappingEntityIdParserFactory $idParserFactory,
		EntityIdComposer $idComposer, //  TODO: change ID Composer and pass a factory of prefixing composer (T165589)
		RepositorySpecificDataValueDeserializerFactory $dataValueDeserializerFactory,
		array $repositoryDatabaseNames,
		array $wiringFiles,
		WikibaseClient $client
	) {
		$this->idParserFactory = $idParserFactory;
		$this->idComposer = $idComposer;
		$this->dataValueDeserializerFactory = $dataValueDeserializerFactory;
		$this->databaseNames = $repositoryDatabaseNames;
		$this->wiringFiles = $wiringFiles;
		$this->client = $client;
	}

	/**
	 * @param string $repositoryName
	 *
	 * @return PerRepositoryServiceContainer
	 *
	 * @throws UnknownForeignRepositoryException
	 */
	public function newContainer( $repositoryName ) {
		if ( !array_key_exists( $repositoryName, $this->databaseNames ) ) {
			throw new UnknownForeignRepositoryException( $repositoryName );
		}

		$container = new PerRepositoryServiceContainer(
			$this->databaseNames[$repositoryName],
			$repositoryName,
			$this->idParserFactory->getIdParser( $repositoryName ),
			$this->idComposer,
			$this->dataValueDeserializerFactory->getDeserializer( $repositoryName ),
			$this->client
		);
		$container->loadWiringFiles( $this->wiringFiles );

		return $container;
	}

}