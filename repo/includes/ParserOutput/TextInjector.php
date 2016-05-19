<?php

namespace Wikibase\Repo\ParserOutput;

/**
 * Helper for injecting text by substituting placeholders.
 * This class is designed to aid with the technique of putting placeholders into
 * cacheable HTML (in ParserOutput), and later replacing it with non-cacheable HTML
 * snippets (for use by OutputPage).
 *
 * @since 0.5
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class TextInjector {

	/**
	 * @var string
	 */
	private $uniqPrefix;

	/**
	 * @var int
	 */
	private $markerIndex;

	/**
	 * @var array[] Array mapping marker names to arrays of callback arguments.
	 */
	private $markers;

	/**
	 * @param array[] $markers Markers generated by another instance of TextInjector,
	 *        for use by inject(); a map of string markers associated with
	 *        parameter arrays.
	 */
	public function __construct( array $markers = array() ) {
		$this->markers = $markers;

		// idea stolen from Parser class in core
		$this->uniqPrefix = "\x7fUNIQ" . wfRandomString( 16 );
		$this->markerIndex = 0;
	}

	/**
	 * Associates a new marker with the given parameters, and returns it.
	 * All parameters passed to this function will be associated with the marker.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function newMarker( $name ) {
		$marker = '$' . $this->uniqPrefix . '#' . ++$this->markerIndex . '$';

		$this->markers[$marker] = array( $name );
		return $marker;
	}

	/**
	 * @return array[] Array mapping marker names to arrays of callback arguments.
	 */
	public function getMarkers() {
		return $this->markers;
	}

	/**
	 * Replaces the markers in $html by calling $callback for each marker in $markers,
	 * passing the arguments associated with each marker to $callback.
	 *
	 * @param string $html
	 * @param callable $callback
	 *
	 * @return string HTML
	 */
	public function inject( $html, $callback ) {
		$search = array();
		$replace = array();

		foreach ( $this->markers as $marker => $args ) {
			$subst = call_user_func_array( $callback, $args );

			if ( is_string( $subst ) ) {
				$search[] = $marker;
				$replace[] = $subst;
			}
		}

		$html = str_replace( $search, $replace, $html );
		return $html;
	}

}