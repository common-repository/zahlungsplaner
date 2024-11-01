<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Delete Zahlungsplaner Bericht
/****************************************************************************************/


	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bericht-delete' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at delete_bericht.php' ); 
	} 
	
  $id = (int) sanitize_key( $_GET['id'] );
  $message = "";   
  global $wpdb;

  if(isset($_POST) && isset($_POST['updateSubmit']))
  {
    // Update der DB ...
    $wpdb->delete($wpdb->prefix.'zahlungsplan_bericht',
        esc_sql(array('id'=>$id)));
    
    header("Location:".admin_url('admin.php?page=bericht_list_bestand&last_message=2'));
    } 

	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
    $id = esc_sql( $id );
  	$res = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan_bericht WHERE id='.$id.' LIMIT 0,1',ARRAY_A);
  	if (empty($res)) {
		return;
  	} 
  
  /* Parameter aus Array erzeugen... */
  $bericht_abfrage = unserialize(base64_decode($res[0]['inhalt']));
  $kategorielist = explode(',',$bericht_abfrage['kategorielist']);
  /* Füge -2 on top ein, damit array_search() korrekt ausgewertet werden kann !!! */
  array_unshift($kategorielist, -2 );


?>


<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Bericht</title>
  </head>
  <div style="background-color: #d6d6d6; font-family:courier;">
  <fieldset style="width: 99%; border: 0px solid #F7F7F7; padding: 10px 10px;">
    <legend>
      <b>Bericht bearbeiten</b>
    </legend>
    <form name="editbestand" id="editbestand" method="post">
      <p>Bezeichnung&nbsp; <input name="bericht" value="<?php echo esc_attr(sanitize_text_field( $res[0]['bericht']));?>" "placeholder="Bericht"
            size="150"
            maxlength="150"
            type="text"
            disabled="disabled">
      </p>
      <table style="width: 456px; height: 150px;" border=0">
        <tr>
          <td style="vertical-align: top; background-color: #d6d6d6; width: 100px">Beschreibung</td>
          <td style="width: 477px;"><textarea name="beschreibung" disabled="disabled" cols="41" rows="10" wrap="soft"><?php echo esc_attr(sanitize_text_field( $res[0]['beschreibung']));?></textarea></td>
        </tr>    
      </table>
       <p>Zahlungsplan Mandant: 
        <select name="mandant" disabled="disabled" list="Mandant">
          <?php
            global $wpdb;
            $mandantId = 0;
            //$results = $wpdb->get_results( $wpdb->prepare( 'SELECT DISTINCT mandant_id FROM '.$wpdb->prefix.'zahlungsplan GROUP BY mandant_id', $zpph )); 
            $results = $wpdb->get_results( 'SELECT DISTINCT mandant_id FROM '.$wpdb->prefix.'zahlungsplan GROUP BY mandant_id' ); 
            if (!empty($results)) 
            {
              echo '<option value="0" >alle</option>';
              foreach ($results as $id)
              {
                echo '<option disabled="disabled" value="'.esc_attr(sanitize_key($id->mandant_id)).'"'.($bericht_abfrage['mandant']==$id->mandant_id?' selected':'').'>'.esc_attr(sanitize_text_field(get_userdata($id->mandant_id)->display_name)).'</option>';
              }
            }
          ?>
          &nbsp;
        </select>
      </p>
      <p>Zeitraum von:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <select name="zeitraumvon" disabled="disabled" list="Zeitraum von">
          <?php
            global $wpdb;
            $vonID = 0;
            //$results = $wpdb->get_results( $wpdb->prepare( 'SELECT DISTINCT start FROM '.$wpdb->prefix.'zahlungsplan GROUP BY start ORDER BY start', $zpph )); 
            $results = $wpdb->get_results( 'SELECT DISTINCT start FROM '.$wpdb->prefix.'zahlungsplan GROUP BY start ORDER BY start' ); 
            if (!empty($results)) 
            {
              echo '<option value="0000-00-00" >gesamt</option>';
              foreach ($results as $id)
              {
                echo '<option value="'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->start)))).'"'.($bericht_abfrage['zeitraumvon']==strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->start))))?' selected':'').'>'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->start)))).'</option>';                
              }
            }
          ?>
        &nbsp;
        </select>
        bis:&nbsp;
        <select name="zeitraumbis" disabled="disabled" list="Zeitraum bis">
          <?php
            global $wpdb;
            $vonID = 0;
            //$results = $wpdb->get_results( $wpdb->prepare( 'SELECT DISTINCT ende FROM '.$wpdb->prefix.'zahlungsplan GROUP BY start ORDER BY ende', $zpph )); 
            $results = $wpdb->get_results( 'SELECT DISTINCT ende FROM '.$wpdb->prefix.'zahlungsplan GROUP BY start ORDER BY ende' ); 
            if (!empty($results)) 
            {
              echo '<option value="0000-00-00" >gesamt</option>';
              foreach ($results as $id)
              {
                echo '<option value="'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->ende)))).'"'.($bericht_abfrage['zeitraumbis']==strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->ende))))?' selected':'').'>'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->ende)))).'</option>';    
              }
            }
          ?>
        &nbsp;
        </select>
      </p>            
      <p>Berichtsart:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <select name="art" disabled="disabled" list="Art">
          <option value="5" <?php echo $bericht_abfrage['art']=='5'?" selected":'';?>>Gesamtaufstellung nach Datum</option>
          <option value="4" <?php echo $bericht_abfrage['art']=='4'?" selected":'';?>>Gesamtaufstellung nach Datum mit CSV-Download</option>  
          <option value="6" <?php echo $bericht_abfrage['art']=='6'?" selected":'';?>>Gesamtaufstellung nach Datum monatlich saldiert</option>
          <option value="6" <?php echo $bericht_abfrage['art']=='7'?" selected":'';?>>Gesamtaufstellung Grafik</option>
          <option value="2" <?php echo $bericht_abfrage['art']=='2'?" selected":'';?>>Gesamtaufstellung tabellarisch</option>
          <option value="1" <?php echo $bericht_abfrage['art']=='1'?" selected":'';?>>nur Summe</option>
          <option value="3" <?php echo $bericht_abfrage['art']=='3'?" selected":'';?>>Summe je Kategorie</option>    
          <option value="3" <?php echo $bericht_abfrage['art']=='8'?" selected":'';?>>Summe je Kategorie Grafik</option>                                  
            &nbsp;
          </select> 
      </p> 
      <p>Berücksichtigen:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <select name="ea" disabled="disabled" list="Ea">
            <option value="1" <?php echo $bericht_abfrage['ea']=='1'?" selected":'';?>>Einnahmen und Ausgaben</option>
            <option value="2" <?php echo $bericht_abfrage['ea']=='2'?" selected":'';?>>Einnahmen</option>
            <option value="3" <?php echo $bericht_abfrage['ea']=='3'?" selected":'';?>>Ausgaben</option>                                
            &nbsp;
          </select>
      </p>  
      <p>  
      <fieldset>
        <table style="width:99%;" border=0">
          <td style="width:6%; text-align:left">Kategorien:</td>
              <?php
              global $wpdb;
              $kategorieId = 0;
              $col = 0;
              $flag = 0;
            
              echo '<fieldset>
                <td style="width:9%; text-align:left"><label>
                    <input type="checkbox" disabled="disabled" name="kategorie[]" value="-1"';
              if ( array_search( -1, $kategorielist ) == true ) 
              {
                echo ' checked';
                $flag = 1;
              }
              echo '>Alle Kategorien</label></td>';
              //$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie'.' WHERE stac=1 ORDER BY kategorie', $zpph )); 
              $results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie'.' WHERE stac=1 ORDER BY kategorie' ); 
              if (!empty($results)) 
              {
                foreach ($results as $id)
                {  
                  echo '<fieldset>
                    <td style="width:9%; text-align:left"><label>
                        <input type="checkbox" disabled="disabled" name="kategorie[]" value="'.$id->id.'"';
                  if ( array_search( $id->id, $kategorielist ) == true && $flag == 0 ) echo ' checked';
                  echo '>'.esc_attr(sanitize_text_field($id->kategorie)).'</label></td>';
                  if ( ++$col > 3 )
                  {
                      $col = 0;
                      echo '</tr><td style="width:5%; text-align:left"';
                  }    
                }
              }      
              ?>
          </tr>
        </table>  
      </fieldset> 
      </p>  
      <p>Aktiv&nbsp;<input name="status" disabled="disabled" value="1" type="checkbox" <?php echo $res[0]['stac']=='1'?" checked":'';?>></p>
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



