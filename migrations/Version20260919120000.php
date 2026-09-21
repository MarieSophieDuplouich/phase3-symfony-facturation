<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute une contrainte d'unicité sur l'email des utilisateurs.
 *
 * Avant d'exécuter cette migration en production, vérifiez qu'il n'existe pas
 * déjà de doublons d'e-mail en base (sinon la migration échouera) :
 *   SELECT email, COUNT(*) FROM "user" GROUP BY email HAVING COUNT(*) > 1;
 */
final class Version20260919120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute une contrainte d'unicité sur user.email";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
    }
}
