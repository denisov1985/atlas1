<?php
/**
 * Created by PhpStorm.
 * User: Dmytro_Denysov
 * Date: 12/13/2017
 * Time: 11:39 AM
 */

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends Controller
{
    /**
     * @Route("/products", name="product_list")
     */
    public function productListPage()
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository(Product::class)->createQueryBuilder('p');

        $query = $qb->andWhere($qb->expr()->isNotNull('p.coverImage'))
            ->setMaxResults(20)
            ->getQuery();


        $products = $query->getResult();

        return $this->render("product/product-list.html.twig", [
            'products' => $products
        ]);
    }

    /**
     * @Route("/product/show", name="product_show")
     */
    public function productShowPage()
    {
        return $this->render("product/product-show.html.twig");
    }
}