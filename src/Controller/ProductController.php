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

class ProductController extends Controller
{
    /**
     * @Route("/products", name="product_list")
     */
    public function productListPage()
    {
        return $this->render("product/product-list.html.twig");
    }

    /**
     * @Route("/product/show", name="product_show")
     */
    public function productShowPage()
    {
        return $this->render("product/product-show.html.twig");
    }
}