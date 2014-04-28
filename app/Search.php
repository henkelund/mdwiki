<?php

require_once APP_DIR . DS . 'Node.php';

class Search
{

    public function query($text, $limit = 5)
    {
        $tokens = array_keys($this->_tokenize($text));
        if (empty($tokens)) {
            return false;
        }
        $index = $this->_loadIndex();
        if (!is_array($index)) {
            return false;
        }
        $hits = array();
        foreach ($tokens as $token) {
            if (isset($index['tokens'][$token])) {
                foreach ($index['tokens'][$token] as $hit) {
                    if (!isset($hits[$hit[0]])) {
                        $hits[$hit[0]] = array('weight' => $hit[1]);
                    } else {
                        $hits[$hit[0]]['weight'] += $hit[1];
                    }
                }
                if (--$limit <= 0) {
                    break;
                }
            }
        }
        $weighLiteral = count($tokens) > 0;
        $pattern = sprintf('/%s/ui', preg_quote($text, '/'));
        foreach ($hits as $id => &$hit) {
            $hit['document'] = $index['documents'][$id];
            $hit['literal'] = $weighLiteral
                ? (boolean) preg_match($pattern, $hit['document']['text'])
                : false;
        }
        usort($hits, array($this, '_sortResult'));
        $result = array();
        foreach ($hits as $item) {
            $result[$item['document']['path']] = $item['document']['name'];
        }
        $suggestions = array();
        if (empty($result)) {
            $candidates = array_keys($index['tokens']);
            foreach ($tokens as $token) {
                $pattern = sprintf('/^%s/', preg_quote($token, '/'));
                $suggestion = false;
                $distance = 10;
                foreach ($candidates as $candidate) {
                    if (preg_match($pattern, $candidate)) {
                        $suggestion = $candidate;
                        break;
                    } else {
                        $dist = levenshtein($candidate, $token);
                        if ($dist < $distance) {
                            $suggestion = $candidate;
                            $distance = $dist;
                        }
                    }
                }
                if ($suggestion) {
                    $suggestions[] = $suggestion;
                }
            }
        }
        return array('result' => $result, 'suggestions' => $suggestions);
    }

    protected function _sortResult(&$lhs, &$rhs)
    {
        if ($lhs['literal']) {
            if (!$rhs['literal']) {
                return -100;
            }
        } else if ($rhs['literal']) {
            return 100;
        }
        return $rhs['weight'] - $lhs['weight'];
    }

    public function index($baseDir)
    {
        if (!is_dir($baseDir) || !is_readable($baseDir)) {
            throw new Exception($baseDir . ' is not a readable direcory');
        }
        $indexFile = $this->_getIndexFileName();
        $data = sprintf(
            '<?php return %s;',
            var_export($this->_buildIndex($baseDir), true)
        );
        if (false === file_put_contents($indexFile, $data)) {
            throw new Exception('Can not write to ' . $indexFile);
        }
    }

    protected function _getIndexFileName()
    {
        return VAR_DIR . DS . 'search.idx';
    }

    protected function _loadIndex()
    {
        $indexFile = $this->_getIndexFileName();
        if (is_file($indexFile) && is_readable($indexFile)) {
            return include $this->_getIndexFileName();
        }
        return false;
    }

    protected function _buildIndex($baseDir)
    {
        $index = array('documents' => array(), 'tokens' => array());
        $nodes = $this->_flatten(new Node($baseDir));
        foreach ($nodes as $node) {
            $document = $node->getDocument();
            if (!$document) {
                continue;
            }
            $text = html_entity_decode(
                strip_tags(
                    $node->name . ' ' .
                    $node->getDocument()->asHtml()
                )
            );
            $id = array_push($index['documents'], array(
                'name' => $node->name,
                'path' => $node->getPath(true),
                'text' => $text
            )) - 1;
            foreach ($this->_tokenize($text) as $token => $weight) {
                if (strlen($token) < 2) {
                    continue;
                }
                if (!isset($index['tokens'][$token])) {
                    $index['tokens'][$token] = array(
                        array($id, $weight)
                    );
                } else {
                    $index['tokens'][$token][] = array($id, $weight);
                }
            }
        }
        return $index;
    }

    protected function _flatten(Node $tree)
    {
        $nodes = array($tree);
        foreach ($tree->getChildren() as $child) {
            $nodes = array_merge($nodes, $this->_flatten($child));
        }
        return $nodes;
    }

    protected function _tokenize($text)
    {
        $tokens = array();
        $words = preg_split(
            '/[\t\r\n ,\.;:\'"\!@#\$%\^&\*_\-\=\+`~\/\\\\\(\)\[\]\{\}\<\>]/',
            $text,
            null,
            PREG_SPLIT_NO_EMPTY
        );
        foreach ($words as $word) {
            $lower = mb_strtolower($word, 'UTF-8');
            if (!isset($tokens[$lower])) {
                $tokens[$lower] = 1;
            } else {
                ++$tokens[$lower];
            }
        }
        return $tokens;
    }
}

