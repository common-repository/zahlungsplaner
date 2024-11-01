<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/* PAGINATION */
	global $wpdb;
	global $zp_pagenum;
	$lcount = zp_get_konfig( "maxlines" );
	
	if ( isset( $_GET ) && isset( $_GET['resetfilter'] ) )
	{
		$search_bestand = "";
		zp_put_konfig( "filterbestand", "" );
	}
	if ( isset( $_POST ) && isset( $_POST['search_bestand'] ) )
	{
		$search_bestand = "%".sanitize_text_field( $_POST['search_bestand']."%" );
		zp_put_konfig( "filterbestand", sanitize_text_field( $_POST['search_bestand'] ) );
	}
	else $search_bestand = "%".zp_get_konfig( "filterbestand" )."%";
	
	$pagecount = ceil( count( $results = $wpdb->get_results( 'SELECT *, (SELECT DISTINCT display_name FROM '.$wpdb->prefix.'users WHERE id=mandant_id) AS user_name FROM '.$wpdb->prefix.'zahlungsplan WHERE bezeichnung LIKE "'.$search_bestand.'"' ) ) / $lcount );
		
	if ( isset( $_GET['pageno'] ) ) $zp_pagenum = (int) sanitize_key( $_GET['pageno'] ); else $zp_pagenum = 1;

	if(isset($_POST) && isset($_POST['firstpage']))
	{
		$zp_pagenum = 1;
		header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&pageno='.$zp_pagenum));
	}
	if(isset($_POST) && isset($_POST['prevpage']))
	{
		if ( $zp_pagenum > 1 ) --$zp_pagenum;
		header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&pageno='.$zp_pagenum));
	}
	if(isset($_POST) && isset($_POST['nextpage']))
	{
		if ( $zp_pagenum < 5 ) ++$zp_pagenum;
		header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&pageno='.$zp_pagenum));
	}
	if(isset($_POST) && isset($_POST['lastpage']))
	{
		$zp_pagenum = $pagecount;
		header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&pageno='.$zp_pagenum));
	}
/* PAGINATION ENDE */


	if(isset($_POST) && isset($_POST['newRecord']))
	{
		header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&action=zahlungsplan-insert&_wpnonce='.wp_create_nonce('zahlungsplan-insert').'&id='.$id->id));
	}
	if ( isset( $_GET['sort'] ) ) $sort = (int) sanitize_key( $_GET['sort'] );
	if ( ! isset( $sort ) ) $sort = 0; 
	elseif ( $sort > 1 ) $sort = 0;
	++$sort;
	$pos = strpos($_SERVER["REQUEST_URI"],'&sort=');
	if ( $pos > 0 ) $uri = substr( $_SERVER["REQUEST_URI"], 0, $pos ); else $uri = $_SERVER["REQUEST_URI"];
	if ( isset( $_GET['msort'] ) ) $msort = $_GET['msort'];
	if ( ! isset( $msort ) ) $msort = 0; 
	elseif ( $msort > 1 ) $msort = 0;
	++$msort;
	$pos = strpos($uri,'&msort=');
	if ( $pos > 0 ) $uri = substr( $uri, 0, $pos );
	/*
	<style type="text/css">
	td, tr, table {
		border:1px #000 solid;
		border-collapse:collapse;
		text-align:right;
	}
	</style>
	*/
?>

<div style="background-color: #d6d6d6; font-family:courier;">
<fieldset style="width: 99%; border: 0px solid #F7F7F7; padding: 10px 10px;">
	<legend>
		<b>Zahlungsplan Bestand</b>
	</legend>
	<table style="width: 99%; background-color: #BBBBBB; border: 1px solid #E4E4E4; border-width: 1px;margin: 0 auto">
		<tr>
			<td style="width:10%; text-align:left">
			<form action="<?php echo admin_url('admin.php?page=zahlungsplan-bestand-manage&last_message=5'); ?>" method="post">
			<input name="search_bestand"
				size="20"
				maxlength="20"
				type="text"
				value = "<?php echo str_replace('%', '', $search_bestand); ?>"
			</td>
			<td style="width:2%; text-align:left">
				<input type="image" src="<?php echo plugins_url('zahlungsplaner/images/lupe-bw.png'); ?>" height="30" width="30" alt="Filter setzen" title="Filter setzen">
			</td>
			</form>
	        <td style="width=2%; text-align:left">
	        <a href="<?php echo admin_url('admin.php?page=zahlungsplan-bestand-manage&resetfilter'); ?>">
		    <img height="30" width="30" alt="Filter löschen" title="Filter löschen" src="<?php echo plugins_url('zahlungsplaner/images/remove-bw.png'); ?>"></a>
	        </td>
	    </tr>
	</table>	
