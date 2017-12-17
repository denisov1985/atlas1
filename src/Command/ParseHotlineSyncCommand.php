<?php
/**
 * Created by PhpStorm.
 * User: Dmytro_Denysov
 * Date: 12/14/2017
 * Time: 4:00 PM
 */

namespace App\Command;

use App\Entity\Product;
use App\Services\Parser\HotLine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;


class ParseHotlineSyncCommand extends Command
{
    private $hotLine;
    private $conntainer;

    /**
     * ParseHotlineCommand constructor.
     * @param HotLine $hotLine
     * @param ContainerInterface $container
     */
    public function __construct(HotLine $hotLine, ContainerInterface $container)
    {
        $this->hotLine = $hotLine;
        $this->conntainer = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('parser:parse:sync')
            ->setDescription('Creates a new user.')
            ->setHelp('This command allows you to create a user...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'data';
        dump(($path));

        $em = $this->conntainer->get('doctrine.orm.entity_manager');
        $query = $em->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->andWhere('p.description != :desc')
            ->andWhere('p.productImage == :image')
            ->setParameter('desc', '')
            ->setParameter('image', '')
            ->getQuery();

        $result = $query->getResult();

        foreach ($result as $product) {
            /** @var Product $product */
            $json = $product->getExternalImage();
            $data = \json_decode($json, true);
            dump($data['data']);

            $crawler = new Crawler($data['data']);
            $images  = $crawler->filter('img');

            $folderName = md5($product->getExternalLink());
            $dir = $path . DIRECTORY_SEPARATOR . $folderName;
            dump($dir);
            if (!is_dir($dir)) {
                mkdir($dir);
            }

            $jsonToSave = [];
            foreach ($images as $img) {
                if (empty($img->getAttribute('data-gallery-image'))) {
                    continue;
                }
                $rawImg = file_get_contents($img->getAttribute('data-gallery-image'));
                $fileData = explode('/', $img->getAttribute('data-gallery-image'));
                file_put_contents($dir . DIRECTORY_SEPARATOR . $fileData[count($fileData) - 1], $rawImg);
                dump($img->getAttribute('data-gallery-image'));
                $jsonToSave[] = $folderName . DIRECTORY_SEPARATOR . $fileData[count($fileData) - 1];
            }
            $product->setProductImage($jsonToSave);
            $em->persist($product);
            $em->flush();
            sleep(1);
        }

        dump(count(($result)));

    }
}