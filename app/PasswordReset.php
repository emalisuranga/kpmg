<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class PasswordReset extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'password_resets';

    protected $primaryKey = 'email';

    protected $fillable = [
        'email', 'token'
    ];
    
    const UPDATED_AT = null;
}
