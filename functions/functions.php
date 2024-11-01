<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* 
/*  ALLGEMEINE FUNKTIONEN
/* 
/****************************************************************************************/


function zp_get_browser_name($user_agent)
{
	if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
	elseif (strpos($user_agent, 'Edge')) return 'Edge';
	elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
	elseif (strpos($user_agent, 'Safari')) return 'Safari';
	elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
	elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
	
	return 'Other';
}
function zp_left($str, $length) {
     return substr($str, 0, $length);
}

function zp_right($str, $length) {
     return substr($str, -$length);
}
function zp_validateDate($date, $format = 'Y-m-d H:i:s')
{
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}
function zp_get_count_kategorie( $stac )
{
	global $wpdb;

	return($wpdb->get_var($wpdb->prepare( "SELECT count(id) AS count FROM ".$wpdb->prefix."zahlungsplan_kategorie WHERE stac=%d", $stac)) );
}
function zp_put_konfig( $parameter, $newval )
{
	global $wpdb;
	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
	$res = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan_konfig WHERE id=1 LIMIT 0,1',ARRAY_A);
	if (empty($res)) return;

	/* Parameter aus Array erzeugen... */
	$bericht_abfrage = unserialize(base64_decode($res[0]['inhalt']));
    $bericht_abfrage[$parameter] = $newval;
	/* Array der parameter erzeugen... */
	$bericht_abfrage = base64_encode(serialize( $bericht_abfrage ));

	// Update der DB ...
	$wpdb->update($wpdb->prefix.'zahlungsplan_konfig',
		esc_sql(array(
		'inhalt'=>$wpdb->_real_escape($bericht_abfrage),
		'stac'=>1)),
		array('id'=>1));
	return;
}
function zp_get_konfig( $parameter )
{
	global $wpdb;

	$inhalt = $wpdb->get_var( "SELECT inhalt FROM ".$wpdb->prefix."zahlungsplan_konfig LIMIT 0,1" );
	/* Parameter aus Array erzeugen... */
	$inhalt = unserialize(base64_decode($inhalt));

	// Wenn ein neuer Parameter noch nicht in der Liste ist, blank zurückgeben, wird beim nächsten Update der Konfiguration hinzugefügt...
	if ( !isset( $inhalt[$parameter] ) == true )
			return( "" );
	//if ( !isset( $inhalt[$parameter] ) == true )
	//	die( "Parameter '".$parameter."' in Konfiguration nicht gefunden.");
	if ( is_int( $inhalt[$parameter] ) )
		return( esc_attr(sanitize_key( $inhalt[$parameter] )));
	elseif ( is_string( $inhalt[$parameter] ) )
		return( esc_attr(sanitize_text_field( $inhalt[$parameter] )));
	
}
function zp_build_sql_kat_where( $bericht_id )
{
	global $wpdb;
	$bestandId = 0;
	$temp = 0;
	
	$kat_where = "";
	$flag = 0;
	
	$bericht_id = esc_sql( $bericht_id );
	$inhalt = $wpdb->get_var( "SELECT inhalt FROM ".$wpdb->prefix."zahlungsplan_bericht WHERE id=".$bericht_id." LIMIT 0,1" );
	/* Parameter aus Array erzeugen... */
	$kategorielist = unserialize(base64_decode($inhalt));	
	$kategorielist = explode(',',$kategorielist['kategorielist']);

	foreach ( $kategorielist as $kat )
	{	
		// das ist der Haken, wenn ALLE Kategorien gewählt sind! Hole alle aktiven...
		if ( $kat == -1 )
        {
            $kat_where = "";
	        $flag = 0;
            $results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE stac=1' );
            foreach ( $results as $katall )
            {
                $kat_where .= ($flag==0?" AND ( kategorie_id=".$katall->id:" OR kategorie_id=".$katall->id );
		        ++$flag;
            }
            break;
        }
        //if ( $wpdb->get_var($wpdb->prepare( "SELECT stac FROM ".$wpdb->prefix."zahlungsplan_kategorie WHERE id=%d", $kat )) == 0 ) break;

		$kat_where .= ($flag==0?" AND ( kategorie_id=".$kat:" OR kategorie_id=".$kat);
		++$flag;
	}
	if ( strlen( $kat_where ) > 0 ) $kat_where .= ")";


    //print_r($kat_where);
    //die("d");

	return( $kat_where );
}	
function zp_dateAddMonth($strDate, $month)
{
	$dateLast = date_create($strDate)->modify('last Day of '.$month.' Month');
	$dateAdd = date_create($strDate)->modify('+ '.$month.' Month');
	return ( ($dateAdd > $dateLast?$dateLast:$dateAdd) );
}
function zp_dateSubMonth($strDate, $month)
{
	$dateLast = date_create($strDate)->modify('last Day of '.$month.' Month');
	$dateAdd = date_create($strDate)->modify('- '.($month*-1).' Month');
	return ( ($dateAdd > $dateLast?$dateLast:$dateAdd) );
}
/* Liefert bei $extra == 1 integer */
function zp_get_sum_total( $bestandId, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra )
{
	global $wpdb;
	$temp = 0;
	$betrag = 0.0;
	$cnt = 0;
	$ea_where = "";
	$shortcode_param_zeitraum = 0;
	
	
	if ( $ea == 1 ) $ea_where = "";
	elseif ( $ea == 2 ) $ea_where = " AND ea=1";
	elseif ( $ea == 3 ) $ea_where = " AND ea=0";
	
	if ( $shortcode_param_bis != "" && $shortcode_param_von != "" ) $shortcode_param_zeitraum = 1;

	$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE id='.$bestandId.' AND stac=%d'.($mandant_id==0?'':' AND mandant_id='.$mandant_id).$ea_where.' LIMIT 0,1', 1));

	if (!empty($results)) 
	{
		foreach ($results as $id)
		{		
			$startdate = $id->start;
			$date = $startdate;	
			$month = 0;
			
			if ( $date < $id->ende )
			{
				while ($date < $id->ende)
				{
					$date = zp_dateAddMonth($startdate, $month)->format('Y-m-d');
					$month = $month+=$id->turnus;
					if ($date <= $id->ende)
					{					
						if ( $shortcode_param_zeitraum == 1 )
							if ( $date < $shortcode_param_von || $date > $shortcode_param_bis ) continue;
						for ($i = 0; $i < $id->anzahl; $i++)
						{
							$cnt++;
							if ( $id->ea == 0 ) $betrag -= $id->betrag;
								else $betrag += $id->betrag; 							
						}
					}
				}
			}
		}	
	}
	if ( $extra == 0 ) return( $betrag );
	elseif ( $extra == 1 ) return( $cnt );
	return( $betrag );
}
function zp_get_all_table( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis )
{
	global $wpdb;
	$bestandId = 0;
	$temp = 0;
	$kat_where = "";
	$ea_where = "";
	$flag = 0;
	$tab = 0;
	$shortcode_param_zeitraum = 0;
	
	$tabellieren = zp_get_konfig( 'tabellieren' );
	$tabfarbe1 = zp_get_konfig( 'tabfarbe1' );
	$tabfarbe2 = zp_get_konfig( 'tabfarbe2' );
	$kat_where = zp_build_sql_kat_where( $bericht_id );
	
	if ( $shortcode_param_bis != "" && $shortcode_param_von != "" ) $shortcode_param_zeitraum = 1;

	if ( $ea == 1 ) $ea_where = "";
	elseif ( $ea == 2 ) $ea_where = " AND ea=1";
	elseif ( $ea == 3 ) $ea_where = " AND ea=0";

	echo '<div style="font-family:courier;">';
	
	$mandant_id = esc_sql( $mandant_id );
	$ea_where = esc_sql( $ea_where );
	$kat_where = esc_sql( $kat_where );
	$results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE stac=1'.($mandant_id==0?'':' AND mandant_id='.$mandant_id).$ea_where.$kat_where );

	if (!empty($results)) 
	{
		foreach ($results as $id)
		{	
			$kategorie_name = $wpdb->get_var( "SELECT kategorie FROM ".$wpdb->prefix."zahlungsplan_kategorie WHERE id=".esc_sql( $id->kategorie_id )." LIMIT 1" );
			echo '<hr>';
			echo 'Mandant: '.get_userdata($id->mandant_id)->display_name.'<br>';
			echo '<small><small><table border="1">';
			echo '<tr>';
			echo '<td>'.esc_attr(sanitize_text_field($kategorie_name)).'</td>';
			echo '<td>'.esc_attr(sanitize_text_field($id->bezeichnung)).'</td>';
			echo '<td>'.($id->ea==1?'Einnahme':'Ausgabe').'</td>';
			echo '<td>'.($id->umbuchung==1?'Umbuchung':'').'</td>';
			echo '<td>'.esc_attr(sanitize_key($id->anzahl)).'</td>';
			echo '</tr>';
			echo '</table></small></small>';
			echo '<small><small><table border="1">';
			echo '<tr>';		
			echo '<td>'.strftime('%d.%m.%Y',strtotime(sanitize_option( "date_format", $id->start))).'</td>';
			echo '<td>'.strftime('%d.%m.%Y',strtotime(sanitize_option( "date_format", $id->ende))).'</td>';			
			if ( $id->umbuchung != 1 ) $pa = $id->betrag * $id->anzahl;
			echo '<td style="text-align:left">Monatlich '.number_format(esc_attr(sanitize_text_field($pa)) , 2, ",", ".").'</td>';	
			if ( $id->umbuchung != 1 ) $pa = $id->betrag * (12 / $id->turnus * $id->anzahl);
			echo '<td style="text-align:left">Jährlich '.number_format(esc_attr(sanitize_text_field($pa)) , 2, ",", ".").'</td>';
			if ( $id->umbuchung != 1 ) $pa = zp_get_sum_total( $id->id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, 0 );
			echo '<td style="text-align:left">Gesamt '.number_format(esc_attr(sanitize_text_field($pa)) , 2, ",", ".").'</td>';
			echo '</tr>'; 
			echo '</table></small></small>';


			$startdate = $id->start;
			$date = $startdate;
			$month = 0;
			
			if ( $date < $id->ende )
			{
				echo '<small><small><table border="1">';
				while ($date < $id->ende)
				{
					$date = zp_dateAddMonth($startdate, $month)->format('Y-m-d');
					$month = $month+=$id->turnus;
					
					if ($date <= $id->ende)
					{
						if ( $shortcode_param_zeitraum == 1 )
							if ( $date < $shortcode_param_von || $date > $shortcode_param_bis ) continue;	
						for ($i = 0; $i < $id->anzahl; $i++)
						{
							echo '<tr>';
							echo '<td style="text-align:left;background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'">'.esc_attr(sanitize_text_field($id->bezeichnung)).'</td>';
							echo '<td style="text-align:right;background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'">'.strftime('%d.%m.%Y',strtotime(sanitize_option( "date_format", $date))).'</td>';
							echo '<td style="text-align:right;background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'">'.($id->umbuchung==1?'(Umbuchung)  ':'').number_format(esc_attr(sanitize_text_field($id->betrag)), 2, ",", ".").'</td>';
							echo '</tr>';
							if ( $tab == 0 ) $tab = $tabellieren; else $tab = 0;
						}
					}
				}
				echo "</table></small></small>";
			}
		}
	}
	echo '<div style="">';
	echo '</div>';
}
function zp_saldieren( $line, $tdate, &$flag, &$sam, &$date, $shortcode_param_zeitraum, $shortcode_param_von, $shortcode_param_bis, $force )
{	
	if ( ( substr( $tdate, 3, 2 ) !=  substr( $line['datum'], 3, 2 ) ) || $force == true ) 
	{ 
		if ( $flag == 1 ) // beim ersten Durchlauf überspringen...
		{
			echo '<tr>';
			echo '<td style="width:1*; text-align:right; "><b>'.strftime('%d.%m.%Y',strtotime($tdate)).'</b></td>';
			
			
			echo '<td style="width:3*; text-align:left"><b>Saldo (laufender Monat) per '.strftime('%d.%m.%Y',strtotime($tdate)).'</b></td>';
			/*
			if ( $shortcode_param_zeitraum == 0 ) echo '<td style="width:3*; text-align:left"><b>Saldo (laufender Monat) per '.strftime('%d.%m.%Y',strtotime($tdate)).'</b></td>';
				else echo '<td style="width:3*; text-align:left"><b>Summe vom '.strftime('%d.%m.%Y',strtotime($shortcode_param_von)).' bis '.strftime('%d.%m.%Y',strtotime($shortcode_param_bis)).' :</b></td>';
			*/
			echo '<td style="width:1*; text-align:right"><b></b></td>';
			echo '<td style="width:1*; text-align:right"><b>'.number_format($sam , 2, ",", ".").'</b></td>';
			echo '</tr>'; 
			$sam = 0.0;
		}
		$date = $line['datum'];
		$flag = 1;
	}
	$sam += $line['betrag'];
}
function zp_get_all_list( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $art )
{
	global $wpdb;
	$bestandId = 0;
	$temp = 0;
	$buffer = array();
	$_buffer = array();
	$csv_buffer = array();
	$pa = 0.0;	
	$tabellieren = zp_get_konfig( 'tabellieren' );
	$tabfarbe1 = zp_get_konfig( 'tabfarbe1' );
	$tabfarbe2 = zp_get_konfig( 'tabfarbe2' );
	$printkat = zp_get_konfig( 'printkat' );
	//$kategorielist = zp_get_konfig( 'kategorielist' );
	$tab = 0;
	$flag = 0;
	$kat_where = "";
	$ea_where = "";
	$last_date = 0;
	$shortcode_param_zeitraum = 0;
	$bericht_id = esc_sql( $bericht_id );
	$bericht_name = $wpdb->get_var( "SELECT bericht FROM ".$wpdb->prefix."zahlungsplan_bericht WHERE id=".$bericht_id." LIMIT 0,1" );
	$kat_where = zp_build_sql_kat_where( $bericht_id );
	
	if ( $ea == 1 ) $ea_where = "";
	elseif ( $ea == 2 ) $ea_where = " AND ea=1";
	elseif ( $ea == 3 ) $ea_where = " AND ea=0";
			
	if ( $shortcode_param_bis != "" && $shortcode_param_von != "" ) $shortcode_param_zeitraum = 1;
		
	echo '<div style="font-family:courier;">';

	$mandant_id = esc_sql( $mandant_id );
	$ea_where = esc_sql( $ea_where );
	$kat_where = esc_sql( $kat_where );
	$results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE stac=1'.($mandant_id==0?'':' AND mandant_id='.$mandant_id).$ea_where.$kat_where ); 
	if (!empty($results)) 
	{
		foreach ($results as $id)
		{	
			$kategorie_name = $wpdb->get_var( "SELECT kategorie FROM ".$wpdb->prefix."zahlungsplan_kategorie WHERE id=".esc_sql( $id->kategorie_id )." LIMIT 1" );
			$kategorie_name = esc_attr(sanitize_text_field($kategorie_name));
			$startdate = $id->start;
			$date = $startdate;
			$month = 0;
			$eb = 0.0;
			
			if ( $date < $id->ende )
			{
				//echo '<small><small><table border="1">';
				while ($date < $id->ende)
				{
					$date = zp_dateAddMonth($startdate, $month)->format('Y-m-d');
					$month = $month+=$id->turnus;
					if ($date <= $id->ende)
					{
						if ( $shortcode_param_zeitraum == 1 )
							if ( $date < $shortcode_param_von || $date > $shortcode_param_bis ) continue;
						for ($i = 0; $i < $id->anzahl; $i++)
						{
							$eb = ($id->ea==1?$id->betrag:$id->betrag*-1);
							if ( $id->umbuchung != 1 ) $pa += $eb;												
							$_buffer['sortdatum'] = $date;
							/* Flag für die einfache Sortierung umdrehen... */
							$_buffer['ea'] = ($id->ea==1?0:1);
							$_buffer['datum'] = strftime('%d.%m.%Y',strtotime(esc_attr(sanitize_option( "date_format", $date))));
							$_buffer['bezeichnung'] = esc_attr(sanitize_text_field($id->bezeichnung));
							$_buffer['kategorie_name'] = esc_attr(sanitize_text_field($kategorie_name));
							$_buffer['umbuchung'] = esc_attr(sanitize_key($id->umbuchung));
							$_buffer['betrag'] = esc_attr(sanitize_text_field($eb));
							array_push( $buffer, $_buffer );
						}
					}
				}
			}			
		}
	}
	echo '</div>';
	echo '<div style="font-family:courier;">';
	echo '<small><small><table style="width: 100%; background-color: #DDDDDD; border: 1px solid #E4E4E4; border-width: 1px;margin: 0 auto">';
	echo '<tr>'.
	'<td style="width:1*; text-align:left; background-color:#BBBBBB">Datum</td>'.
	'<td style="width:3*; text-align:left; background-color:#BBBBBB">Bezeichnung</td>'.
	'<td style="width:1*; text-align:right; background-color:#BBBBBB">Betrag</td>'.
	'<td style="width:1*; text-align:right; background-color:#BBBBBB">Saldo</td>'.
	'</tr>';
						
	sort( $buffer );
	$i = 0;
	$sa = 0.0;
	$sam = 0.0;
	$csv = "";
	array_push( $csv_buffer, "Datum;Kategorie;Bezeichnung;Betrag;Saldo;\n" );
	$date = "";
	foreach($buffer as $line)
	{
		if ( $art == 6 ) zp_saldieren( $line, $date, $flag, $sam, $date, $shortcode_param_zeitraum, $shortcode_param_von, $shortcode_param_bis, false );
		

		if ( $line['umbuchung'] != 1 ) $sa += $line['betrag'];
		echo '<tr>'.
		'<td style="width:1*; text-align:right; background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'">'.$line['datum'].'</td>'.
		'<td style="width:3*; text-align:left; background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'">'.($printkat==1?$line['kategorie_name'].' ':'').$line['bezeichnung'].'</td>'.
		'<td style="width:1*; text-align:right; background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'">'.($line['umbuchung']==1?'(Umbuchung)  ':'').number_format($line['betrag'] , 2, ",", ".").'</td>'.
		'<td style="width:1*; text-align:right; background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'">'.number_format($sa , 2, ",", ".").'</td>'.
		'</tr>';
		
		//if ( $art == 6 ) zp_saldieren( $line, $date, $flag, $sam, $date, $shortcode_param_zeitraum, $shortcode_param_von, $shortcode_param_bis, false );
		
		$date = $line['datum'];
		
		if ( $tab == 0 ) $tab = $tabellieren; else $tab = 0;
		$csv = $line['datum'].";".$line['kategorie_name'].";".$line['bezeichnung'].";".strval(number_format($line['betrag'] , 2, ",", ".")).";".strval(number_format($sa , 2, ",", ".")).";\n";
		array_push( $csv_buffer, $csv );		
	}
	// Saldieren
	//if ( $art == 6 ) zp_saldieren( $line, $date, $flag, $sam, $date, $shortcode_param_zeitraum, $shortcode_param_von, $shortcode_param_bis, true );
	if ( $art == 6 ) zp_saldieren( $line, $line['datum'], $flag, $sam, $date, $shortcode_param_zeitraum, $shortcode_param_von, $shortcode_param_bis, true );
	unset( $buffer );
	unset( $_buffer );
	echo '<tr>';
	//echo '<td style="width:1*; text-align:right; "><b>'.strftime('%d.%m.%Y',strtotime($last_date)).'</b></td>';
	echo '<td style="width:1*; text-align:right; "><b>'.strftime('%d.%m.%Y',strtotime($line['datum'])).'</b></td>';
	if ( $shortcode_param_zeitraum == 0 ) echo '<td style="width:3*; text-align:left"><b>Summe gesamter Bestand :</b></td>';
		else echo '<td style="width:3*; text-align:left"><b>Summe vom '.strftime('%d.%m.%Y',strtotime($shortcode_param_von)).' bis '.strftime('%d.%m.%Y',strtotime($shortcode_param_bis)).' :</b></td>';
	echo '<td style="width:1*; text-align:right"><b></b></td>';
	echo '<td style="width:1*; text-align:right"><b>'.number_format($pa , 2, ",", ".").'</b></td>';
	echo '</tr>';
	echo '</table></small></small>';
	echo '<br>';
	if ( $art == 4 ) 
	{		
		$csv = "";
		foreach ($csv_buffer as $line)
			$csv .= $line;
		// CSV-Download anbieten...	
		require( dirname( __FILE__ , 2 ) . '/functions/csv_download.js');
		echo '<script type="text/javascript">
			var buffer = '.json_encode($csv).";
		</script>";
		$filename = "'".$bericht_name.".csv'";
		?>
			<iframe id="CsvExpFrame" style="display: none"></iframe>
			<input id="csv-download" type="button" value="Download CSV" onclick="ExportToCsv(<?php echo $filename; ?>);"
		<?php
		unset( $csv_buffer );	
	}
	echo '<div style="">';
	echo '</div>';
}
function zp_get_all_by_categorie_list( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis )
{
	global $wpdb;
	$bestandId = 0;
	$temp = 0;
	$buffer = array();
	$_buffer = array();
	$pa = 0.0;	
	$tabellieren = zp_get_konfig( 'tabellieren' );
	$tabfarbe1 = zp_get_konfig( 'tabfarbe1' );
	$tabfarbe2 = zp_get_konfig( 'tabfarbe2' );
	$tab = 0;
	$kat_where = "";
	$ea_where = "";
	$flag = 0;
	$shortcode_param_zeitraum = 0;	
	$kat_where = zp_build_sql_kat_where( $bericht_id );
	
	if ( $ea == 1 ) $ea_where = "";
	elseif ( $ea == 2 ) $ea_where = " AND ea=1";
	elseif ( $ea == 3 ) $ea_where = " AND ea=0";
		
	if ( $shortcode_param_bis != "" && $shortcode_param_von != "" ) $shortcode_param_zeitraum = 1;

	echo '<div style="font-family:courier;">';
	
	$mandant_id = esc_sql( $mandant_id );
	$ea_where = esc_sql( $ea_where );
	$kat_where = esc_sql( $kat_where );
	$results = $wpdb->get_results( 'SELECT *, (SELECT DISTINCT kategorie FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE id=kategorie_id) AS kategorie_name FROM '.$wpdb->prefix.'zahlungsplan WHERE stac=1'.($mandant_id==0?'':' AND mandant_id='.$mandant_id).$ea_where.$kat_where.' ORDER BY kategorie_name' );

	if (!empty($results)) 
	{
		foreach ($results as $id)
		{	
			$last_name = "";
			$startdate = $id->start;
			$date = $startdate;
			$month = 0;
			$eb = 0.0;
			
			if ( $date < $id->ende )
			{
				//echo '<small><small><table border="1">';
				while ($date < $id->ende)
				{
					$date = zp_dateAddMonth($startdate, $month)->format('Y-m-d');
					$month = $month+=$id->turnus;
					if ($date <= $id->ende)
					{
						if ( $shortcode_param_zeitraum == 1 )
							if ( $date < $shortcode_param_von || $date > $shortcode_param_bis ) continue;						
						for ($i = 0; $i < $id->anzahl; $i++)
						{
							$eb = ($id->ea==1?$id->betrag:$id->betrag*-1);
							if ( $id->umbuchung != 1 ) $pa += $eb;					
							$_buffer['kategorie_name'] = esc_attr(sanitize_text_field($id->kategorie_name));
							$_buffer['umbuchung'] = esc_attr(sanitize_key($id->umbuchung));
							$_buffer['betrag'] = esc_attr(sanitize_text_field($eb));
							array_push( $buffer, $_buffer );

						}
					}
				}
			}			
		}
	}
	echo '</div>';
	echo '<div style="font-family:courier;">';
	echo '<small><small><table style="width: 99%; background-color: #DDDDDD; border: 1px solid #E4E4E4; border-width: 1px;margin: 0 auto">';
	echo '<tr>'.
	'<td style="width:40%; text-align:left; background-color:#BBBBBB">Kategorie</td>'.
	'<td style="width:40%; text-align:left; background-color:#BBBBBB"></td>'.
	'<td style="width:19%; text-align:right; background-color:#BBBBBB">Betrag</td>'.
	'</tr>';
	sort( $buffer );
	$ks = 0.0;
	$flag = 0;
	$index = 0;
	$kn = "";
	$bl = count( $buffer );
	foreach($buffer as $line)
	{
		++$index;			
		if ( $flag == 0 ) 
		{
			$kn = $line['kategorie_name'];
			$flag = 1;
		}
		if ( $kn != $line['kategorie_name'] || $index == $bl )
		{
			if ( $index == $bl ) if ( $line['umbuchung'] != 1 ) $ks += $line['betrag'];
			echo '<tr>';
			echo '<td style="width:40%; text-align:left; background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'">Summe '.$kn.'</td>';
			echo '<td style="width:40%; text-align:left; background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'"></td>';
			echo '<td style="width:19%; text-align:right; background-color:#'.($tab==0?$tabfarbe1:$tabfarbe2).'">'.number_format($ks , 2, ",", ".").'</td>';
			echo '</tr>';		
			if ( $tab == 0 ) $tab = $tabellieren; else $tab = 0;	
			$ks = 0.0;
			$kn = $line['kategorie_name'];
			$flag = 0;
		}
		if ( $line['umbuchung'] != 1 ) $ks += $line['betrag'];	
	}
	unset( $buffer );
	unset( $_buffer );
	echo '</table></small></small>';
	echo '<hr>';
	echo '<small><small><table style="width: 99%; background-color: #DDDDDD; border: 1px solid #E4E4E4; border-width: 1px;margin: 0 auto">';
	echo '<tr>';
	if ( $shortcode_param_zeitraum == 0 ) echo '<td style="width:65%; text-align:left">Summe gesamter Bestand :</td>';
		else echo '<td style="width:65%; text-align:left">Summe vom '.strftime('%d.%m.%Y',strtotime($shortcode_param_von)).' bis '.strftime('%d.%m.%Y',strtotime($shortcode_param_bis)).' :</td>';
	echo '<td style="width:19%; text-align:right">'.number_format($pa , 2, ",", ".").'</td>';
	echo '</tr>';
	echo '</table></small></small>';
	echo '<div style="">';
	echo '</div>';
}
function zp_get_berichtszeitraum_monate( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra )
{
	global $wpdb;
	$bestandId = 0;
	$temp = 0;
	$kat_where = "";
	$flag = 0;
	$pa = 0.0;
	$shortcode_param_zeitraum = 0;
	$printsign = zp_get_konfig( 'printsign' );
	$kat_where = zp_build_sql_kat_where( $bericht_id );
	$high_date = "0000-00-00";
	$low_date = "9999-99-99";
	
	if ( $ea == 1 ) $ea_where = "";
	elseif ( $ea == 2 ) $ea_where = " AND ea=1";
	elseif ( $ea == 3 ) $ea_where = " AND ea=0";
		
	if ( $shortcode_param_bis != "" && $shortcode_param_von != "" ) $shortcode_param_zeitraum = 1;
	
	$mandant_id = esc_sql( $mandant_id );
	$ea_where = esc_sql( $ea_where );
	$kat_where = esc_sql( $kat_where );
	//$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE id='.$bestandId.' AND stac=%d'.$ea_where.' LIMIT 0,1', 1));
	$results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE stac=1'.($mandant_id==0?'':' AND mandant_id='.$mandant_id).$ea_where.$kat_where );
	
	if (!empty($results)) 
	{
		foreach ($results as $id)
		{
			$startdate = $id->start;
			$date = $startdate;	
			$month = 0;
			
			while ($date < $id->ende)
			{
				$date = zp_dateAddMonth($startdate, $month)->format('Y-m-d');
				$month = $month+=$id->turnus;
				if ($date <= $id->ende)
				{					
					if ( $shortcode_param_zeitraum == 1 )
						if ( $date < $shortcode_param_von || $date > $shortcode_param_bis ) continue;
					// kleinstes und größtes Datum für $extra=2 merken...
					if ( $id->start < $low_date ) $low_date =$id->start;
					if ( $id->ende > $high_date ) $high_date = $id->ende;
				}
			}
		}
	}
	// Bei Gesamtzeitraum sind $shortcode_param_von und $shortcode_param_bis nicht gesetzt... 
	if ( zp_validateDate( $high_date, 'Y-m-d' ) && zp_validateDate( $low_date, 'Y-m-d' ) )
	{
		$interval = date_diff( date_create( $high_date ), date_create( $low_date ) );
		$pa = 1+intval( $interval->m + ($interval->y * 12) );
	}
	return( $pa );
}
/*
	Liefert nur die Summen aus zp_get_sum_total()
*/
function zp_get_all_sum( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra )
{
	global $wpdb;
	$bestandId = 0;
	$temp = 0;
	$kat_where = "";
	$flag = 0;
	$pa = 0.0;
	$printsign = zp_get_konfig( 'printsign' );
	$kat_where = zp_build_sql_kat_where( $bericht_id );
	$high_date = "0000-00-00";
	$low_date = "9999-99-99";
	$ehoch = 0.0;
	$eniedrig = 0.0;
	$ahoch = 0.0;
	$aniedrig = 0.0;
	
	$mandant_id = esc_sql( $mandant_id );
	$kat_where = esc_sql( $kat_where );
	$results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE stac=1'.($mandant_id==0?'':' AND mandant_id='.$mandant_id).$kat_where );
	
	if (!empty($results)) 
	{
		foreach ($results as $id)
		{	
			if ( $id->umbuchung != 1 ) $pa += zp_get_sum_total( $id->id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra );

			/* ..die Gesamtlaufzeit in Monten (höchsten Wert) merken */
			if ( $extra == 2 || $extra == 3 )
			{
				// kleinstes und größtes Datum für $extra=2 merken...
				if ( $id->start < $low_date ) $low_date =$id->start;
				if ( $id->ende > $high_date ) $high_date = $id->ende;
			}
			/* ..die höchsten und niedrigsten Betrag der Einnahmen merken...ohne Umbuchungen */
			if ( $id->umbuchung != 1 )
			{	
				// $ea (Bericht betrachtet nur) = 1 Einnahmen&Ausgaben, = 2 Einnahmen, = 3 Ausgaben 
				if ( $ea == 1 || $ea == 2 )
				{
					if ( $flag == 0 && $extra == 5 && $id->ea == 1 )
					{
						$eniedrig = $id->betrag;
						$flag = 1;
					}
					if ( $extra == 4 && $id->betrag > 0.00 && $id->ea == 1  )
						if ( $id->betrag > $ehoch ) $ehoch = $id->betrag;
					if ( $extra == 5 && $id->betrag > 0.00 && $id->ea == 1  )
						if ( $id->betrag < $eniedrig ) $eniedrig = $id->betrag;
				}
				if ( $ea == 1 || $ea == 3 )
				{
					/* ..die höchsten und niedrigsten Betrag der Ausgaben merken... */
					if ( $flag == 0 && $extra == 7 && $id->ea == 0 )
						{
							$aniedrig = $id->betrag;
							$flag = 1;
						}
					if ( $extra == 6 && $id->betrag > 0.00 && $id->ea == 0 )
						if ( $id->betrag > $ahoch ) $ahoch = $id->betrag;
					if ( $extra == 7 && $id->betrag > 0.00 && $id->ea == 0 )
						if ( $id->betrag < $aniedrig ) $aniedrig = $id->betrag;
				}
			}		
		}
	}
	if ( $extra == 3 ) 
	{
		if ( zp_validateDate( $shortcode_param_von, 'Y-m-d' ) && zp_validateDate( $shortcode_param_bis, 'Y-m-d' ) )
		{
			$interval = date_diff( date_create( $shortcode_param_bis ), date_create( $shortcode_param_von ) );
			echo 1+intval( $interval->m + ( $interval->y * 12 ) );
			return;			
		}	
		$interval = date_diff( date_create( $high_date ), date_create( $low_date ) );
		echo 1+intval( $interval->m + ( $interval->y * 12 ) );
		return;
	}
	if ( $extra == 2 )
	{	
		if ( zp_validateDate( $shortcode_param_von, 'Y-m-d' ) && zp_validateDate( $shortcode_param_bis, 'Y-m-d' ) )
		{
			$interval = date_diff( date_create( $shortcode_param_bis ), date_create( $shortcode_param_von ) );
			$pa = $pa / ( 1+intval( $interval->m + ( $interval->y * 12 ) ) );		
		}	
		elseif ( zp_validateDate( $high_date, 'Y-m-d' ) && zp_validateDate( $low_date, 'Y-m-d' ) )
		{
			$interval = date_diff( date_create( $high_date ), date_create( $low_date ) );
			$pa = $pa / ( 1+intval( $interval->m + ( $interval->y * 12 ) ) );
		}
	}

	if( $extra == 4 ) $pa = $ehoch;
	if( $extra == 5 ) $pa = $eniedrig;
	if( $extra == 6 ) $pa = $ahoch*-1;
	if( $extra == 7 ) $pa = $aniedrig*-1;
	// ...nur Anzahl liefern?...sonst Durchschnitt oder Summe...
	// Vorzeichen (-) ausgeben?			
	if ( $extra == 1 ) echo $pa;
		else echo str_replace(($printsign==1?'':'-'), '', number_format($pa , 2, ",", "."));
}
	
