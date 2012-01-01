<?php

/* layout.html */
class __TwigTemplate_c4911b66f2e73556a178676ebd28f972 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">
\t<head>
\t\t<meta charset=\"utf-8\">
\t\t<title>Bootstrap, from Twitter</title>
\t\t<meta name=\"description\" content=\"\">
\t\t<meta name=\"author\" content=\"\">
\t\t
\t\t<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
\t\t<!--[if lt IE 9]>
\t\t      <script src=\"http://html5shim.googlecode.com/svn/trunk/html5.js\"></script>
\t\t    <![endif]-->
\t\t
\t\t<!-- Le styles -->
\t\t<link href=\"bootstrap.min.css\" rel=\"stylesheet\">
\t</head>
\t<body>
\t\t";
        // line 18
        $this->displayBlock('content', $context, $blocks);
        // line 20
        echo "\t</body>
</html>";
    }

    // line 18
    public function block_content($context, array $blocks = array())
    {
        // line 19
        echo "\t\t";
    }

    public function getTemplateName()
    {
        return "layout.html";
    }

}
