<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SocietyDocumentStatus extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'society_documents_status';
}
