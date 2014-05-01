<?php

class View
{

    protected $_template = null;

    public function __construct($template)
    {
        $this->_template = $template;
    }

    public function toHtml($args = array())
    {
        $templateFile = VIEW_DIR . DS . $this->_template;
        if (!is_file($templateFile) || !is_readable($templateFile)) {
            return false;
        }
        extract($args, EXTR_SKIP);
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }

    public static function render($template, $args = array())
    {
        $view = new self($template);
        return $view->toHtml($args);
    }
}

