<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241209092458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_month (article_id INT NOT NULL, month_id INT NOT NULL, INDEX IDX_F90162177294869C (article_id), INDEX IDX_F9016217A0CBDE4 (month_id), PRIMARY KEY(article_id, month_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE month (id INT AUTO_INCREMENT NOT NULL, month_number SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_month ADD CONSTRAINT FK_F90162177294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_month ADD CONSTRAINT FK_F9016217A0CBDE4 FOREIGN KEY (month_id) REFERENCES month (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Article DROP months');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_month DROP FOREIGN KEY FK_F90162177294869C');
        $this->addSql('ALTER TABLE article_month DROP FOREIGN KEY FK_F9016217A0CBDE4');
        $this->addSql('DROP TABLE article_month');
        $this->addSql('DROP TABLE month');
        $this->addSql('ALTER TABLE article ADD months LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
    }
}
