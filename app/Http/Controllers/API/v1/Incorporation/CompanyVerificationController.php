<?php
namespace App\Http\Controllers\API\v1\incorporation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyPostfix;
use App\Company;
use App\CompanyCertificate;
use App\Address;
use App\Setting;
// use App\CompanyMember;
// use App\CompanyFirms;
// use App\DocumentsGroup;
// use App\Documents;
// use App\Country;
// use App\Share;
// use App\ShareGroup;
// use App\CompanyDocuments;
// use App\CompanyDocumentStatus;
// use App\User;
// use App\People;
// use App\CompanyMemberFirmBenif;
// use App\CompanyObjective1;
// use App\CompanyObjective2;
// use App\CompanyObjective3;
// use App\CompanyObjective4;
// use App\CompanyObjective5;
// use App\CompanyObjective;
// use App\CompanyStatus;
// use App\Order;
// use App\Secretary;
// use App\Province;
// use App\District;
// use App\City;
// use App\GNDivision;
// use App\CompanyDocumentCopies;
// use App\CompanyPublicRequest;
// use App\CompanyPublicRequestDocument;
use Storage;
use Cache;
use App;
use URL;
use App\Http\Helper\_helper;
use PDF;

class CompanyVerificationController extends Controller
{
    use _helper;
  
    function __construct() {
        
    }


    function checkCompanyVerification(Request $request  ) {

        $certificateNo = urldecode(trim($request->certificateNo));

        if(!$certificateNo){

            return response()->json([
                'message'       => "Certificate is Invalid.",
                'status'        => false,
                ], 200);
           
        }

        $companyFound = CompanyCertificate::where('registration_no',strtoupper($certificateNo))->count();

        if($companyFound == 1 ) {

            $certificate = CompanyCertificate::where('registration_no',strtoupper($certificateNo))->first();
            $companyInfo = Company::where('id',$certificate->company_id)->first();



            return response()->json([
                'message'       => "This is a Valid Certificate.",
                'company_name'  => $companyInfo->name,
                'incorporation_at'  => $companyInfo->incorporation_at,
                'status'        => true,
                ], 200);
           
        } else {
            return response()->json([
                'message'       => "This is not a Valid Certificate.",
                'status'        => false,
                ], 200);
           
        }

    }


}