function zp_do_bericht( $art, $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra )
{	
	if (  $art == 7 || $art == 8  )	
		require( dirname( __FILE__, 2 ) . '/functions/statistics.php' );
	if ( ( $extra != 0 ) && ( $art == 2 || $art == 5 || $art == 4 || $art == 3 || $art == 6 ) )	
	{
		echo "Parameter '%anzahl', '%durchschnitt', '%dauer' bei dieser Berichtsart nicht unterstützt! ";
		return;
	}		
	elseif ( $art == 1 ) zp_get_all_sum( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra );
	elseif ( $art == 2 ) zp_get_all_table( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra );	
	elseif ( $art == 4 || $art == 5 || $art == 6 ) zp_get_all_list( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $art, $extra );
	elseif ( $art == 3 ) zp_get_all_by_categorie_list( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra );
	elseif ( $art == 7 ) zp_get_draw_balances( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra );
	elseif ( $art == 8 ) zp_get_draw_categorie( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra );
}
	
function zp_run( $zahlungsplaner_parameter, $bericht_id, $mandant_id, $art, $ea, $shortcode_param_von, $shortcode_param_bis )
{		
	/*
		Parameter ($zp_param[]) :
			Aufruf Compiler zur Rückgabe spezieller Inhalte oder dynamischer Anpassung von Berichten
		Arten ($art) :
			1 = nur Summen
			2 = Gesamtaufstellung tabellarisch 
			3 = Summe je Kategorie
			4 = Gesamtaufstellung nach Datum mit CSV-Download
			5 = Gesamtaufstellung nach Datum
			6 = Gesamtaufstellung nach Datum saldiert
			7 = Gesamtaufstellung Grafik
			8 = Summe je Kategorie Grafik
					
		Die Auswertungs-Routinen :
			zp_get_all_sum
			zp_get_all_table (Gesamtbestand)
			zp_get_all_list
			zp_get_all_by_categorie_list
			
	*/
	global $wpdb;
	$flag = 0;
	$shortcode_param_von = "";
	$shortcode_param_bis = "";
	//if ( $shortcode_param_von == "0000-00-00" ) $shortcode_param_von = 
	
	//echo $shortcode_param_von.$shortcode_param_bis;
	//die();

	foreach ( $zahlungsplaner_parameter as $zp_param )
	{

		/*
		DATUMSFUNKTIONEN
		Heutiges Datum [zahlungsplaner Ausgaben {heute%monat+1}Y-m-d]
		bzw.
		Heutiges Datum [zahlungsplaner {heute}Y-m-d deutsche Form {heute}d.m.Y]
		Beispiel : rufe den Summenbericht "Ausgaben" und drucke das heutige Datum dazu
		Diesen Monat [zahlungsplaner Ausgaben monat {heute}m.Y]
		ergibt :
		Diesen Monat -810,00 12.2017
		
		BERICHTSDATEN
		Beispiel : drucke die Spalte "bericht" (der Name des Berichts) aus Bericht "Ausgaben"
		[zahlungsplaner Ausgaben {bericht}name]
		
		*/
		
		// Ist es am Ende dann nur Anzahl oder Durchschnitt...$extra 1=Anzahl liefern, 2=Durchschnitt liefern
		$extra = 0;	
		if ( strpos(" ".$zp_param, "%anzahl") > 0 ) 
		{
			$extra = 1;
			$zp_param = str_replace("%anzahl", "", $zp_param );	
		}
		if ( strpos(" ".$zp_param, "%durchschnitt") > 0 ) 
		{
			$extra = 2;	
			$zp_param = str_replace("%durchschnitt", "", $zp_param );
		}
		if ( strpos(" ".$zp_param, "%dauer") > 0 ) 
		{
			$extra = 3;	
			$zp_param = str_replace("%dauer", "", $zp_param );
		}
		if ( strpos(" ".$zp_param, "%hoch+") > 0 ) 
		{
			$extra = 4;	
			$zp_param = str_replace("%hoch+", "", $zp_param );
		}
		if ( strpos(" ".$zp_param, "%niedrig+") > 0 ) 
		{
			$extra = 5;	
			$zp_param = str_replace("%niedrig+", "", $zp_param );
		}
		if ( strpos(" ".$zp_param, "%hoch-") > 0 ) 
		{
			$extra = 6;	
			$zp_param = str_replace("%hoch-", "", $zp_param );
		}
		if ( strpos(" ".$zp_param, "%niedrig-") > 0 ) 
		{
			$extra = 7;	
			$zp_param = str_replace("%niedrig-", "", $zp_param );
		}
		if ( zp_left($zp_param,9) == "{bericht}" ) 
		{
			$col = $wpdb->get_var( "SELECT inhalt FROM ".$wpdb->prefix."zahlungsplan_bericht WHERE id=".$bericht_id." LIMIT 0,1" );
			/* Parameter aus Array erzeugen... */
			$inhalt = unserialize(base64_decode($col));
			$kategorielist = explode(',',$inhalt['kategorielist']);
			$flag = 1;	// Flag setzen, damit nur die Berichts-Items gedruckt werden

			$item = substr($zp_param,9,strlen($zp_param)-9);
			$bericht_id = esc_sql( $bericht_id );
			if ( $item == "name" ) echo esc_attr(sanitize_text_field($wpdb->get_var( "SELECT bericht FROM ".$wpdb->prefix."zahlungsplan_bericht WHERE id=".$bericht_id." LIMIT 0,1" )));
			elseif ( $item == "beschreibung" ) echo esc_attr(sanitize_text_field($wpdb->get_var( "SELECT beschreibung FROM ".$wpdb->prefix."zahlungsplan_bericht WHERE id=".$bericht_id." LIMIT 0,1" )));
			//elseif ( $item == "status" ) echo $wpdb->get_var( "SELECT stac FROM ".$wpdb->prefix."zahlungsplan_bericht WHERE id=".$bericht_id." LIMIT 0,1" );
			elseif ( $item == "von" ) echo ($inhalt['zeitraumvon'] == "0000-00-00"?"gesamt":esc_attr(sanitize_option( "date_format", $inhalt['zeitraumvon'])));
			elseif ( $item == "bis" ) echo ($inhalt['zeitraumbis'] == "0000-00-00"?"gesamt":esc_attr(sanitize_option( "date_format", $inhalt['zeitraumbis'])));
			elseif ( $item == "ea" ) 
			{
				if ( $inhalt['ea'] == 1 ) echo "Einnahmen und Ausgaben";
				elseif ( $inhalt['ea'] == 2 ) echo "Einnahmen";
				elseif ( $inhalt['ea'] == 3 ) echo "Ausgaben";
			}
			elseif ( $item == "mandant" ) echo ($inhalt['mandant'] == 0?"alle":esc_attr(sanitize_text_field(get_userdata($inhalt['mandant'])->display_name)));
			elseif ( $item == "kategorien" ) 
			{
				if ( $inhalt['kategorielist'] == -1 ) 
				{
					echo "alle";
					break;
				}
				$inhalt = explode(',',$inhalt['kategorielist']);
				$kategorielist = "";
				foreach ( $inhalt as $id )
				{
					$name = esc_sql( $id );
					$kategorie_name = esc_attr(sanitize_text_field($wpdb->get_var( 'SELECT kategorie FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE id='.$id ))); 
					$kategorielist .= $kategorie_name.", ";
				}
				echo zp_left($kategorielist, strlen($kategorielist)-2); 
			}

			echo " ";
		}
		if ( zp_left($zp_param,6) == "{heute" ) 
		{
			$flag = 1;	// Flag setzen, damit nur die Datumsberechnungen gedruckt werden
			$monat = 0;
			
			$p = strpos($zp_param,"}")+1;
			$form = substr($zp_param,$p,strlen($zp_param)-$p);
			$p = strpos( $zp_param, '%monat' );
			if ( $p > 0 ) $monat = intval(substr($zp_param,$p+6,strpos($zp_param,"}")-6-$p));
			$p = strpos( $zp_param, '%jahr' );
			if ( $p > 0 ) $monat = intval(substr($zp_param,$p+5,strpos($zp_param,"}")-5-$p))*12;
			if ( $monat > 0 ) echo zp_dateAddMonth("", $monat)->format("$form");
			elseif ( $monat < 0 ) echo zp_dateSubMonth("", $monat)->format("$form");
			else echo date("$form");

			echo " ";
		}
		if ( zp_left($zp_param,4) == "jahr")
		{	
			$zeitraum_von = "";
			$zeitraum_bis = "";
			$shortcode_param_von = date("Y-")."01-01";
			$shortcode_param_bis = date("Y-")."12-31";
			
			//echo "<br>";
			//echo "von ".$shortcode_param_von." bis ".$shortcode_param_bis;
			//echo "<br>";
			//echo "<br>";
			
			if ( strlen($zp_param) > 4 )
			{
				$uparam = substr($zp_param,4,strlen($zp_param)-4);
				if ( intval($uparam) > 0 ) 
				{
					$shortcode_param_von = zp_dateAddMonth($shortcode_param_von, 12*intval($uparam))->format('Y-m-d');
					$shortcode_param_bis = zp_dateAddMonth($shortcode_param_bis, 12*intval($uparam))->format('Y-m-d');
				}
				elseif ( intval($uparam) < 0 ) 
				{
					$shortcode_param_von = zp_dateSubMonth($shortcode_param_von, 12*intval($uparam))->format('Y-m-d');
					$shortcode_param_bis = zp_dateSubMonth($shortcode_param_bis, 12*intval($uparam))->format('Y-m-d');
				}
			}
			//echo "<br>";
			//echo "von ".$shortcode_param_von." bis ".$shortcode_param_bis;
			//echo "<br>";
			//echo $uparam;
			//echo "<br>";
			
		}	
		if ( zp_left($zp_param,5) == "monat")
		{	
			$zeitraum_von = "";
			$zeitraum_bis = "";
			$shortcode_param_von = date("Y-m-")."01";
			$shortcode_param_bis = date("Y-m-").date('t', date("m"));
			
			if ( strlen($zp_param) > 5 )
			{
				$uparam = substr($zp_param,5,strlen($zp_param)-5);
				if ( intval($uparam) > 0 ) 
				{
					$shortcode_param_von = zp_dateAddMonth($shortcode_param_von, intval($uparam))->format('Y-m-d');
					$shortcode_param_bis = zp_dateAddMonth($shortcode_param_bis, intval($uparam))->format('Y-m-d');
				}
				elseif ( intval($uparam) < 0 ) 
				{
					$shortcode_param_von = zp_dateSubMonth($shortcode_param_von, intval($uparam))->format('Y-m-d');
					$shortcode_param_bis = zp_dateSubMonth($shortcode_param_bis, intval($uparam))->format('Y-m-d');
				}
			}
		}
		if ( zp_left($zp_param,9) == "zeitraum:")
		{	
			$zeitraum_von = "";
			$zeitraum_bis = "";
			
			if ( strlen($zp_param) == 30 )
			{
				$ctrl = 0;
				$uparam = substr($zp_param,9,strlen($zp_param)-9);
				if ( zp_validateDate(zp_left($uparam,10), 'd.m.Y') )
					$zeitraum_von = zp_left($uparam,10);
				else $ctrl = 1;
				if ( zp_validateDate(zp_right($uparam,10), 'd.m.Y') )
					$zeitraum_bis = zp_right($uparam,10);
				else $ctrl = 1;
				if ( $ctrl == 1 )
				{
					echo "Parameter Zeitraum '$uparam' ungültig !";
					return;
				}
				$shortcode_param_von = strftime("%Y-%m-%d",strtotime($zeitraum_von));
				$shortcode_param_bis = strftime("%Y-%m-%d",strtotime($zeitraum_bis));
			}
		} 	
		if ( $flag == 0 ) 
		{
			// Berichte starten und Ausgaben durchführen	
			zp_do_bericht( $art, $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra );
			echo " ";
			$flag = 1;
		}
		
	} // foreach
}
?>
