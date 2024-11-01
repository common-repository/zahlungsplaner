<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Delete Zahlungsplaner Kategorie
/****************************************************************************************/


	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'kategorie-delete' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at delete_kategorie.php' ); 
	} 
	
  $id = (int) sanitize_key( $_GET['id'] );
  $message = "";   
  global $wpdb;

  if(isset($_POST) && isset($_POST['updateSubmit']))
  {
    // Prüfe auf Bestandseinträge mit dieser Kategorie
    $id = esc_sql( $id );
  	$res = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE kategorie_id='.$id.' LIMIT 0,1',ARRAY_A);
  	if ( !empty($res) )
    {
      $message = "Diese Kategorie kann nicht gelöscht werden, da sie im Zahlungsplan verwendet wird!";
  	}
    elseif ( zp_get_count_kategorie( 1 ) < 2 )
    {
      $message = "Mindestens eine Kategorie muss aktiv sein! ";
    }
    else
    {
    $message = "";    
      // Update der DB ...
      $wpdb->delete($wpdb->prefix.'zahlungsplan_kategorie',
          esc_sql(array('id'=>$id)));
      
      header("Location:".admin_url('admin.php?page=kategorie_list_bestand&last_message=2'));
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
      <b>Kategorie löschen</b>
    </legend>
    <form name="editbestand" id="editbestand" method="post">
      <p>Bezeichnung&nbsp; <input name="Kategorie" disabled="disabled" value="<?php echo esc_attr(sanitize_text_field($res[0]['kategorie']));?>" "placeholder="Kategorie"
            size="45"
            maxlength="45"
            type="text"
            required="required">
      </p>
      <p>Aktiv&nbsp;<input name="Status" disabled="disabled" value="1" type="checkbox" <?php echo $res[0]['stac']=='1'?" checked":'';?>></p>
      <input class="button-primary" style="cursor: pointer;
              "type="submit" name="updateSubmit" value="Löschen">
    </form>
  </div>
</html>


<?php
  if ( $message != "" ) 
  {
    echo "<h4>$message</h4>";
  } 	 
?>




