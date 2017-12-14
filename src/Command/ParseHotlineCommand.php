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

        for ($i = 0; $i < 54; $i++) {
            $links = $this->hotLine->getPageLinks($i);
            foreach ($links as $link) {
                $product = $em->getRepository(Product::class)->findByExternalLink($link->getUrl());
                if (empty($product)) {
                    $product = new Product();
                    $product->setName($link->getTitle());
                    $product->setExternalLink($link->getUrl());
                    $em->persist($product);
                }
            }
            $em->flush();
        }
    }
}