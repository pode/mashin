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
