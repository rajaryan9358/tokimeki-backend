<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* fk_checkbox.twig */
class __TwigTemplate_d4d58a80c7e45512fe9a07b6bec6e5ff2ad1b9d1eaafd2fc4a695c7ff9f22f61 extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<input type=\"hidden\" name=\"fk_checks\" value=\"0\">
<input type=\"checkbox\" name=\"fk_checks\" id=\"fk_checks\" value=\"1\"";
        // line 3
        echo ((($context["checked"] ?? null)) ? (" checked") : (""));
        echo ">
<label for=\"fk_checks\">";
        // line 4
        echo _gettext("Enable foreign key checks");
        echo "</label>
";
    }

    public function getTemplateName()
    {
        return "fk_checkbox.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  44 => 4,  40 => 3,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "fk_checkbox.twig", "/var/www/html/phpmyadmin/templates/fk_checkbox.twig");
    }
}
