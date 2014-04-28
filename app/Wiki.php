<?php

require_once APP_DIR . DS . 'Node.php';

class Wiki
{

    protected $_docTree = array();

    protected $_pathIndex = array();

    public function __construct($wikiDir)
    {
        if (!is_dir($wikiDir) || !is_readable($wikiDir)) {
            throw new Exception($wikiDir . ' is not a readable direcory');
        }
        $this->_docTree = new Node($wikiDir);
        $this->_buildPathIndex($this->_docTree);
    }

    protected function _buildPathIndex(Node $node)
    {
        $this->_pathIndex[$node->getPath(true)] = $node;
        foreach ($node->getChildren() as $child) {
            $this->_buildPathIndex($child);
        }
    }

    public function render($path = '')
    {
        $navigationHtml = $this->_docTree->asHtml();
        $currentNode = null;
        if (isset($this->_pathIndex[$path])) {
            $currentNode = $this->_pathIndex[$path];
        }
        ob_start();
        include VIEW_DIR . DS . 'wiki.phtml';
        return ob_get_clean();
    }
}

