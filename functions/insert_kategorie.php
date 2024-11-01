<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Einfügen Zahlungsplaner Kategorie
/****************************************************************************************/


	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'kategorie-insert' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at insert_kategorie.php' ); 
	} 
	
  global $wpdb;
  $message = "";   

  if(isset($_POST) && isset($_POST['updateSubmit']))
  {
    $kategorie = sanitize_text_field( $_POST['Kategorie'] );
    if ( ! $kategorie ) $kategorie = '';
    if ( strlen( $kategorie ) > 150 ) $kategorie = substr( $kategorie, 0, 150 );
    $kategorie = esc_sql( $kategorie );
     // Prüfe auf doppelten Eintrag
  	$res = $wpdb->get_results('SELECT kategorie FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE kategorie="'.$kategorie.'" LIMIT 0,1',ARRAY_A);
  	if ( !empty($res) )
    {
      $message = "Diese Kategorie ist bereits vorhanden!";
  	} 
    else
    {
      if ( strlen($kategorie) > 150 ) $kategorie = substr($kategorie, 0, 150 );
      $message = "";
      // Update der DB ...
      $wpdb->insert($wpdb->prefix.'zahlungsplan_kategorie',
          esc_sql(array(
          'kategorie'=>$kategorie,
          'stac'=>(!isset($_POST['Status'])?0:1))
          ));
      header("Location:".admin_url('admin.php?page=kategorie_list_bestand&last_message=1'));
    } 
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
      <b>Kategorie anlegen</b>
    </legend>
    <form name="editbestand" id="editbestand" method="post">
      <p>Bezeichnung&nbsp; <input name="Kategorie" value="" "placeholder="Kategorie"
            size="150"
            maxlength="150"
            type="text"
            required="required">
      </p>
      <p>Aktiv&nbsp;<input name="Status" value="1" type="checkbox" checked></p>
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

