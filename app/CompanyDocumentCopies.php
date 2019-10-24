<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class CompanyDocumentCopies extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'request_document_copies'; 
}
