<?php

require_once('include/BaseExport.inc');

class ExportDisplay
{

    public $export;

    public function __construct($export)
    {
        $this->export = $export;
    }

    public function doDraw()
    {
        $headers = $this->export->getHeaders();

        foreach ($headers as $name => $value) {
            header("$name: $value");
        }

        header('Content-Disposition: inline; filename="' . $this->export->getFilename() . '"');

        print($this->export->getOutput());
    }

    public function doDownload()
    {
        $headers = $this->export->getHeaders();

        foreach ($headers as $name => $value) {
            header("$name: $value");
        }

        header('Content-Disposition: attachment; filename="' . $this->export->getFilename() . '"');

        print($this->export->getOutput());
    }

    public function doEmail($email)
    {
        $body = '';

        $headers = $this->export->getHeaders();

        foreach ($headers as $name => $value) {
            $body .= "$name: $value\n";
        }
        $body .= 'Content-Disposition: inline; filename="' . $this->export->getFilename() . "\"\n";

        $body .= "\n";

        $body .= $this->export->getOutput();
    }
}
