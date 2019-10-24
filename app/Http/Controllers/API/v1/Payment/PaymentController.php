<?php

namespace App\Http\Controllers\API\v1\Payment;

use App\Company;
use App\Http\Controllers\API\v1\Payment;
use App\Http\Controllers\Controller;
use App\Http\Helper\_helper;
use App\lib\LGPSClient;
use App\Order;
use App\OrderItem;
use App\Payment as Transection;
use App\User;
use Illuminate\Http\Request;
use PDF;
use Storage;
use App\NameRenewel;
use App\Tender;
use App\TenderApplication;
use App\TenderApplicantItem;
use App\Secretary;
use App\SecretaryFirm;
use App\Auditor;
use App\AuditorFirm;
use App\Society;
use App\ChangeName;
use App\ChangeAddress;
use App\Address;
use App\TenderRenewalReRegistration;
use App\CompanyPublicRequest;
use App\CompanyChangeRequestItem;
use App\SecretaryCertificateRequest;
use App\AnnualReturn;
use App\AuditorRenewal;
use App\ShareCalls;
use App\RegisterOfCharges;
use App\Charges;
use App\AppointmentOfAdmins;
use App\CompanyNotices;
use App\OverseasAlteration;
use App\OffshoreAlteration;
use App\OverseaseOfNameChange;
use App\Form9;
use App\ProspectusRegistration;
 use App\ReductionStatedCapital;
use App\AnnualAccounts;
use App\SpecialResolution;
use App\CompanyCertificate;
use App\ShareClasses;

class PaymentController extends Controller
{
    use _helper, PriceList;

    private $company;

    private static $RSeed = 0;

    private $service_code = "";

    private $otx_amount = "";

    private $tax = "";

    private $tax_price = "";

    private $tx_amount = "100";

    private $tx_ref_no = "";

    private $description = "";

    private $cipher_message = null;

    private $success = '';

    private $email = '';

    /*
    |--------------------------------------------------------------------------
    | Generate Random Number for ransaction Ref - LGPS Sample PHP Client Module
    |--------------------------------------------------------------------------
     */

    public static function seed($s = 0)
    {
        self::$RSeed = abs(intval($s)) % 9999999 + 1;
        self::num();
    }

    // public static function num($min = 0, $max = 9999999)
    // {
    //     if (self::$RSeed == 0) {
    //         self::seed(mt_rand());
    //     }

    //     self::$RSeed = (self::$RSeed * 125) % 2796203;
    //     return self::$RSeed % ($max - $min + 1) + $min;
    // }

    // public function getRandomNumber()
    // {
    //     $timestamp = time();
    //     $this->seed($timestamp);
    //     return config('lgps.ref_char').$this->num(1, 1000000);
    // }

    public function getRandomNumber()
    {
        return config('lgps.ref_char') . $this->getNumberSequence('PAYMENT_REFERENCE_NUMBER')->next_no;
    }

    public function getServiceCode()
    {
        return config('lgps.lgps_service_code');
    }

    /*
    |--------------------------------------------------------------------------
    | Payment For Transaction with generate invoice and update Company table
    |--------------------------------------------------------------------------
     */

