<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230411164806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1027Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1027Platform'."
        );

        $this->addSql('CREATE TABLE nextbox_neos_qrcode_domain_model_qrcode (persistence_object_identifier VARCHAR(40) NOT NULL, urlshortener VARCHAR(40) DEFAULT NULL, resource VARCHAR(40) DEFAULT NULL, UNIQUE INDEX UNIQ_EF9FADBFFFC379 (urlshortener), UNIQUE INDEX UNIQ_EF9FADBC91F416 (resource), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE nextbox_neos_qrcode_domain_model_qrcode ADD CONSTRAINT FK_EF9FADBFFFC379 FOREIGN KEY (urlshortener) REFERENCES nextbox_neos_urlshortener_domain_model_urlshortener (persistence_object_identifier)');
        $this->addSql('ALTER TABLE nextbox_neos_qrcode_domain_model_qrcode ADD CONSTRAINT FK_EF9FADBC91F416 FOREIGN KEY (resource) REFERENCES neos_flow_resourcemanagement_persistentresource (persistence_object_identifier)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1027Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1027Platform'."
        );

        $this->addSql('DROP TABLE nextbox_neos_qrcode_domain_model_qrcode');
    }
}
