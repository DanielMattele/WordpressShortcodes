<?php
function konzert_shortcode( $atts ) {
  $werte = shortcode_atts( array (
	'stadt' => 'Stadt wird noch bekannt gegeben',
	'ort' => 'Ort wird noch bekannt gegeben',
	'ort_adresse' => '',
	'ort_plz' => '',
	'ort_lat' => '',
	'ort_lng' => '',
	'programm' => 'Programm wird noch bekannt gegeben',
	'dauer' => '1 Stunde', //Angabe mit Minuten oder Stunden
	'pause' => 'nein', //ja oder nein
	'eintritt_normal' => '15', //Zahl ohne € oder 'frei'
	'eintritt_ermaessigt' => '12',
	'eintritt_andere' => '',
	'vorverkaufsstelle' => 'Eventim',
	'vorverkaufsstelle_alternative' => '',
	'vorverkaufsstelle_alternative_link' => '#',
	'vorverkauf_aktiv' => 'nein', //ja, nein, beendet, ausverkauft
	'vorverkauf_link' => '#',
	'zusatztext' => '',
	'uebersicht_ausgeben' => 'nein',
	'als_code_ausgeben' => 'nein',
	), $atts );
  $code = '';
  $code.= '<dl><dt>Ort</dt><dd>'.$werte['ort'];
  $code.= '</dd><dt>Programm</dt><dd><i>'.$werte['programm'].'</i></br>(Dauer: ca. '.$werte['dauer'];
  if ($werte['pause'] == 'ja') {
	$code.= ' mit';
  }
  else {
	$code.= ' ohne';
  }
  $code.= ' Pause)</dd><dt>Eintritt</dt><dd>';
  if ($werte['eintritt_normal'] == 'frei') {
	$code.= 'Der Eintritt ist frei; Spenden werden erbeten.</dd>';
  }
  elseif ($werte['eintritt_normal'] == '?') {
	$code.= 'Informationen zu Karten und Eintrittspreisen folgen in Kürze.</dd>';
  }
  else {
	$code.= $werte['eintritt_normal'].' €';
	if ($werte['eintritt_ermaessigt'] != '' AND $werte['eintritt_ermaessigt'] != NULL) {
	  $code.= ' / '.$werte['eintritt_ermaessigt'].' € ermäßigt';
	}
	if ($werte['eintritt_andere'] != '') {
	  $code.= ' / '.$werte['eintritt_andere'];
	}
	if ($werte['vorverkaufsstelle'] != 'keine') {
	  $code.= ' zzgl. Vorverkaufsgebühren';
	}
	$code.= '</dd><dt>Karten</dt><dd>';
	if ($werte['vorverkauf_aktiv'] == 'nein') {
	  $code.= 'Karten erhalten Sie an der Abendkasse und im Vorverkauf. Der Vorverkauf startet in Kürze.</dd>';
	}
	elseif ($werte['vorverkauf_aktiv'] == 'beendet') {
	  $code.= do_shortcode('[highlight background="#973934" color="#ffffff"]Der Vorverkauf für dieses Konzert ist beendet.[/highlight]').'</dd>';
	}
	elseif ($werte['vorverkauf_aktiv'] == 'ausverkauft') {
	  $code.= do_shortcode('[highlight background="#973934" color="#ffffff"]Dieses Konzert ist ausverkauft![/highlight]').'</dd>';
	}
	else {
	  if ($werte['vorverkaufsstelle'] == 'keine') {
		$code.= 'Karten erhalten Sie an der Abendkasse.</dd>';
	  }
	  elseif ($werte['vorverkaufsstelle'] == 'Eventim') {
		$code.= 'Karten erhalten Sie bei <a href="http://www.eventim.de/" target="_blank">eventim.de</a>, an deren <a href="http://www.eventim.de/tickets.html?affiliate=EVE&doc=search/ticketAgency" target="_blank">Vorverkaufsstellen</a>';
		if ($werte['vorverkaufsstelle_alternative'] != '') {
		  if ($werte['vorverkaufsstelle_alternative_link'] != '#') {
			$code.= ', <a href="'.$werte['vorverkaufsstelle_alternative_link'].'" target="_blank">'.$werte['vorverkaufsstelle_alternative'].'</a>';
		  }
		  else {
			$code.= ', '.$werte['vorverkaufsstelle_alternative'];
		  }
		}
		$code.= ' sowie an der Abendkasse.</br></br>'.do_shortcode('[button title="Karten in unserem Eventim-Light-Onlineshop bestellen" link="'.$werte['vorverkauf_link'].'" target="_blank" align="left" icon="icon-ticket" icon_position="left" size="1"]').'</dd>';
	  }
	  else {
		$code.= 'Karten erhalten Sie '.$werte['vorverkaufsstelle'].' sowie an der Abendkasse.</br></br>';
		if ($werte['vorverkauf_link'] != '#') {
		  $code.= do_shortcode('[button title="Karten '.$werte['vorverkaufsstelle'].' bestellen" link="'.$werte['vorverkauf_link'].'" target="_blank" align="left" icon="icon-ticket" icon_position="left" size="1"]');
		}
	  }
	}
  }
  if ($werte['zusatztext'] != '') {
	$code.= '<dt></dt><dd>'.$werte['zusatztext'].'</dd>';
  }
  $code.= '</dl>';
  if ($werte['uebersicht_ausgeben'] == 'ja') {
	echo '<pre>';
	print_r($werte);
	echo '</pre>';
  }
  if ($werte['als_code_ausgeben'] == 'ja') {
	return htmlspecialchars($code);
  }
  else {
	if ($werte['ort_adresse'] != '' AND $werte['ort_plz'] != '' AND $werte['ort_lat'] != '' AND $werte['ort_lng'] != '') {
	  $code.= do_shortcode('[map lat="'.$werte['ort_lat'].'" lng="'.$werte['ort_lng'].'" height="400" zoom="16" type="ROADMAP" controls="hide" draggable="disable" title="'.$werte['ort'].'"]'.'<small>'.$werte['ort_adresse'].'<br>'.$werte['ort_plz'].' '.$werte['stadt'].'</small>'.'[/map]');
	}
	return $code;
  }
}
add_shortcode( 'Konzert', 'konzert_shortcode' );
?>