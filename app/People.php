<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Secretary;
use Auditor;
use OwenIt\Auditing\Contracts\Auditable;
class People extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'people';

    protected $fillable = [
        'id','title','first_name','last_name','other_name','profile_pic','address_id','foreign_address_id','nic','passport_no','passport_issued_country',
        'telephone','mobile','email','occupation','status','is_srilankan'
    ];
    
    public function Secretary()
    {
        return $this->belongsTo('App\Secretary');
    }
    public function Auditor()
    {
        return $this->belongsTo('App\Auditor');
    }
}
