<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228165728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE areas (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, tenant_id BINARY(16) NOT NULL, INDEX IDX_58B0B25C9033212A (tenant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tenants (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(100) NOT NULL, is_active TINYINT NOT NULL, is_super_tenant TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_B8FC96BB989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tenant_videos (tenant_id BINARY(16) NOT NULL, video_id BINARY(16) NOT NULL, INDEX IDX_801006B9033212A (tenant_id), INDEX IDX_801006B29C1004E (video_id), PRIMARY KEY (tenant_id, video_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id BINARY(16) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, tenant_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX IDX_1483A5E99033212A (tenant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE videos (id BINARY(16) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, url VARCHAR(2048) NOT NULL, duration_seconds INT DEFAULT NULL, thumbnail_url VARCHAR(50) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vr_devices (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, identifier VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, is_active TINYINT NOT NULL, last_seen_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, tenant_id BINARY(16) NOT NULL, area_id BINARY(16) DEFAULT NULL, UNIQUE INDEX UNIQ_C6E50674772E836A (identifier), INDEX IDX_C6E506749033212A (tenant_id), INDEX IDX_C6E50674BD0F409C (area_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vr_device_videos (vr_device_id BINARY(16) NOT NULL, video_id BINARY(16) NOT NULL, INDEX IDX_4AC7D6552C5E07F6 (vr_device_id), INDEX IDX_4AC7D65529C1004E (video_id), PRIMARY KEY (vr_device_id, video_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE areas ADD CONSTRAINT FK_58B0B25C9033212A FOREIGN KEY (tenant_id) REFERENCES tenants (id)');
        $this->addSql('ALTER TABLE tenant_videos ADD CONSTRAINT FK_801006B9033212A FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tenant_videos ADD CONSTRAINT FK_801006B29C1004E FOREIGN KEY (video_id) REFERENCES videos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E99033212A FOREIGN KEY (tenant_id) REFERENCES tenants (id)');
        $this->addSql('ALTER TABLE vr_devices ADD CONSTRAINT FK_C6E506749033212A FOREIGN KEY (tenant_id) REFERENCES tenants (id)');
        $this->addSql('ALTER TABLE vr_devices ADD CONSTRAINT FK_C6E50674BD0F409C FOREIGN KEY (area_id) REFERENCES areas (id)');
        $this->addSql('ALTER TABLE vr_device_videos ADD CONSTRAINT FK_4AC7D6552C5E07F6 FOREIGN KEY (vr_device_id) REFERENCES vr_devices (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vr_device_videos ADD CONSTRAINT FK_4AC7D65529C1004E FOREIGN KEY (video_id) REFERENCES videos (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE areas DROP FOREIGN KEY FK_58B0B25C9033212A');
        $this->addSql('ALTER TABLE tenant_videos DROP FOREIGN KEY FK_801006B9033212A');
        $this->addSql('ALTER TABLE tenant_videos DROP FOREIGN KEY FK_801006B29C1004E');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E99033212A');
        $this->addSql('ALTER TABLE vr_devices DROP FOREIGN KEY FK_C6E506749033212A');
        $this->addSql('ALTER TABLE vr_devices DROP FOREIGN KEY FK_C6E50674BD0F409C');
        $this->addSql('ALTER TABLE vr_device_videos DROP FOREIGN KEY FK_4AC7D6552C5E07F6');
        $this->addSql('ALTER TABLE vr_device_videos DROP FOREIGN KEY FK_4AC7D65529C1004E');
        $this->addSql('DROP TABLE areas');
        $this->addSql('DROP TABLE tenants');
        $this->addSql('DROP TABLE tenant_videos');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE videos');
        $this->addSql('DROP TABLE vr_devices');
        $this->addSql('DROP TABLE vr_device_videos');
    }
}
