<?php

class BaseExport
{
    var $output = '';
    var $outputBuilt = false;

    var $headers = [];
    var $headersBuilt = false;

    var $displayFilterClosed = 'none';
    var $displayFilterPriority = 'all';
    var $displayFilterAging = 'all';
    var $displayShowEstimate = 'n';
    var $displayShowInactive = 'n';

    function buildOutput()
    {
        $this->output = '';
        $this->outputBuilt = true;
    }

    function getOutput()
    {
        if (!$this->outputBuilt) {
            $this->buildOutput();
        }

        return $this->output;
    }

    function buildHeaders()
    {
        $this->headers = [];
        $this->headersBuilt = true;
    }

    function getHeaders()
    {
        if (!$this->headersBuilt) {
            $this->buildHeaders();
        }

        return $this->headers;
    }

    function setFilterClosed($displayFilterClosed)
    {
        $this->displayFilterClosed = $displayFilterClosed;
    }

    function setFilterPriority($displayFilterPriority)
    {
        $this->displayFilterPriority = $displayFilterPriority;
    }

    function setFilterAging($displayFilterAging)
    {
        $this->displayFilterAging = $displayFilterAging;
    }

    function setShowInactive($displayShowInactive)
    {
        $this->displayShowInactive = $displayShowInactive;
    }

    function setShowEstimate($displayShowEstimate)
    {
        $this->displayShowEstimate = $displayShowEstimate;
    }
}
