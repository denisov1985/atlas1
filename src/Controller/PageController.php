<?php
/**
 * Created by PhpStorm.
 * User: Dmytro_Denysov
 * Date: 12/13/2017
 * Time: 11:39 AM
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends Controller
{
    /**
     * @Route("/", name="home_page")
     */
    public function homePage()
    {
        return $this->render("page/home-page.html.twig");
    }
}