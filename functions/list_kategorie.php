<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/* PAGINATION */
	global $wpdb;
	global$zp_pagenum;
	$lcount = zp_get_konfig( "maxlines" );

	if ( isset( $_GET ) && isset( $_GET['resetfilter'] ) )
	{
		$search_kategorie = "";
		zp_put_konfig( "filterkategorie", "" );
	}
	if ( isset( $_POST ) && isset( $_POST['search_kategorie'] ) )
	{
		$search_kategorie = "%".sanitize_text_field( $_POST['search_kategorie']."%" );
		zp_put_konfig( "filterkategorie", sanitize_text_field( $_POST['search_kategorie'] ) );
	}
	else $search_kategorie = "%".zp_get_konfig( "filterkategorie" )."%";
	$pagecount = ceil( count( $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE kategorie LIKE "'.$search_kategorie.'"' ) ) / $lcount );
					   
	if ( isset( $_GET['pageno'] ) ) $zp_pagenum = (int) sanitize_key( $_GET['pageno'] ); else $zp_pagenum = 1;

	if(isset($_POST) && isset($_POST['firstpage']))
	{
		$zp_pagenum = 1;
		header("Location:".admin_url('admin.php?page=kategorie_list_bestand&pageno='.$zp_pagenum));
	}
	if(isset($_POST) && isset($_POST['prevpage']))
	{
		if ( $zp_pagenum > 1 ) --$zp_pagenum;
		header("Location:".admin_url('admin.php?page=kategorie_list_bestand&pageno='.$zp_pagenum));
	}
	if(isset($_POST) && isset($_POST['nextpage']))
	{
		if ( $zp_pagenum < $pagecount ) ++$zp_pagenum;
		header("Location:".admin_url('admin.php?page=kategorie_list_bestand&pageno='.$zp_pagenum));
	}
	if(isset($_POST) && isset($_POST['lastpage']))
	{
		$zp_pagenum = $pagecount;
		header("Location:".admin_url('admin.php?page=kategorie_list_bestand&pageno='.$zp_pagenum));
	}
/* PAGINATION ENDE */

	if(isset($_POST) && isset($_POST['newRecord']))
	{
		header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&action=kategorie-insert&_wpnonce='.wp_create_nonce('kategorie-insert').'&id='.$id->id));
	}  	
	if ( isset( $_GET['sort'] ) ) $sort = (int) sanitize_key( $_GET['sort'] );
	if ( ! isset( $sort ) ) $sort = 0; 
	elseif ( $sort > 1 ) $sort = 0;
	++$sort;
	$pos = strpos($_SERVER["REQUEST_URI"],'&sort=');
	if ( $pos > 0 ) $uri = substr( $_SERVER["REQUEST_URI"], 0, $pos ); else $uri = $_SERVER["REQUEST_URI"];
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
		<b>Zahlungsplan Kategorie</b>
	</legend>
<table style="width: 99%; background-color: #BBBBBB; border: 1px solid #E4E4E4; border-width: 1px;margin: 0 auto">
	<tr>
		<td style="width:10%; text-align:left">
		<form action="<?php echo admin_url('admin.php?page=kategorie_list_bestand&last_message=5'); ?>" method="post">
		<input name="search_kategorie"
			size="20"
			maxlength="20"
			type="text"
			value = "<?php echo str_replace('%', '', $search_kategorie); ?>"
		</td>
		<td style="width:2%; text-align:left">
			<input type="image" src="<?php echo plugins_url('zahlungsplaner/images/lupe-bw.png'); ?>" height="30" width="30" alt="Filter setzen" title="Filter setzen">
		</td>
		</form>
        <td style="width=2%; text-align:left">
        <a href="<?php echo admin_url('admin.php?page=kategorie_list_bestand&resetfilter'); ?>">
	    <img height="30" width="30" alt="Filter löschen" title="Filter löschen" src="<?php echo plugins_url('zahlungsplaner/images/remove-bw.png'); ?>"></a>
        </td>
    </tr>
</table>
<table style="width: 99%; background-color: #BBBBBB; border: 1px solid #E4E4E4; border-width: 1px;margin: 0 auto">
    <tr>
    <tr>
    	<td style="width:35%; text-align:left">Bezeichnung
					<a href="<?php echo $uri.'&sort='.$sort; ?>">
							<img height="20" width="20" title="Sortierung" src="<?php echo plugins_url('zahlungsplaner/images/'.($sort==1?'up-bw.png':'down-bw.png')); ?>"></a>
		</td> 
    	<td style="width:10%; text-align:left">Aktion</td>
    </tr>
<style="background-color: #F9F9F9;">
<?php
	global $wpdb;
	global $zp_pagenum;
	$lstart = ($zp_pagenum-1)*$lcount;
	$entrys = 0;
	$kategorieId = 0;
	$listetrows = 0;
	$lstart = esc_sql( $lstart );
	$lcount = esc_sql( $lcount );
	//$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie'.' ORDER BY kategorie '.($sort==1?'ASC':'DESC'), $zpph )); 
	$entrys = count( $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE kategorie LIKE "'.$search_kategorie.'"' ) ); 
	$results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie'.' WHERE kategorie LIKE "'.$search_kategorie.'" ORDER BY kategorie '.($sort==1?'ASC':'DESC').' LIMIT '.$lstart.','.$lcount ); 
	if (!empty($results)) 
	{
		$table_name = $wpdb->prefix.'zahlungsplan_kategorie';
		foreach ($results as $id)
		{
			wp_nonce_field( 'kategorie-pedit_'.$kategorieId );
   			echo '<tr style="background-color: #F9F9F9">
    				<td height="30" style="width:90%; text-align:left">'.esc_attr(sanitize_text_field( $id->kategorie ) ).'</td>
    				<td height="30" style="width:10%; text-align:left">
    					<a href="'.admin_url('admin.php?page=zahlungsplan-bestand-manage&action=kategorie-edit&_wpnonce='.wp_create_nonce('kategorie-edit').'&id='.$id->id.'&pageno='.$zp_pagenum).'">
							<img height="20" width="20" title="Bearbeiten" src="'.plugins_url('zahlungsplaner/images/edit-bw.png').'"></a>
    					<a href="'.admin_url('admin.php?page=zahlungsplan-bestand-manage&action=kategorie-paste&_wpnonce='.wp_create_nonce('kategorie-paste').'&id='.$id->id.'&status='.$id->stac.'&pageno='.$zp_pagenum).'">
							<img height="20" width="20" title="Kopieren" src="'.plugins_url('zahlungsplaner/images/paste-bw.png').'"></a>
    					<a href="'.admin_url('admin.php?page=zahlungsplan-bestand-manage&action=kategorie-pause&_wpnonce='.wp_create_nonce('kategorie-pause').'&id='.$id->id.'&status='.$id->stac.'&pageno='.$zp_pagenum).'">
							<img height="20" width="20" title="'.($id->stac=="1"?"Deaktivieren":"Aktivieren").'" src="'.plugins_url('zahlungsplaner/images/'.($id->stac=="1"?"pause-bw.png":"play-bw.png")).'"></a>												
    					<a href="'.admin_url('admin.php?page=zahlungsplan-bestand-manage&action=kategorie-delete&_wpnonce='.wp_create_nonce('kategorie-delete').'&id='.$id->id.'&pageno='.$zp_pagenum).'">
							<img height="20" width="20" title="Löschen" src="'.plugins_url('zahlungsplaner/images/remove-bw.png').'"></a>
						</td>
    			</tr>';
			++$listetrows;			
		}
		while ( $listetrows++ < $lcount )
		{
			echo '<tr style="background-color: #F9F9F9">';
			for ( $i = 0; $i < 2; $i++ ) echo '<td height="30"></td>'; 
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
		if ( $_GET['last_message'] == "4" )
			{ echo "<h4>Mindestens eine Kategorie muss aktiv sein!</h4>"; }
	}
	if ( $search_kategorie != "%%" ) echo "<h4>Filter für Kategorie steht auf '".str_replace('%', '', $search_kategorie)."'</h4>"; 
?>
 

