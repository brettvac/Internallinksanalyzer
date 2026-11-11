<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_internalliksanalyzer
 * @version     1.0
 * @license     GNU General Public License version 2
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    /**
     * Register component services.
     *
     * @param   Container $container  DI container.
     *
     * @return  void
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(
            new MVCFactory('\\Naftee\\Component\\Internallinksanalyzer')
        );

        $container->registerServiceProvider(
            new ComponentDispatcherFactory('\\Naftee\\Component\\Internallinksanalyzer')
        );

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                return $component;
            }
        );
    }
};
