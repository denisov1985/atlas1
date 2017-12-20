<?php
/**
 * Created by PhpStorm.
 * User: Dmytro_Denysov
 * Date: 12/14/2017
 * Time: 4:00 PM
 */

namespace App\Command;

use App\Entity\Image;
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
            ->andWhere('p.id > :id')
            //->andWhere('p.productImage = :image')
            ->setParameter('desc', '')
            ->setParameter('id', 11444)
            //->setParameter('image', '')
            ->getQuery();

        $result = $query->getResult();
        $total = count($result);
        foreach ($result as $key => $product) {
            dump("$key / $total");
            dump($product->getName());
            /** @var Product $product */
            $json = $product->getExternalImage();
            $data = \json_decode($json, true);
            //dump($data['data']);

            $crawler = new Crawler($data['data']);
            $images  = $crawler->filter('img');

            foreach ($images as $img) {
                if (empty($img->getAttribute('data-gallery-image'))) {
                    continue;
                }
                $fileData = explode('.', $img->getAttribute('data-gallery-image'));
                $rawImg = file_get_contents($img->getAttribute('data-gallery-image'));
                $checkSum = md5($rawImg);
                $imgPath  = $checkSum[0] . $checkSum[1];
                $imgFullPath = join(DIRECTORY_SEPARATOR, [$path, $imgPath]);
                if (!is_dir($imgFullPath)) {
                    mkdir($imgFullPath);
                }
                $fullPath = join(DIRECTORY_SEPARATOR, [$path, $imgPath, $checkSum . '.' . $fileData[count($fileData) - 1]]);

                $result = $em->getRepository(Image::class)->findByName($checkSum);

                if (!file_exists($fullPath)) {
                    file_put_contents($fullPath, $rawImg);
                }

                if (!empty($result)) {
                    $image = $result[0];
                }   else  {
                    $image = new Image();
                    $image->setName($checkSum);
                }
                if (!$image->hasProduct($product)) {
                    $image->addProduct($product);
                }
                $em->persist($image);
            }

            $em->persist($product);
            $em->flush();

            sleep(1);
        }

        dump(count(($result)));

    }
}