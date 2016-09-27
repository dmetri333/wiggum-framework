<?php
namespace wiggum\commons;

use \wiggum\http\Request;

abstract class Handler {
	
	/**
	 *
	 * @var array
	 */
	protected $knownContentTypes = [ 
			'application/json',
			'application/xml',
			'text/xml',
			'text/html' 
	];
	
	/**
	 *
	 * @param Request $request        	
	 * @return string
	 */
	protected function determineContentType(Request $request) {
		$acceptHeader = $request->getHeader('Accept');
		$selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);
		
		if (count($selectedContentTypes)) {
			return current($selectedContentTypes);
		}
		
		// handle +json and +xml specially
		if (preg_match ('/\+(json|xml)/', $acceptHeader, $matches)) {
			$mediaType = 'application/' . $matches[1];
			if (in_array($mediaType, $this->knownContentTypes)) {
				return $mediaType;
			}
		}
		
		return 'text/html';
	}

	/**
	 * Render error as HTML.
	 *
	 * @param \Exception|\Throwable $error
	 *
	 * @return string
	 */
	protected function renderErrorAsHtml($error) {
		$html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($error));
	
		if (($code = $error->getCode())) {
			$html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
		}
	
		if (($message = $error->getMessage())) {
			$html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
		}
	
		if (($file = $error->getFile())) {
			$html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
		}
	
		if (($line = $error->getLine())) {
			$html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
		}
	
		if (($trace = $error->getTraceAsString())) {
			$html .= '<h3>Trace</h3>';
			$html .= sprintf('<pre>%s</pre>', htmlentities($trace));
		}
	
		return $html;
	}
	
	/**
	 * Render error as Text.
	 *
	 * @param \Exception|\Throwable $error
	 *
	 * @return string
	 */
	protected function renderErrorAsText($error) {
		$text = sprintf('Type: %s' . PHP_EOL, get_class($error));
	
		if ($code = $error->getCode()) {
			$text .= sprintf('Code: %s' . PHP_EOL, $code);
		}
	
		if ($message = $error->getMessage()) {
			$text .= sprintf('Message: %s' . PHP_EOL, htmlentities($message));
		}
	
		if ($file = $error->getFile()) {
			$text .= sprintf('File: %s' . PHP_EOL, $file);
		}
	
		if ($line = $error->getLine()) {
			$text .= sprintf('Line: %s' . PHP_EOL, $line);
		}
	
		if ($trace = $error->getTraceAsString()) {
			$text .= sprintf('Trace: %s', $trace);
		}
	
		return $text;
	}
	
	/**
	 * Render error as Array.
	 *
	 * @param \Exception|\Throwable $error
	 *
	 * @return array
	 */
	protected function renderErrorAsArray($error) {
		return [
			'type' => get_class($error),
			'code' => $error->getCode(),
			'message' => $error->getMessage(),
			'file' => $error->getFile(),
			'line' => $error->getLine(),
			'trace' => explode("\n", $error->getTraceAsString())
		];
	}
	
	/**
	 * Render error as Xml.
	 *
	 * @param \Exception|\Throwable $error
	 *
	 * @return array
	 */
	protected function renderErrorAsXml($error) {
		$xml = "";
		
		$xml .= "  <error>\n";
		$xml .= "    <type>" . get_class($error) . "</type>\n";
		$xml .= "    <code>" . $error->getCode() . "</code>\n";
		$xml .= "    <message>" . $this->createCdataSection($error->getMessage()) . "</message>\n";
		$xml .= "    <file>" . $error->getFile() . "</file>\n";
		$xml .= "    <line>" . $error->getLine() . "</line>\n";
		$xml .= "    <trace>" . $this->createCdataSection($error->getTraceAsString()) . "</trace>\n";
		$xml .= "  </error>\n";
		
		return $xml;
	}
	
	/**
	 * Returns a CDATA section with the given content.
	 *
	 * @param string $content
	 * @return string
	 */
	private function createCdataSection($content) {
		return sprintf('<![CDATA[%s]]>', str_replace(']]>', ']]]]><![CDATA[>', $content));
	}
	
}
