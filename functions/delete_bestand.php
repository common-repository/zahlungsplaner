<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*****************************************************************************************
/* Edit Zahlungsplaner Bestand
/****************************************************************************************/

	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'zahlungsplan-delete' ) ) 
	{
 	   // This nonce is not valid.
 	   die( 'Security check at delete_bestand.php' ); 
	} 
	
  $id = (int) sanitize_key( $_GET['id'] );
  global $wpdb;
  
  if(isset($_POST) && isset($_POST['updateSubmit']))
  {
    // Update der DB ...
    $wpdb->delete($wpdb->prefix.'zahlungsplan',
        esc_sql(array('id'=>$id)));
        
        header("Location:".admin_url('admin.php?page=zahlungsplan-bestand-manage&last_message=2'));
	/*
		In Array einlesen, für spätere Erweiterungen schon jetzt so gewählt.
	*/
  } 
  $id = esc_sql( $id );
  $res = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'zahlungsplan WHERE id='.$id.' LIMIT 0,1',ARRAY_A);
  if (empty($res))
  {
	  return;
  } 

?>

<html>
  <div style="background-color: #d6d6d6; font-family:courier;">
  <fieldset style="width: 99%; border: 0px solid #F7F7F7; padding: 10px 10px;">
    <legend>
      <b>Zahlungsplan löschen</b>
    </legend>
    <form name="editbestand" id="editbestand" method="post">
      <p><input name="Mandant" disabled="disabled" value="<?php echo esc_attr(sanitize_key($res[0]['mandant_id']));?>" type="hidden"></p>
      <p><input name="id" value="<?php echo $id;?>" disabled="disabled" type="hidden"></p>
      <p>Mandant&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="Mandant" value="<?php echo esc_attr(sanitize_text_field(get_userdata($res[0]['mandant_id'])->display_name));?>" disabled="disabled"
            readonly="readonly"
            size="45"
            type="text">
      </p>
      <p>Bezeichnung&nbsp; <input name="Bezeichnung" disabled="disabled" value="<?php echo esc_attr(sanitize_text_field($res[0]['bezeichnung']));?>" "placeholder="Bezeichnung"
            size="45"
            maxlength="45"
            type="text"
            required="required">
      </p>
      <table style="width: 456px; height: 150px;" border=0">
        <tr>
          <td style="vertical-align: top; background-color: #d6d6d6; width: 100px">Beschreibung</td>
          <td style="width: 477px;"><textarea name="Beschreibung" disabled="disabled" cols="41" rows="10" wrap="soft"><?php echo esc_attr(sanitize_text_field($res[0]['beschreibung']));?></textarea></td>
        </tr>
      </table>
      <p>Kategorie&nbsp;&nbsp;&nbsp;
          <select name="Kategorie" disabled="disabled" list="Kategorie">
            <?php
              global $wpdb;
              $zpph = array();
              $kategorieId = 0;
              $results = $wpdb->get_results( $wpdb->prepare( 'SELECT id, kategorie FROM '.$wpdb->prefix.'zahlungsplan_kategorie WHERE stac=1 ORDER BY kategorie', $zpph )); 
              if (!empty($results)) 
              {
                foreach ($results as $id)
                {
                  echo '<option value="'.$id->id.'"'.($res[0]['kategorie_id']==$id->id?" selected":'').'>'.esc_attr(sanitize_text_field($id->kategorie)).'</option>';
                }
              }
            ?>
            &nbsp;
          </select>
      <p> 
      <p>Art&nbsp;
          <select name="Art" disabled="disabled" list="Art">
            <option value="1" <?php echo $res[0]['ea']=='1'?" selected":'';?>>Einnahme</option>
            <option value="0" <?php echo $res[0]['ea']=='0'?" selected":'';?>>Ausgabe</option>
            &nbsp;
          </select>
      </p>
      <p>Umbuchung&nbsp;<input name="Umbuchung" disabled="disabled" value="1" type="checkbox" <?php echo $res[0]['umbuchung']=='1'?" checked":'';?>></p>
      <p>Start <input name="Beginn" disabled="disabled" value="<?php echo strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$res[0]['start']))));?>" size="10">
      </p>
      <p>Ende&nbsp; <input name="Ende" disabled="disabled" value="<?php echo strftime("%d.%m.%Y",strtotime(esc_attr(sanitize_option( "date_format",$res[0]['ende']))));?>" size="10">
      </p>
      <p>Turnus alle
          <select name="Turnus" disabled="disabled" list="turnus">
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
          <select name="Anzahl" disabled="disabled" list="Anzahl">
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
      <p>Betrag <input name="Betrag" disabled="disabled" value="<?php echo number_format(esc_attr(sanitize_text_field($res[0]['betrag'])) , 2, ",", ".");?>" size="16" pattern="^\d{0,9}([\.,]\d{2})?$"
            required="required"
            autocomplete="off"
            type="text"><br>
      </p>
      <p>Aktiv&nbsp;<input name="Status" disabled="disabled" value="1" type="checkbox" <?php echo $res[0]['stac']=='1'?" checked":'';?>></p>
      <input class="button-primary" style="cursor: pointer;
              "type="submit" name="updateSubmit" value="Löschen">
    </form>
  </div>
</html>

