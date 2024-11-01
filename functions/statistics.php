<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* 
/*  STATISTISCHE FUNKTIONEN
/* 
/****************************************************************************************/
function zp_get_draw_balances( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra )
{
	global $wpdb;
	
	$buffer = array();
	$_buffer = array();
	$kat_where = "";
	$ea_where = "";
	$first_date = "ZZZZ-ZZ-ZZ";
	$last_date = "";
	$shortcode_param_zeitraum = 0;
	$bericht_id = esc_sql( $bericht_id );
	$kat_where = zp_build_sql_kat_where( $bericht_id );
		
	if ( $ea == 1 ) $ea_where = "";
	elseif ( $ea == 2 ) $ea_where = " AND ea=1";
	elseif ( $ea == 3 ) $ea_where = " AND ea=0";
			
	if ( $shortcode_param_bis != "" && $shortcode_param_von != "" ) $shortcode_param_zeitraum = 1;
	
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
							/* Flag für die einfache Sortierung ist gedreht... */
							$eb = ($id->ea==1?$id->betrag:$id->betrag*-1);
							if ( $date > $last_date ) $last_date = $date;
							if ( $date < $first_date ) $first_date = $date;	
							$zr = "'".zp_left( $date, 7 )."'";
							$index = -1;
							$point = 0;
							foreach ( $buffer as $list )
							{
								if ( $zr == $list[0] ) 
								{
									$index = $point;
									break; 
								}		
								++$point;					
							}
							if ( $index == -1 )	
							{
								$index = array_push( $buffer, array( $zr, 0, 0, 0 ) ) - 1;
							}
							if ( $id->umbuchung != 1 )
								if ( $id->ea==1 ) $buffer[$index][1] = $buffer[$index][1] + $id->betrag; 
								else $buffer[$index][3] = $buffer[$index][3] + $id->betrag;
						}
					}
				}
			}			
		}
	}
	sort( $buffer );
	for ( $i = 0; $i < count( $buffer ); $i++ )
		if ( $i > 0 )
			$buffer[$i][2] = $buffer[$i-1][2] + $buffer[$i][1] - $buffer[$i][3];
		else $buffer[$i][2] = $buffer[$i][2] + $buffer[$i][1] - $buffer[$i][3];

	zp_draw_balances( strftime('%d.%m.%Y',strtotime(esc_attr(sanitize_option( "date_format", $first_date))))." bis ".strftime('%d.%m.%Y',strtotime(esc_attr(sanitize_option( "date_format", $last_date)))), $buffer );
	unset( $buffer );
	unset( $_buffer );
}
function zp_draw_balances( $zeitraum, $buffer )
{
	echo '
	<html>
	<head>
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">';
	echo "
			google.charts.load('current', {'packages':['corechart']});
			google.charts.setOnLoadCallback(drawChart);

			function drawChart() {
				var data = google.visualization.arrayToDataTable([
					['Jahr', 'Einnahmen', 'Saldo', 'Ausgaben'],";
					$output = "";
					foreach ( $buffer as $data )
							$output .= "[".implode(",",$data)."],";
						echo zp_left( $output, -1 ); 
						
	echo "]);";
	echo "
				var options = {
					width: 620,
					height: 240,
					series: {
						0: { lineWidth: 1 },
						1: { lineWidth: 3 },
						2: { lineWidth: 1 }
					},
					colors: ['green', 'gray', 'red'],
					title: '".$zeitraum."',
					curveType: 'function',
					legend: { position: 'bottom' }
				};

				var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

				chart.draw(data, options);
			}
		</script>
	</head>
	<body>";
	echo '
		<div id="curve_chart" style="position: relative; left: -45px;"></div>
	</body>
</html>';
}









function zp_get_draw_categorie( $bericht_id, $mandant_id, $ea, $shortcode_param_von, $shortcode_param_bis, $extra )
{
	global $wpdb;
	$buffer = array();
	$_buffer = array();
	$e_buffer = array();
	$a_buffer = array();
	$first_date = "ZZZZ-ZZ-ZZ";
	$last_date = "";
	$kat_where = "";
	$ea_where = "";
	$shortcode_param_zeitraum = 0;	
	$kat_where = zp_build_sql_kat_where( $bericht_id );
	
	if ( $ea == 1 ) $ea_where = "";
	elseif ( $ea == 2 ) $ea_where = " AND ea=1";
	elseif ( $ea == 3 ) $ea_where = " AND ea=0";
		
	if ( $shortcode_param_bis != "" && $shortcode_param_von != "" ) $shortcode_param_zeitraum = 1;

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
				while ($date < $id->ende)
				{
					$date = zp_dateAddMonth($startdate, $month)->format('Y-m-d');
					$month = $month+=$id->turnus;
					if ($date <= $id->ende)
					{
						if ( $date > $last_date ) $last_date = $date;
						if ( $date < $first_date ) $first_date = $date;
						if ( $shortcode_param_zeitraum == 1 )
							if ( $date < $shortcode_param_von || $date > $shortcode_param_bis ) continue;						
						for ($i = 0; $i < $id->anzahl; $i++)
						{				
							$_buffer['kategorie_name'] = esc_attr(sanitize_text_field($id->kategorie_name));
							$_buffer['betrag'] = esc_attr(sanitize_text_field(($id->umbuchung == 1?0.00:$id->betrag)));
							$_buffer['ea'] = esc_attr(sanitize_key($id->ea));
							array_push( $buffer, $_buffer );

						}
					}
				}
			}			
		}
	}
	foreach($buffer as $line)
	{	
		if ( $line['ea'] == 1 )
		{
			$index = -1;
			$point = 0;
			foreach ( $e_buffer as $list )
			{
				if ( $line['kategorie_name'] == $list[0] ) 
				{
					$index = $point;
					break; 
				}		
				++$point;					
			}
			if ( $index == -1 )	
				$index = array_push( $e_buffer, array( $line['kategorie_name'], 0 ) ) - 1;
			$e_buffer[$index][1] += $line['betrag'];	
		}
		elseif ( $line['ea'] == 0 )
		{
			$index = -1;
			$point = 0;
			foreach ( $a_buffer as $list )
			{
				if ( $line['kategorie_name'] == $list[0] ) 
				{
					$index = $point;
					break; 
				}		
				++$point;					
			}
			if ( $index == -1 )	
				$index = array_push( $a_buffer, array( $line['kategorie_name'], 0 ) ) - 1;
			$a_buffer[$index][1] += $line['betrag'];	
		}
	}
	
	if ( $ea == 2 )
		zp_draw_categorie( "Einnahmen", strftime('%d.%m.%Y',strtotime(esc_attr(sanitize_option( "date_format", $first_date))))." bis ".strftime('%d.%m.%Y',strtotime(esc_attr(sanitize_option( "date_format", $last_date)))), $e_buffer );
		elseif ( $ea == 3 )
			zp_draw_categorie( "Ausgaben", strftime('%d.%m.%Y',strtotime(esc_attr(sanitize_option( "date_format", $first_date))))." bis ".strftime('%d.%m.%Y',strtotime(esc_attr(sanitize_option( "date_format", $last_date)))), $a_buffer );
		elseif ( $ea == 1 )
			zp_draw_both_categorie( strftime('%d.%m.%Y',strtotime(esc_attr(sanitize_option( "date_format", $first_date))))." bis ".strftime('%d.%m.%Y',strtotime(esc_attr(sanitize_option( "date_format", $last_date)))), $e_buffer, $a_buffer );
	unset( $buffer );
	unset( $_buffer );
	unset( $e_buffer );
	unset( $a_buffer );
}


