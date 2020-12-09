<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201209173635 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sequence DROP FOREIGN KEY FK_5286D72B166D1F9C');
        $this->addSql('ALTER TABLE sequence ADD CONSTRAINT FK_5286D72B166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sequence DROP FOREIGN KEY FK_5286D72B166D1F9C');
        $this->addSql('ALTER TABLE sequence ADD CONSTRAINT FK_5286D72B166D1F9C FOREIGN KEY (project_id) REFERENCES user (id)');
    }
}
