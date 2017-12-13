<?php
/**
 * Created by PhpStorm.
 * User: Dmytro_Denysov
 * Date: 12/13/2017
 * Time: 4:39 PM
 */

namespace App\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @Embedded\Embedded
 */
class AppExtension extends AbstractExtension
{
    private $container;
    private $menu;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->menu = Yaml::parseFile(implode(DIRECTORY_SEPARATOR, [
            $this->container->get('kernel')->getProjectDir(),
            'config',
            'menu',
            'main.yaml'
        ]));
    }

    public function getMenu()
    {
        return $this->menu['main'];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_menu', [$this, 'getMenu'])
        ];
    }

}