<?php
/**
 * Created by PhpStorm.
 * User: Dmytro_Denysov
 * Date: 12/14/2017
 * Time: 4:07 PM
 */

namespace App\Services\Parser;

use App\Entity\Product;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DomCrawler\Crawler;

class HotLine implements ParserInterface
{
    const BASE_URL = 'http://hotline.ua';

    private $client;
    private $crawler;

    /**
     * HotLine constructor.
     * @param ClientInterface $client
     * @param Crawler $crawler
     */

    public function __construct(ClientInterface $client, Crawler $crawler)
    {
        $this->client = $client;
        $this->crawler = $crawler;
    }

    /**
     * @param $pageNumber
     * @return array|Link
     */
    public function getPageLinks($pageNumber)
    {
        $links = [];
        $url = self::BASE_URL . '/sport/sportivnoe-pitanie/79690/?p=' . $pageNumber;
        $content = $this->_getContent($url);
        file_put_contents("c:\\symfony\\content.html", $content);

        $crawler = new Crawler($content);
        $crawler = $crawler->filter('.info-description .h4 a');

        foreach ($crawler as $domElement) {
            $link = new Link();
            $link->setTitle(trim($domElement->textContent));
            $link->setUrl(self::BASE_URL . trim($domElement->getAttribute('href')));
            dump($link);
            $links[] = $link;
        }
        return $links;
    }

    public function getPageContent($pageUrl, Product $product)
    {
        $content = $this->_getContent($pageUrl);

        $crawler = new Crawler($content);
        $data = $crawler->filter('.resume-description .text');
        $description = str_ireplace('... развернуть  свернуть', '', trim($data->text()));
        $product->setDescription($description);
        $data = $crawler->filter('.resume-price .value');
        $priceText = trim($data->text());
        $priceData = explode('–', $priceText);
        if (isset($priceData[1])) {
            $lowPrice  = (int) trim(str_ireplace(' ', '', $priceData[0]));
            $highPrice = (int) trim(mb_convert_encoding($priceData[1], 'ASCII'));
            $avg = ($lowPrice + $highPrice) / 2;
        }   else  {
            $avg = 0;
        }

        $product->setPrice($avg);
        $data = $crawler->filter('.table-type-1 .table-row div');

        $spec = [];
        $lastKey = '';
        foreach ($data as $key => $domElement) {
            $content = trim($domElement->textContent);
            if((bool)($key % 2)) {
                if ($lastKey === 'Товар на сайте производителя:') {
                    $content = $domElement->childNodes[1]->childNodes[0]->attributes[1]->value;
                }
                $spec[$lastKey] = $content;
            } else {
                $lastKey = $content;
            }
        }
        $product->setExternalProperties(\json_encode($spec, JSON_UNESCAPED_UNICODE));
        dump($product);
    }



    private function _getContent($pageUrl) {
        $response = $this->client->request('GET', $pageUrl, [
            'headers' => [
                'Cookie' => 'hl_sid=614e4c802eb086fd12c3f0f5530d9e42; hl_guest_id=896237554042a437de310fb06d2c324e; PHPSESSID=3c4a5ce1d1c099d987c0d981bac31558; hluniqueid=0dddd5e542cd43fcfa245808feba1c32; hluniqueid_ctl=681b9c2330e838dfd3d11b6eb989b924; region_popup=3; search_uid=af571a45980c95cced4f; language=ru; catmode=lines; guest_visited_cards=%5B%227728132%22%2C%221936300%22%2C%221403461%22%2C%2211662246%22%2C%227703417%22%2C%221426474%22%2C%228299860%22%5D; fullinfo=0; _dc_gtm_UA-2141710-13=1; gd_order_primary=0; _ga=GA1.2.939944401.1513258887; _gid=GA1.2.1975691555.1513343570; region=1; city_id=187; region_mode=1; currency=uah'
            ]
        ]);
        return $response->getBody()->getContents();
    }

    public static function getCategories()
    {
        return explode('-', '79680-79681-79682-79683-79685-79686-79687-79688-79689-79690-79942-139594-139595-139596-139597-139598-139599-139600-139608-371222-556450');
    }

}