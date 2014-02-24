<?php
/**
 * Zip class.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Zip extends ZipArchive
{
    /**
     * The ZipArchive instance.
     *
     * @var ZipArchive
     */
    private $_zip;
    /**
     * The filename of the ediding zip.
     *
     * @var string
     */
    private $_filename;
    /**
     * Constructs the zip.
     *
     * @return null
     */
    public function __construct()
    {
        $this->_zip = new ZipArchive();
        $this->_filename = tempnam(sys_get_temp_dir(), '[ZIP]');
    }
    /**
     * Adds a file in the zip.
     *
     * @param string $file
     * @param string $destination
     * @throws Exception In case the zip can not be opened.
     */
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
    /**
     * Returns the path of the zip.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_filename;
    }
}
