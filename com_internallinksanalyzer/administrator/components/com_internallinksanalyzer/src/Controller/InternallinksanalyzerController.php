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
use Joomla\CMS\Router\Route;

/**
 * Internal Links Analyzer controller.
 *
 * Handles actions related to analysing an article.
 *
 * @since 1.0
 */
class InternallinksanalyzerController extends BaseController
{
    /**
     * Analyse the selected article.
     *
     * Redirects to the results view after an article has been selected.
     *
     * @return void
     *
     * @since 1.0
     */
    public function analyze(): void
    {
        $articleId = $this->input->getInt('article_id', 0);

        if ($articleId <= 0) {
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_internallinksanalyzer' .
                    '&view=Internallinksanalyzer',
                    false
                )
            );

            return;
        }

        $this->setRedirect(
            Route::_(
                'index.php?option=com_internallinksanalyzer' .
                '&view=InternallinksanalyzerResult' .
                '&article_id=' . $articleId,
                false
            )
        );
    }
    
}