<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261025212020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE campagne DROP CONSTRAINT fk_539b5d1616227374');
        $this->addSql('DROP INDEX idx_539b5d1616227374');
        $this->addSql('ALTER TABLE campagne RENAME COLUMN datedebut TO date_debut');
        $this->addSql('ALTER TABLE campagne RENAME COLUMN campagne_id TO groupement_id');
        $this->addSql('ALTER TABLE campagne ADD CONSTRAINT FK_539B5D16E66695CE FOREIGN KEY (groupement_id) REFERENCES groupement (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_539B5D16E66695CE ON campagne (groupement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE campagne DROP CONSTRAINT FK_539B5D16E66695CE');
        $this->addSql('DROP INDEX IDX_539B5D16E66695CE');
        $this->addSql('ALTER TABLE campagne RENAME COLUMN date_debut TO datedebut');
        $this->addSql('ALTER TABLE campagne RENAME COLUMN groupement_id TO campagne_id');
        $this->addSql('ALTER TABLE campagne ADD CONSTRAINT fk_539b5d1616227374 FOREIGN KEY (campagne_id) REFERENCES groupement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_539b5d1616227374 ON campagne (campagne_id)');
    }
}
