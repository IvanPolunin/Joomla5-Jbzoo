<?php
namespace Google\Task;
/**
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use InvalidArgumentException;

class Composer
{
  /**
   * @param Event $event Composer event passed in for any script method
   * @param Filesystem $filesystem Optional. Used for testing.
   */
  public static function cleanup(
      Event $event,
      ?Filesystem $filesystem = null
  ) {
    $composer = $event->getComposer();
    $extra = $composer->getPackage()->getExtra();
    $servicesToKeep = isset($extra['google/apiclient-services']) ?
      $extra['google/apiclient-services'] : [];
    if ($servicesToKeep) {
      $serviceDir = sprintf(
          '%s/google/apiclient-services/src/Google/Service',
          $composer->getConfig()->get('vendor-dir')
      );
      self::verifyServicesToKeep($serviceDir, $servicesToKeep);
      $finder = self::getServicesToRemove($serviceDir, $servicesToKeep);
      $filesystem = $filesystem ?: new Filesystem();
      if (0 !== $count = count($finder)) {
        $event->getIO()->write(
            sprintf(
                'Removing %s google services',
                $count
            )
        );
        foreach ($finder as $file) {
          $realpath = $file->getRealPath();
          $filesystem->remove($realpath);
          $filesystem->remove($realpath . '.php');
        }
      }
    }
  }

  /**
   * @throws InvalidArgumentException when the service doesn't exist
   */
  private static function verifyServicesToKeep(
      $serviceDir,
      array $servicesToKeep
  ) {
    $finder = (new Finder())
        ->directories()
        ->depth('== 0');

    foreach ($servicesToKeep as $service) {
      if (!preg_match('/^[a-zA-Z0-9]*$/', $service)) {
        throw new InvalidArgumentException(
            sprintf(
                'Invalid Google service name "%s"',
                $service
            )
        );
      }
      try {
        $finder->in($serviceDir . '/' . $service);
      } catch (InvalidArgumentException $e) {
        throw new InvalidArgumentException(
            sprintf(
                'Google service "%s" does not exist or was removed previously',
                $service
            )
        );
      }
    }
  }

  private static function getServicesToRemove(
      $serviceDir,
      array $servicesToKeep
  ) {
    // find all files in the current directory
    return (new Finder())
        ->directories()
        ->depth('== 0')
        ->in($serviceDir)
        ->exclude($servicesToKeep);
  }
}
