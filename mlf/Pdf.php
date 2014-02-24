<?php
/**
 * Pdf class for printing functionality.
 *
 * @author Thanasis <hello@thanpa.com>
 */
class Pdf
{
    /**
     * The path to the PDF file.
     *
     * @var string
     */
    private $_path = '';
    /**
     * The content to the PDF file.
     *
     * @var string
     */
    private $_content = '';
    /**
     * Costructs the PDF instance.
     *
     * <p>Locates the path to use.
     * <p>Executes the rasterize of the contents.
     *
     * @param string $html
     * @return null
     * @throws Exception In case the exit code is not 0.
     */
    public function __construct($html)
    {
        $tmp = sys_get_temp_dir();
        $source = "{$tmp}/download.html";
        $this->_path = "{$tmp}/download.pdf";
        $this->_virginFiles(array($source, $this->_path));
        file_put_contents($source, $html);
        $output = array();
        $code = 0;
        exec("phantomjs ../vendor/rasterize.js {$source} {$this->_path} 210mm*297mm", $output, $code);
        if ($code !== 0) {
            throw new Exception("Exit code of phantomjs is '{$code}'");
        }
    }
    /**
     * Returns the binary content of the created PDF file.
     *
     * @return string
     */
    public function getContent()
    {
        if (empty($this->_content)) {
            $this->_content = file_get_contents($this->_path);
        }
        return $this->_content;
    }
    /**
     * Returns the path of the PDF file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }
    /**
     * Returns the binary content of the created PDF file.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }
    /**
     * Makes virgin (unlinks/empties) a list of files by the provided paths.
     *
     * @param array $files
     * @return boolean
     * @throws Exception In case the list of paths is empty.
     */
    private function _virginFiles(array $files)
    {
        if (empty($files)) {
            throw new Exception('Files must be an non empty array');
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
}