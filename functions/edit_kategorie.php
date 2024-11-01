<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Edit Zahlungsplaner Kategorie
/****************************************************************************************/


	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'kategorie-edit' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at edit_bestand.php' ); 
	} 
	
  $id = (int) sanitize_key( $_GET['id'] );

  $message = "";
  global $wpdb;

  if(isset($_POST) && isset($_POST['updateSubmit']))
  {
    $kategorie = sanitize_text_field( $_POST['kategorie'] );
    if ( ! $kategorie ) $kategorie = '';
    if ( strlen( $kategorie ) > 150 ) $kategorie = substr( $kategorie, 0, 150 );
    $kategorie = esc_sql( $kategorie );
    $id = esc_sql( $id );

    // Prüfe auf doppelten Eintrag
  	$res = $wpdb->get_results('SELECT kategorie FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE kategorie="'.$kategorie.'" AND id<>'.$id.' LIMIT 0,1',ARRAY_A);
  	if ( !empty($res) )
    {
      $message = "Diese Kategorie ist bereits vorhanden!";
  	}
    elseif ( !isset($_POST['status']) && zp_get_count_kategorie( 1 ) < 2 )
    {
        $message = "Mindestens eine Kategorie muss aktiv sein! ";
    }
    else
    {
        if ( strlen($kategorie) > 150 ) $kategorie = substr($kategorie, 0, 150 );
        $message = "";
        // Update der DB ...
        $wpdb->update($wpdb->prefix.'zahlungsplan_kategorie',
          esc_sql(array(
          'kategorie'=>$kategorie,
          'stac'=>!isset($_POST['status'])?0:1)),
          array('id'=>$id));

        header("Location:".admin_url('admin.php?page=kategorie_list_bestand&last_message=1'));
    }
  }  

	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
    $id = esc_sql( $id );
  	$res = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE id='.$id.' LIMIT 0,1',ARRAY_A);
  	if (empty($res)) {
		return;
  	} 

?>

<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Kategorie</title>
  </head>
  <div style="background-color: #d6d6d6; font-family:courier;">
  <fieldset style="width: 99%; border: 0px solid #F7F7F7; padding: 10px 10px;">
    <legend>
      <b>Kategorie bearbeiten</b>
    </legend>
    <form name="editbestand" id="editbestand" method="post">
      <p>Bezeichnung&nbsp; <input name="kategorie" value="<?php echo esc_attr(sanitize_text_field( $res[0]['kategorie'] ));?>" "placeholder="Kategorie"
            size="150"
            maxlength="150"
            type="text"
            required="required">
      </p>
      <p>Aktiv&nbsp;<input name="status" value="1" type="checkbox" <?php echo $res[0]['stac']=='1'?" checked":'';?>></p>
      <input class="button-primary" style="cursor: pointer;
              "type="submit" name="updateSubmit" value="Speichern">
    </form>
  </div>
</html>


<?php
  if ( $message != "" ) 
  {
    echo "<h4>$message</h4>";
  } 	 
?>




