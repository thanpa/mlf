<?php
class Pdf
{
    private $_path = '';
    private $_content = '';
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
    public function getContent()
    {
        if (empty($this->_content)) {
            $this->_content = file_get_contents($this->_path);
        }
        return $this->_content;
    }
    public function getPath()
    {
        return $this->_path;
    }
    public function __toString()
    {
        return $this->getContent();
    }
    private function _virginFiles($files)
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