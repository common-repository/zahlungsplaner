<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Einfügen Zahlungsplaner Bestand
/****************************************************************************************/

  if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bericht-insert' ) ) 
  {
 	   // This nonce is not valid.
 	   die( 'Security check at insert_bericht.php' ); 
  } 
  
function zp_updateDB( $id, $bericht_name, $beschreibung, $zeitraumvon, $zeitraumbis, $kategorie, $mandant, $art, $ea, $status )
{
  global $wpdb;
  
  /* Array der parameter erzeugen... */
  $bericht_abfrage = base64_encode(serialize(array(
  'mandant'=>$mandant,
  'zeitraumvon'=>$zeitraumvon,
  'zeitraumbis'=>$zeitraumbis,
  'art'=>$art,
  'ea'=>$ea,
  'kategorielist'=>implode(',',$kategorie)
  )));
  
  // Update der DB ...
  $wpdb->update($wpdb->prefix.'zahlungsplan_bericht',
      esc_sql(array(
      'bericht'=>$bericht_name,
      'beschreibung'=>$beschreibung,
      'inhalt'=>$wpdb->_real_escape($bericht_abfrage),
      'stac'=>$status)),
      array('id'=>$id));
}

	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bericht-insert' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at insert_bericht.php' ); 
	} 
	
  global $wpdb;
  $message = "";   

  if( ( isset($_POST) && isset($_POST['updateSubmit']) || (isset($_POST) && isset($_POST['runTest']))) )
  {
    $bericht_name  = sanitize_text_field( $_POST['bericht'] ); 
    $bericht_name = str_replace(" ", "-", trim($bericht_name));
    //if ( ! $bericht_name ) $bericht_name = '';
    if ( strlen( $bericht_name ) > 150 ) $bericht_name = substr( $bericht_name, 0, 150 );
    $bericht_name = esc_sql( $bericht_name );
    $status = sanitize_key( $_POST['status'] );
    $art = sanitize_key( $_POST['art'] );
    $ea = sanitize_key( $_POST['ea'] );
    $mandant = sanitize_key( $_POST['mandant'] );
    $zeitraumvon  = sanitize_option( "date_format", $_POST['zeitraumvon'] ); 
    $zeitraumbis  = sanitize_option( "date_format", $_POST['zeitraumbis'] ); 
    $beschreibung  = sanitize_text_field( $_POST['beschreibung'] ); 
    if ( isset($_POST['kategorie'])) $kategorie  = $_POST['kategorie']; else $kategorie = array();
    // ...sind die id's       $kategorie = explode( ";", sanitize_text_field( implode( ";", $_POST['kategorie']) ) )
    for ( $i = 0; $i < count( $kategorie ); $i++ )
      $kategorie[$i] = (int) sanitize_key($kategorie[$i]);
    // Prüfe auf doppelten Eintrag und Datum...
  	$res = $wpdb->get_results('SELECT bericht FROM '.$wpdb->prefix.'zahlungsplan_bericht WHERE bericht="'.$bericht_name.'" LIMIT 0,1',ARRAY_A);
  	if ( !empty($res) )
    {
      $message = "Dieser Bericht ist bereits vorhanden!";
  	} 
    elseif ( strtotime($zeitraumvon) > strtotime($zeitraumbis) )
    {
      $message = "Zeitraum von ist größer als Zeitraum bis!";
  	}    
    elseif ( $zeitraumvon == "0000-00-00" && $zeitraumbis != "0000-00-00" )
    {
      $message = "Zeitraum 'gesamt' muss für beide Datumsangaben gewählt sein!";
  	}  
    elseif ( $zeitraumbis == "0000-00-00" && $zeitraumvon != "0000-00-00" )
    {
      $message = "Zeitraum 'gesamt' muss für beide Datumsangaben gewählt sein!";
	  }  
    elseif ( count( $kategorie ) == 0 )
    {
      $message = "Eine Kategorie muss gewählt werden!";
  	}   
    else 
    {
      if ( strlen($bericht_name) > 150 ) $bericht_name = substr($bericht_name, 0, 150 );
      $message = "";
      /* Array der parameter erzeugen... */
      $bericht_abfrage = base64_encode(serialize(array(
      'mandant'=>$mandant,
      'zeitraumvon'=>$zeitraumvon,
      'zeitraumbis'=>$zeitraumbis,
      'art'=>$art,
      'ea'=>$ea,
      'kategorielist'=>implode(',',$kategorie)
      )));
      // Insert in DB ...
      $wpdb->insert($wpdb->prefix.'zahlungsplan_bericht',
          esc_sql(array(
          'bericht'=>$bericht_name,
          'beschreibung'=>$beschreibung, 
          'inhalt'=>$wpdb->_real_escape($bericht_abfrage),         
          'stac'=>$status))
          );
          
      if ( isset($_POST['runTest']) )
      {
        zp_updateDB( $id, $bericht_name, $beschreibung, $zeitraumvon, $zeitraumbis, $kategorie, $mandant, $art, $ea, $status );
        // Create post object
        $my_post = array(
          'post_title'    => wp_strip_all_tags( 'Zahlungsplan Vorschau Bericht : '.str_replace(" ", "-", trim($_POST['bericht'])) ),
          'post_content'  =>  '<small><font color="#FF0000">Es wurde dieser neue Beitrag angelegt, um das Ergebnis darzustellen.
                              Sie können den Beitrag bearbeiten und ggf. veröffentlichen, oder ihn wieder löschen.
                              Hier nun das Ergebnis : </font></small>
                              <h>
                              [zahlungsplaner '.str_replace(" ", "-", trim($_POST['bericht'])).']',
          'post_status'   => 'draft',
          'post_author'   => get_current_user_id()
        );
        
        $post_id = wp_insert_post( $my_post );
        $host = $_SERVER['HTTP_HOST'];
        $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'wp-admin/?p='.$post_id.'&preview=true';  
        header("Location: http://$host$uri/$extra");
      }
      else  header("Location:".admin_url('admin.php?page=bericht_list_bestand&last_message=1'));
    } 
  }  