    public function payMyProduct(Request $request, LGPSClient $lgpsClientReq)
    {
        \DB::beginTransaction();
        try {
            $this->extraPayment($request);
            $user = null;
            if ($request->email != null) {
                $user = $this->getAuthUser($request->email);
            }

            $order = new Order();
            $order->module = $this->settings($request->payment['module_type'], 'key')->id;
            $order->module_id = $request->payment['module_id'];
            $order->description = $request->payment['description'];
            $order->created_by = $user == null ? null : $user->userid;
            $order->status = $this->settings('COMMON_STATUS_ACTIVE', 'key')->id;
            $order->save();


            foreach ($request->payment['item'] as $key => $value) {
                $quantity = round($value['quantity']);
                $OrderItem = new OrderItem();
                $unit_price = $this->getUnitPrice($value['fee_type']);
                $subtotal = $this->getSubTotal($value['fee_type'], $quantity);
                $tax = $this->getItemTaxAmount();
                $total = $this->getItemTotalAmount($subtotal, $tax);

                $OrderItem->order_id = $order->id;
                $OrderItem->item_name = $value['description'];
                $OrderItem->quantity = $quantity;
                $OrderItem->unit_price = $unit_price;
                $OrderItem->subtotal = $subtotal;
                $OrderItem->tax = $tax;
                $OrderItem->total = $total;
                $OrderItem->save();
            }

            $total = OrderItem::where('order_id', $order->id)
                ->sum('total');

            $orderTax = $this->getOrderTaxAmount($total);
            $orderSubtotal =  $total;
            
            $penalty = 0;
            $penalty_without_tax = 0;
            if(isset($request->payment['penalty']) && floatval($request->payment['penalty'])) {
                $penalty = floatval($request->payment['penalty']);
                $penalty_without_tax = $penalty;
                $penalty = $penalty + $this->getOrderTaxAmount($penalty);
                $orderTax = $orderTax + $this->getOrderTaxAmount($penalty);
                
            }

            $delevery_option = null;
            $delvery_charge = 0;
            $delvery_charge_without_tax = 0;

            if(
                isset($request->payment['delevery_option']) &&
                isset($this->settings($request->payment['delevery_option'], 'key')->value)  &&
                floatval($this->settings($request->payment['delevery_option'], 'key')->value)
               
            ) {

                if($request->payment['delevery_option'] == 'DELEVERY_SELF_COLLECTION'){
                    $delevery_option = 'Self-collection';
                }else if($request->payment['delevery_option'] == 'DELEVERY_REGISTERED_POST') {
                    $delevery_option = 'Registered Post';
                }else if($request->payment['delevery_option'] == 'DELEVERY_FAST_COURIER') {
                    $delevery_option = 'Fast courier';
                }else {
                    $delevery_option = null;
                }


                $delvery_charge = floatval($this->settings($request->payment['delevery_option'], 'key')->value);
                $delvery_charge_without_tax = $delvery_charge;
                $delvery_charge = $delvery_charge + $this->getOrderTaxAmount($delvery_charge);
                $orderTax = $orderTax + $this->getOrderTaxAmount($delvery_charge);
            }


            $totalPrice = $this->getOrderTotalAmount($orderTax + $penalty + $delvery_charge, $orderSubtotal);

            $order = Order::find($order->id);
            $order->tax = $orderTax;
            $order->subtotal = $orderSubtotal;
            $order->total = $totalPrice;
          //  $order->penalty = $penalty;
            $order->penalty = $penalty_without_tax;
            $order->delevery_option = $delevery_option;
          //  $order->delevery_charge = $delvery_charge;
            $order->delevery_charge = $delvery_charge_without_tax;
            if ($order->save()) {
                $this->getCipherToken($order->id, $totalPrice, $lgpsClientReq);
                \DB::commit();
                return response()->json(['token' => $lgpsClientReq->getPaymentRequest()], 200);
            } else {
                \DB::rollBack();
                return response()->json(['error' => 'Unsuccess your Order Process'], 200);
            }
        } catch (\ErrorException $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function getCipherToken($orderId, $totalPrice, $lgpsClientReq)
    {
        if (config('lgps.lgps_on_live_mode') == true) {
            $this->tx_amount =   str_replace(',', '', $totalPrice);
        }

        // $this->tx_amount =   str_replace(',', '', $totalPrice);
        $this->tx_ref_no = $this->getRandomNumber();

        $this->setCertificate($lgpsClientReq);

        $lgpsClientReq->setServiceCode($this->getServiceCode()); //Set Service code to Payment request
        $lgpsClientReq->setTransactionRefNo($this->tx_ref_no); //Set Transaction Ref No. to Payment request
        $lgpsClientReq->setTransactionAmount($this->tx_amount); //Set Transaction Amount to Payment request
        $lgpsClientReq->setReturnURL(env('APP_URL', '') . '/eroc/payment/success'); //Set Return URL to Payment request

        $pay = new Transection();
        $pay->ref_no = $this->tx_ref_no;
        $pay->order_id = $orderId;
        $pay->invoice_no = $this->genarateInvoiceNumber();
        $pay->transection_status = false;
        $pay->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Response
    |--------------------------------------------------------------------------
     */

    public function setPaymentSuccess(Request $request, LGPSClient $lgpsClientRes)
    {
        try {
            $reqToken = $request->lgpsPaymentResponse;
            if (!is_null($reqToken)) {

                $this->setCertificate($lgpsClientRes);

                $lgpsClientRes->setPaymentResponse($reqToken); // Set Payment response

                $receivedPaymentGatewayName = $lgpsClientRes->getReceivedPaymentGateway(); //Extract received Payment Gateway Name
                $receivedTransactionRefNo = $lgpsClientRes->getReceivedTransactionRefNo(); //Extract received transaction ref no.
                $receivedTransactionStatus = $lgpsClientRes->getReceivedTransactionStatus(); //Extract received transaction status
                $convenienceFee = $lgpsClientRes->getConvenienceFee();

                $this->success = $this->paymentForName($receivedTransactionRefNo, $convenienceFee, $receivedPaymentGatewayName, $receivedTransactionStatus);
            }

            return redirect(env('FRONT_APP_URL', '') . '/success/payment' . $this->success);
        } catch (LGPSException $e) {
            $error = true;
            Logger::getInstance()->error("Exception Occured: " . $e->getMessage());
        }
    }

    public function paymentForName($ref_no, $convenience_fee, $gateway_name, $transection_status)
    {
        \DB::beginTransaction();
        try {

            $inArray = array('MODULE_NAME_RESERVATION', 'MODULE_INCORPORATION', 'MODULE_NAME_RENEWAL','MODULE_NAME_CHANGE');

            if ($transection_status == 'true') {

                $pay = Transection::find($ref_no);

                if ($pay) {

                    $invoiceNumber = $pay->invoice_no;
                    $oritem = Order::find($pay->order_id);
                    if (!is_null($oritem)) {

                        if ( isset($this->settings($oritem->module, 'id')->key) && in_array($this->settings($oritem->module, 'id')->key, $inArray)) {
                            $this->company = Company::find($oritem->module_id);
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_REDUCTION_OF_STATED_CAPITAL') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_SPECIAL_RESOLUTION') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_APPOINTMENT_OF_ADMIN') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }
                        

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_COMPANY_ISSUE_OF_SHARES') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }
                        
                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_CALLS_ON_SHARES') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_COMPANY_ADDRESS_CHANGE') {
                            $request = ChangeAddress::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->type_id) ? Company::find($request->type_id) : null;
                        }
                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_COMPANY_ACCOUNTING_ADDRESS_CHANGE') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }
                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_COMPANY_RECORDS_REGISTER_ADDRESS_CHANGE') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }
                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_COMPANY_BALANCE_SHEET_DATE_CHANGE') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }
                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_COMPANY_SATISFACTION_CHARGE_CHANGE') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_PROSPECTUS_OF_REG') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_ANNUAL_ACCOUNTS') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_PUBLIC_REQUEST_DOCUMENTS') {
                            $request = CompanyPublicRequest::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }
                        
                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_OTHERS_COURT_ACCOUNTS') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_CHARGES_REGISTRATION') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_STATEMENT_OF_AFFAIRS') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_OVERSEAS_ALTERATIONS') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_OFFSHORE_ALTERATIONS') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_OVERSEAS_NAME_CHANGE') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_DIR_SEC_CHANGE') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();
                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;

                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'MODULE_COMPANY_ISSUE_OF_DEBENTURES') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'OVERSEAS_STRIKE_OFF') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }

                        if(isset($this->settings($oritem->module, 'id')->key) && $this->settings($oritem->module, 'id')->key == 'OFFSHORE_STRIKE_OFF') {
                            $request = CompanyChangeRequestItem::where('id', $oritem->module_id)->first();

                            $this->company = isset($request->company_id) ? Company::find($request->company_id) : null;
                        }



                        

                        /*** get company certificate number */

                        if(isset($this->settings($oritem->module, 'id')->key) && ( $this->settings($oritem->module, 'id')->key == 'MODULE_NAME_RESERVATION' || $this->settings($oritem->module, 'id')->key == 'MODULE_NAME_CHANGE' ) ) {
                            $changeTable = ChangeName::where('new_company_id', $oritem->module_id)
                                        ->first();

                            if(isset($changeTable->old_company_id)) {
                            $certificate_info = CompanyCertificate::where('company_id', $changeTable->old_company_id)->first();
                            $certificate_no = isset($certificate_info->registration_no) ? $certificate_info->registration_no : '';
                            } else {
                                $certificate_no = '';
                            }

                        } else {

                            if(isset($this->company->id)) {
                                $certificate_info = CompanyCertificate::where('company_id', $this->company->id)->first();
                                $certificate_no = isset($certificate_info->registration_no) ? $certificate_info->registration_no : '';
                            } else {
                                $certificate_no = '';
                            }


                        }
                    /*** end get company certificate number */

                        
                        
                        

                        

                        $storagePath = 'invoice/' . date('Y') . '/' . date('m') . '/' . $invoiceNumber . '.pdf';

                        $pdf = PDF::loadView('vendor.invoice.name-reservation-invoice', $this->buildArray($oritem, $pay, $convenience_fee, $certificate_no));
                        // $pdf->mpdf->showWatermarkImage = true;
                        // $pdf->mpdf->SetWatermarkImage(public_path('images\logos\e-roc-bottom.png'), 0.2);
                        // $pdf->mpdf->SetAutoPageBreak(false, 0);
                        $content = $pdf->output();
                        $path = Storage::disk('sftp')->put($storagePath, $content);

                        $pay->convenience_fee = $convenience_fee;
                        $pay->gateway_name = $gateway_name;
                        $pay->path = $storagePath;
                        $pay->file_token = md5(uniqid());
                        $pay->transection_status = $transection_status;

                        if ($pay->save()) {
                            if ($this->company) {
                                if ($oritem->module  == $this->settings('MODULE_NAME_RESERVATION', 'key')->id) {
                                    $this->company->status = $this->settings('COMPANY_NAME_PENDING', 'key')->id;

                                    $changeTable = ChangeName::where('new_company_id', $oritem->module_id)
                                        ->where('status', $this->settings('COMPANY_NAME_PROCESSING', 'key')->id)
                                        ->first();

                                    if ($changeTable != null) {
                                        $changeTable->status =  $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id;
                                        $changeTable->save();

                                        $oldCom = Company::find($changeTable->old_company_id);
                                        $oldCom->status = $this->settings('COMPANY_NAME_CHANGE_PROCESSING', 'key')->id;
                                        $oldCom->save();
                                    }
                                }

                                if ($oritem->module  == $this->settings('MODULE_NAME_RENEWAL', 'key')->id) {
                                    $this->company->name_renew_at = date('Y-m-d');
                                }

                                if ($oritem->module  == $this->settings('MODULE_INCORPORATION', 'key')->id) {
                                    if ($this->company->status == $this->settings('COMPANY_FOREIGN_STATUS_PAYMENT_PENDING', 'key')->id) {
                                        $this->company->status = $this->settings('COMPANY_FOREIGN_STATUS_PAYMENT_DONE', 'key')->id;
                                    } else {
                                        $this->company->status = $this->settings('COMPANY_STATUS_PENDING', 'key')->id;
                                    }
                                }
                                $this->company->save();
                            }


                            $module = $oritem->module;
                            switch ($module) {
                                case $this->settings('MODULE_TENDER', 'key')->id:
                                    TenderApplication::where('id', $oritem->module_id)->update(['status' => $this->settings('TENDER_PENDING', 'key')->id]);
                                    TenderApplicantItem::where('tender_application_id', $oritem->module_id)->update(['status' => $this->settings('TENDER_ITEM_APPLIED', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_TENDER_AWARDING', 'key')->id:
                                    TenderApplicantItem::where('tender_item_id', $oritem->module_id)->update(['status' => $this->settings('TENDER_PCA2_SUBMITED', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_SECRETARY', 'key')->id:
                                    Secretary::where('id', $oritem->module_id)->update(['status' => $this->settings('SECRETARY_PENDING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_SECRETARY_FIRM', 'key')->id:
                                    SecretaryFirm::where('id', $oritem->module_id)->update(['status' => $this->settings('SECRETARY_PENDING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_AUDITOR', 'key')->id:
                                    Auditor::where('id', $oritem->module_id)->update(['status' => $this->settings('AUDITOR_PENDING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_AUDITOR_FIRM', 'key')->id:
                                    AuditorFirm::where('id', $oritem->module_id)->update(['status' => $this->settings('AUDITOR_PENDING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_SOCIETY', 'key')->id:
                                    Society::where('id', $oritem->module_id)->update(['status' => $this->settings('SOCIETY_PENDING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_SOCIETY_BULK', 'key')->id:
                                    Society::where('bulk_id', $oritem->module_id)
                                            ->where('status',$this->settings('SOCIETY_PROCESSING', 'key')->id )
                                            ->update(['status' => $this->settings('SOCIETY_PENDING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_TENDER_RENEWAL_PCA3', 'key')->id:
                                    TenderRenewalReRegistration::where('id', $oritem->module_id)->update(['status' => $this->settings('TENDER_RENEWAL_PCA3_SUBMITTED', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_TENDER_RENEWAL_PCA4', 'key')->id:
                                    TenderRenewalReRegistration::where('id', $oritem->module_id)->update(['status' => $this->settings('TENDER_RENEWAL_PCA4_SUBMITTED', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_TENDER_REREGISTRATION_PCA3', 'key')->id:
                                    TenderRenewalReRegistration::where('id', $oritem->module_id)->update(['status' => $this->settings('TENDER_REREGISTRATION_PCA3_SUBMITTED', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_TENDER_REREGISTRATION_PCA4', 'key')->id:
                                    TenderRenewalReRegistration::where('id', $oritem->module_id)->update(['status' => $this->settings('TENDER_REREGISTRATION_PCA4_SUBMITTED', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_NAME_CHANGE', 'key')->id:
                                   // $changeTable = ChangeName::find($oritem->module_id);
                                    $changeTable = ChangeName::where('new_company_id',$oritem->module_id )->first();

                                    if ($changeTable != null) {
                                        $changeTable->status =  $this->settings('COMPANY_NAME_CHANGE_PENDING', 'key')->id;
                                        if ($changeTable->save()) {
                                            $oldCom = Company::find($changeTable->new_company_id);
                                            $oldCom->status = $this->settings('COMPANY_NAME_CHANGE_PENDING', 'key')->id;
                                            $oldCom->save();
                                        }
                                    }

                                    break;
                                case $this->settings('MODULE_COMPANY_ADDRESS_CHANGE', 'key')->id:
                                    ChangeAddress::where('id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_ADDRESS_CHANGE_PENDING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_PUBLIC_REQUEST_DOCUMENTS', 'key')->id:
                                    CompanyPublicRequest::where('id', $oritem->module_id)->update(['status' => $this->settings('PRINT_PROCESSING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_COMPANY_ACCOUNTING_ADDRESS_CHANGE', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('ACCOUNTING_ADDRESS_CHANGE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_ISSUING_CERTIFIED_COPIES_OF_SECRETARIES', 'key')->id:
                                    SecretaryCertificateRequest::where('id', $oritem->module_id)->update(['status' => $this->settings('PRINT_PROCESSING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_ANNUAL_RETURN', 'key')->id:
                                    AnnualReturn::where('request_id', $oritem->module_id)->update(['status' => $this->settings('ANNUAL_RETURN_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('ANNUAL_RETURN_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_CALLS_ON_SHARES', 'key')->id:
                                    ShareCalls::where('request_id', $oritem->module_id)->update(['status' => $this->settings('CALLS_ON_SHARES_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('CALLS_ON_SHARES_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_DIR_SEC_CHANGE', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_CHANGE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_COMPANY_ISSUE_OF_DEBENTURES', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_CHANGE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_AUDITOR_RENEWAL', 'key')->id:
                                    AuditorRenewal::where('auditor_id', $oritem->module_id)->update(['status' => $this->settings('AUDITOR_RENEWAL_PENDING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_AUDITOR_RENEWAL_FIRM', 'key')->id:
                                    AuditorRenewal::where('firm_id', $oritem->module_id)->update(['status' => $this->settings('AUDITOR_RENEWAL_PENDING', 'key')->id]);
                                    break;
                                case $this->settings('MODULE_COMPANY_BALANCE_SHEET_DATE_CHANGE', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('BALANCE_SHEET_DATE_CHANGE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_COMPANY_RECORDS_REGISTER_ADDRESS_CHANGE', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('RECORDS_REGISTER_ADDRESS_CHANGE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_COMPANY_SATISFACTION_CHARGE_CHANGE', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_CHANGE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;        
                                case $this->settings('MODULE_COMPANY_ISSUE_OF_SHARES', 'key')->id:
                                    ShareClasses::where('request_id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_ISSUE_OF_SHARES_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_ISSUE_OF_SHARES_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_REGISTER_OF_CHARGES', 'key')->id:
                                    RegisterOfCharges::where('request_id', $oritem->module_id)->update(['status' => $this->settings('REGISTER_OF_CHARGES_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('REGISTER_OF_CHARGES_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_CHARGES_REGISTRATION', 'key')->id:
                                    Charges::where('request_id', $oritem->module_id)->update(['status' => $this->settings('CHARGES_REGISTRATION_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('CHARGES_REGISTRATION_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_REDUCTION_OF_STATED_CAPITAL', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_CHANGE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    ReductionStatedCapital::where('request_id', $oritem->module_id)->update(['status' => $this->settings('COMMON_STATUS_ACTIVE', 'key')->id]);
                                    break;
                                    
                                case $this->settings('MODULE_OVERSEAS_NAME_CHANGE', 'key')->id:
                                    OverseaseOfNameChange::where('request_id', $oritem->module_id)->update(['status' => $this->settings('OVERSEAS_NAME_CHANGE_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('OVERSEAS_NAME_CHANGE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    
                                    
                                    $oversease_record = OverseaseOfNameChange::where('request_id', $oritem->module_id)->first();

                                    $changeTable = ChangeName::where('id',$oversease_record->change_id )->first();

                                    if ($changeTable != null) {
                                        $changeTable->status =  $this->settings('COMPANY_NAME_CHANGE_PENDING', 'key')->id;
                                        if ($changeTable->save()) {
                                            $oldCom = Company::find($changeTable->new_company_id);
                                            $oldCom->status = $this->settings('COMPANY_NAME_CHANGE_PENDING', 'key')->id;
                                            $oldCom->save();
                                        }
                                    }

                                    
                                    
                                    break;

                                case $this->settings('MODULE_APPOINTMENT_OF_ADMIN', 'key')->id:
                                    AppointmentOfAdmins::where('request_id', $oritem->module_id)->update(['status' => $this->settings('APPOINTMENT_OF_ADMIN_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('APPOINTMENT_OF_ADMIN_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                    
                                case $this->settings('MODULE_COMPANY_NOTICE', 'key')->id:
                                    CompanyNotices::where('request_id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_NOTICE_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_NOTICE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;

                                case $this->settings('MODULE_OVERSEAS_ALTERATIONS', 'key')->id:
                                    OverseasAlteration::where('request_id', $oritem->module_id)->update(['status' => $this->settings('OVERSEAS_ALTERATIONS_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('OVERSEAS_ALTERATIONS_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;

                                case $this->settings('MODULE_OFFSHORE_ALTERATIONS', 'key')->id:
                                    OffshoreAlteration::where('request_id', $oritem->module_id)->update(['status' => $this->settings('OFFSHORE_ALTERATIONS_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('OFFSHORE_ALTERATIONS_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                
                                case $this->settings('MODULE_COMPANY_SHARE_FORM9', 'key')->id:
                                    Form9::where('request_id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_SHARE_FORM9_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_SHARE_FORM9_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;

                                case $this->settings('MODULE_PROSPECTUS_OF_REG', 'key')->id:
                                    ProspectusRegistration::where('request_id', $oritem->module_id)->update(['status' => $this->settings('PROSPECTUS_OF_REG_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('PROSPECTUS_OF_REG_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_ANNUAL_ACCOUNTS', 'key')->id:
                                    AnnualAccounts::where('request_id', $oritem->module_id)->update(['status' => $this->settings('ANNUAL_ACCOUNTS_PENDING', 'key')->id]);
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('ANNUAL_ACCOUNTS_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                case $this->settings('MODULE_OTHERS_COURT_ACCOUNTS', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('OTHERS_COURT_ORDER_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;

                                case $this->settings('MODULE_SPECIAL_RESOLUTION', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('COMPANY_CHANGE_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    SpecialResolution::where('request_id', $oritem->module_id)->update(['status' => $this->settings('COMMON_STATUS_ACTIVE', 'key')->id]);
                                    break;
                                
                                case $this->settings('MODULE_STATEMENT_OF_AFFAIRS', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('STATEMENT_OF_AFFAIRS_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                    
                                case $this->settings('OVERSEAS_STRIKE_OFF', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('OVERSEAS_STRIKE_OFF_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;

                                case $this->settings('OFFSHORE_STRIKE_OFF', 'key')->id:
                                    CompanyChangeRequestItem::where('id', $oritem->module_id)->update(['status' => $this->settings('OFFSHORE_STRIKE_OFF_PENDING', 'key')->id, 'created_at' => date('Y-m-d H:i:s', time())]);
                                    break;
                                
                                
                                    // default: 
                                    // echo "";
                            }

                            $this->success = md5(uniqid(rand(), true)) . '-' . $pay->file_token;
                            $token = $this->setSecToken($this->email, $this->success, $this->settings('TOKEN_PAYMENT_SUCCESS', 'key')->id);
                            if (!is_null($token)) {
                                $this->success = '?token=' . $token->token;
                            };
                            $this->ship($this->email, 'invoice', null, null, $content, 'invoice.pdf', 'Receipt');
                        }
                    }
                }
            }
            \DB::commit();
            return $this->success;
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }

    public function buildArray($data, $pay, $convenience_fee, $certificate_no = '')
    {
        $tenderNumber = null;
        $tenderName = null;
        $orderNumber = null;

        $moduleArray = array(
            $this->settings('MODULE_TENDER', 'key')->id,
            $this->settings('MODULE_TENDER_AWARDING', 'key')->id
        );

        $moduleTenderRenewalArray = array(
            $this->settings('MODULE_TENDER_RENEWAL_PCA3', 'key')->id,
            $this->settings('MODULE_TENDER_RENEWAL_PCA4', 'key')->id,
            $this->settings('MODULE_TENDER_REREGISTRATION_PCA3', 'key')->id,
            $this->settings('MODULE_TENDER_REREGISTRATION_PCA4', 'key')->id
        );

        if (in_array($data->module, $moduleArray) || in_array($data->module, $moduleTenderRenewalArray)) {

            if (in_array($data->module, $moduleTenderRenewalArray)) {
                $renderReinfor = TenderRenewalReRegistration::find($data->module_id);
                if ($renderReinfor) {
                    $user = TenderApplication::leftjoin('tenders', 'tender_applications.tender_id', '=', 'tenders.id')
                        ->where('tender_applications.id', '=', $renderReinfor->tender_application_id)->first();
                    $orderNumber = $user->number;
                } else {
                    return;
                }
            }

            if ($data->module  == $this->settings('MODULE_TENDER', 'key')->id) {
                $user = TenderApplication::leftjoin('tenders', 'tender_applications.tender_id', '=', 'tenders.id')
                    ->where('tender_applications.id', '=', $data->module_id)->first();
                $orderNumber = $user->number;
            }

            if ($data->module  == $this->settings('MODULE_TENDER_AWARDING', 'key')->id) {
                $user = TenderApplicantItem::leftjoin('tender_applications', 'tender_application_items.tender_application_id', '=', 'tender_applications.id')
                    ->leftjoin('tender_items', 'tender_application_items.tender_item_id', '=', 'tender_items.id')
                    ->leftjoin('tenders', 'tender_applications.tender_id', '=', 'tenders.id')
                    ->where('tender_application_items.tender_item_id', '=', $data->module_id)
                    ->select('tender_items.number as tender_number', 'tenders.number as number', 'tender_items.name as name', 'applicant_fullname', 'applicant_email', 'applicant_address', 'tenderer_address')
                    ->first();

                $orderNumber = $user->number;
                $tenderNumber = $user->tender_number;
                $tenderName = $user->name;
            }

            $this->email = $user->applicant_email;

            $addModuleName = '';
            $address1 = $user->applicant_address == null ? $user->tenderer_address : $user->applicant_address;
            $address2 = null;
            $city = null;
            $district = null;
            $province = null;
            $country = null;
            $postcode = null;
            $mobileNumber = null;
            $TelephoneNumber = null;
            $username = $user->applicant_fullname;
        } else {

            $addModuleName = '';
            $this->email = User::find($data->created_by)->email;
            $user = $this->getAuthUser($this->email);

            if ($user->is_srilankan == 'yes') {

                $address1 = $user->address1;
                $address2 = $user->address2;
                $city = $user->city;
                $district = $user->district;
                $province = $user->province;
                $country = $user->country;
                $postcode = $user->postcode;
                $mobileNumber = $user->mobile;
                $TelephoneNumber = $user->telephone;
                $username = $user->title . $user->first_name . ' ' . $user->last_name;
            } else {
                $address = Address::where('id', $user->foreign_address_id)->first();

                $address1 = $address->address1;
                $address2 = $address->address2;
                $city = $address->city;
                $district = $address->district;
                $province = $address->province;
                $country = $address->country;
                $postcode = $address->postcode;
                $mobileNumber = $user->mobile;
                $TelephoneNumber = $user->telephone;
                $username = $user->title . $user->first_name . ' ' . $user->last_name;
            }
        }


        $dataArray[0] =  [
            'address1' => $address1,
            'address2' => $address2,
            'city' => $city,
            'district' => $district,
            'province' => $province,
            'country' => $country,
            'postcode' => $postcode,
            'mobileNumber' => $mobileNumber,
            'TelephoneNumber' => $TelephoneNumber,
            'username' => $username,
        ];

        $item = OrderItem::where('order_id', $data->id)->get()->toArray();

        $dataArray[1] =  [
            'addModuleName' => $addModuleName,
            'refNo' => $pay->ref_no,
            'moduleId' => $orderNumber !== null ? $orderNumber : null,
            'invNo' => $pay->invoice_no,
            'invName' => $data->description,
            'companyname' => ($this->company  == null ? null : $this->company->name . ' ' . $this->company->postfix),
            'companyref' => ($this->company  == null ? null : $this->company->id),
            'item' => $item,
            'TaxPercentage' => $this->getTaxPercentage(),
            'tax' => $data->tax,
            'conveniencefee' => $convenience_fee,
            'certificate_no' => $certificate_no,
            'subtotal' => $data->subtotal,
            'total' => $data->total + $convenience_fee,
            'tenderNumber' => $tenderNumber,
            'tenderName' => $tenderName,
            'penalty' => $data->penalty,
            'delevery_option' => $data->delevery_option,
            'delevery_charge' => $data->delevery_charge
        ];

        return $dataArray[0] +  $dataArray[1];
    }

    /*
    |--------------------------------------------------------------------------
    | Common Method - Payment
    |--------------------------------------------------------------------------
     */

    public function setCertificate($lgpsClient)
    {
        $lgpsClient->setLogs(env('LOG_DIRECTORY_PATH', ''), env('ENABLE_LOGS', ''));
        $lgpsClient->setClientPublicKey(env('CLIENT_PUBLIC_KEY', '')); //Set Client Public Key
        $lgpsClient->setClientPrivateKey(env('CLIENT_PRIVATE_KEY', ''), env('CLIENT_KEY_PASSWORD', '')); //Set Client Private Key
        $lgpsClient->setLgpsPublicKey(env('LGPS_PUBLIC_KEY', '')); //Set LGPS public Key
    }

    public function extraPayment(Request $request)
    {
        if (isset($request->payment['extraPay'])) {
            if ($request->payment['extraPay']  === 'NAME_RENEWEL') {
                $renewel = new NameRenewel();
                $renewel->company_id = $request->payment['module_id'];
                $renewel->save();
            }
        }
    }
}
