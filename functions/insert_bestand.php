<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Einfügen Zahlungsplaner Bestand
/****************************************************************************************/

	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'zahlungsplan-insert' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at insert_bestand.php' ); 
	} 
	
  global $wpdb;

  if(isset($_POST) && isset($_POST['updateSubmit']))
  {
    $mandant = sanitize_key( $_POST['mandant'] );
    $bezeichnung = sanitize_text_field( $_POST['bezeichnung'] ); 
    $beschreibung = sanitize_textarea_field( $_POST['beschreibung'] ); 
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
    $wpdb->insert($wpdb->prefix.'zahlungsplan',
        esc_sql( array(
        'mandant_id'=>$_POST['mandant'],
        'bezeichnung'=>$_POST['bezeichnung'],
        'beschreibung'=>$_POST['beschreibung'],
        'kategorie_id'=>$_POST['kategorie'],
        'ea'=>$_POST['art'],       
        'betrag'=>str_replace(',', '.', $_POST['betrag']),
        'umbuchung'=>(!isset($_POST['umbuchung'])?0:1),
        'start'=>strftime("%Y-%m-%d",strtotime($_POST['beginn'])),
        'ende'=>strftime("%Y-%m-%d",strtotime($_POST['ende'])),     
        'turnus'=>$_POST['turnus'],
        'anzahl'=>$_POST['anzahl'],
        'stac'=>(!isset($_POST['status'])?0:1)))
        );
        
        header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&last_message=1'));
    
  }  

 

	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/

/*
  	$res = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE id='.$id.' LIMIT 0,1',ARRAY_A);
  	if (empty($res)) {
		return;
  	} 
*/

?>

<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Zahlungsplan</title>
  </head>
  <div style="background-color: #d6d6d6; font-family:courier;">
  <fieldset style="width: 99%; border: 0px solid #F7F7F7; padding: 10px 10px;">
    <legend>
      <b>Zahlungsplan anlegen</b>
    </legend>
    <form name="editbestand" id="editbestand" method="post">
      <p>Mandant&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
        <select name="mandant" list="mandant">
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
                echo '<option value="'.sanitize_key($id->ID).'"'.(get_current_user_id()==$id->ID?' selected':'').'>'.esc_attr(sanitize_text_field($id->display_name)).'</option>';
              }
            }
          ?>
          &nbsp;
        </select>    
      </p>      
      <p>Bezeichnung&nbsp; <input name="bezeichnung" value="" "placeholder="Bezeichnung"
            size="150"
            maxlength="150"
            type="text"
            required="required">
      </p>
      <table style="width: 456px; height: 150px;" border=0">
        <tr>
          <td style="vertical-align: top; background-color: #d6d6d6; width: 100px">Beschreibung</td>
          <td style="width: 477px;"><textarea name="beschreibung" cols="41" rows="10" wrap="soft" ></textarea></td>
        </tr>
      </table>
      <p>Kategorie&nbsp;&nbsp;&nbsp;
          <select name="kategorie" list="kategorie" value="-1" checked>
            <?php
              global $wpdb;
              $zpph = array();
              $kategorieId = 0;
              $results = $wpdb->get_results( $wpdb->prepare( 'SELECT id, kategorie FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE stac=1 ORDER BY kategorie', $zpph )); 
              if (!empty($results)) 
              {
                foreach ($results as $id)
                {
                  echo '<option value="'.sanitize_key($id->id).'">'.esc_attr(sanitize_text_field($id->kategorie)).'</option>';
                }
              }
            ?>
            &nbsp;
          </select>
      <p> 
      <p>Art&nbsp;
          <select name="Art" list="Art">
            <option value="1" >Einnahme</option>
            <option value="0" >Ausgabe</option>
            &nbsp;
          </select>
      </p>
      <p>Umbuchung&nbsp;<input name="umbuchung" value="0" type="checkbox"></p>
      <?php
        $browser = zp_get_browser_name($_SERVER['HTTP_USER_AGENT']);
        if ( $browser == 'Safari' || $browser == 'Internet Explorer')
        {
          $date_from_prev = strftime("%d.%m.%Y",time());
          $date_to_prev = zp_dateAddMonth(strftime("%d.%m.%Y",time()), 1)->format('d.m.Y');
        }
        else 
        {
          $date_from_prev = strftime("%Y-%m-%d",time());  
          $date_to_prev = zp_dateAddMonth(strftime("%Y-%m-%d",time()), 1)->format('Y-m-d');          
        }
      ?>
      <p>Start <input name="beginn" type="date" required="required" pattern="^\s*(3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2})\s*$" value="<?php echo $date_from_prev;?>" size="12"></p>
      <p>Ende&nbsp; <input name="ende" type="date" required="required" pattern="^\s*(3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2})\s*$" value="<?php echo $date_to_prev?>" size="12"></p>
      <p>Turnus alle
          <select name="turnus" list="turnus">
            <option value="1" selected >1</option>
            <option value="2" >2</option>
            <option value="3" >3</option>
            <option value="4" >4</option>
            <option value="5" >5</option>
            <option value="6" >6</option>
            <option value="7" >7</option>
            <option value="8" >8</option>
            <option value="9" >9</option>
            <option value="10" >10</option>
            <option value="11" >11</option>
            <option value="12" >12</option>
            <option value="24" >24</option>
            <option value="36" >36</option>
            &nbsp;
          </select>
          Monat(e), Anzahl je Turnus
          <select name="anzahl" list="Anzahl">
            <option value="1" selected >1</option>
            <option value="2" >2</option>
            <option value="3" >3</option>
            <option value="4" >4</option>
            <option value="5" >5</option>
            <option value="6" >6</option>
            <option value="7" >7</option>
            <option value="8" >8</option>
            <option value="9" >9</option>
            <option value="10" >10</option>
            <option value="11" >11</option>
            <option value="12" >12</option>
            &nbsp;
          </select>
      </p>
      <p>Betrag <input name="betrag" value="0,00" size="16" pattern="^\d{0,9}([\.,]\d{2})?$"
            required="required"
            autocomplete="off"
            type="text"><br>
      </p>
      <p>Aktiv&nbsp;<input name="status" value="1" type="checkbox" checked></p>
      <input class="button-primary" style="cursor: pointer;
              "type="submit" name="updateSubmit" value="Speichern">
    </form>
  </div>
</html>

