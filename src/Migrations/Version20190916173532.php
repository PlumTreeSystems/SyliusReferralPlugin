<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190916173532 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_customer ADD enroller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_customer ADD CONSTRAINT FK_7E82D5E6687083A9 FOREIGN KEY (enroller_id) REFERENCES sylius_customer (id)');
        $this->addSql('CREATE INDEX IDX_7E82D5E6687083A9 ON sylius_customer (enroller_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_customer DROP FOREIGN KEY FK_7E82D5E6687083A9');
        $this->addSql('DROP INDEX IDX_7E82D5E6687083A9 ON sylius_customer');
        $this->addSql('ALTER TABLE sylius_customer DROP enroller_id');
    }
}
