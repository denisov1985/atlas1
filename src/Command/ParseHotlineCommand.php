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


class ParseHotlineCommand extends Command
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
            ->setName('parser:parse:hotline')
            ->setDescription('Creates a new user.')
            ->setHelp('This command allows you to create a user...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->conntainer->get('doctrine.orm.entity_manager');

            for ($i = 329; $i < 400; $i++) {
                sleep(5);
                try {
                    $links = $this->hotLine->getPageLinks($i);
                } catch (\Exception $e) {
                    $i--;
                    dump($e->getMessage());
                    sleep(5);
                    continue;
                }
                dump('Links: ' . count($links));
                if (count($links) === 0) {
                    break;
                }
                foreach ($links as $link) {
                    $product = $em->getRepository(Product::class)->findByExternalLink($link->getUrl());
                    if (empty($product)) {
                        $product = new Product();
                        $product->setName($link->getTitle());
                        $product->setExternalLink($link->getUrl());
                        $product->setExternalProperties('');
                        $product->setPrice(0);
                        $product->setDescription('');
                    }   else  {
                        $product = $product[0];
                    }
                    //$this->hotLine->getPageContent($link->getUrl(), $product);
                    $em->persist($product);
                }
                $em->flush();
            }
    }
}