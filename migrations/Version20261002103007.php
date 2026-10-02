<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261002103007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Catalogue schema: waza categories, techniques and motions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE motion (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, updated_at DATETIME NOT NULL, format VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, source VARCHAR(255) NOT NULL, duration_seconds DOUBLE PRECISION DEFAULT NULL, phases CLOB NOT NULL, technique_id INTEGER NOT NULL, CONSTRAINT FK_F5FEA1E81F8ACB26 FOREIGN KEY (technique_id) REFERENCES technique (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5FEA1E81F8ACB26 ON motion (technique_id)');
        $this->addSql('CREATE TABLE technique (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, search_text CLOB NOT NULL, slug VARCHAR(96) NOT NULL, name VARCHAR(96) NOT NULL, kanji VARCHAR(32) NOT NULL, english VARCHAR(160) NOT NULL, position INTEGER NOT NULL, gokyo_group INTEGER DEFAULT NULL, prohibited BOOLEAN NOT NULL, notes CLOB DEFAULT NULL, category_id INTEGER NOT NULL, CONSTRAINT FK_D73B984112469DE2 FOREIGN KEY (category_id) REFERENCES waza_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D73B9841989D9B62 ON technique (slug)');
        $this->addSql('CREATE INDEX IDX_D73B984112469DE2 ON technique (category_id)');
        $this->addSql('CREATE TABLE waza_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, slug VARCHAR(64) NOT NULL, name VARCHAR(64) NOT NULL, kanji VARCHAR(16) NOT NULL, english VARCHAR(128) NOT NULL, family VARCHAR(255) NOT NULL, position INTEGER NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9729310F989D9B62 ON waza_category (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE motion');
        $this->addSql('DROP TABLE technique');
        $this->addSql('DROP TABLE waza_category');
    }
}
