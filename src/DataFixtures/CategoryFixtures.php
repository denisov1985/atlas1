<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14.12.2017
 * Time: 22:00
 */

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    private function _getData()
    {
        $names = 'Аминокислоты (BCAA);
Протеин казеин;
Протеин сывороточный;
Протеин говяжий, куриный;
Протеин яичный;
Протеин соевый;
Протеин конопляный;
Протеин рисовый, гороховый;
Протеин многокомпонентный;
Протеин другие;
Гейнер;
Креатин;
Минералы и витамины;
Препараты для суставов и связок;
Тестостероновые препараты;
Препараты для снижения веса / жиросжигатели;
Предтренировочные комплексы;
Послетренировочные комплексы;
Заменители питания;
Энергетики;
Другое;
Аксессуары для спортивного питания
';
        return explode(';', $names);
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->_getData() as $name) {
            $category = new Category();
            $category->setName(trim($name));
            $manager->persist($category);
        }
        $manager->flush();
    }

}