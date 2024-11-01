<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Edit Zahlungsplaner Bestand
/****************************************************************************************/

	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'zahlungsplan-edit' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at edit_bestand.php' ); 
	} 
	
  $id = (int) sanitize_key( $_GET['id'] );
  global $wpdb;
  
  if(isset($_POST) && isset($_POST['updateSubmit']))
  {
    $mandant = sanitize_key( $_POST['mandant'] );
    $bezeichnung = sanitize_text_field( $_POST['bezeichnung'] ); 
    $beschreibung = sanitize_text_field( $_POST['beschreibung'] ); 
    $kategorie  = $_POST['kategorie']; 
    // ...sind die id's       $kategorie = explode( ";", sanitize_text_field( implode( ";", $_POST['kategorie']) ) )
    for ( $i = 0; $i < count( $kategorie ); $i++ )
      $kategorie[$i] = (int) sanitize_key($kategorie[$i]);
    $art = sanitize_key( $_POST['art'] );
    $umbuchung = sanitize_key( $_POST['umbuchung'] );
    $beginn  = sanitize_option( "date_format", $_POST['beginn'] ); 
    $ende  = sanitize_option( "date_format", $_POST['ende'] ); 
    $turnus = sanitize_key( $_POST['turnus'] );
    $anzahl = sanitize_key( $_POST['anzahl'] );
    $betrag = sanitize_text_field( $_POST['betrag'] );
    $status = sanitize_key( $_POST['status'] );    
    if ( strlen($bezeichnung) > 150 ) $bezeichnung = substr($bezeichnung, 0, 150 );
    // Update der DB ...
    $wpdb->update($wpdb->prefix.'zahlungsplan',
        esc_sql(array(
        'mandant_id'=>$mandant,
        'bezeichnung'=>$bezeichnung,
        'beschreibung'=>$beschreibung,
        'kategorie_id'=>$kategorie,
        'ea'=>$art,       
        'betrag'=>str_replace(',', '.', $betrag),
        'umbuchung'=>$umbuchung,
        'start'=>strftime("%Y-%m-%d",strtotime($beginn)),
        'ende'=>strftime("%Y-%m-%d",strtotime($ende)),        
        'turnus'=>$turnus,
        'anzahl'=>$anzahl,
        'stac'=>$status)), 
        array('id'=>$id));
        
        header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&last_message=1'));
  }  
	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
    $id = esc_sql( $id );
  	$res = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE id='.$id.' LIMIT 0,1',ARRAY_A);
  	if (empty($res)) {
		return;
  	} 

?>

