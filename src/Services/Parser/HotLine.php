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
    const BASE_URL = 'http://hotline.ua/';

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
        $url = self::BASE_URL . 'sport/sportivnoe-pitanie/79690/?p=' . $pageNumber;
        $content = $this->_getContent($url);

        $crawler = new Crawler($content);
        $crawler = $crawler->filter('.info-description a');

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
        $response = $this->client->request('GET', $pageUrl);
        return $response->getBody()->getContents();
    }

}