<table style="width: 99%; background-color: #BBBBBB; border: 1px solid #E4E4E4; border-width: 1px;margin: 0 auto">
    <tr>
</td> 
    	<td style="width:27%; text-align:left">Bezeichnung
  					<a href="<?php echo $uri.'&sort='.$sort.'&pageno='.$zp_pagenum; ?>">
							<img height="20" width="20" title="Sortierung" src="<?php echo plugins_url('zahlungsplaner/images/'.($sort==1?'up-bw.png':'down-bw.png')); ?>"></a>
</td> 
    	<td style="width:18%; text-align:left">Mandant
  					<a href="<?php echo $uri.'&msort='.$msort.'&pageno='.$zp_pagenum; ?>">
							<img height="20" width="20" title="Sortierung" src="<?php echo plugins_url('zahlungsplaner/images/'.($msort==1?'up-bw.png':'down-bw.png')); ?>"></a>

    	<td style="width:15%; text-align:left">Kategorie</td>
    	<td style="width:2%; text-align:left">UB</td>   
    	<td style="width:2%; text-align:left"></td>
    	<td style="width:6%; text-align:left">Betrag</td> 
    	<td style="width:7%; text-align:left">Start</td>     
    	<td style="width:7%; text-align:left">Ende</td>   
    	<td style="width:3%; text-align:left">Turnus</td>   
    	<td style="width:3%; text-align:left">Anzahl</td>   
    	<td style="width:10%; text-align:left">Aktion</td>
    </tr>

