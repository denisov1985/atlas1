<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171220163045 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product ADD cover_image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADE5A0E336 FOREIGN KEY (cover_image_id) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04ADE5A0E336 ON product (cover_image_id)');
        $this->addSql('ALTER TABLE product_image RENAME INDEX product_id TO IDX_64617F034584665A');
        $this->addSql('ALTER TABLE product_image RENAME INDEX image_id TO IDX_64617F033DA5256D');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADE5A0E336');
        $this->addSql('DROP INDEX UNIQ_D34A04ADE5A0E336 ON product');
        $this->addSql('ALTER TABLE product DROP cover_image_id');
        $this->addSql('ALTER TABLE product_image RENAME INDEX idx_64617f034584665a TO product_id');
        $this->addSql('ALTER TABLE product_image RENAME INDEX idx_64617f033da5256d TO image_id');
    }
}
