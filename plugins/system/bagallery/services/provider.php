<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Balbooa\Plugin\System\Gallery\Extension\Bagallery;

return new class () implements ServiceProviderInterface {
    
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new Bagallery(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'Bagallery'),
                    Factory::getApplication(),
                    $container->get(LanguageFactoryInterface::class)
                );

                return $plugin;
            }
        );
    }
};
