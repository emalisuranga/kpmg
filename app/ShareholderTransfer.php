<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ShareholderTransfer extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'annual_share_transfer';
    public $timestamps = false;
}
