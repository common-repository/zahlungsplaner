<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
	
	$plugin_data = get_plugin_data( dirname( __FILE__ , 2 )."/zahlungsplaner.php", true, true );
?>

<div style="margin-top: 10px">
<table style="float:left; ">
<tr>
<td style="float:left;">
	<strong>Zahlungsplaner Version <?php echo " ".$plugin_data['Version']; ?></strong>
</td>
</tr>
</table>
<table style="float:right; ">
<tr>
<td style="float:right;" width="40";>
  	<a href="http://blog.4bothmann.de/spende-senden" target="_blank">
		<img id="img" title="Spenden per PayPal" src="<?php echo plugins_url('zahlungsplaner/images/spenden.png'); ?>" height="20" width="20">
	</a>
</td>

<td style="float:right;">
	<a class="pz_header_link" style="margin-left:8px;" target="_blank" href="http://blog.4bothmann.de/spende-senden">Donate</a>
</td>
<td style="float:right;">
	<a class="zp_header_link" style="margin-left:8px;" target="_blank" href="http://blog.4bothmann.de/technik/webdesign/wordpress/plugins/">Über</a>
</td>
<td style="float:right;">
	<a class="zp_header_link" target="_blank" href="http://blog.4bothmann.de">Jörg Bothmann</a>
</td>

</tr>
</table>
</div>
<div style="clear: both"></div>