?>

<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Bericht</title>
  </head>
  <div style="background-color: #d6d6d6; font-family:courier;">
  <fieldset style="width: 99%; border: 0px solid #F7F7F7; padding: 10px 10px;">
    <legend>
      <b>Bericht anlegen</b>
    </legend>
    <form name="editbestand" id="editbestand" method="post">
      <p>Bezeichnung&nbsp; <input name="bericht" value="" "placeholder="Kategorie"
            size="150"
            maxlength="150"
            type="text"
            required="required">
      </p>
      <table style="width: 456px; height: 150px;" border=0">
        <tr>
          <td style="vertical-align: top; background-color: #d6d6d6; width: 100px">Beschreibung</td>
          <td style="width: 477px;"><textarea name="beschreibung" cols="41" rows="10" wrap="soft"></textarea></td>
        </tr>
      </table>
      <p>Zahlungsplan Mandant: 
        <select name="mandant" list="Mandant">
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
                echo '<option value="'.$id->mandant_id.'">'.esc_attr(sanitize_text_field(get_userdata($id->mandant_id)->display_name)).'</option>';
              }
            }
          ?>
          &nbsp;
        </select>
      </p>
      <p>Zeitraum von:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <select name="zeitraumvon" list="Zeitraum von">
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
                echo '<option value="'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->start)))).'">'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->start)))).'</option>';
              }
            }
          ?>
        &nbsp;
        </select>
        bis:&nbsp;
        <select name="zeitraumbis" list="Zeitraum bis">
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
                echo '<option value="'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->ende)))).'">'.strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$id->ende)))).'</option>';
              }
            }
          ?>
        &nbsp;
        </select>
      </p>    
      <p>Berichtsart:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <select name="art" list="Art">
          <option value="5" >Gesamtaufstellung nach Datum</option>
          <option value="4" >Gesamtaufstellung nach Datum mit CSV-Download</option>  
          <option value="6" >Gesamtaufstellung nach Datum monatlich saldiert</option>
          <option value="7" >Gesamtaufstellung Grafik</option>
          <option value="2" >Gesamtaufstellung tabellarisch</option>
          <option value="1" >nur Summe</option>
          <option value="3" >Summe je Kategorie</option>      
          <option value="8" >Summe je Kategorie Grafik</option>        
            &nbsp;
          </select>
      <p>      
      <p>Berücksichtigen:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <select name="ea" list="Ea">
            <option value="1" >Einnahmen und Ausgaben</option>
            <option value="2" >Einnahmen</option>
            <option value="3" >Ausgaben</option>                                
            &nbsp;
          </select>
      <p>     
      <fieldset>
        <table style="width:99%;" border=0">
          <td style="width:6%; text-align:left">Kategorien:</td>
              <?php
              global $wpdb;
              $kategorieId = 0;
              $col = 0;
            
              echo '<fieldset>
                <td style="width:9%; text-align:left"><label>
                    <input type="checkbox" name="kategorie[]" value="-1" checked';
              echo '>Alle Kategorien</label></td>';
              //$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie'.' WHERE stac=1 ORDER BY kategorie', $zpph )); 
              $results = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'zahlungsplan_kategorie'.' WHERE stac=1 ORDER BY kategorie' ); 
              if (!empty($results)) 
              {
                foreach ($results as $id)
                {  
                  echo '<fieldset>
                    <td style="width:9%; text-align:left"><label>
                        <input type="checkbox" name="kategorie[]" value="'.$id->id.'"';
                  echo '>'.esc_attr( sanitize_text_field($id->kategorie) ).'</label></td>';
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
      <p>Aktiv&nbsp;<input name="status" value="1" type="checkbox" checked></p>
      <table style="width:99%;" border=0">
        <tr>
          <td style="width:4%; text-align:left"><input class="button-primary" style="cursor: pointer;
                "type="submit" name="updateSubmit" value="Speichern"></td>
          <td style="width:2%; text-align:left"><input class="button-primary" style="cursor: pointer;
                "type="submit" name="runTest" value="Speichern und Beitrag zur Vorschau erzeugen"></td>
          <td style="width:24%; text-align:left"></td>
          <td style="width:24%; text-align:left"></td>          
        </tr>
        <tr>
          <td style="width:4%; text-align:left"></td>
          <td style="width:2%; text-align:left"><small>Es wird jeweils ein neuer Beitrag im Status "Entwurf" erzeugt und auf seine Vorschau weitergeleitet. Dabei wird das Ergebnis des Reports ausgegeben.</small></td>
          <td style="width:24%; text-align:left"></td>
          <td style="width:24%; text-align:left"></td>
        </tr>        
      </table> 
    </form>
  </div>
</html>


<?php
  if ( $message != "" ) 
  {
    echo "<h4>$message</h4>";
  } 	 
?>

