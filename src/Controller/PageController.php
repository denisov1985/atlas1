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

    /**
     * @Route("/shipping", name="page_shipping")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shippingPage()
    {
        return $this->render("_shared/in-development.html.twig");
    }

    /**
     * @Route("/vendors", name="page_vendors")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function vendorsPage()
    {
        return $this->render("_shared/in-development.html.twig");
    }

    /**
     * @Route("/contact", name="page_contact")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contactPage()
    {
        return $this->render("_shared/in-development.html.twig");
    }
}