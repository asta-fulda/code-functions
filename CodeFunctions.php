<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Hook to parse <snippet>...</snippet> tags into pretty code snipets.
 *
 * @author Dustin Frisch <dustin.frisch@gmail.com>
 * @copyright Copyright © 2010 Dustin Frisch
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 3.0 or later
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once "geshi.php";

$wgHooks['ParserFirstCallInit'][] = 'codeFunctions_Setup';

$wgExtensionCredits['parserhook'][] = array(
	'version' => '0.0.1',
	'description' => 'Provides pretty code snipets in the wikitext',
	'name' => 'CodeFunctions',
	'author' => 'Dustin Frisch'
);

function codeFunctions_Setup(&$parser) {
	$parser->setHook('snippet', 'codeFunctions_render');

	return true;
}

function codeFunctions_render($input, $args, $parser, $frame) {
	// Read arguments
  $prefix = (isset($args['prefix']))
      ? $args['prefix']
			: null;
  $prefix1 = (isset($args['prefix1']))
			? $args['prefix1']
			: $prefix;
	$prefix2 = (isset($args['prefix2']))
			? $args['prefix2']
			: $prefix;
	
	$target1 = (isset($args['target1']))
      ? $args['target1']
			: null;
	$target2 = (isset($args['target2']))
      ? $args['target2']
			: null;
	
	$language = (isset($args['language']))
      ? $args['language']
			: null;

	// Build target line
	$target = ($target1 && $target2)
			? ($target1 . ' @ ' . $target2)
			: ($target1)
				  ? $target1
				  : ($target2)
				      ? $target2
							: null;

	// Parsing tags in arguments and lines
	$target = $parser->recursiveTagParse($target, $frame);
	$prefix1 = $parser->recursiveTagParse($prefix1, $frame);
	$prefix2 = $parser->recursiveTagParse($prefix2, $frame);
	$language = $parser->recursiveTagParse($language, $frame);
	$input = $parser->recursiveTagParse($input, $frame);

  // Strip leading and tailing spaces
	$input = trim($input);

	// Split input in lines for indention
	$lines = preg_split('/[\\n\\r]/', $input);
  
	// Apply syntax hilighting if requested
	if ($language) {
    $geshi = new GeSHi($input, $language);
		$geshi->set_header_type(GESHI_HEADER_NONE);
		$geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);

		$code = $geshi->parse_code();

	} else {
		$code = '';
		for ($i = 0; $i < count($lines); $i++) {
			$code .= preg_replace('/ /', '&nbsp;', $lines[$i]);
      $code .= '<br />';
		}
	}

	// Build output
	$result = '';
	
	$result .= '<div class="snippet">';
	$result .= '<table class="snippet" cellpadding="0" cellspacing="0" style="';
	$result .= '		font-family		: \'Bitstream Vera Sans Mono\', Courier, monospace;';
	$result .= '		border-style	: solid;';
	$result .= '		border-width	: 1px;';
	$result .= '		border-color	: #ECECEC;';
	$result .= '">';
	
	// Render header
	if ($target) {
		$result .= '<tr><th class="snippet" colspan="3" style="';
		$result .= '		padding-left				: 3px;';
		$result .= '		border-bottom-style	: solid;';
		$result .= '		border-bottom-width	: 1px;';
		$result .= '		border-bottom-color	: #DDDDDD;';
		$result .= '		background-color		: #ECECEC;';
		$result .= '		text-align					: left;';
		$result .= '		font-weight					: normal;';
		$result .= '">';
		$result .= $target;
		$result .= '</th></tr>';
	}
	
	$result .= '<tr>';

	// Render line number
	$result .= '<td><div class="snippet line_nr" style="';
	$result .= '		display							: block;';
	$result .= '		whitespace					: pre;';
	$result .= '		line-height					: 1.5em;';
	$result .= '		padding-left				: 0.5em;';
	$result .= '		padding-right				: 0.5em;';
	$result .= '		margin							: 0;';
	$result .= '		text-align					: right;';
	$result .= '		color								: #AAAAAA;';
	$result .= '		background-color		: #ECECEC;';
	$result .= '		border-right-style	: solid;';
	$result .= '		border-right-width	: 1px;';
	$result .= '		border-right-color	: #DDDDDD;';
	$result .= '">';
	for ($i = 0; $i < count($lines); $i++) {
		$result .= '' . $i . '<br />';
	}
	$result .= '</td>';

	// Render prefix
	$result .= '<td><div class="snippet prefix" style="';
	$result .= '		display				: block;';
	$result .= '		whitespace		: pre;';
	$result .= '		color					: #AAAAAA;';
	$result .= '		line-height		: 1.5em;';
	$result .= '		padding-left	: 0.5em;';
	$result .= '		padding-right	: 0.5em;';
	$result .= '		margin		: 0;';
	$result .= '">';
	for ($i = 0; $i < count($lines); $i++) {
		if (substr($lines[$i], 0, 2) != '  ') {
			$result .= $prefix1;
		} else {
			$result .= $prefix2;
		}
		$result .= '<br />';
	}
	$result .= '</td>';
	
	// Render lines
	$result .= '<td width="100%"><div class="snippet line" style="';
	$result .= '		display			: block;';
	$result .= '		whitespace	: pre;';
	$result .= '		line-height	: 1.5em;';
	$result .= '		margin			: 0;';
	$result .= '">		';
	$result .= $code;
	$result .= '</td>';

	$result .= '</tr></table>';
	$result .= '</div>';

  // Strip line breaks to avoid wiki parsing
	$result = preg_replace('/[\\n\\r]/', '', $result);

	return $result;
}
?>
