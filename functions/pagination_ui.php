<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
	
	global $zp_pagenum;

	echo  "Seite $zp_pagenum von ".ceil( $entrys/$lcount ).", $entrys Einträge";
	
	echo '<table style="width:168px; border=0">';
		
	if ( $zp_pagenum > 1 )
	{
		echo '<td height="40" style="width:42px; text-align:center; name="first" id="first" method="post">
				<form name="first" id="first" method="post">
				<input class="button-primary" style="cursor: pointer;"
					type="submit" name="firstpage" value="<<"></td>';
		echo '<td height="40" style="width:42px; text-align:center; name="prev" id="prev" method="post">
				<form name="prev" id="prev" method="post">
				<input class="button-primary" style="cursor: pointer;"
					type="submit" name="prevpage" value="<"></td>';
	}
	else echo '<td height="40" style="width:42px;"></td><td height="40" style="width:42px;"></td>';

	if ( $entrys > ( $zp_pagenum * $lcount ) )
	{
		echo '<td height="40" style="width:42px; text-align:center; name="next" id="next" method="post">
				<form name="next" id="next" method="post">
				<input class="button-primary" style="cursor: pointer;"
					type="submit" name="nextpage" value=">"></td>';
		echo '<td height="40" style="width:42px; text-align:center; name="last" id="last" method="post">
				<form name="last" id="last" method="post">
				<input class="button-primary" style="cursor: pointer;"
					type="submit" name="lastpage" value=">>"></td>';
	}		
	else echo '<td height="40" style="width:42px;"></td><td height="40" style="width:42px;"></td>';
	echo '</tr>
		</table>
		<br />';
?>