<?php

require_once APP_DIR . DS . 'Document.php';
require_once APP_DIR . DS . 'View.php';

class Node
{

    protected $_name = null;

    protected $_document = null;

    protected $_parent = null;

    protected $_children = array();

    protected $_isCurrent = false;

    public function __construct($file)
    {
        $this->_name = preg_replace('/\.[a-z]{2,4}$/', '', basename($file));
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
                $dirs[$child->getName()] = $child;
            } else {
                $files[$i] = $child;
            }
        }
        foreach ($files as $i => $file) {
            if (isset($dirs[$file->getName()])) {
                $dirs[$file->getName()]->_document = $file->_document;
                unset($this->_children[$i]);
            }
        }
    }

    public function getName()
    {
        return $this->_name;
    }

    public function hasChildren()
    {
        return !empty($this->_children);
    }

    public function getChildren()
    {
        return $this->_children;
    }

    public function hasParent()
    {
        return !is_null($this->_parent);
    }

    public function getParent()
    {
        return $this->_parent;
    }

    public function getPath($escape = false)
    {
        $segment = $escape ? urlencode($this->getName()) : $this->getName();
        return $this->_parent
            ? sprintf('%s/%s', $this->_parent->getPath($escape), $segment)
            : '';
    }

    public function getChildLinksHtml($level = 1)
    {
        return View::render('node.phtml', array(
            'node'  => $this,
            'level' => $level
        ));
    }

    public function getCrumbsHtml()
    {
        $crumbs = array();
        $node = $this;
        while ($node) {
            array_unshift($crumbs, $node);
            $node = $node->getParent();
        }
        return View::render('crumbs.phtml', array(
            'crumbs' => $crumbs
        ));
    }

    public function getDocument()
    {
        return $this->_document;
    }

    public function isCurrent($flag = null)
    {
        if (!is_null($flag)) {
            $this->_isCurrent = !!$flag;
        }
        return $this->_isCurrent;
    }
}

