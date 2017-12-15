<?php
/**
 * Created by PhpStorm.
 * User: Dmytro_Denysov
 * Date: 12/14/2017
 * Time: 4:13 PM
 */

namespace App\Services\Parser;

use App\Entity\Product;

interface ParserInterface
{
    public function getPageLinks($pageNumber);
    public function getPageContent($pageUrl, Product $product);
}