<style="background-color: #F9F9F9;">
<?php
	global $wpdb;
	global $zp_pagenum;
	$zpph = array();
	$bestandId = 0;
	$lstart = ($zp_pagenum-1)*$lcount;
	$entrys = 0;
	$listetrows = 0;
	
	if ( $sort == 1 && $msort == 1) $sort_order = "bezeichnung ASC, user_name ASC";
	elseif ( $sort == 2 && $msort == 1) $sort_order = "bezeichnung DESC, user_name ASC";
	elseif ( $sort == 1 && $msort == 2) $sort_order = "user_name ASC, bezeichnung DESC";
	elseif ( $sort == 2 && $msort == 2) $sort_order = "user_name DESC, bezeichnung DESC";

	$lstart = esc_sql( $lstart );
	$lcount = esc_sql( $lcount );
	$sort_order = esc_sql( $sort_order );
	//$results = $wpdb->get_results( 'SELECT *, (SELECT DISTINCT display_name FROM '.$wpdb->prefix.'users WHERE id=mandant_id) AS user_name FROM '.$wpdb->prefix.'zahlungsplan ORDER BY '.$sort_order ); 
	$entrys = count( $results = $wpdb->get_results( 'SELECT *, (SELECT DISTINCT display_name FROM '.$wpdb->prefix.'users WHERE id=mandant_id) AS user_name FROM '.$wpdb->prefix.'zahlungsplan WHERE bezeichnung LIKE "'.$search_bestand.'"' ) );
	$results = $wpdb->get_results( 'SELECT *, (SELECT DISTINCT display_name FROM '.$wpdb->prefix.'users WHERE id=mandant_id) AS user_name FROM '.$wpdb->prefix.'zahlungsplan WHERE bezeichnung LIKE "'.$search_bestand.'" ORDER BY '.$sort_order.' LIMIT '.$lstart.','.$lcount );  		
	if (!empty($results)) 
	{
		$table_name = $wpdb->prefix.'zahlungsplan_kategorie';
		foreach ($results as $id)
		{
			wp_nonce_field( 'bestand-pedit_'.$bestandId );
			$kategorie_name = $wpdb->get_var( "SELECT kategorie FROM $table_name WHERE id=".sanitize_key($id->kategorie_id)." LIMIT 1" );
   			echo '<tr style="background-color: #F9F9F9">
    				<td height="30" style="width:27%; text-align:left;">'.esc_attr(sanitize_text_field( $id->bezeichnung )).'</td>
    				<td height="30" style="width:18%; text-align:left">'.esc_attr(sanitize_text_field( get_userdata($id->mandant_id)->display_name )).'</td>
    				<td height="30"style="width:15%; text-align:left;">'.esc_attr(sanitize_text_field($kategorie_name )).'</td>
    				<td height="30" style="width:2%">'.($id->umbuchung==1?'<img height="20" width="20" title="Umbuchung" align="middle" src="'.plugins_url('zahlungsplaner/images/check-bw.png').'"':"").'</td>
    				<td height="30" style="width:2%">'.($id->ea==1?'<img height="20" width="20" title="Einnahme" align="middle" src="'.plugins_url('zahlungsplaner/images/plus.png').'"':'<img  height="22" width="22" title="Ausgabe" align="middle" src="'.plugins_url('zahlungsplaner/images/minus.png').'"').'</td>
  			  		<td height="30" style="width:6%; text-align:right";>'.number_format(esc_attr(sanitize_text_field( $id->betrag )), 2, ",", ".").'</td>
   			 		<td height="30" style="width:7%; text-align:right";>'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format", $id->start)))).'</td> 
    				<td height="30" style="width:7%; text-align:right";>'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format", $id->ende)))).'</td>   
    				<td height="30" style="width:3%; text-align:right";>'.esc_attr(sanitize_key( $id->turnus )).'</td>  
    				<td height="30" style="width:3%; text-align:right";>'.esc_attr(sanitize_key( $id->anzahl )).'</td>  
    				<td height="30" style="width:10%; text-align:left">
    					<a href="'.admin_url('admin.php?page=zahlungsplan-bestand-manage&action=zahlungsplan-edit&_wpnonce='.wp_create_nonce('zahlungsplan-edit').'&id='.$id->id.'&pageno='.$zp_pagenum).'">
							<img height="20" width="20" title="Bearbeiten" src="'.plugins_url('zahlungsplaner/images/edit-bw.png').'"></a>
						<a href="'.admin_url('admin.php?page=zahlungsplan-bestand-manage&action=zahlungsplan-paste&_wpnonce='.wp_create_nonce('zahlungsplan-paste').'&id='.$id->id.'&status='.$id->stac.'&pageno='.$zp_pagenum).'">
							<img height="20" width="20" title="Kopieren" src="'.plugins_url('zahlungsplaner/images/paste-bw.png').'"></a>
    					<a href="'.admin_url('admin.php?page=zahlungsplan-bestand-manage&action=zahlungsplan-pause&_wpnonce='.wp_create_nonce('zahlungsplan-pause').'&id='.$id->id.'&status='.$id->stac.'&pageno='.$zp_pagenum).'">
							<img height="20" width="20" title="'.($id->stac=="1"?"Deaktivieren":"Aktivieren").'" src="'.plugins_url('zahlungsplaner/images/'.($id->stac=="1"?"pause-bw.png":"play-bw.png")).'"></a>
    					<a href="'.admin_url('admin.php?page=zahlungsplan-bestand-manage&action=zahlungsplan-delete&_wpnonce='.wp_create_nonce('zahlungsplan-delete').'&id='.$id->id.'&pageno='.$zp_pagenum).'">
							<img height="20" width="20" title="Löschen" src="'.plugins_url('zahlungsplaner/images/remove-bw.png').'"></a>
						</td>    				
    				
    			</tr>'; 
			++$listetrows;		
		}
		while ( $listetrows++ < $lcount )
		{
			echo '<tr style="background-color: #F9F9F9">';
			for ( $i = 0; $i < 11; $i++ ) echo '<td height="30"></td>'; 
			echo '</tr>';
		}
  	}
?>
</table>
</form>
<?php require( dirname( __FILE__ , 2 ) . '/functions/pagination_ui.php'); ?>	
<form name="editbestand" id="editbestand" method="post">
<input class="button-primary" style="cursor: pointer;
				"type="submit" name="newRecord" value="Anlegen">
</form>
</div>

<?php
	if (isset($_POST) && isset($_GET['last_message'])) 
	{
 		if ( $_GET['last_message'] == "1" ) 
			{ echo "<h4>Speichern erfolgreich beendet.</h4>"; }
 		if ( $_GET['last_message'] == "2" ) 
			{ echo "<h4>Löschen erfolgreich beendet.</h4>"; }
		if ( $_GET['last_message'] == "3" ) 
			{ echo "<h4>Kopieren erfolgreich beendet.</h4>"; }
	} 	 
	if ( $search_bestand != "%%" ) echo "<h4>Filter für Bericht steht auf '".str_replace('%', '', $search_bestand)."'</h4>"; 
?>


