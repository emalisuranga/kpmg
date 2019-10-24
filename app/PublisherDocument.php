<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class PublisherDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tender_publisher_documents';
    public $timestamps = false;
}
