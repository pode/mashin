Copyright 2009 ABM-utvikling
 
This file is part of "Podes mashin".
 
"Podes mashin" is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
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

* INSTALLATION

1. Rename the file config-dist.php to config.php prior to use: 
mv config-dist.php config.php

2. The following piece of code should be inserted into the "opacuserjs" system preference. 

"/~magnus/mashin/mashin.php" should be changed to point to the actual location of the script mashin.php on your server.  

----------------------------8<---------------------------

jQuery(document).ready(function () {
	var id = $(".unapi-id").attr("title");
	$.get("/~magnus/mashin/mashin.php", { id: id },
		function(text){
			$("#action").after(text);
		}
	); 
});

----------------------------8<---------------------------

* CUSTOMIZATION

See config.php for further customizations. 
