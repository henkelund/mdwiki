<?php

require_once APP_DIR . DS . 'Document.php';

class Node
{

    public $name = null;

    protected $_document = null;

    protected $_parent = null;

    protected $_children = array();

    public function __construct($file)
    {
        $this->name = preg_replace('/\.[a-z]{2,4}$/', '', basename($file));
        if (is_dir($file)) {
            $children = scandir($file);
            foreach ($children as $child) {
                $childFile = $file . DS . $child;
                if (strpos($child, '.') === 0 || !is_readable($childFile)) {
                    continue;
                }
                $childNode = new self($childFile);
                $childNode->_parent = $this;
                $this->_children[] = $childNode;
            }
        } else {
            $this->_document = new Document($file);
        }
        $this->_mergeChildren();
    }

    protected function _mergeChildren()
    {
        $dirs = array();
        $files = array();
        foreach ($this->_children as $i => $child) {
            if (is_null($child->_document)) {
                $dirs[$child->name] = $child;
            } else {
                $files[$i] = $child;
            }
        }
        foreach ($files as $i => $file) {
            if (isset($dirs[$file->name])) {
                $dirs[$file->name]->_document = $file->_document;
                unset($this->_children[$i]);
            }
        }
    }

    public function hasChildren()
    {
        return !empty($this->_children);
    }

    public function getChildren()
    {
        return $this->_children;
    }

    public function getPath($escape = false)
    {
        $segment = $escape ? urlencode($this->name) : $this->name;
        return $this->_parent
            ? sprintf('%s/%s', $this->_parent->getPath($escape), $segment)
            : '';
    }

    public function asHtml($level = 1)
    {
        ob_start();
        include VIEW_DIR . DS . 'node.phtml';
        return ob_get_clean();
    }

    public function getDocument()
    {
        return $this->_document;
    }
}

