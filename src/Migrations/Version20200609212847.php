<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200609212847 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE complaint_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE attached');
        $this->addSql('ALTER TABLE complaint ADD user_id INT NOT NULL, ADD type_id INT NOT NULL, CHANGE subject subjet VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE complaint ADD CONSTRAINT FK_5F2732B5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE complaint ADD CONSTRAINT FK_5F2732B5C54C8C93 FOREIGN KEY (type_id) REFERENCES complaint_type (id)');
        $this->addSql('CREATE INDEX IDX_5F2732B5A76ED395 ON complaint (user_id)');
        $this->addSql('CREATE INDEX IDX_5F2732B5C54C8C93 ON complaint (type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE complaint DROP FOREIGN KEY FK_5F2732B5C54C8C93');
        $this->addSql('CREATE TABLE attached (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE complaint_type');
        $this->addSql('ALTER TABLE complaint DROP FOREIGN KEY FK_5F2732B5A76ED395');
        $this->addSql('DROP INDEX IDX_5F2732B5A76ED395 ON complaint');
        $this->addSql('DROP INDEX IDX_5F2732B5C54C8C93 ON complaint');
        $this->addSql('ALTER TABLE complaint DROP user_id, DROP type_id, CHANGE subjet subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