function zp_draw_categorie( $art, $bezeichnung, $buffer )
{
	echo '
	<html>
	<head>
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
			google.charts.load("current", {packages:["corechart"]});
			google.charts.setOnLoadCallback(drawChart);

			function drawChart() {
				var data = google.visualization.arrayToDataTable([';
				$output = "['Kategorie', '".$art."' ],";
				foreach ( $buffer as $data )
						$output .= "['".$data[0]."', ".$data[1]."],";
					echo zp_left( $output, -1 ); 
					
			echo "]);";
			echo "				
				var options = {
					title: '".$art." nach Kategorien vom ".$bezeichnung."',
					is3D: true 
				};
				var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
				chart.draw(data, options);
			}
		</script>
	</head>
	<body>";
	echo '
 		<div id="piechart_3d" style="position: relative; left: -45px;"></div>
	</body>
	</html>';
}
function zp_draw_both_categorie( $bezeichnung, $e_buffer, $a_buffer )
{
	$art = "Einnahmen";
	echo '
	<html>
	<head>
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
			google.charts.load("current", {packages:["corechart"]});
			google.charts.setOnLoadCallback(drawEChart);
			google.charts.setOnLoadCallback(drawAChart);

			function drawEChart() {
				var data = google.visualization.arrayToDataTable([';
				$output = "['Kategorie', '".$art."' ],";
				foreach ( $e_buffer as $data )
						$output .= "['".$data[0]."', ".$data[1]."],";
					echo zp_left( $output, -1 ); 
					
			echo "]);";
			echo "				
				var options = {
					title: '".$art." nach Kategorien vom ".$bezeichnung."',
					is3D: true 
				};
				var chart = new google.visualization.PieChart(document.getElementById('piechart_3d_E'));
				chart.draw(data, options);
			}";
			$art = "Ausgaben";
			echo '
			
			function drawAChart() {
				var data = google.visualization.arrayToDataTable([';
				$output = "['Kategorie', '".$art."' ],";
				foreach ( $a_buffer as $data )
						$output .= "['".$data[0]."', ".$data[1]."],";
					echo zp_left( $output, -1 ); 
					
			echo "]);";
			echo "				
				var options = {
					title: '".$art." nach Kategorien vom ".$bezeichnung."',
					is3D: true 
				};
				var chart = new google.visualization.PieChart(document.getElementById('piechart_3d_A'));
				chart.draw(data, options);
			}
		</script>
	</head>
	<body>";
	echo '
 		<div id="piechart_3d_E" style="position: relative; left: -45px;"></div>
		<div id="piechart_3d_A" style="position: relative; left: -45px;"></div>
	</body>
	</html>';
}

?>