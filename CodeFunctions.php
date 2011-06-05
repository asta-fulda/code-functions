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

$wgHooks['ParserFirstCallInit'][] = 'codeFunctions_Setup';

$wgExtensionCredits['parserhook'][] = array(
	'version' => '0.0.1',
	'description' => 'Provides pretty code snipets in the wikitext',
	'name' => 'CodeFunctions',
	'author' => 'Dusitn Frisch'
);

function codeFunctions_Setup(&$parser) {
	$parser->setHook('snippet', 'codeFunctions_hook');

	return true ;
}

function codeFunctions_hook($input, $args, $parser, $frame) {
	// Read arguments
	$prefix1 = isset($args['prefix1'])
			? $args['prefix1']
			: (isset($args['prefix'])
				? $args['prefix']
				: '');
	$prefix2 = isset($args['prefix2'])
			? $args['prefix2']
			: $prefix1;
	
	$target1 = $args['target1'];
	$target2 = $args['target2'];
	
	$target = (isset($target1) && isset($target2))
			? ($target1 . ' @ ' . $target2)
			: (isset($target1)
				? $target1
				: $target2);
	
	// Parsing tags in arguments and lines
	$target = $parser->recursiveTagParse($target, $frame);
	$prefix1 = $parser->recursiveTagParse($prefix1, $frame);
	$prefix2 = $parser->recursiveTagParse($prefix2, $frame);
	$input = $parser->recursiveTagParse($input, $frame);
	
	// Get lines
	$lines = preg_split('/[\\n\\r]/', trim($input));

	// Build output
	$result = '';
	
	$result .= '<div class="snippet">								';
	$result .= '<table										';
	$result .= '	class="snippet"									';
	$result .= '	cellpadding="0"									';
	$result .= '	cellspacing="0"									';
	$result .= '	style="										';
	$result .= '		font-family	: \'Bitstream Vera Sans Mono\', Courier, monospace;	';
	$result .= '		border-style	: solid;						';
	$result .= '		border-width	: 1px;							';
	$result .= '		border-color	: #ECECEC;						';
	$result .= '	">										';
	
	// Render header
	if (isset($target)) {
		$result .= '<tr><th											';
		$result .= '	class="snippet"										';
		$result .= '	colspan="3"										';
		$result .= '	style="											';
		$result .= '		padding-left		: 3px;							';
		$result .= '		border-bottom-style	: solid;						';
		$result .= '		border-bottom-width	: 1px;							';
		$result .= '		border-bottom-color	: #DDDDDD;						';
		$result .= '		background-color	: #ECECEC;						';
		$result .= '		text-align		: left;							';
		$result .= '		font-weight		: normal;						';
		$result .= '	">											';
		$result .= $target;
		$result .= '</th></tr>											';
	}
	
	$result .= '<tr>';

	// Render line number
	$result .= '<td><div						';
	$result .= '	class="snippet line_nr"				';
	$result .= '	style="						';
	$result .= '		display			: block;	';
	$result .= '		whitespace		: pre;		';
	$result .= '		line-height		: 1.5em;	';
	$result .= '		padding-left		: 0.5em;	';
	$result .= '		padding-right		: 0.5em;	';
	$result .= '		margin			: 0;		';
	$result .= '		text-align		: right;	';
	$result .= '		color			: #AAAAAA;	';
	$result .= '		background-color	: #ECECEC;	';
	$result .= '		border-right-style	: solid;	';
	$result .= '		border-right-width	: 1px;		';
	$result .= '		border-right-color	: #DDDDDD;">	';
	for ($i = 0; $i < count($lines); $i++) {
		$result .= '' . $i . '<br />';
	}
	$result .= '</td>';

	// Render prefix
	$result .= '<td><div					';
	$result .= '	class="snippet prefix"			';
	$result .= '	style="	display		: block;	';
	$result .= '		whitespace	: pre;		';
	$result .= '		color		: #AAAAAA;	';
	$result .= '		line-height	: 1.5em;	';
	$result .= '		padding-left	: 0.5em;	';
	$result .= '		padding-right	: 0.5em;	';
	$result .= '		margin		: 0;">		';
	for ($i = 0; $i < count($lines); $i++) {
		if (substr($lines[$i], 0, 1) != ' ') {
			$result .= str_replace(' ', '&nbsp;', $prefix1);
		} else {
			$result .= str_replace(' ', '&nbsp;', $prefix2);
		}
		$result .= '<br />';
	}
	$result .= '</td>';
	
	// Render lines
	$result .= '<td width="100%"><div			';
	$result .= '	class="snippet line"			';
	$result .= '	style="	display		: block;	';
	$result .= '		whitespace	: pre;		';
	$result .= '		line-height	: 1.5em;	';
	$result .= '		margin		: 0;">		';
	for ($i = 0; $i < count($lines); $i++) {
		$result .= str_replace(' ', '&nbsp;', $lines[$i]) . "<br />";
	}
	$result .= '</td>';

	$result .= '</tr></table>';
	$result .= '</div>';
	
	return $result;
}
?>
