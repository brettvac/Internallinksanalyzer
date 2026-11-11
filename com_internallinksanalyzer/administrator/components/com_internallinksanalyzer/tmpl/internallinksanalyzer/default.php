<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_internallinksanalyzer
 * @version     1.0
 * @license     GNU General Public License version 2
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER')); ?>
            </h1>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">
                <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_SELECT_ARTICLE')); ?>
            </h2>
        </div>

        <div class="card-body">
            <form method="get" action="<?php echo Route::_('index.php?option=com_internallinksanalyzer'); ?>">
                <input type="hidden" name="option" value="com_internallinksanalyzer">
                <input type="hidden" name="task" value="internallinksanalyzer.analyze">

                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="article_id" class="form-label">
                            <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_ARTICLE')); ?>
                        </label>

                        <select name="article_id" id="article_id" class="form-select" required>
                            <option value="0">
                                <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_SELECT_ARTICLE_OPTION')); ?>
                            </option>
                            <?php foreach ($this->articles as $article) : ?>
                                <option value="<?php echo (int) $article->id; ?>">
                                    #<?php echo (int) $article->id; ?> - <?php echo $this->escape($article->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mt-3 mt-md-0">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_ANALYZE')); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>