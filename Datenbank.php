<?php

function konzerte_accordion_datenbank_shortcode ($atts) {
  
  $servername = "";
  $username = "";
  $password = "";
  $dbname = "";
  
  $accordion_items = '';
  $termine = array();
  
  $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  $stmt = $conn->prepare("SELECT konzerte.*, location.*, programm.* FROM konzerte
  						  LEFT JOIN location ON location.id = konzerte.id_location
						  LEFT JOIN programm ON programm.id = konzerte.id_programm
						  WHERE datum >= :jetzt AND datum < :naechstes_jahr
						  ORDER BY konzerte.datum ASC");
  $stmt->execute(array('jetzt' => date("Y-m-d H:i:s"), 'naechstes_jahr' => (date("Y")+1).'-01-01 00:00:00'));
  
  while ($row = $stmt->fetch()) {
	$termine[] = $row;
  }
  
  foreach ($termine as $value) {
	
	//Stadt:
	if ($value['ausgabe_stadt'] == NULL) {
	  $stadt = $value['stadt'];
	  if ($value['stadtteil'] != NULL) {
		$stadt .= '-'.$value['stadtteil'];
	  }
	}
	else {
	  $stadt = $value['ausgabe_stadt'];
	}
	
	//Datum:
	$datum = substr($value['datum'], 8, 2).'.'.substr($value['datum'], 5, 2).'.'.substr($value['datum'], 0, 4);
	
	if (substr($value['datum'], 11, 2) != '00' AND substr($value['datum'], 11, 2) != NULL) {
	  $datum .= ', '.substr($value['datum'], 11, 2);
	  
	  if (substr($value['datum'], 14, 2) != '00' AND substr($value['datum'], 14, 2) != NULL) {
		$datum .= ':'.substr($value['datum'], 14, 2);
	  }
	  $datum .= ' Uhr';
	}
	
	//Dauer:
	if ($value['abweichende_dauer']) {
	  if (substr($value['abweichende_dauer'], 3, 2) == '00'){
		$dauer = substr($value['abweichende_dauer'], 1, 1);
		$dauer .= ($dauer == '1') ? ' Stunde' : ' Stunden';
	  }
	  else {
		$dauer = (substr($value['abweichende_dauer'], 0, 2)*60+substr($value['abweichende_dauer'], 3, 2)).' Minuten';
	  }
	}
	else {
	  if (substr($value['dauer'], 3, 2) == '00'){
		$dauer = substr($value['dauer'], 1, 1);
		$dauer .= ($dauer == '1') ? ' Stunde' : ' Stunden';
	  }
	  else {
		$dauer = (substr($value['dauer'], 0, 2)*60+substr($value['dauer'], 3, 2)).' Minuten';
	  }
	}
	
	//Pause:
	if ($value['abweichende_pause']) {
	  switch($value['abweichende_pause']) {
		case 'mit Pause':
		  $pause = 'ja';
		  break;
		default:
		  $pause = 'nein';
	  }
	}
	else {
	  switch($value['pause']) {
		case 'mit Pause':
		  $pause = 'ja';
		  break;
		default:
		  $pause = 'nein';
	  }
	}
	
	$content = do_shortcode('[Konzert 
							  stadt="'.$stadt.'"
							  ort="'.$value['name'].'"
							  ort_adresse="'.$value['strasse'].'&nbsp'.$value['hausnummer'].'"
							  ort_plz="'.$value['plz'].'"
							  ort_lat="'.$value['koordinaten_lat'].'"
							  ort_lng="'.$value['koordinaten_long'].'"
							  programm="'.$value['titel'].'"
							  dauer="'.$dauer.'"
							  pause="'.$pause.'"
							  eintritt_normal="'.$value['eintritt_normal'].'"
							  eintritt_ermaessigt="'.$value['eintritt_ermaessigt'].'"
							  eintritt_andere="'.$value['eintritt_andere'].'"
							  vorverkaufsstelle="'.$value['vorverkaufsstelle'].'"
							  vorverkaufsstelle_alternative="'.$value['vorverkaufsstelle_alternative'].'"
							  vorverkaufsstelle_alternative_link="'.$value['vorverkaufsstelle_alternative_link'].'"
							  vorverkauf_aktiv="'.$value['vorverkauf_aktiv'].'"
							  vorverkauf_link="'.$value['vorverkauf_link'].'"
							  zusatztext="'.$value['accordion_kommentar'].'"
							  ]');
	$content = '<p>'.$content.'</p>';
		
	$accordion_items .= do_shortcode('[accordion_item title="'.$datum.': '.$stadt.($value['abgesagt']?' ABGESAGT!':'').'"]'.$content.'[/accordion_item]');
  }
  
  return(do_shortcode('[accordion title="Unsere nächsten Konzerte:" open1st="0" openAll="0" style=""]'.$accordion_items.'[/accordion]'));

}

add_shortcode('concerts_accordion', 'konzerte_accordion_datenbank_shortcode');

function konzerte_preview_datenbank_shortcode ($atts) {
  
  $werte = shortcode_atts(array('years' => 1), $atts);
  
  $servername = "";
  $username = "";
  $password = "";
  $dbname = "";
  
  $content = '';
  $termine = array();
  
  $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  $stmt = $conn->prepare("SELECT konzerte.datum, konzerte.archiv_kommentar, location.stadt, location.stadtteil, location.ausgabe_stadt, location.name FROM konzerte
  						  LEFT JOIN location ON location.id = konzerte.id_location 
						  WHERE datum >= :jetzt AND datum < :naechstes_jahr
						  ORDER BY konzerte.datum ASC");
  $stmt->execute(array('jetzt' => date("Y-m-d H:i:s"), 'naechstes_jahr' => (date("Y")+$werte['years']).'-01-01 00:00:00'));
  
  while ($row = $stmt->fetch()) {
	$termine[] = $row;
  }
  
  $content = '<h3>Unsere nächsten Konzerte:</h3><br><dl>';
  
  foreach ($termine as $value) {
	$content .= '<dt>'.substr($value['datum'], 8, 2).'.'.substr($value['datum'], 5, 2).'.'.substr($value['datum'], 0, 4).'</dt> <dd><b>';
	
	if ($value['ausgabe_stadt'] == NULL) {
	  $content .= $value['stadt'];
	  if ($value['stadtteil'] != NULL) {
		$content .= '-'.$value['stadtteil'];
	  }
	}
	else {
	  $content .= $value['ausgabe_stadt'];
	}
	
	$content .= '</b>';
	
	if ($value['name'] != NULL) {
	  $content .= ' – '.$value['name'];
	}
	
	if ($value['archiv_kommentar'] != NULL) {
	  $content.= '</br><i>'.$value['archiv_kommentar'].'</i>';
	}
	
	$content .= '</dd>';
  }
  
  $content .= '</dl>';
  
  return($content);
}

add_shortcode('concerts_preview', 'konzerte_preview_datenbank_shortcode');

function naechste_konzerte_datenbank_shortcode ($atts) {
  
  $werte = shortcode_atts(array('limit' => '3'), $atts);
  
  $servername = "";
  $username = "";
  $password = "";
  $dbname = "";
  
  $content = '';
  $termine = array();
    
  $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  
  $stmt = $conn->prepare("SELECT konzerte.datum, konzerte.abgesagt, location.stadt, location.stadtteil, location.ausgabe_stadt, location.name FROM konzerte
  						  LEFT JOIN location ON location.id = konzerte.id_location 
						  WHERE datum >= :jetzt
						  ORDER BY konzerte.datum ASC
						  LIMIT :limit");
  $stmt->execute(array('jetzt' => date("Y-m-d H:i:s"), 'limit' => $werte['limit']));
  
  while ($row = $stmt->fetch()) {
	$termine[] = $row;
  }
  
  foreach ($termine as $value) {
	$content .= '<p>'.substr($value['datum'], 8, 2).'.'.substr($value['datum'], 5, 2).'.'.substr($value['datum'], 0, 4);
	
	if (substr($value['datum'], 11, 2) != '00' AND substr($value['datum'], 11, 2) != NULL) {
	  $content .= ', '.substr($value['datum'], 11, 2);
	  
	  if (substr($value['datum'], 14, 2) != '00' AND substr($value['datum'], 14, 2) != NULL) {
		$content .= ':'.substr($value['datum'], 14, 2);
	  }
	  $content .= ' Uhr';
	}
	
	$content .= '<br>';
	
	if ($value['ausgabe_stadt'] == NULL) {
	  $content .= $value['stadt'];
	  if ($value['stadtteil'] != NULL) {
		$content .= '-'.$value['stadtteil'];
	  }
	}
	else {
	  $content .= $value['ausgabe_stadt'];
	}
	
	$content .= '</b>';
	
	if ($value['name'] != NULL) {
	  $content .= ' – '.$value['name'];
	}
	if ($value['abgesagt']) {
	  $content .= '</br><strong>ABGESAGT!</strong>';
	}	
	$content .= '</p>'.do_shortcode('[divider height="5" style="default" line="default" themecolor="1"]');
  }
  
  $content .= '<p><a href="/wordpress/konzerte">' . do_shortcode('[icon type="icon-right-thin"]') . 'mehr Konzerte</a></p>';
  
  return($content);
  
}

add_shortcode('next_concerts', 'naechste_konzerte_datenbank_shortcode');

function konzerte_teaser_shortcode ($atts) {
  
  $werte = shortcode_atts(array('id' => NULL, 'show_title' => 'yes', 'image' => NULL, 'image_zoom' => NULL, 'image_width' => '1/4'), $atts);
  
  $servername = "";
  $username = "";
  $password = "";
  $dbname = "";
  
  $content = '';
  
  $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  $stmt = $conn->prepare("SELECT obertitel, titel, untertitel, teaser FROM programm 
						  WHERE id = :id");
  $stmt->execute(array('id' => $werte['id'],));
  
  while ($row = $stmt->fetch()) {
	if ($werte['show_title'] == 'yes') {
	  if ($row['obertitel'] == NULL) {
		$content .= do_shortcode('[fancy_heading title="'.$row['titel'].'" style="line"]'.$row['untertitel'].'[/fancy_heading]');
	  }
	  else {
		$content .= '<center><h3>'.$row['obertitel'].'</h3></center>'.do_shortcode('[fancy_heading title="'.$row['titel'].'" style="line"][/fancy_heading]');
	  }
	}
	
	$content .= $row['teaser'];	
  }
  
  if (($werte['image'] != NULL) AND ($werte['image'] != '')) {
	
	$image = do_shortcode('[image src="'.$werte['image'].'" link_image="'.$werte['image_zoom'].'"]');
	
	switch ($werte['image_width']) {
	  case '1/3':
		$result = do_shortcode('[two_third]'.$content.'[/two_third]').do_shortcode('[one_third]'.$image.'[/one_third]');
		break;
	  case '2/5':
		$result = do_shortcode('[three_fifth]'.$content.'[/three_fifth]').do_shortcode('[two_fifth]'.$image.'[/two_fifth]');
		break;
	  case '1/5':
		$result = do_shortcode('[four_fifth]'.$content.'[/four_fifth]').do_shortcode('[one_fifth]'.$image.'[/one_fifth]');
		break;
	  default:
		$result = do_shortcode('[three_fourth]'.$content.'[/three_fourth]').do_shortcode('[one_fourth]'.$image.'[/one_fourth]');
	}
  }
  else {
	$result = $content;
  }
  
  return($result);
  
}

add_shortcode('teaser', 'konzerte_teaser_shortcode');

function konzerte_archive_navigation_shortcode ($atts) {
  
  date_default_timezone_set("Europe/Berlin");
  $werte = shortcode_atts(array('first' => '2013', 'last' => date("Y"), 'current' => 'current'), $atts);
  
  $content = '<center><a href="wordpress/konzerte/#'.$werte['current'].'">Zum aktuellen Programm</a>';
  for ($i = $werte['last']; $i >= $werte['first']; $i--) {
	$content .= ' '.do_shortcode('[icon type="icon-dot-3"]').' <a href="wordpress/konzerte/#'.$i.'">'.$i.'</a>';
  }
  
  $content .='</center>';
  
  return($content);
  
}

add_shortcode('archive_navigation', 'konzerte_archive_navigation_shortcode');

function konzerte_archive_shortcode ($atts) {
  
  $werte = shortcode_atts( array ('year' => NULL, 'show_year' => 'yes', 'image' => NULL, 'image_zoom' => NULL), $atts );
  date_default_timezone_set("Europe/Berlin");
    
  $servername = "";
  $username = "";
  $password = "";
  $dbname = "";
  
  $termine = array();
  
  $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  $stmt = $conn->prepare("SELECT konzerte.datum, konzerte.archiv_kommentar, location.stadt, location.stadtteil, location.ausgabe_stadt, location.name FROM konzerte
  						  LEFT JOIN location ON location.id = konzerte.id_location 
						  WHERE datum LIKE :jahr AND datum < :jetzt
						  ORDER BY konzerte.datum DESC");
  $stmt->execute(array('jahr' => $werte['year'].'%', 'jetzt' => date("Y-m-d H:i:s")));
  
  while ($row = $stmt->fetch()) {
	$termine[] = $row;
  }
  
  if ($werte['show_year'] == 'yes') {
	$heading = do_shortcode('[fancy_heading title="'.$werte['year'].'" style="line"][/fancy_heading]');
  }
  
  $content = '<dl>';
  
  foreach ($termine as $value) {
	$content .= '<dt>'.substr($value['datum'], 8, 2).'.'.substr($value['datum'], 5, 2).'.'.substr($value['datum'], 0, 4).'</dt> <dd><b>';
	
	if ($value['ausgabe_stadt'] == NULL) {
	  $content .= $value['stadt'];
	  if ($value['stadtteil'] != NULL) {
		$content .= '-'.$value['stadtteil'];
	  }
	}
	else {
	  $content .= $value['ausgabe_stadt'];
	}
	
	$content .= '</b>';
	
	if ($value['name'] != NULL) {
	  $content .= ' – '.$value['name'];
	}
	
	if ($value['archiv_kommentar'] != NULL) {
	  $content.= '</br><i>'.$value['archiv_kommentar'].'</i>';
	}
	
	$content .= '</dd>';
  }
  
  $content .= '</dl>';
  
  if (($werte['image'] != NULL) AND ($werte['image'] != "")) {
	$image = do_shortcode('[image src="'.$werte['image'].'" link_image="'.$werte['image_zoom'].'"]');
	$result = $heading.do_shortcode('[three_fifth]'.$content.'[/three_fifth]').do_shortcode('[two_fifth]'.$image.'[/two_fifth]');
  }
  else {
	$result = $heading.$content;
  }
  
  return($result);
  
}
add_shortcode('archive', 'konzerte_archive_shortcode');

?>