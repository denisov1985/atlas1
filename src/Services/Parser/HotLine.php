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

    private $initialCookies = 'hl_sid=7a68b8dc06701b44007b3da678a2a9a0; region=1; city_id=187; region_mode=1; currency=uah; hl_guest_id=94f71aae1127e6ebe7aab67903608f10; gd_order_primary=0; PHPSESSID=4c80db6cae7e493debc44bcd6d15cedf; _ga=GA1.2.2130024232.1513417454; hluniqueid=173ee67249954ca5c8d6bf02fc30669f; hluniqueid_ctl=7c9f5d5b2d65e0cd71544ed9605e33df; language=ru; region_popup=3; fullinfo=1; _dc_gtm_UA-2141710-13=1; guest_visited_cards=%5B%228667005%22%2C%221415190%22%2C%2211907155%22%2C%227728133%22%2C%228223703%22%2C%222238722%22%5D; _gat_UA-2141710-13=1; _gid=GA1.2.815135830.';
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

        $cookieFile = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cookies.txt';
        if (!file_exists($cookieFile)) {
            file_put_contents($cookieFile, '');
        }
        $cookieString = file_get_contents($cookieFile);
        $cookieData = \json_decode($cookieString, true);;
        $requestCookiesData = [];
        foreach ($cookieData as $key => $value) {
            $requestCookiesData[] = "$key=$value";
        }
        $requestCookie = implode('; ', $requestCookiesData);
        dump("Parsing: $pageUrl");
        //$agent = $this->userAgentCollection[rand(0, count($this->userAgentCollection) - 1)];
        //dump($agent);
        if (!empty($this->initialCookies)) {
            $requestCookie = $this->initialCookies;
        }
        $response = $this->client->request('GET', $pageUrl, [
            'headers' => [
                'User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
                'Cookie' => $requestCookie . mktime()
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

        $oldCookies = json_decode(file_get_contents($cookieFile), true);
        foreach ($cookieCollection as $key => $value) {
            $oldCookies[$key] = $value;
        }
        file_put_contents($cookieFile, \json_encode($oldCookies));
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