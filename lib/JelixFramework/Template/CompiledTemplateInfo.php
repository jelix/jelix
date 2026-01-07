<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2026 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Template;

class CompiledTemplateInfo
{
    /** @var string full path of the file containing the compiled template */
    public readonly string $compiledTemplatePath;
    public readonly string $className;
    public readonly string $classNamespace;

    function __construct(
        string $libPath,
        /** @var string full path of the template file */
        public readonly string $templatePath,
        public readonly string $module,
        public readonly string $templateFileName,
        public readonly string $theme,
        public readonly string $locale,
        public readonly ?CompiledTemplateInfo $substituteTemplate = null
        )
    {
        $m = ucfirst($module);
        $t = ucfirst($theme);
        if ($locale == '_') {
            $l = 'Any';
        }
        else {
            $l = ucfirst(str_replace('_', '', $locale));
        }
        $templateName = str_replace('.ctpl', '', $templateFileName);
        $this->compiledTemplatePath = $libPath.'Templates/'.$m.'/'.$t.'/'.$l.'/'.$templateName.'.php';
        $this->classNamespace = 'Jelix\\BuiltComponents\\Templates\\'.$m.'\\'.$t.'\\'.$l;
        $this->className = $templateName;
    }

    public function getFullClassName()
    {
        return $this->classNamespace.'\\'.$this->className;
    }
}