<?php

namespace Wikibase\Lib\Store;

/**
 * Interface that contains method for the PropertyOrderProvider
 *
 * @license GNU GPL v2+
 * @author Lucie-Aimée Kaffee
 */
interface PropertyOrderProvider {

	/**
	 * Get order of properties in the form array( $propertyIdSerialization => $ordinalNumber )
	 *
	 * @return null|int[] An associative array mapping property ID strings to ordinal numbers.
	 * 	The order of properties is represented by the ordinal numbers associated with them.
	 * 	The array is not guaranteed to be sorted.
	 * 	Null if no information exists.
	 * @throws PropertyOrderProviderException
	 */
	public function getPropertyOrder();

}
