<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104163123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ads_categories (ads_id INT NOT NULL, categories_id INT NOT NULL, INDEX IDX_6FC8F3A8FE52BF81 (ads_id), INDEX IDX_6FC8F3A8A21214B7 (categories_id), PRIMARY KEY(ads_id, categories_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_ads (user_id INT NOT NULL, ads_id INT NOT NULL, INDEX IDX_95DF97ABA76ED395 (user_id), INDEX IDX_95DF97ABFE52BF81 (ads_id), PRIMARY KEY(user_id, ads_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ads_categories ADD CONSTRAINT FK_6FC8F3A8FE52BF81 FOREIGN KEY (ads_id) REFERENCES ads (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ads_categories ADD CONSTRAINT FK_6FC8F3A8A21214B7 FOREIGN KEY (categories_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_ads ADD CONSTRAINT FK_95DF97ABA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_ads ADD CONSTRAINT FK_95DF97ABFE52BF81 FOREIGN KEY (ads_id) REFERENCES ads (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ads_categories DROP FOREIGN KEY FK_6FC8F3A8FE52BF81');
        $this->addSql('ALTER TABLE ads_categories DROP FOREIGN KEY FK_6FC8F3A8A21214B7');
        $this->addSql('ALTER TABLE user_ads DROP FOREIGN KEY FK_95DF97ABA76ED395');
        $this->addSql('ALTER TABLE user_ads DROP FOREIGN KEY FK_95DF97ABFE52BF81');
        $this->addSql('DROP TABLE ads_categories');
        $this->addSql('DROP TABLE user_ads');
    }
}
