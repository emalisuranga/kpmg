<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SettingType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'setting_types';

    protected $primaryKey = "id";

    public $timestamps = false;

}
