<?php

require_once APP_DIR . DS . 'Node.php';
require_once LIB_DIR . DS . 'PorterStemmer.php';
require_once APP_DIR . DS . 'View.php';

class Search
{

    protected static $_nonLetters = '\t\r\n ,\.;:"\!@#\$%\^&\?\*_\-\=\+`~\/\\\\\(\)\[\]\{\}\<\>';

    protected static $_stopwords = array(
        'a\'s', 'able', 'about', 'above', 'according', 'accordingly', 'across',
        'actually', 'after', 'afterwards', 'again', 'against', 'ain\'t', 'all',
        'allow', 'allows', 'almost', 'alone', 'along', 'already', 'also',
        'although', 'always', 'am', 'among', 'amongst', 'an', 'and', 'another',
        'any', 'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways',
        'anywhere', 'apart', 'appear', 'appreciate', 'appropriate', 'are',
        'aren\'t', 'around', 'as', 'aside', 'ask', 'asking', 'associated',
        'at', 'available', 'away', 'awfully', 'be', 'became', 'because',
        'become', 'becomes', 'becoming', 'been', 'before', 'beforehand',
        'behind', 'being', 'believe', 'below', 'beside', 'besides', 'best',
        'better', 'between', 'beyond', 'both', 'brief', 'but', 'by', 'c\'mon',
        'c\'s', 'came', 'can', 'can\'t', 'cannot', 'cant', 'cause', 'causes',
        'certain', 'certainly', 'changes', 'clearly', 'co', 'com', 'come',
        'comes', 'concerning', 'consequently', 'consider', 'considering',
        'contain', 'containing', 'contains', 'corresponding', 'could',
        'couldn\'t', 'course', 'currently', 'definitely', 'described',
        'despite', 'did', 'didn\'t', 'different', 'do', 'does', 'doesn\'t',
        'doing', 'don\'t', 'done', 'down', 'downwards', 'during', 'each',
        'edu', 'eg', 'eight', 'either', 'else', 'elsewhere', 'enough',
        'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'every',
        'everybody', 'everyone', 'everything', 'everywhere', 'ex', 'exactly',
        'example', 'except', 'far', 'few', 'fifth', 'first', 'five',
        'followed', 'following', 'follows', 'for', 'former', 'formerly',
        'forth', 'four', 'from', 'further', 'furthermore', 'get', 'gets',
        'getting', 'given', 'gives', 'go', 'goes', 'going', 'gone', 'got',
        'gotten', 'greetings', 'had', 'hadn\'t', 'happens', 'hardly', 'has',
        'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'s', 'hello',
        'help', 'hence', 'her', 'here', 'here\'s', 'hereafter', 'hereby',
        'herein', 'hereupon', 'hers', 'herself', 'hi', 'him', 'himself', 'his',
        'hither', 'hopefully', 'how', 'howbeit', 'however', 'i\'d', 'i\'ll',
        'i\'m', 'i\'ve', 'ie', 'if', 'ignored', 'immediate', 'in', 'inasmuch',
        'inc', 'indeed', 'indicate', 'indicated', 'indicates', 'inner',
        'insofar', 'instead', 'into', 'inward', 'is', 'isn\'t', 'it', 'it\'d',
        'it\'ll', 'it\'s', 'its', 'itself', 'just', 'keep', 'keeps', 'kept',
        'know', 'known', 'knows', 'last', 'lately', 'later', 'latter',
        'latterly', 'least', 'less', 'lest', 'let', 'let\'s', 'like', 'liked',
        'likely', 'little', 'look', 'looking', 'looks', 'ltd', 'mainly',
        'many', 'may', 'maybe', 'me', 'mean', 'meanwhile', 'merely', 'might',
        'more', 'moreover', 'most', 'mostly', 'much', 'must', 'my', 'myself',
        'name', 'namely', 'nd', 'near', 'nearly', 'necessary', 'need', 'needs',
        'neither', 'never', 'nevertheless', 'new', 'next', 'nine', 'no',
        'nobody', 'non', 'none', 'noone', 'nor', 'normally', 'not', 'nothing',
        'novel', 'now', 'nowhere', 'obviously', 'of', 'off', 'often', 'oh',
        'ok', 'okay', 'old', 'on', 'once', 'one', 'ones', 'only', 'onto', 'or',
        'other', 'others', 'otherwise', 'ought', 'our', 'ours', 'ourselves',
        'out', 'outside', 'over', 'overall', 'own', 'particular',
        'particularly', 'per', 'perhaps', 'placed', 'please', 'plus',
        'possible', 'presumably', 'probably', 'provides', 'que', 'quite', 'qv',
        'rather', 'rd', 're', 'really', 'reasonably', 'regarding',
        'regardless', 'regards', 'relatively', 'respectively', 'right', 'said',
        'same', 'saw', 'say', 'saying', 'says', 'second', 'secondly', 'see',
        'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self',
        'selves', 'sensible', 'sent', 'serious', 'seriously', 'seven',
        'several', 'shall', 'she', 'should', 'shouldn\'t', 'since', 'six',
        'so', 'some', 'somebody', 'somehow', 'someone', 'something',
        'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry',
        'specified', 'specify', 'specifying', 'still', 'sub', 'such', 'sup',
        'sure', 't\'s', 'take', 'taken', 'tell', 'tends', 'th', 'than',
        'thank', 'thanks', 'thanx', 'that', 'that\'s', 'thats', 'the', 'their',
        'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'there\'s',
        'thereafter', 'thereby', 'therefore', 'therein', 'theres', 'thereupon',
        'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve',
        'think', 'third', 'this', 'thorough', 'thoroughly', 'those', 'though',
        'three', 'through', 'throughout', 'thru', 'thus', 'to', 'together',
        'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try',
        'trying', 'twice', 'two', 'un', 'under', 'unfortunately', 'unless',
        'unlikely', 'until', 'unto', 'up', 'upon', 'us', 'use', 'used',
        'useful', 'uses', 'using', 'usually', 'value', 'various', 'very',
        'via', 'viz', 'vs', 'want', 'wants', 'was', 'wasn\'t', 'way', 'we',
        'we\'d', 'we\'ll', 'we\'re', 'we\'ve', 'welcome', 'well', 'went',
        'were', 'weren\'t', 'what', 'what\'s', 'whatever', 'when', 'whence',
        'whenever', 'where', 'where\'s', 'whereafter', 'whereas', 'whereby',
        'wherein', 'whereupon', 'wherever', 'whether', 'which', 'while',
        'whither', 'who', 'who\'s', 'whoever', 'whole', 'whom', 'whose', 'why',
        'will', 'willing', 'wish', 'with', 'within', 'without', 'won\'t',
        'wonder', 'would', 'wouldn\'t', 'yes', 'yet', 'you', 'you\'d',
        'you\'ll', 'you\'re', 'you\'ve', 'your', 'yours', 'yourself',
        'yourselves', 'zero'
    );

