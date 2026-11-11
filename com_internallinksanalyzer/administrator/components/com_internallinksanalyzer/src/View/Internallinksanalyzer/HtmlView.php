<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_internallinksanalyzer
 * @version     1.0
 * @license     GNU General Public License version 2
 */

namespace Naftee\Component\Internallinksanalyzer\Administrator\View\Internallinksanalyzer;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

/**
 * Internal Links Analyzer HTML view.
 *
 * Displays the article selection screen.
 *
 * @since 1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Published articles.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $articles = [];

    /**
     * Display the view.
     *
     * @param string|null $tpl Template name.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function display($tpl = null)
    {        
        $model = $this->getModel();

        $this->articles = $model->getArticles();
        
        ToolbarHelper::title(Text::_('COM_INTERNALLINKSANALYZER'),'link internallinksanalyzer');

        parent::display($tpl);
    }
}