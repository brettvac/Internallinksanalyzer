<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_internalliksanalyzer
 * @version     1.0
 * @license     GNU General Public License version 2
 */

namespace Naftee\Component\Internallinksanalyzer\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

/**
 * Default controller for Internal Links Analyzer.
 *
 * @since  1.0
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var string
     *
     * @since 1.0
     */
    protected $default_view = 'internallinksanalyzer';
      
    public function display($cachable = false, $urlparams = false)
    {
        $viewName = $this->input->get('view', $this->default_view);
        
        $viewType = Factory::getDocument()->getType();
        
        $viewLayout = $this->input->get('layout', 'default');
        
        $model = $this->getModel('Internallinksanalyzer', 'Administrator');

        $view = $this->getView($viewName, $viewType, 'Administrator');

        // the View needs a pointer to the Model
        $view->setModel($model, true);

        $view->setLayout($viewLayout);
        
        $view->display();
    }
}
