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

    public function getBreadcrumbs()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $routeName = $request->get('_route');
        $result = $this->_findMenuByRoute($routeName, $this->menu['main']);
        if (empty($result)) {
            return [[
                'title' => ''
            ]];
        }
        end($result);
        $last = key($result);
        $result[$last]['is_text'] = true;
        return $result;
    }

    private function _findMenuByRoute($routeName, $inputMenu, $initial = []) {
        foreach ($inputMenu as $route => $menu) {
            if ($route == $routeName) {
                $initial[$route] = $menu;
                return $initial;
            }
            if (isset($menu['sub'])) {
                $result = $this->_findMenuByRoute($routeName, $menu['sub'], [$route => $menu]);
                if (!empty($result)) {
                    return $result;
                }
            }
        }
        return [];
    }

    public function getMenu()
    {
        return $this->menu['main'];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_menu', [$this, 'getMenu']),
            new TwigFunction('get_breadcrumbs', [$this, 'getBreadcrumbs']),
        ];
    }

}