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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HotLine
{
    const BASE_URL = 'http://hotline.ua';

    private $client;
    private $crawler;
    private $userAgentCollection;

    private $initialCookies = 'region=1; city_id=187; region_mode=1; currency=uah; hl_sid=614e4c802eb086fd12c3f0f5530d9e42; hl_guest_id=896237554042a437de310fb06d2c324e; PHPSESSID=3c4a5ce1d1c099d987c0d981bac31558; region_popup=3; search_uid=af571a45980c95cced4f; language=ru; catmode=lines; fullinfo=0; gd_order_primary=0; _dc_gtm_UA-2141710-13=1; _gat_UA-2141710-13=1; guest_visited_cards=%5B%227728132%22%2C%221936300%22%2C%221403461%22%2C%2211662246%22%2C%227703417%22%2C%221426474%22%2C%228299860%22%2C%221426473%22%5D; _ga=GA1.2.939944401.1513258887; _gid=GA1.2.427384566.';
    /**
     * HotLine constructor.
     * @param ClientInterface $client
     * @param Crawler $crawler
     */

    public function __construct(ClientInterface $client, Crawler $crawler)
    {
        $this->client = $client;
        $this->crawler = $crawler;
        $this->userAgentCollection = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'user-agent.json'), true);
    }

    /**
     * @param $pageNumber
     * @return array|Link
     */
    public function getPageLinks($pageNumber)
    {
        $links = [];
        if ($pageNumber == 0) {
            $url = self::BASE_URL . '/sport/sportivnoe-pitanie/';
        }   else  {
            $url = self::BASE_URL . '/sport/sportivnoe-pitanie/?p=' . $pageNumber;
        }
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

    public function getPageContent($pageUrl, Product $product)
    {
        $content = $this->_getContent($pageUrl);

        $crawler = new Crawler($content);

        $data = explode('"csrf-token" content="', $content);
        $data = explode('"', $data[1]);
        $token = $data[0];

        $image = $this->getImage($pageUrl, $token);
        $product->setExternalImage($image);

        $data = $crawler->filter('.resume-description .text');
        $description = str_ireplace('... развернуть  свернуть', '', trim($data->text()));

        $product->setDescription($description);
        $data = $crawler->filter('.resume-price .value');

        try {
            $priceText = trim($data->text());
            $priceData = explode('–', $priceText);
        } catch (\Exception $e) {
            $priceData = [];
        }

        if (isset($priceData[1])) {
            $lowPrice  = (int) trim(str_ireplace(' ', '', $priceData[0]));
            $highPrice = (int) trim(mb_convert_encoding($priceData[1], 'ASCII'));
            $avg = (int) floor(($lowPrice + $highPrice) / 2);
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

        dump("Parsing: $pageUrl");
        //$agent = $this->userAgentCollection[rand(0, count($this->userAgentCollection) - 1)];
        //dump($agent);
        if (!empty($this->initialCookies)) {
            $requestCookie = $this->initialCookies;
        }
        $response = $this->client->request('GET', $pageUrl, [
            'headers' => [
                'User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
                'Cookie' => $requestCookie . mktime() . ';'
            ]
        ]);


        $headers = $response->getHeaders();
        dump($headers);
        if (!isset($headers['Set-Cookie'])) {
            throw new NotFoundHttpException('Not found');
        }
        $cookies = $headers['Set-Cookie'];
        $cookieCollection = [];
        foreach ($cookies as $cookie) {
            $data = explode(';', $cookie);
            $data = explode('=', $data[0]);
            $cookieCollection[$data[0]] = $data[1];
        }

        return $response->getBody()->getContents();
    }

    public function getImage($url, $token)
    {
        $requestCookie = '';
        if (!empty($this->initialCookies)) {
            $requestCookie = $this->initialCookies;
        }
        $response = $this->client->request('GET', $url . 'get-product-gallery-content/', [
            'headers' => [
                'User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
                'Cookie' => $requestCookie . mktime(),
                'X-CSRF-Token' => $token,
                'X-Requested-With' => 'XMLHttpRequest',
                'Referer' => $url
            ]
        ]);
        return $response->getBody()->getContents();
    }

    public static function getCategories()
    {
        return explode('-', '139594-79680-79681-79682-79683-79685-79686-79687-79688-79689-79690-79942-139595-139596-139597-139598-139599-139600-139608-371222-556450');
    }

}