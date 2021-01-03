<?php
namespace Htec\Mapper;

use Htec\Core\Validator;
use Htec\Mapper;

final class Route extends Mapper
{
    private const TABLE_CALCULATED_ROUTES = 'calculated_routes';
    private const TABLE_BFS_ROUTES = 'bfs_routes';
    private const TABLE_BFS_INVERTED_ROUTES = 'bfs_routes_inverted';

    protected string $table = 'routes';

    public function getColumnsDefinition()
    {
        return [
            ['column' => 'id', 'name' => 'id', 'type' => Validator::TYPE_INT],
            ['column' => 'airline_id', 'name' => 'airlineId', 'type' => Validator::TYPE_INT, 'unique' => true],
            ['column' => 'source_airport_id', 'name' => 'sourceAirportId', 'type' => Validator::TYPE_INT, 'unique' => true],
            ['column' => 'destination_airport_id', 'name' => 'destinationAirportId', 'type' => Validator::TYPE_INT, 'unique' => true],
            ['column' => 'codeshare', 'name' => 'codeshare', 'type' => Validator::TYPE_STR],
            ['column' => 'stops', 'name' => 'stops', 'type' => Validator::TYPE_INT],
            ['column' => 'equipment', 'name' => 'equipment', 'type' => Validator::TYPE_STR],
            ['column' => 'price', 'name' => 'price', 'type' => Validator::TYPE_FLOAT],
        ];
    }

    public function initialCalculation($originAirportId, $destinationAirportId)
    {
        $this->initializeBFSTable($originAirportId, $destinationAirportId);
        $this->initializeBFSInvertedTable($originAirportId, $destinationAirportId);
        $this->resolveBidirectionalBFSEncounter();
    }

