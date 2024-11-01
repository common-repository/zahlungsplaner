<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Edit Zahlungsplaner Konfiguration
/****************************************************************************************/


	if ( ! wp_verify_nonce( $_wpnonce, 'zahlungsplan_einstellungen' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at edit_config.php' ); 
	} 

  $message = "";   
  global $wpdb;

  if (isset($_POST) && isset($_POST['updateSubmit']))
  {
    $message = "";
    /* Array der parameter erzeugen... */
    $bericht_abfrage = base64_encode(serialize(array(
    'tabellieren'=>sanitize_key( $_POST['tabellieren'] ),
    'tabfarbe1'=>sanitize_text_field( $_POST['tabfarbe1'] ),
    'tabfarbe2'=>sanitize_text_field( $_POST['tabfarbe2'] ),
    'printkat'=>sanitize_key( $_POST['printkat'] ),
    'printsign'=>sanitize_key( $_POST['printsign'] ),
    'maxlines'=>sanitize_key( $_POST['maxlines'] ),
    'filterbestand'=>sanitize_text_field( $_POST['filterbestand'] ),
    'filterkategorie'=>sanitize_text_field( $_POST['filterkategorie'] ),
    'filterbericht'=>sanitize_text_field( $_POST['filterbericht'] )
    )));
    
    // Update der DB ...
    $wpdb->update($wpdb->prefix.'zahlungsplan_konfig',
        esc_sql(array(
        'inhalt'=>$wpdb->_real_escape($bericht_abfrage),
        'stac'=>1)),
        array('id'=>1));

    header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&last_message=1'));
  } 


	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
  $res = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan_konfig WHERE id=1 LIMIT 0,1',ARRAY_A);
  if (empty($res)) return;

  /* Parameter aus Array erzeugen... */
  $bericht_abfrage = unserialize(base64_decode($res[0]['inhalt']));
  
  /* Prüfe auf Vorhandensein der einzelnen Parameter. Sind neue hinzugekommen? */
  if (!isset($bericht_abfrage['tabellieren'])) $bericht_abfrage['tabellieren'] = "1";
  if (!isset($bericht_abfrage['tabfarbe1'])) $bericht_abfrage['tabfarbe1'] = "DDDDDD";
  if (!isset($bericht_abfrage['tabfarbe2'])) $bericht_abfrage['tabfarbe2'] = "E8FDE7";
  if (!isset($bericht_abfrage['printkat'])) $bericht_abfrage['printkat'] = "1";
  if (!isset($bericht_abfrage['printsign'])) $bericht_abfrage['printsign'] = "1";
  if (!isset($bericht_abfrage['maxlines'])) $bericht_abfrage['maxlines'] = "10";
  if (!isset($bericht_abfrage['filterbestand'])) $bericht_abfrage['filterbestand'] = "";
  if (!isset($bericht_abfrage['filterkategorie'])) $bericht_abfrage['filterkategorie'] = "";
  if (!isset($bericht_abfrage['filterbericht'])) $bericht_abfrage['filterbericht'] = "";

?>

<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Konfiguration</title>
  </head>
  <div style="background-color: #d6d6d6; font-family:courier;">
  <fieldset style="width: 99%; border: 0px solid #F7F7F7; padding: 10px 10px;">
    <legend>
      <b>Konfiguration bearbeiten</b>
    </legend>
    <form name="editkonfig" id="editkonfig" method="post">
      <p><b>Bestandslisten:</b></p>
      <p>Anzahl Zeilen in Listen&nbsp;&nbsp;<input name="maxlines" value="<?php echo esc_attr( $bericht_abfrage['maxlines'] );?>" placeholder="10"
          size="2"
          maxlength="2"
          type="text"
          required="required">
      <p>Filter für Bestandsdaten&nbsp;<input name="filterbestand" value="<?php echo esc_attr( $bericht_abfrage['filterbestand'] );?>" placeholder=""
          size="20"
          maxlength="20"
          type="text">
      <p>Filter für Kategorien&nbsp;&nbsp;&nbsp;&nbsp;<input name="filterkategorie" value="<?php echo esc_attr( $bericht_abfrage['filterkategorie'] );?>" placeholder=""
          size="20"
          maxlength="20"
          type="text">
      <p>Filter für Berichte&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="filterbericht" value="<?php echo esc_attr( $bericht_abfrage['filterbericht'] );?>" placeholder=""
          size="20"
          maxlength="20"
          type="text">
      <p><b>Ausgabe Tabellenformat:</b></p>
      Ausgabe tabelliert darstellen&nbsp;<input name="tabellieren" value="1" type="checkbox" <?php echo $bericht_abfrage['tabellieren']=='1'?" checked":'';?>>
      Basisfarbe #<input name="tabfarbe1" value="<?php echo esc_attr( $bericht_abfrage['tabfarbe1'] );?>" "placeholder="DDDDDD"
          size="6"
          maxlength="6"
          type="text"
          required="required">
      Alternativfarbe #<input name="tabfarbe2" value="<?php echo esc_attr( $bericht_abfrage['tabfarbe2'] );?>" "placeholder="D1D1D1"
          size="6"
          maxlength="6"
          type="text"
          required="required">     
      </p>
      <p>Ausgabe mit Kategorie&nbsp;<input name="printkat" value="1" type="checkbox" <?php echo $bericht_abfrage['printkat']=='1'?" checked":'';?>></p>
      <p><b>Berichtsart Summen:</b></p>
      <p>Ausgabe mit Vorzeichen&nbsp;<input name="printsign" value="1" type="checkbox" <?php echo $bericht_abfrage['printsign']=='1'?" checked":'';?>></p>
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




