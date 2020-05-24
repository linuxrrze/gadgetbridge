<?php
namespace OCA\GadgetBridge;

use Doctrine\DBAL\DBALException;
use OCA\GadgetBridge\Devices\Device;
use OCA\GadgetBridge\Devices\DeviceFactory;
use OCA\GadgetBridge\InvalidDatabaseException;


class Database
{

    private $databasePath;
    public function __construct($path)
    {
        $this->databasePath = $path;

        $this->parseDatabaseForDevices();
    }

    protected function parseDatabaseForDevices()
    {
        // Doctrine doesn't throw an error if the db is invalid.
        // A lower level check: let's see if the first 16 bytes say "SQLite Format"
        // Taken from https://stackoverflow.com/questions/22275154/check-if-a-file-is-a-valid-sqlite-database
        // var_dump($this->databasePath);
        $handle = fopen($this->databasePath, "r");
        // var_dump($handle);
        $contents = fread($handle, 15);
        fclose($handle);

        if ($contents !== "SQLite format 3") {
            throw new InvalidDatabaseException('Unprocessable entity');
        }

        $connection = $this->getDatabaseConnection($this->databasePath);

        $connection->close();

        return true;
    }

    public function getDevices(): array
    {
        $query = $this->getDatabaseConnection()->getQueryBuilder();
        $query->automaticTablePrefix(false);
        $query->select('*')
            ->from('DEVICE');

        $result = $query->execute();
        $devices = [];
        foreach ($result->fetchAll() as $deviceRaw) {
            $devices[] = DeviceFactory::make($deviceRaw, $this);
        }
        return $devices;
    }

    public function getDeviceById(int $deviceId): Device
    {
        $query = $this->getDatabaseConnection()->getQueryBuilder();
        $query->automaticTablePrefix(false);
        $query->select('*')
            ->from('DEVICE')
            ->where($query->expr()->eq('_id', $query->createNamedParameter($deviceId)));
        return DeviceFactory::make($query->execute()->fetch(), $this);
    }

    /**
     * @param string $path
     * @return IDBConnection
     * @throws \InvalidArgumentException
     */
    public function getDatabaseConnection()
    {
        $factory = new \OC\DB\ConnectionFactory(\OC::$server->getSystemConfig());

        try {
            return $factory->getConnection('sqlite3', [
                'user' => '',
                'password' => '',
                'path' => $this->databasePath,
                'sqlite.journal_mode' => 'WAL',
                'tablePrefix' => '',
            ]);
        } catch (DBALException $e) {
            throw new InvalidDatabaseException('Unprocessable entity');
        }
    }
}
 