<?php

namespace vikilaboy\activerecord\history\storages;

use Yii;
use vikilaboy\activerecord\history\entities\History;
use vikilaboy\activerecord\history\storages\Base as BaseStorage;
use vikilaboy\activerecord\history\Module;

/**
 * Database storage history of changes.
 * @author Belosludcev Vasilij <https://github.com/vikilaboy>
 * @since 1.0.0
 */
class Database extends BaseStorage
{
    /**
     * @var Module Instance of history module class.
     */
    protected $module;
    
    /**
     * @inhertidoc
     */
    public function init()
    {
        parent::init();
        $this->module = Module::getInstance();
    }
    
    /**
     * @inheritdoc
     */
    public function flush()
    {
        $collection = $this->getCollection();
        if (!empty($collection)) {
            list($sql, $params) = $this->prepareQuery($collection);
            return (bool)$this->module->db->createCommand($sql, $params)->execute();
        }
        return true;
    }
    
    /**
     * Prepare SQL query uses collection data.
     * @param History[] $collection Changes collection of active record models.
     * @return string
     */
    protected function prepareQuery(array $collection)
    {
        $queryBuilder = $this->module->db->getQueryBuilder();
        $sql = [];
        $params = [];
        for ($i = 0; $i != count($collection); $i++) {
            $row = [
                'table_name' => $collection[$i]->tableName,
                'field_name' => $collection[$i]->fieldName,
                'row_id' => $collection[$i]->rowId,
                'old_value' => $collection[$i]->oldValue,
                'new_value' => $collection[$i]->newValue,
                'event' => $collection[$i]->event,
                'created_at' => $collection[$i]->createdAt,
                'created_by' => $collection[$i]->createdBy,
            ];
            $sql[] = $queryBuilder->insert($this->module->tableName, $row, $params);
        }
        return [implode(';' . PHP_EOL, $sql), $params];
    }
}
