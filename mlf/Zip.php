<?php
class Zip extends ZipArchive
{
    private $_zip;
    private $_filename;
    public function __construct()
    {
        $this->_zip = new ZipArchive();
        $this->_filename = tempnam(sys_get_temp_dir(), '[ZIP]');
    }
    public function add($file, $destination = null)
    {
        if (empty($destination)) {
            $destination = strrchr($file, '/');
        }
        if ($this->_zip->open($this->_filename, ZipArchive::CREATE) !== true) {
            throw new Exception("Can not open zip file '{$this->_filename}'.");
        }
        $this->_zip->addFile($file, $destination);
        $this->_zip->close();
    }
    public function getPath()
    {
        return $this->_filename;
    }
}
