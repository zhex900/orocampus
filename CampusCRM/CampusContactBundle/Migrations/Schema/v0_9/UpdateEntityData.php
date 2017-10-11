<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 11/10/17
 * Time: 9:11 PM
 */

namespace CampusCRM\CampusContactBundle\Migrations\Schema\v0_9;

use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateEntityData extends ParametrizedMigrationQuery
{

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->migrateConfigs($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->migrateConfigs($logger);
    }

    /**
     * Change config show_step_in_grid to false
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function migrateConfigs(LoggerInterface $logger, $dryRun = false)
    {
        $query  = 'SELECT c.id, c.data'
            . ' FROM oro_entity_config c'
            . ' WHERE c.class_name = :class_name';

        $params = ['class_name' => 'Oro\\Bundle\\ContactBundle\\Entity\\Contact'];
        $types  = ['field' => Type::STRING];

        $this->logQuery($logger, $query, $params, $types);

        $updateQueries = [];

        // prepare update queries
        $rows = $this->connection->fetchAll($query, $params, $types);

        foreach ($rows as $row) {
            $id = $row['id'];
            $data = $this->connection->convertToPHPValue($row['data'], 'array');

            if (isset($data['workflow']['show_step_in_grid'])) {
                $data['workflow']['show_step_in_grid'] = 0;

                $value = $this->connection->convertToDatabaseValue($data, Type::TARRAY);

                $updateQueries[] = [
                    'UPDATE oro_entity_config SET data = :data WHERE id = :id',
                    ['id' => $id, 'data' => $value],
                    ['id' => Type::INTEGER, 'data' => Type::STRING]
                ];
            }
        }

        // execute update queries
        foreach ($updateQueries as $val) {
            $this->logQuery($logger, $val[0], $val[1], $val[2]);
            if (!$dryRun) {
                $this->connection->executeUpdate($val[0], $val[1], $val[2]);
            }
        }
    }
}