<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Zahlungsplan</title>
  </head>
  <div style="background-color: #d6d6d6; font-family:courier;">
  <fieldset style="width: 99%; border: 0px solid #F7F7F7; padding: 10px 10px;">
    <legend>
      <b>Zahlungsplan bearbeiten</b>
    </legend>
    <form name="editbestand" id="editbestand" method="post">
    <p>Mandant&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
      <select name="mandant" list="Mandant">
        <?php
          global $wpdb;
          $zpph = array();
          $mandantId = 0;
          $results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'users', $zpph )); 
          if (!empty($results)) 
          {
            //echo '<option value="0" >alle</option>';
            foreach ($results as $id)
            {
              echo '<option value="'.sanitize_key($id->ID).'"'.($res[0]['mandant_id']==$id->ID?' selected':'').'>'.esc_attr(sanitize_text_field($id->display_name)).'</option>';
            }
          }
        ?>
        &nbsp;
      </select>    
    </p>
    <p>Bezeichnung&nbsp; <input type="text" name="bezeichnung" value="<?php echo esc_attr(sanitize_text_field( $res[0]['bezeichnung'] ));?>" "placeholder="Bezeichnung"
            size="150"
            maxlength="150"
            type="text"
            required="required">
      </p>
      <table style="width: 456px; height: 150px;" border=0">
        <tr>
          <td style="vertical-align: top; background-color: #d6d6d6; width: 100px">Beschreibung</td>
          <td style="width: 477px;"><textarea name="beschreibung" cols="41" rows="10" wrap="soft"><?php echo esc_attr(sanitize_textarea_field($res[0]['beschreibung'] ));?></textarea></td>
        </tr>
      </table>
      <p>Kategorie&nbsp;&nbsp;&nbsp;
          <select name="kategorie" list="Kategorie">
            <?php
              global $wpdb;
              $zpph = array();
              $kategorieId = 0;
              $results = $wpdb->get_results( $wpdb->prepare( 'SELECT id, kategorie FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE stac=1 ORDER BY kategorie', $zpph )); 
              if (!empty($results)) 
              {
                foreach ($results as $id)
                {
                  echo '<option value="'.sanitize_key($id->id).'"'.($res[0]['kategorie_id']==$id->id?" selected":'').'>'.esc_attr(sanitize_text_field($id->kategorie)).'</option>';
                }
              }
            ?>
            &nbsp;
          </select>
      <p> 
      <p>Art&nbsp;
          <select name="art" list="Art">
            <option value="1" <?php echo $res[0]['ea']=='1'?" selected":'';?>>Einnahme</option>
            <option value="0" <?php echo $res[0]['ea']=='0'?" selected":'';?>>Ausgabe</option>
            &nbsp;
          </select>
      </p>
      <p>Umbuchung&nbsp;<input name="umbuchung" value="1" type="checkbox" <?php echo esc_attr( $res[0]['umbuchung']=='1'?" checked":'' );?>></p> 
      <?php
        $browser = zp_get_browser_name($_SERVER['HTTP_USER_AGENT']);
        if ( $browser == 'Safari' || $browser == 'Internet Explorer')
        {
          $date_from_prev = strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$res[0]['start']))));
          $date_to_prev = strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$res[0]['ende']))));
        }
        else
        {
          $date_from_prev = esc_attr(sanitize_option( "date_format",$res[0]['start']));
          $date_to_prev = esc_attr(sanitize_option( "date_format",$res[0]['ende']));     
        }
      ?>
      <p>Start <input name="beginn" type="date" required="required" pattern="^\s*(3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2})\s*$" value="<?php echo $date_from_prev;?>" size="12"></p>
      <p>Ende&nbsp; <input name="ende" type="date" required="required" pattern="^\s*(3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2})\s*$" value="<?php echo $date_to_prev;?>" size="12"></p>
      <p>Turnus alle
          <select name="turnus" list="turnus">
            <option value="1" <?php echo $res[0]['turnus']=='1'?" selected":'';?>>1</option>
            <option value="2" <?php echo $res[0]['turnus']=='2'?" selected":'';?>>2</option>
            <option value="3" <?php echo $res[0]['turnus']=='3'?" selected":'';?>>3</option>
            <option value="4" <?php echo $res[0]['turnus']=='4'?" selected":'';?>>4</option>
            <option value="5" <?php echo $res[0]['turnus']=='5'?" selected":'';?>>5</option>
            <option value="6" <?php echo $res[0]['turnus']=='6'?" selected":'';?>>6</option>
            <option value="7" <?php echo $res[0]['turnus']=='7'?" selected":'';?>>7</option>
            <option value="8" <?php echo $res[0]['turnus']=='8'?" selected":'';?>>8</option>
            <option value="9" <?php echo $res[0]['turnus']=='9'?" selected":'';?>>9</option>
            <option value="10" <?php echo $res[0]['turnus']=='10'?" selected":'';?>>10</option>
            <option value="11" <?php echo $res[0]['turnus']=='11'?" selected":'';?>>11</option>
            <option value="12" <?php echo $res[0]['turnus']=='12'?" selected":'';?>>12</option>
            <option value="24" <?php echo $res[0]['turnus']=='24'?" selected":'';?>>24</option>
            <option value="36" <?php echo $res[0]['turnus']=='36'?" selected":'';?>>36</option>
            &nbsp;
          </select>
          Monat(e), Anzahl je Turnus
          <select name="anzahl" list="Anzahl">
            <option value="1" <?php echo $res[0]['anzahl']=='1'?" selected":'';?>>1</option>
            <option value="2" <?php echo $res[0]['anzahl']=='2'?" selected":'';?>>2</option>
            <option value="3" <?php echo $res[0]['anzahl']=='3'?" selected":'';?>>3</option>
            <option value="4" <?php echo $res[0]['anzahl']=='4'?" selected":'';?>>4</option>
            <option value="5" <?php echo $res[0]['anzahl']=='5'?" selected":'';?>>5</option>
            <option value="6" <?php echo $res[0]['anzahl']=='6'?" selected":'';?>>6</option>
            <option value="7" <?php echo $res[0]['anzahl']=='7'?" selected":'';?>>7</option>
            <option value="8" <?php echo $res[0]['anzahl']=='8'?" selected":'';?>>8</option>
            <option value="9" <?php echo $res[0]['anzahl']=='9'?" selected":'';?>>9</option>
            <option value="10" <?php echo $res[0]['anzahl']=='10'?" selected":'';?>>10</option>
            <option value="11" <?php echo $res[0]['anzahl']=='11'?" selected":'';?>>11</option>
            <option value="12" <?php echo $res[0]['anzahl']=='12'?" selected":'';?>>12</option>
            &nbsp;
          </select>
      </p>
      <p>Betrag <input name="betrag" value="<?php echo esc_attr(sanitize_text_field( str_replace(".",",",$res[0]['betrag'].(strpos($res[0]['betrag'],".")==0?".00":""))) );?>" size="16" pattern="^\d{0,9}([\.,]\d{2})?$"
            required="required"
            autocomplete="off"
            type="text"><br>
      </p>
      <p>Aktiv&nbsp;<input name="status" value="1" type="checkbox" <?php echo $res[0]['stac']=='1'?" checked":'';?>></p>
      <input class="button-primary" style="cursor: pointer;
              "type="submit" name="updateSubmit" value="Speichern">
    </form>
  </div>
</html>

