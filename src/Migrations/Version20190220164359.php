<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190220164359 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscriber ADD school_id INT NOT NULL, DROP school');
        $this->addSql('ALTER TABLE subscriber ADD CONSTRAINT FK_AD005B69C32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('CREATE INDEX IDX_AD005B69C32A47EE ON subscriber (school_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscriber DROP FOREIGN KEY FK_AD005B69C32A47EE');
        $this->addSql('DROP INDEX IDX_AD005B69C32A47EE ON subscriber');
        $this->addSql('ALTER TABLE subscriber ADD school VARCHAR(255) NOT NULL COLLATE utf8_polish_ci, DROP school_id');
    }
}
