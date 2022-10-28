<?php

namespace App\EventListener;

use App\Utils\Sql;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Event\SchemaAlterTableAddColumnEventArgs;
use Doctrine\DBAL\Event\SchemaAlterTableRemoveColumnEventArgs;
use Doctrine\DBAL\Event\SchemaColumnDefinitionEventArgs;
use Doctrine\DBAL\Event\SchemaCreateTableEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

class DoctrineSchemaListener implements EventSubscriber
{
    # TODO: also check for changes in JOIN in virtual columns

    # REF: https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/events.html

    private array $functions = [];

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function onSchemaCreateTable(SchemaCreateTableEventArgs $eventArgs): void
    {
        #TODO: Possibly implement
//        dump('schemaCreateTable');
//
//        $tableName = $eventArgs->getTable()->getName();
//        $columns = $eventArgs->getTable()->getColumns();
//
//        dump("Table: {$tableName}");
//        foreach ($columns as $col) {
//            $columnName = $col->getName();
//            $columnDefinition = $col->getColumnDefinition();
//            $customSchemaOptions = $col->getCustomSchemaOptions();
//
//            if ($customSchemaOptions['virtual'] ?? null) {
//                if (!$this->functionExists($tableName, $columnName)) {
//                    $sqlNeeded = true;
//                } else {
//                    if (!preg_match('/SELECT \((.*)\) INTO/', $this->functions[$tableName][$columnName], $matches)) {
//                        # TODO: error, wrong function definiton
//                    }
//
//                    $sqlNeeded = $matches[1] !== $columnDefinition;
//                }
//
//                if ($sqlNeeded) {
//                    $sql = $this->getCreateFunctionSql($tableName, $col, $customSchemaOptions);
//
//                    $eventArgs->addSql(Sql::escape(Sql::toOneline($sql)));
//                }
//
//                $eventArgs->preventDefault();
//            } else {
//                $eventArgs->addSql(Sql::escape(Sql::toOneline()))
//            }
//        }
    }

    public function onSchemaColumnDefinition(SchemaColumnDefinitionEventArgs $eventArgs): void
    {
    }

    public function onSchemaAlterTableAddColumn(SchemaAlterTableAddColumnEventArgs $eventArgs): void
    {
        $tableName = $eventArgs->getTableDiff()->name;
        $column = $eventArgs->getColumn();
        $columnName = $column->getName();
        $columnDefinition = $column->getColumnDefinition();
        $customSchemaOptions = $column->getCustomSchemaOptions();

        if ($customSchemaOptions['virtual'] ?? null) {
            if (!$this->functionExists($tableName, $columnName)) {
                $sqlNeeded = true;
            } else {
                if (!preg_match('/SELECT \((.*)\) INTO/', $this->functions[$tableName][$columnName], $matches)) {
                    # TODO: error, wrong function definiton
                }

                $sqlNeeded = $matches[1] !== $columnDefinition;
            }

            if ($sqlNeeded) {

                $sql = $this->getCreateFunctionSql($tableName, $column, $customSchemaOptions);

                $eventArgs->addSql(Sql::escape(Sql::toOneline($sql)));
            }

            $eventArgs->preventDefault();
        }
    }

    public function onSchemaAlterTableRemoveColumn(SchemaAlterTableRemoveColumnEventArgs $eventArgs): void
    {
        $tableName = $eventArgs->getTableDiff()->name;
        $column = $eventArgs->getColumn();
        $columnName = $column->getName();
        $columnDefinition = $column->getColumnDefinition();
        $customSchemaOptions = $column->getCustomSchemaOptions();

        if ($customSchemaOptions['virtual'] ?? null) {
            $sql = null;

            if (!$this->functionExists($tableName, $columnName)) {
                $sql = "DROP FUNCTION {$columnName}({$tableName})";
            } else {
                if (!preg_match('/SELECT \((.*)\) INTO/', $this->functions[$tableName][$columnName], $matches)) {
                    # TODO: error, wrong function definiton
                }

                if ($matches[1] !== $columnDefinition) {
                    $sql = $this->getCreateFunctionSql(
                        $tableName,
                        $eventArgs->getColumn()->setColumnDefinition($matches[1]),
                        $customSchemaOptions
                    );
                }
            }

            if ($sql) {
                $eventArgs->addSql(Sql::escape(Sql::toOneline($sql)));
            }
            $eventArgs->preventDefault();
        }
    }

    public function postConnect(ConnectionEventArgs $eventArgs): void
    {
        $functions = $eventArgs->getConnection()->executeQuery('
            SELECT *, pg_get_function_identity_arguments(p.oid) AS tname 
            FROM pg_proc p 
                INNER JOIN pg_namespace n ON n.oid=p.pronamespace 
            WHERE n.nspname=\'public\' 
              AND provolatile = \'s\'
        ')->fetchAllAssociative();

        foreach ($functions as $function) {
            $this->functions[$function['tname']][$function['proname']] = Sql::toOneline($function['prosrc']);
        }
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs): void
    {
        $schema = $eventArgs->getSchema();

        # Removes "CREATE SCHEMA public" from down migrations
        if (!$schema->hasNamespace('public')) {
            $schema->createNamespace('public');
        }
    }

    private function getCreateFunctionSql(string $tableName, Column $column, array $customSchemaOptions): string
    {

        $fqcn = array_values(
            array_filter(
                $this->entityManager->getMetadataFactory()->getAllMetadata(),
                static fn (ClassMetadata $metadata) => $metadata->getTableName() === $tableName
            )
        )[0]->getName();
        $customSchemaOptions = str_replace(['%TABLENAME%', '%FQCN%'], [$tableName, $fqcn], $customSchemaOptions);

        $columnName = $column->getName();
        $columnType = $column->getType();
        $columnTypeName = $columnType->getName();

        $doctrineTypes = (new \ReflectionClass(Types::class))->getConstants();

        if (!in_array($columnTypeName, $doctrineTypes, true)) {
            $columnTypeName = (new \ReflectionClass($columnType::class))->getConstant('DB_TYPE');
        }

        if ($columnTypeName === 'datetime') {
            $columnTypeName = 'timestamp';
        }

        $columnDefinition = $column->getColumnDefinition();

        #TODO: fix for table name 'user'
//        if ($tableName === 'user') {
//            $tableName = '"user"';
//        }

        return "
            CREATE OR REPLACE FUNCTION {$columnName}({$tableName}) RETURNS {$columnTypeName}
                STABLE
                LANGUAGE plpgsql
            AS
            \$\$
            DECLARE
                xx {$columnTypeName};
            BEGIN
                SELECT ({$columnDefinition})
                INTO xx
                FROM {$tableName}
                " . ($customSchemaOptions['join'] ?? '') . "
                WHERE {$tableName}.id = $1.id;
                RETURN xx;
            END;
            \$\$;
        ";
    }

    private function functionExists(string $tableName, string $columnName): bool
    {
        return array_key_exists($tableName, $this->functions)
            && array_key_exists($columnName, $this->functions[$tableName]);
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postConnect,
            Events::onSchemaCreateTable,
            Events::onSchemaColumnDefinition,
            Events::onSchemaAlterTableAddColumn,
            Events::onSchemaAlterTableRemoveColumn,
            ToolEvents::postGenerateSchema,
        ];
    }
}
