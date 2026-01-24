<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120UpdatePaiement extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter les colonnes manquantes à la table Paiement';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE paiement ADD periode VARCHAR(100) NOT NULL DEFAULT "Janvier 2026" AFTER statut');
        $this->addSql('ALTER TABLE paiement ADD montant NUMERIC(10, 2) NOT NULL DEFAULT 850.00 AFTER periode');
        $this->addSql('ALTER TABLE paiement MODIFY statut VARCHAR(255) NOT NULL DEFAULT "en_attente"');
        $this->addSql('ALTER TABLE paiement MODIFY date_paiement DATE NULL');
        $this->addSql('ALTER TABLE paiement MODIFY moyen_paiement VARCHAR(50) NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE paiement DROP COLUMN periode');
        $this->addSql('ALTER TABLE paiement DROP COLUMN montant');
    }
}