    public function getResultHtml($query, $limit = 5)
    {
        $result = $this->query($query, $limit = 5);
        return View::render('search.phtml', array(
            'result' => $result
        ));
    }

    public function query($text, $limit = 5)
    {
        $text = trim($text);
        if (empty($text)) {
            return false;
        }
        $index = $this->_loadIndex();
        if (!is_array($index)) {
            return false;
        }

        $hits = array();
        $pattern = sprintf(
            '/(^|[%s])%s/ui',
            self::$_nonLetters,
            preg_quote($text, '/')
        );

        // Look for literal match in name and strong fields
        foreach ($index['documents'] as $id => &$document) {
            if (preg_match($pattern, $document['name'])) {
                $hits[$id] = array(
                    'weight'  => 200,
                    'excerpt' => $document['name']
                );
                if (--$limit <= 0) {
                    break;
                }
                continue;
            }
            foreach ($document['fields'] as $value => &$tags) {
                if (preg_match($pattern, $value)) {
                    $hits[$id] = array(
                        'weight'  => 200,
                        'excerpt' => $value
                    );
                    --$limit;
                    break;
                }
            }
            if ($limit <= 0) {
                break;
            }
        }

        // Look for literal match in full text
        if ($limit > 0) {
            foreach ($index['documents'] as $id => &$document) {
                if (isset($hits[$id])) {
                    continue;
                }
                if (preg_match($pattern, $document['text'])) {
                    $hits[$id] = array(
                        'weight'  => 100,
                        'excerpt' => ''
                    );
                    if (--$limit <= 0) {
                        break;
                    }
                }
            }
        }

        // Look for tokenized hits
        if ($limit > 0) {
            $tokens = array_keys($this->_tokenize($text));
            $tokenHits = array();
            foreach ($tokens as $token) {
                if (isset($index['tokens'][$token])) {
                    foreach ($index['tokens'][$token] as &$hit) {
                        if (!isset($tokenHits[$hit[0]])) {
                            $tokenHits[$hit[0]] = array(
                                'weight'  => $hit[1],
                                'excerpt' => ''
                            );
                        } else {
                            $tokenHits[$hit[0]]['weight'] += $hit[1];
                        }
                    }
                }
            }
            uasort($tokenHits, array($this, '_sortHits'));
            foreach ($tokenHits as $id => $tokenHit) {
                if (isset($hits[$id])) {
                    $hits[$id]['weight'] += $tokenHit['weight'];
                } else {
                    $hits[$id] = $tokenHit;
                }
                if (--$limit <= 0) {
                    break;
                }
            }
        }

        uasort($hits, array($this, '_sortHits'));
        foreach ($hits as $id => &$hit) {
            $hit['name'] = $index['documents'][$id]['name'];
            $hit['path'] = $index['documents'][$id]['path'];
        }

        return array_values($hits);
    }

