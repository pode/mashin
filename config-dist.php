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

// Configuration file for "mashin"

// Base URL for SRU
// Change this to the base URL of your own installation
$config['sru'] = 'http://torfeus.deich.folkebibl.no:9999/biblios';

// Last.fm
// Get your own API key here: http://www.last.fm/api
$config['lastfm'] = array(
	'api_key' => 'YOUR API KEY', 
	'api_root' => 'http://ws.audioscrobbler.com/2.0/'
);

?>
