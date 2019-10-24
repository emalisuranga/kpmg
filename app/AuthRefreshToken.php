<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class AuthRefreshToken extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'oauth_refresh_tokens';

    public $timestamps = false;
}
