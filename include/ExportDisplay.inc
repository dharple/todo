<?php

require_once('include/BaseExport.inc');

class ExportDisplay {

	var $export;

	function ExportDisplay($export) {
		$this->export = $export;
	}

	function doDraw() {
		$headers = $this->export->getHeaders();

		foreach($headers as $name => $value) {
			header("$name: $value");
		}

		header("Content-Disposition: inline; filename=\"" . $this->export->getFilename() . "\"");

		print($this->export->getOutput());
	}

	function doDownload() {
		$headers = $this->export->getHeaders();

		foreach($headers as $name => $value) {
			header("$name: $value");
		}

		header("Content-Disposition: attachment; filename=\"" . $this->export->getFilename() . "\"");

		print($this->export->getOutput());
	}

	function doEmail($email) {
		$body = '';

		$headers = $this->export->getHeaders();

		foreach($headers as $name => $value) {
			$body .= "$name: $value\n";
		}
		$body .= "Content-Disposition: inline; filename=\"" . $this->export->getFilename() . "\"\n";

		$body .= "\n";

		$body .= $this->export->getOutput();
	}

}
