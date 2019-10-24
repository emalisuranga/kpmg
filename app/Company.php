<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Company extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'companies';

    protected $primaryKey = "id";

    protected $fillable = [
        'id','type_id','name','name_si','name_ta','postfix','abbreviation_desc','is_name_change','address_id',
        'email','objective','status', 'user_comment' ,'created_by', 'name_resavation_at', 'incorporation_at'
    ];
}
