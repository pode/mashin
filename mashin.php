<?php

/*

Copyright 2009 ABM-utvikling

This file is part of "Podes mashin".

"Podes mashin" is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published bythe Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
 "Podes mashin" is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with "Podes mashin". If not, see <http://www.gnu.org/licenses/>.
        
Source code available from:
http://github.com/pode/mashin/
                        
This code is intended to be used with the open source ILS Koha.

*/

if (!empty($_GET['id'])) {

	include_once('config.php');
	require('File/MARCXML.php');

	// $_GET['id'] will be on the form koha:biblionumber:123, so chop off "koha:biblionumber:"
	$biblionumber = substr($_GET['id'], 18);
	
	// Chesk that $biblionumber can be cast to an integer
	if (!is_int((int) $biblionumber)) { exit; }
	
	// Get the MARC record from SRU
	$version = '1.2';
	$query = "rec.id=$biblionumber";
	$recordSchema = 'marcxml';
	$startRecord = 1; 
	$maximumRecords = 1;
	
	// Build the SRU url
	$sru_url = $config['sru'];

	$sru_url .= "?operation=searchRetrieve";
	$sru_url .= "&version=$version";
	$sru_url .= "&query=$query";
	$sru_url .= "&recordSchema=$recordSchema";
	$sru_url .= "&startRecord=$startRecord";
	$sru_url .= "&maximumRecords=$maximumRecords";
	
	// Fetch the SRU data
	$sru_data = file_get_contents($sru_url) or exit("SRU error");
	
	// Turn the returned XML in to pure MARCXML
	$sru_data = str_replace("<record xmlns=\"http://www.loc.gov/MARC21/slim\">", "<record>", $sru_data);
	preg_match_all('/(<record>.*?<\/record>)/si', $sru_data, $treff);
	$marcxml = implode("\n\n", $treff[0]);
	$marcxml = '<?xml version="1.0" encoding="utf-8"?>' . "\n<collection>\n$marcxml\n</collection>";
	
	// Parse the XML
	$records = new File_MARCXML($marcxml, File_MARC::SOURCE_STRING);
	// Get the first (and only) record
	$record = $records->next();

	$out = '';
	// Decide what to do based on the contents of the MARC record
	
	// Journals
	// Fetch TOC (most recent articles) by way of Journal TOCs
	// http://www.journaltocs.hw.ac.uk/index.php?action=api
	
	if ($record->getField("022") && $record->getField("022")->getSubfield("a")) {
	
	  $issn = marctrim($record->getField("022")->getSubfield("a"));
	  if ($issn) {
	    
	    $url = 'http://www.journaltocs.hw.ac.uk/api/journals/' . $issn . '?output=articles';
	    $xml = simplexml_load_file($url);

	    $xml->registerXPathNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#"); 
	    $xml->registerXPathNamespace("prism", "http://prismstandard.org/namespaces/1.2/basic/"); 
	    $xml->registerXPathNamespace("dc", "http://purl.org/dc/elements/1.1/");
	    $xml->registerXPathNamespace("mn", "http://usefulinc.com/rss/manifest/");
	    $xml->registerXPathNamespace("content", "http://purl.org/rss/1.0/modules/content/");

	    echo('<div id="mashin" style="background-color: #F3F3F3; padding: 3px 3px 0.5em 1em; border: 1px solid #E8E8E8; margin-top: 0.5em;"><h4>Nyeste artikler (New articles)</h4><ul>');

	    foreach($xml->item as $item) {

	      $namespaces = $item->getNameSpaces(true);
	      $dc = $item->children($namespaces['dc']);
	      
	      $creator = $dc->creator;
	      $link = $item->link;
	      $title = $item->title;
	      
	      echo("<li>$creator: <a href=\"$link\">$title</a></li>");
	      
	    }

	    echo('</ul></div>');
	    
	  }
	  
	}
	
	// Music
	if ($record->getField("245") && $record->getField("245")->getSubfield("h") && marctrim($record->getField("245")->getSubfield("h")) == 'lydopptak') {
	
		$artist = '';
		if ($record->getField("100") && $record->getField("100")->getSubfield("a")) {
			$artist = deinvert(marctrim($record->getField("100")->getSubfield("a")));
		} elseif ($record->getField("110") && $record->getField("110")->getSubfield("a")) {
			$artist = marctrim($record->getField("110")->getSubfield("a"));
		} elseif ($record->getField("700") && $record->getField("700")->getSubfield("a")) {
			$artist = deinvert(marctrim($record->getField("700")->getSubfield("a")));
		} elseif ($record->getField("710") && $record->getField("710")->getSubfield("a")) {
			$artist = marctrim($record->getField("710")->getSubfield("a"));
		}
		
		// Artist
		
		$url = $config['lastfm']['api_root'];
		$url .= "?method=artist.getinfo";
		$url .= "&api_key=" . $config['lastfm']['api_key'];
		$url .= "&artist=" . urlencode($artist);
		$url .= "&format=json";
		
		if ($artist_data = json_decode(file_get_contents($url), true)) {
			// Check for errors
			if ($artist_data['error']) {
				$out .= "<p>Sorry, something went wrong!<br />({$artist_data['message']})</p>";
				exit;
			}
			// Name
			$out .= '<p style="font-weight: bold;">' . $artist_data['artist']['name'] . '</p>';
			// Image
			if ($artist_data['artist']['image'][2]['#text']) {
				$out .= '<p><img src="' . $artist_data['artist']['image'][2]['#text'] . '" alt="' . $artist_data['artist']['name'] . '" title="' . $artist_data['artist']['name'] . '" /></p>';
			}
			// Description
			if ($artist_data['artist']['bio']['summary']) {
				$out .= '<p>' . remove_lastfm_links($artist_data['artist']['bio']['summary'])  . '</p>';
			}
			if (is_array($artist_data['artist']['similar']['artist'])) {
				$out .= '<p>Lignende artister:</p>';
				$out .= '<ul>';		
				foreach($artist_data['artist']['similar']['artist'] as $sim) {
					$out .= '<li><a href="/cgi-bin/koha/opac-search.pl?q=' . urlencode($sim['name']) . '">' . $sim['name'] . '</a></li>';
				}
				$out .= '</ul>';
			}
			// Link to Last.fm
			$out .= '<p><a href="' . $artist_data['artist']['url'] . '">Les mer hos Last.fm</a></p>';
		}
		
		$out .= '<hr />';
		
		// Album
		
		$album = marctrim($record->getField("245")->getSubfield("a"));
		$url = $config['lastfm']['api_root'];
		$url .= "?method=album.getinfo";
		$url .= "&api_key=" . $config['lastfm']['api_key'];
		$url .= "&artist=" . urlencode($artist);
		$url .= "&album=" . urlencode($album);
		$url .= "&format=json";
		
		if ($album = json_decode(file_get_contents($url), true)) {
			// Check for errors
			if ($album['error']) {
				$out .= "<p>Beklager, det oppstod en feil!<br />({$album['message']})</p>";
				exit;
			}
			// Title
			$out .= '<p style="font-weight: bold;">' . $album['album']['name'] . '</p>';
			// Image
			if ($album['album']['image'][2]['#text']) {
				$out .= '<p class="albumbilde"><img src="' . $album['album']['image'][2]['#text'] . '" alt="' . $album['album']['name'] . '" title="' . $album['album']['name'] . '" /></p>';
			}
			// Description
			if ($album['album']['wiki']['summary']) {
				$out .= '<p>' . remove_lastfm_links($album['album']['wiki']['summary'])  . '</p>';
			}
			// Link to Last.fm
			$out .= '<p class="les-mer"><a href="' . $album['album']['url'] . '">Les mer hos Last.fm</a></p>';
		}
		
	} 

	// Return output
	if ($out) {
		echo('<div id="mashin" style="background-color: #F3F3F3; padding: 3px 3px 0.5em 1em; border: 1px solid #E8E8E8; margin-top: 0.5em;">');
		echo($out);
		echo('</div>');
	}

} else {

	echo('<div style="color: red;">Error! biblionumber not found!</div>');
	
}

/*
De-invert personal names, eg: 
Wesseltoft, Bugge -> Bugge Wesseltoft
*/
function deinvert($s) {
	// Check that the string actually contains a comma
	if (substr_count($s, ',', 2) > 0) {
		list($first, $last) = split(', ', $s);
		return "$last $first";
	} else {
		return $s;
	}
}

/*
Links in the description text don't make sense. 
*/
function remove_lastfm_links($s) {
	return preg_replace("/<a .*?>(.*?)<\/a>/i", "$1", $s);
}

/*
For some reason this:
$post->getField("zzz")->getSubfield("a")
always returns this:
[a]: Title...
This function chops off the first 5 characters...
*/

function marctrim($s) {
	return substr($s, 5);
}

?>
