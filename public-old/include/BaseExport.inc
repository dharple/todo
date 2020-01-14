<?php

class BaseExport {
	var $output = '';
	var $outputBuilt = FALSE;

	var $headers = array(); 
	var $headersBuilt = FALSE;

	var $displayFilterClosed = 'none';
	var $displayFilterPriority = 'all';
	var $displayFilterAging = 'all';
	var $displayShowEstimate = 'n';
	var $displayShowInactive = 'n';

	function buildOutput() {
		$this->output = '';
		$this->outputBuilt = TRUE;
	}

	function getOutput() {
		if(!$this->outputBuilt)
			$this->buildOutput();

		return $this->output;
	}

	function buildHeaders() {
		$this->headers = array(); 
		$this->headersBuilt = TRUE;
	}

	function getHeaders() {
		if(!$this->headersBuilt)
			$this->buildHeaders();

		return $this->headers;
	}

	function setFilterClosed($displayFilterClosed) {
		$this->displayFilterClosed = $displayFilterClosed;
	}

	function setFilterPriority($displayFilterPriority) {
		$this->displayFilterPriority = $displayFilterPriority;
	}

	function setFilterAging($displayFilterAging) {
		$this->displayFilterAging = $displayFilterAging;
	}

	function setShowInactive($displayShowInactive) {
		$this->displayShowInactive = $displayShowInactive;
	}

	function setShowEstimate($displayShowEstimate) {
		$this->displayShowEstimate = $displayShowEstimate;
	}

}

?>
