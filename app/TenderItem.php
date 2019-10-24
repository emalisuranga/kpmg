<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class TenderItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tender_items';
}
