<?php

class BaseExport
{
    public $output = '';
    public $outputBuilt = false;

    public $headers = [];
    public $headersBuilt = false;

    public $displayFilterClosed = 'none';
    public $displayFilterPriority = 'all';
    public $displayFilterAging = 'all';
    public $displayShowEstimate = 'n';
    public $displayShowInactive = 'n';

    public function buildOutput()
    {
        $this->output = '';
        $this->outputBuilt = true;
    }

    public function getOutput()
    {
        if (!$this->outputBuilt) {
            $this->buildOutput();
        }

        return $this->output;
    }

    public function buildHeaders()
    {
        $this->headers = [];
        $this->headersBuilt = true;
    }

    public function getHeaders()
    {
        if (!$this->headersBuilt) {
            $this->buildHeaders();
        }

        return $this->headers;
    }

    public function setFilterClosed($displayFilterClosed)
    {
        $this->displayFilterClosed = $displayFilterClosed;
    }

    public function setFilterPriority($displayFilterPriority)
    {
        $this->displayFilterPriority = $displayFilterPriority;
    }

    public function setFilterAging($displayFilterAging)
    {
        $this->displayFilterAging = $displayFilterAging;
    }

    public function setShowInactive($displayShowInactive)
    {
        $this->displayShowInactive = $displayShowInactive;
    }

    public function setShowEstimate($displayShowEstimate)
    {
        $this->displayShowEstimate = $displayShowEstimate;
    }
}
