<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220506132158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_2FB3D0EE8A90ABA9 ON project');
        $this->addSql('ALTER TABLE project CHANGE `key` project_key VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE18EB714A ON project (project_key)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_2FB3D0EE18EB714A ON project');
        $this->addSql('ALTER TABLE project CHANGE project_key `key` VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE8A90ABA9 ON project (`key`)');
    }
}
