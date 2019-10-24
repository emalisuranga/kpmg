<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ShareholdersDetails extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'company_shareholders_details';

    protected $primaryKey = "id";
}
