<?php

namespace App\Http\Controllers\API\v1\Payment;

trait NamePriceList
{

    private $TaxPercentage;
    private $ValueOfName;

    public function __construct()
    {
        $this->TaxPercentage = floatval($this->settings('GOV_VAT', 'key')->value);
        $this->ValueOfName = floatval($this->settings('NAME_RESERVATION_FEE', 'key')->value);
    }

    public function getTaxPercentage()
    {
        return $this->format($this->TaxPercentage);
    }

    public function getValueOfName() 
    {
        return $this->format($this->ValueOfName);
    }

    public function getTaxOfName()
    {
        return $this->format($this->ValueOfName * ($this->TaxPercentage / 100));
    }

    public function getValuetOfName()
    {
        return $this->format($this->ValueOfName * ($this->TaxPercentage + 100) / 100);
    }

    public function format($value)
    {
        return number_format($value, 2); 
    }

    public function getDescription(){
        return 'For approval of a name of a company (Name Request)';
    }

}
