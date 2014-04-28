<?php

require_once LIB_DIR . DS . 'Parsedown.php';

class Document
{

    protected $_fileName = null;

    public function __construct($file)
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new Exception($file . ' is not a readable file');
        }
        $this->_fileName = $file;
    }

    public function getContents()
    {
        return file_get_contents($this->_fileName);
    }

    public function asHtml()
    {
        $contents = $this->getContents();
        if (preg_match('/\.md$/', $this->_fileName)) {
            $parser = new Parsedown();
            $contents = $parser->text($contents);
        }
        return $contents;
    }
}

