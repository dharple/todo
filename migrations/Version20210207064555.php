<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial migration.
 */
final class Version20210207064555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration';
    }

    public function up(Schema $schema): void
    {
        $schema = new Schema();

        $user = $schema->createTable('user');
        $user->addColumn('id', 'integer', ['autoincrement' => true]);
        $user->addColumn('fullname', 'string');
        $user->addColumn('password', 'string');
        $user->addColumn('timezone', 'string', ['length' => 128]);
        $user->addColumn('username', 'string', ['length' => 32]);
        $user->setPrimaryKey(['id']);
        $user->addUniqueIndex(['username']);
 
        $section = $schema->createTable('section');
        $section->addColumn('id', 'integer', ['autoincrement' => true]);
        $section->addColumn('name', 'string');
        $section->addColumn('status', 'string', ['length' => 20]);
        $section->addColumn('user_id', 'integer', ['notnull' => false]);
        $section->setPrimaryKey(['id']);
        $section->addForeignKeyConstraint($user, ['user_id'], ['id']);

        $item = $schema->createTable('item');
        $item->addColumn('id', 'integer', ['autoincrement' => true]);
        $item->addColumn('completed', 'datetime', ['notnull' => false]);
        $item->addColumn('created', 'datetime');
        $item->addColumn('priority', 'integer');
        $item->addColumn('section_id', 'integer', ['notnull' => false]);
        $item->addColumn('status', 'string', ['length' => 20]);
        $item->addColumn('task', 'string');
        $item->addColumn('user_id', 'integer', ['notnull' => false]);
        $item->setPrimaryKey(['id']);
        $item->addForeignKeyConstraint($user, ['user_id'], ['id']);
        $item->addForeignKeyConstraint($section, ['section_id'], ['id']);

        foreach ($schema->toSql($this->connection->getDatabasePlatform()) as $query) {
            $this->addSql($query);
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
