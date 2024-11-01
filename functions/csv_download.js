<script type="text/javascript">  	
	function ExportToCsv(filename) {
		var csv = buffer;
		if (navigator.userAgent.search("Trident") >= 0) {
			window.CsvExpFrame.document.open("text/html", "replace");
			window.CsvExpFrame.document.write(csv);
			window.CsvExpFrame.document.close();
			window.CsvExpFrame.focus();
			window.CsvExpFrame.document.execCommand('SaveAs', true, filename);
		} else {
			var uri = "data:text/csv;charset=utf-8," + escape(csv);
			link = document.createElement('a');
			link.setAttribute('href', uri);
			link.setAttribute('download', filename);
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		}
	};
</script>  
