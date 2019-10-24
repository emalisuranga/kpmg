<?php

namespace App\Http\Controllers\API\v1\Payment;

trait PriceList
{

    private $TaxPercentage;
    private $ValueOfName;

    public function __construct()
    {
        $this->TaxPercentage = floatval($this->settings('PAYMENT_GOV_VAT', 'key')->value);
    }

    public function getTaxPercentage()
    {
        return $this->format($this->TaxPercentage);
    }

    // Sub Order

    public function getUnitPrice($type) 
    {
        return $this->format(floatval($this->settings($type, 'key')->value));
    }

    public function getSubTotal($type, $item)
    {
        if($type == 'PAYMENT_PENALTY_LIMITED_DATE'){
             if($this->format($this->getUnitPrice($type) * round($item)) > $this->settings('PENALTY_LIMITED_MAX_AMOUNT', 'key')->value){
                return $this->settings('PENALTY_LIMITED_MAX_AMOUNT', 'key')->value;
             }
        }
        return $this->format($this->getUnitPrice($type) * round($item));
    }

    public function getItemTaxAmount()
    {
        return $this->format('0.00');
    }

    public function getItemTotalAmount($subtotal, $tax)
    {
        return $this->format($subtotal +  $tax);
    }

    // public function getConveniencefee($total)
    // {
    //     return $this->format($total * 1.02 );
    // }

    // Main Order 

    public function getOrderTaxAmount($total)
    {
        return $this->format($total * ($this->TaxPercentage / 100));
    }

    public function getOrderTotalAmount($tax, $subtotal)
    {
        return $this->format($tax + $subtotal);
    }

    // public function getTotalAmount($type, $item)
    // {
    //     return $this->format(($item * $this->getItemValue($type)) * ($this->TaxPercentage + 100) / 100));
    // }

    public function format($value)
    {
        return str_replace(',', '', number_format($value, 2)); 
    }

    public function getDescription($type){
        return $this->settings($type, 'key')->value;
    }

}
