<?php

/**
 * @return mixed
 */
function getAllDatabaseTablesName()
{
    //$tables = \DB::connection()->getDoctrineSchemaManager()->listTableNames();
    return \DB::select('SHOW TABLES');
}