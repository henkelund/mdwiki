<?php

require_once APP_DIR . DS . 'Node.php';
require_once APP_DIR . DS . 'View.php';
require_once APP_DIR . DS . 'Search.php';

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
        if (isset($_GET['q'])) {

            $search = new Search();
            return $search->getResultHtml($_GET['q']);

        } else {

            $path = rtrim($path, '/');
            $currentNode = null;
            if (isset($this->_pathIndex[$path])) {
                $currentNode = $this->_pathIndex[$path];
                $currentNode->isCurrent(true);
            }
            return View::render('wiki.phtml', array(
                'navigationHtml' => $this->_docTree->getChildLinksHtml(),
                'currentNode'    => $currentNode
            ));

        }
    }
}

