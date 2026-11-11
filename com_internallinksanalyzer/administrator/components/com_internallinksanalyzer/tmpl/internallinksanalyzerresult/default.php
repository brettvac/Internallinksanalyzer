<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_internalliksanalyzer
 * @version     1.0
 * @license     GNU General Public License version 2
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>

<div class="container-fluid">

    <?php if ($this->target) : ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h2 class="h4 mb-1">
                            <?php echo $this->escape($this->target->title); ?>
                        </h2>
                        <div class="text-muted">
                            <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_ARTICLE_ID')); ?>:
                            <strong><?php echo (int) $this->target->id; ?></strong>
                        </div>
                    </div>

                    <div class="mt-3 mt-md-0">
                        <span class="badge bg-primary fs-6">
                            <?php echo count($this->results); ?> <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_INCOMING_LINKS')); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($this->results)) : ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">
                        <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_ARTICLES_LINKING_HERE')); ?>
                    </h2>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th><?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_SOURCE_ARTICLE')); ?></th>
                                <th><?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_ANCHOR_TEXT')); ?></th>
                                <th><?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_URL')); ?></th>
                                <th><?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_CONTEXT')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->results as $result) : ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo (int) $result['article_id']; ?></strong><br>
                                        <?php echo $this->escape($result['article_title']); ?>
                                    </td>
                                    <td><?php echo $this->escape($result['link_text']); ?></td>
                                    <td>
                                        <a href="<?php echo $this->escape($result['link_url']); ?>" target="_blank" rel="noopener noreferrer">
                                            <code class="small"><?php echo $this->escape($result['link_url']); ?></code>
                                        </a>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo $this->escape($result['context']); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else : ?>
            <div class="alert alert-info">
                <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_NO_INCOMING_LINKS')); ?>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <div class="alert alert-warning">
            <?php echo $this->escape(Text::_('COM_INTERNALLINKSANALYZER_NO_ARTICLE')); ?>
        </div>
    <?php endif; ?>
</div>