    protected function _sortHits(&$lhs, &$rhs)
    {
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
            $html = $node->getDocument()->asHtml();
            $text = html_entity_decode(
                strip_tags(
                    $node->getName() . ' ' . $html
                )
            );
            $id = array_push($index['documents'], array(
                'name'   => $node->getName(),
                'path'   => $node->getPath(true),
                'text'   => $text,
                'fields' => $this->_getFields($html)
            )) - 1;
            foreach ($this->_tokenize($text) as $token => $weight) {
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

    protected function _getFields($text)
    {
        $fields = array();
        if (!function_exists('tidy_repair_string')
                || !function_exists('simplexml_load_string')) {
            return array($fields);
        }
        $xml = tidy_repair_string($text, array( 
            'output-xml'      => true, 
            'input-xml'       => true,
            'input-encoding'  => 'UTF-8',
            'output-encoding' => 'UTF-8'
        ));
        $xmlWrapping = '<?xml version="1.0" encoding="UTF-8"?><root>%s</root>';
        $doc = @simplexml_load_string(sprintf($xmlWrapping, $xml));
        if ($doc === false) {
            return $fields;
        }
        $xpath = '//h1 | //h2 | //h3 | //h4 | //h5 | //h6 | '
                    . '//strong | //em | //li | //a';
        foreach ($doc->xpath($xpath) as $field) {
            $value = trim((string) $field);
            if (!empty($value)) {
                if (!isset($fields[$value])) {
                    $fields[$value] = array($field->getName());
                } else {
                    $fields[$value][] = $field->getName();
                }
            }
        }
        return $fields;
    }

    protected function _tokenize($text)
    {
        $tokens = array();
        $words = preg_split(
            sprintf('/[%s]+/', self::$_nonLetters),
            $text,
            null,
            PREG_SPLIT_NO_EMPTY
        );
        foreach ($words as $word) {
            if (mb_strlen($word, 'UTF-8') < 3) {
                continue;
            }
            $lower = mb_strtolower($word, 'UTF-8');
            if (in_array($lower, self::$_stopwords)) {
                continue;
            }
            $stem = PorterStemmer::Stem($lower);
            if (!isset($tokens[$stem])) {
                $tokens[$stem] = 1;
            } else {
                ++$tokens[$stem];
            }
        }
        return $tokens;
    }
}

