<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_internalliksanalyzer
 * @version     1.0
 * @license     GNU General Public License version 2
 */

namespace Naftee\Component\Internallinksanalyzer\Administrator\View\InternallinksanalyzerResult;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Router\Route;

class HtmlView extends BaseHtmlView
{
    protected $target = null;

    protected $targetId = 0;

    protected $results = [];
    
    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        $this->targetId = $input->getInt('article_id', 0);

        $model = $this->getModel();

        if ($this->targetId > 0 && $model) {
            $this->target = $model->getArticle($this->targetId);

            if ($this->target) {
                $this->results = $model->findIncomingLinks(
                    $this->targetId
                );
            }
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(
            Text::_('COM_INTERNALLINKSANALYZER_RESULTS'),
            'link'
        );

        ToolbarHelper::back('JTOOLBAR_BACK', Route::_('index.php?option=com_internallinksanalyzer&view=internallinksanalyzer', false));
    }

}