<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611155756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE booked_rdv (id INT AUTO_INCREMENT NOT NULL, rdv_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', begin_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', client_surname VARCHAR(100) NOT NULL, booking_token VARCHAR(50) NOT NULL, is_paid TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_7D5FCE42CE4B5C8E (booking_token), INDEX IDX_7D5FCE424CCE3F86 (rdv_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booked_rdv ADD CONSTRAINT FK_7D5FCE424CCE3F86 FOREIGN KEY (rdv_id) REFERENCES rdv (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rdv DROP FOREIGN KEY FK_FBD57E0D4CCE3F86
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rdv DROP FOREIGN KEY FK_FBD57E0DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_rdv
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_rdv (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, rdv_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', begin_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_FBD57E0D4CCE3F86 (rdv_id), INDEX IDX_FBD57E0DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rdv ADD CONSTRAINT FK_FBD57E0D4CCE3F86 FOREIGN KEY (rdv_id) REFERENCES rdv (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_rdv ADD CONSTRAINT FK_FBD57E0DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booked_rdv DROP FOREIGN KEY FK_7D5FCE424CCE3F86
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE booked_rdv
        SQL);
    }
}
