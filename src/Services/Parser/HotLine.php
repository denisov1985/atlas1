<?php
/**
 * Created by PhpStorm.
 * User: Dmytro_Denysov
 * Date: 12/14/2017
 * Time: 4:07 PM
 */

namespace App\Services\Parser;

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

    public function getPageContent($pageUrl)
    {
        // TODO: Implement getPageContent() method.
    }

    private function _getContent($pageUrl) {
        $response = $this->client->request('GET', $pageUrl, [
            'headers' => [
                'Cookie' => 'hl_sid=6fa608770cfa24524c615761e72b7db3; region=1; city_id=187; region_mode=1; currency=uah; hl_guest_id=3a39b040711337ecd2da0a9aeae53740; PHPSESSID=5b7b59dd3591c4ca521926fb89a8c9a6; _ga=GA1.2.982223333.1513281737; hluniqueid=173ee67249954ca5c8d6bf02fc30669f; hluniqueid_ctl=5cffedffc4cd5814a7f959dd21850912; region_popup=3; search_uid=d5412f03aeed67877e6a; guest_visited_cards=%5B%221429583%22%5D; gd_order_primary=0; _dc_gtm_UA-2141710-13=1; language=ru; _gid=GA1.2.282290992.1513282080'
            ]
        ]);
        return $response->getBody()->getContents();
    }

    private function _getCategories()
    {
        return explode('-', '79680-79681-79682-79683-79685-79686-79687-79688-79689-79690-79942-139594-139595-139596-139597-139598-139599-139600-139608-371222-556450');
    }

}