    public function resetCalculationTable()
    {
        $tableName = self::TABLE_CALCULATED_ROUTES;

        $this->execute("drop temporary table if exists {$tableName};");
        $this->execute("create temporary table {$tableName}
        (
            path     varchar(255) NOT NULL DEFAULT '',
            distance double DEFAULT NULL,
            price    double NOT NULL DEFAULT '0',
            KEY tmp_routes_path (path)
        ) ENGINE=MEMORY;");
    }

    private function initializeBFSTable($originAirportId, $destinationAirportId)
    {
        $tableName = self::TABLE_BFS_ROUTES;

        $this->execute("drop temporary table if exists {$tableName};");
        $this->execute("create temporary table {$tableName} ENGINE=MEMORY
        select r.destination_airport_id as currentNode,
               concat(source_airport_id, ',', destination_airport_id) as path,
               CALCULATE_DISTANCE(sa.latitude, sa.longitude, da.latitude, da.longitude) as distance,
               price
        from routes as r
                 inner join airports as sa on sa.id = r.source_airport_id
                 inner join airports as da on da.id = r.destination_airport_id
        where r.source_airport_id = {$originAirportId};");

        $this->execute("create index tmp_routes_node on {$tableName} (currentNode);");
        $this->execute("create index tmp_routes_path on {$tableName} (path);");
    }

    public function iterateBFSTable($originAirportId, $destinationAirportId)
    {
        $tableCalculatedName = self::TABLE_CALCULATED_ROUTES;
        $tableName = self::TABLE_BFS_ROUTES;
        $tableBufferName = $tableName . '_buffer';

        $this->execute("drop temporary table if exists {$tableBufferName};");
        $this->execute("create temporary table {$tableBufferName} like {$tableName};");
        $this->execute("insert into {$tableBufferName} select currentNode, path, distance, min(price) from {$tableName} group by currentNode, path;");
        $this->execute("drop temporary table {$tableName};");
        $this->execute("create temporary table {$tableName} ENGINE=MEMORY select r.destination_airport_id as currentNode,
                                     concat(path, ',', r.destination_airport_id) as path,
                                     tr.distance+CALCULATE_DISTANCE(sa.latitude, sa.longitude, da.latitude, da.longitude) as distance,
                                     tr.price+r.price as price
                              from {$tableBufferName} tr
                                       inner join routes r on tr.currentNode = r.source_airport_id and not find_in_set(r.destination_airport_id, tr.path)
                                       inner join airports as sa on sa.id = r.source_airport_id
                                       inner join airports as da on da.id = r.destination_airport_id having @minprice is null or price < @minprice;");
        $this->execute("create index tmp_routes_node on {$tableName} (currentNode);");
        $this->execute("create index tmp_routes_path on {$tableName} (path);");
        $this->execute("insert into {$tableCalculatedName} select path, distance, price from {$tableName} where currentNode = {$destinationAirportId};");
        $this->execute("delete from {$tableName} where currentNode = {$destinationAirportId};");
        $this->execute("set @minprice = (select MIN(price) from {$tableCalculatedName});");
        $this->execute("delete from {$tableCalculatedName} where price > @minprice;");
        $this->execute("delete from {$tableName} where @minprice is not null and price > @minprice;");
    }

    public function getBFSTableCount()
    {
        return $this->getCount(self::TABLE_BFS_ROUTES);
    }

    private function initializeBFSInvertedTable($originAirportId, $destinationAirportId)
    {
        $tableName = self::TABLE_BFS_INVERTED_ROUTES;

        $this->execute("drop temporary table if exists {$tableName};");
        $this->execute("create temporary table {$tableName} ENGINE=MEMORY
            select r.source_airport_id as currentNode,
                   concat(source_airport_id, ',', destination_airport_id) as path,
                   CALCULATE_DISTANCE(sa.latitude, sa.longitude, da.latitude, da.longitude) as distance,
                   price
            from routes as r
                     inner join airports as sa on sa.id = r.source_airport_id
                     inner join airports as da on da.id = r.destination_airport_id
            where r.destination_airport_id = {$destinationAirportId};");
        $this->execute("create index tmp_routes_inverted_node on {$tableName} (currentNode);");
        $this->execute("create index tmp_routes_inverted_path on {$tableName} (path);");
    }

    public function iterateBFSInvertedTable($originAirportId, $destinationAirportId)
    {
        $tableCalculatedName = self::TABLE_CALCULATED_ROUTES;
        $tableName = self::TABLE_BFS_INVERTED_ROUTES;
        $tableBufferName = $tableName . '_buffer';

        $this->execute("drop temporary table if exists {$tableBufferName};");
        $this->execute("create temporary table {$tableBufferName} like {$tableName};");
        $this->execute("insert into {$tableBufferName} select currentNode, path, distance, min(price) from {$tableName} group by currentNode, path;");
        $this->execute("drop temporary table {$tableName};");
        $this->execute("create temporary table {$tableName} ENGINE=MEMORY select r.source_airport_id as currentNode,
                                      concat(r.source_airport_id, ',', path) as path,
                                      tr.distance+CALCULATE_DISTANCE(sa.latitude, sa.longitude, da.latitude, da.longitude) as distance,
                                      tr.price+r.price as price
                               from {$tableBufferName} tr
                                        inner join routes r on tr.currentNode = r.destination_airport_id and not find_in_set(r.source_airport_id, tr.path)
                                        inner join airports as sa on sa.id = r.source_airport_id
                                        inner join airports as da on da.id = r.destination_airport_id having @minprice is null or price < @minprice;");
        $this->execute("create index tmp_routes_inverted_node on {$tableName} (currentNode);");
        $this->execute("create index tmp_routes_inverted_path on {$tableName} (path);");
        $this->execute("insert into {$tableCalculatedName} select path, distance, price from {$tableName} where currentNode = {$originAirportId};");
        $this->execute("delete from {$tableName} where currentNode = {$originAirportId};");
        $this->execute("set @minprice = (select MIN(price) from {$tableCalculatedName});");
        $this->execute("delete from {$tableCalculatedName} where price > @minprice;");
        $this->execute("delete from {$tableName} where @minprice is not null and price > @minprice;");
    }

    public function getBFSInvertedTableCount()
    {
        return $this->getCount(self::TABLE_BFS_INVERTED_ROUTES);
    }

    public function resolveBidirectionalBFSEncounter()
    {
        $tableCalculatedName = self::TABLE_CALCULATED_ROUTES;
        $tableBRSName = self::TABLE_BFS_ROUTES;
        $tableBFSInvertedName = self::TABLE_BFS_INVERTED_ROUTES;

        $this->execute("set @intersections = (
                                select group_concat(distinct currentNode) 
                                from {$tableBRSName} tr inner join {$tableBFSInvertedName} tri using(currentNode)
                            );");

        $this->execute("insert into {$tableCalculatedName} select
                                              concat(replace(tr.path, concat(',', tr.currentNode), ','), tri.path) path,
                                              tr.distance+tri.distance distance,
                                              tr.price+tri.price price
                                from {$tableBRSName} tr inner join {$tableBFSInvertedName} tri using(currentNode) 
                                where @minprice is null or tr.price + tri.price < @minprice
                                order by price limit 1;");


        $this->execute("delete from {$tableBRSName} where not isnull(@intersections) and find_in_set(currentNode, @intersections);");
        $this->execute("delete from {$tableBFSInvertedName} where not isnull(@intersections) and find_in_set(currentNode, @intersections);");

        $this->execute("set @minprice = (select MIN(price) from {$tableCalculatedName});");
        $this->execute("delete from {$tableCalculatedName} where price > @minprice;");
        $this->execute("delete from {$tableBRSName} where @minprice is not null and price > @minprice;");
        $this->execute("delete from {$tableBFSInvertedName} where @minprice is not null and price > @minprice;");


        $this->deleteExpensiveDuplicates($tableBRSName);
        $this->deleteExpensiveDuplicates($tableBFSInvertedName);
    }

    private function deleteExpensiveDuplicates($tableName)
    {
        $tableNameCheap = $tableName . '_cheap';

        $this->execute("drop temporary table if exists {$tableNameCheap};");
        $this->execute("create temporary table {$tableNameCheap} SELECT
                        currentNode,
                        min(price) minprice
                FROM
                        {$tableName}
                GROUP BY
                        currentNode;");
        $this->execute("DELETE
                r
            FROM
                {$tableName} r
                    INNER JOIN {$tableNameCheap} USING (currentNode)
            where minprice <> price;");
    }

    private function getCount($tableName)
    {
        return $this->fetchScalar("select count(1) from {$tableName};");
    }

    public function fetchCalculatedRoute()
    {
        $tableCalculatedName = self::TABLE_CALCULATED_ROUTES;
        return $this->fetchRow("select * from {$tableCalculatedName};");
    }
}
