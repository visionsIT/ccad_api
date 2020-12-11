<?php

namespace Modules\Account\Models;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $fillable = [
        'name', 'value', 'table_name', 'description', 'guard_name', 'status'
    ];
    /**
     * @param $table_name
     */
    public static function generateFor($table_name)
    {
        self::firstOrCreate(['name' => 'browse_'.$table_name, 'value' => 'Browse '.$table_name, 'table_name' => $table_name, 'guard_name' => config('auth.defaults.guard')]);
        self::firstOrCreate(['name' => 'read_'.$table_name, 'value' => 'Read '.$table_name, 'table_name' => $table_name, 'guard_name' => config('auth.defaults.guard')]);
        self::firstOrCreate(['name' => 'edit_'.$table_name, 'value' => 'Edit '.$table_name, 'table_name' => $table_name, 'guard_name' => config('auth.defaults.guard')]);
        self::firstOrCreate(['name' => 'add_'.$table_name, 'value' => 'Add '.$table_name, 'table_name' => $table_name, 'guard_name' => config('auth.defaults.guard')]);
        self::firstOrCreate(['name' => 'delete_'.$table_name, 'value' => 'Delete '.$table_name, 'table_name' => $table_name, 'guard_name' => config('auth.defaults.guard')]);
    }

    /**
     * @param $table_name
     */
    public static function removeFrom($table_name)
    {
        self::where(['table_name' => $table_name])->delete();
    }

    /**
     * @param $value
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = str_replace('_', ' ', $value);
    }

    /**
     * @param $value
     */
    public function search_permissions($value)
    {
        self::where(['table_name' => $value])->get();
    }
}
