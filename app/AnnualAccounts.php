<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class AnnualAccounts extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'annual_accounts';
    public $timestamps = false;
}
