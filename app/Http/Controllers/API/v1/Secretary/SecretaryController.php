<?php
namespace App\Http\Controllers\API\v1\Secretary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\_helper;
use App\Address;
use App\Secretary;
use App\SecretaryFirm;
use App\People;
use App\User;
use App\Setting;
use App\SecretaryWorkingHistory;
use App\SecretaryCertificate;
use App\SecretaryFirmPartner;
use App\SecretaryDocument;
use App\SecretaryComment;
use App\DocumentsGroup;
use App\Documents;
use App\SecretaryCertificateRequest;
use App\SecretaryDocumentStatus;
use App\SecretaryChangeRequestItem;
use App\Translations;

use App\SecretaryChnagesIndividual;
use App\SecretaryChnagesFirm;

use Storage;
use App;
use URL;
use PDF;
use Carbon\Carbon;
class SecretaryController extends Controller
{
        use _helper;

    // save individual secretary data to database...
    public function saveSecretaryData (Request $request){
        if(isset($request->nic)){
            $nic = strtoupper($request->nic);
            $isAlreadySec = Secretary::where('nic','ILIKE', "$nic")->value('id');
            if(!isset($isAlreadySec)){

                $secAddressResidential = new Address();
                $secAddressResidential->address1 = $request->input('residentialLocalAddress1');
                $secAddressResidential->address2 = $request->input('residentialLocalAddress2');
                $secAddressResidential->city = $request->input('residentialCity');
                $secAddressResidential->district = $request->input('residentialDistrict');
                $secAddressResidential->province = $request->input('residentialProvince');
                $secAddressResidential->country = 'Sri Lanka';
                $secAddressResidential->postcode = $request->input('residentialPostCode');
                $secAddressResidential->gn_division = $request->input('rgnDivision');
                $secAddressResidential->save();

                $secAddressBusiness = new Address();
                $bAddress = $request->input('businessName');
                if(!empty($bAddress)){
                $secAddressBusiness->address1 = $request->input('businessLocalAddress1');
                $secAddressBusiness->address2 = $request->input('businessLocalAddress2');
                $secAddressBusiness->city = $request->input('businessCity');
                $secAddressBusiness->district = $request->input('businessDistrict');
                $secAddressBusiness->province = $request->input('businessProvince');
                $secAddressBusiness->country = 'Sri Lanka';
                $secAddressBusiness->postcode = $request->input('businessPostCode');
                $secAddressBusiness->gn_division = $request->input('bgnDivision');
                $secAddressBusiness->save();
                }

                $regUser = $request->input('registeredUser');
                
                $people = new People();
                if($regUser==false){
                // if not a registered user, bellow details insert into people table...
                $people->title = $request->input('title');
                $people->first_name = $request->input('firstname');
                $people->email = $request->input('email');
                $people->mobile = $request->input('mobile');
                $people->telephone = $request->input('tel');
                $people->last_name = $request->input('lastname');
                $people->other_name = $request->input('othername');
                $people->nic = $request->input('nic');
                $people->address_id = $secAddressResidential->id;
                $people->is_srilankan = 'yes';
                $people->save();
                }

                // if applicant already roc user... 
                $secNic = strtoupper($request->nic);
                $peopleId = People::where('nic', 'ILIKE', "$secNic")->value('id');

                $loggedUserEmail = $request->input('loggedInUser');
                $loggedUserId = User::where('email', $loggedUserEmail)->value('id');
                
                $secinfo = new Secretary();
                $secinfo->title = $request->input('title');
                $secinfo->first_name = $request->input('firstname');
                $secinfo->last_name = $request->input('lastname');
                $secinfo->other_name = $request->input('othername');
                $secinfo->email = $request->input('email');
                $secinfo->mobile = $request->input('mobile');
                $secinfo->telephone = $request->input('tel');
                $secinfo->nic = $secNic;
                $secinfo->business_name = $request->input('businessName');
                $secinfo->which_applicant_is_qualified = $request->input('subClauseQualified');
                $secinfo->professional_qualifications = $request->input('pQualification');
                $secinfo->educational_qualifications = $request->input('eQualification');
                $secinfo->work_experience = $request->input('wExperience');
                $secinfo->address_id = $secAddressResidential->id;
                $secinfo->business_address_id = $secAddressBusiness->id;
                $secinfo->is_unsound_mind = $request->input('isUnsoundMind');
                $secinfo->is_insolvent_or_bankrupt = $request->input('isInsolventOrBankrupt');
                $secinfo->reason = $request->input('reason1');
                $secinfo->is_competent_court = $request->input('isCompetentCourt');
                $secinfo->competent_court_type = $request->input('reason2');
                $secinfo->other_details = $request->input('otherDetails');
                $secinfo->status = $this->settings('SECRETARY_PROCESSING','key')->id;
                $secinfo->created_by = $loggedUserId;
                $secinfo->is_existing_secretary = $request->input('isExistSec');
                $secinfo->name_si = $request->input('sinFullName');
                $secinfo->name_ta = $request->input('tamFullName');
                if($regUser==false){
                $secinfo->people_id = $people->id;   // if applicant is not a roc user... 
                }else{
                $secinfo->people_id = $peopleId;  // if applicant already roc user... 
                }
                $secinfo->save();
                $isExistSec = $request->input('isExistSec');
                if($isExistSec == 1){
                    $secCertificate = new SecretaryCertificate();
                    $secCertificate->secretary_id = $secinfo->id;
                    $secCertificate->certificate_no = $request->input('certificateNo');
                    $secCertificate->status = $this->settings('COMMON_STATUS_ACTIVE','key')->id;
                    $secCertificate->save();
                }
                
                $workHis = $request->input('workHis');
                $his = array();
                foreach($workHis as $history){
                    if(!empty($history)){
                    
                    $secWorkHistory = new SecretaryWorkingHistory();
                    $secWorkHistory->secretary_id =  $secinfo->id;
                    $secWorkHistory->company_name = $history['companyName'];
                    $secWorkHistory->position = $history['position'];
                    $secWorkHistory->from = $history['from'];
                    $secWorkHistory->to = $history['to'];
                    $secWorkHistory->save();

                    }
                    $his[] = $history['companyName'];
                }
        
                $data = array();
                $data[] = $secinfo->id;
                $secId = $secinfo->id;
                return response()->json([
                    'message' => 'successfully inserted!!!',
                    'status' =>true,
                    'secId' => $secId,
                ], 200);                

            }else{

                $secStatus = Secretary::leftJoin('settings','secretaries.status','=','settings.id')
                                         ->where('secretaries.nic','ILIKE', "$nic")
                                           ->get(['settings.key as statusKey']);

                if($secStatus[0]['statusKey'] === 'SECRETARY_PROCESSING'){

                $secResAddressId = Secretary::where('nic','ILIKE', "$nic")->value('address_id');
                if(isset($secResAddressId)){
                Address::where('id',$secResAddressId)
                            ->update(['address1' => $request->input('residentialLocalAddress1'),
                                    'address2' => $request->input('residentialLocalAddress2'),
                                    'province' => $request->input('residentialProvince'), 
                                    'district' => $request->input('residentialDistrict'), 
                                    'city' => $request->input('residentialCity'), 
                                    'postcode' => $request->input('residentialPostCode'), 
                                    'gn_division' => $request->input('rgnDivision'),     
                                    ]);
                }

                $secBusAddressId = Secretary::where('nic','ILIKE', "$nic")->value('business_address_id');
                if(isset($secBusAddressId)){            
                Address::where('id',$secBusAddressId)
                            ->update(['address1' => $request->input('businessLocalAddress1'),
                                    'address2' => $request->input('businessLocalAddress2'),
                                    'province' => $request->input('businessProvince'), 
                                    'district' => $request->input('businessDistrict'), 
                                    'city' => $request->input('businessCity'), 
                                    'postcode' => $request->input('businessPostCode'), 
                                    'gn_division' => $request->input('bgnDivision'),     
                                    ]);
                }else{
                   
                    $secAddressBusiness = new Address();
                    $bAddress = $request->input('businessName');
                    if(!empty($bAddress)){
                    $secAddressBusiness->address1 = $request->input('businessLocalAddress1');
                    $secAddressBusiness->address2 = $request->input('businessLocalAddress2');
                    $secAddressBusiness->city = $request->input('businessCity');
                    $secAddressBusiness->district = $request->input('businessDistrict');
                    $secAddressBusiness->province = $request->input('businessProvince');
                    $secAddressBusiness->country = 'Sri Lanka';
                    $secAddressBusiness->postcode = $request->input('businessPostCode');
                    $secAddressBusiness->gn_division = $request->input('bgnDivision');
                    $secAddressBusiness->save();
                    }
                    $bAddressID = $secAddressBusiness->id;
                    $secId = Secretary::where('nic','ILIKE', "$nic")->value('id');
                             Secretary::where('id', $secId)
                                     ->update(['business_address_id' => $bAddressID,  
                                              ]);
                }

                $secId = Secretary::where('nic','ILIKE', "$nic")->value('id');
                Secretary::where('id', $secId)
                            ->update(['title' => $request->input('title'),  
                                    'first_name' => $request->input('firstname'),
                                    'last_name' => $request->input('lastname'),
                                    'other_name' => $request->input('othername'),
                                    'business_name' => $request->input('businessName'),
                                    'professional_qualifications' => $request->input('pQualification'),
                                    'educational_qualifications' => $request->input('eQualification'),
                                    'work_experience' => $request->input('wExperience'),
                                    'is_unsound_mind' => $request->input('isUnsoundMind'),
                                    'is_insolvent_or_bankrupt' => $request->input('isInsolventOrBankrupt'),
                                    'reason' => $request->input('reason1'),
                                    'is_competent_court' => $request->input('isCompetentCourt'),
                                    'competent_court_type' => $request->input('reason2'),
                                    'which_applicant_is_qualified' => $request->input('subClauseQualified'),
                                    'other_details' => $request->input('otherDetails'),
                                    'is_existing_secretary' => $request->input('isExistSec'),
                                    'status' => $this->settings('SECRETARY_PROCESSING','key')->id,
                                    'name_si' => $request->input('sinFullName'),
                                    'name_ta' => $request->input('tamFullName'),
                                    'email' => $request->input('email'),
                                    'telephone' => $request->input('tel'),
                                    'mobile' => $request->input('mobile'),
                                    ]);

                $isExistSec = $request->input('isExistSec');
                if($isExistSec == 1){
                    $certificate = SecretaryCertificate::updateOrCreate(
                                    [
                                        'secretary_id' =>  $secId
                                    ],
                                    [
                                        'certificate_no' => $request->input('certificateNo'),
                                        'status' => $this->settings('COMMON_STATUS_ACTIVE','key')->id,                                
                                    ]);
                }else if($isExistSec == 0){
                    $removeCertificate = SecretaryCertificate::where('secretary_id', $secId)->delete();
                } 
                $remove = SecretaryWorkingHistory::where('secretary_id', $secId)->delete();
                $workHis = $request->input('workHis');
                foreach($workHis as $history){
                    if(!empty($history)){
                        if(!empty($history)){
                    
                            $secWorkHistory = new SecretaryWorkingHistory();
                            $secWorkHistory->secretary_id =  $secId;
                            $secWorkHistory->company_name = $history['companyName'];
                            $secWorkHistory->position = $history['position'];
                            $secWorkHistory->from = $history['from'];
                            $secWorkHistory->to = $history['to'];
                            $secWorkHistory->save();        
                        }                                              
                    }
                } 
                return response()->json([
                    'message' => 'successfully updated!!!',
                    'status' =>true,
                    'secId' => $secId,
                ], 200);

                }else{
                    return response()->json([
                        'message' => 'secretary is already registered!!!',
                        'status' =>false,
                    ], 200);
                }
            }        
        }else{
            return response()->json([
                'message' => 'please insert valid nic number!!!',
                'status' =>true,
            ], 200);
        }
    }

    // load individual secretary data using nic number...
    public function loadSecretaryData(Request $request){

        if(!$request->nic){
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }else if($request->nic){
            $nic = strtoupper($request->nic);
            // to check applicant already registered as a secretary...
            $isAlreadySec = Secretary::where('nic','ILIKE', "$nic")->value('id');
            if(!$isAlreadySec){
            $secretaryDetails = People::where('nic','ILIKE', "$nic")->first();

            if($secretaryDetails){
                $secretaryAddressId = $secretaryDetails->address_id;
                $secretaryAddress = Address::where('id', $secretaryAddressId)->first();            
                return response()->json([
                    'message' => 'already eroc user',
                    'user' => true, // to check applicant already registered as roc user...  
                    'status' =>true, // to load data from eroc people...
                    'issec' => false,
                    'data'   => array(
                                    'secretary'     => $secretaryDetails,
                                    'secretaryAddress'=> $secretaryAddress,
                                )
                ], 200);            
            }else{
                return response()->json([
                    'message' => 'we can \'t find a registered User.',
                    'user' => false,
                    'status' =>false,
                    'issec' => false,
                ], 200);
            }

            }else if($isAlreadySec){
            return response()->json([
                'message' => 'Sorry you can not register again',
                'issec' => true,
                'secId' => $isAlreadySec,
                'status' =>false,
            ], 200);
            }
        }        
    }


    // save secretary firm data(firm info and members info) to database...
    public function saveSecretaryFirmData(Request $request){

        if(isset($request)){
            $regNum =  $request->input('registrationNumber');
            $isRegistered = SecretaryFirm::where('registration_no', $regNum)->value('id');

            if(!isset($isRegistered)){
            $secAddressFirm = new Address();
            $secAddressFirm->address1 = $request->input('businessLocalAddress1');
            $secAddressFirm->address2 = $request->input('businessLocalAddress2');
            $secAddressFirm->city = $request->input('businessCity');
            $secAddressFirm->district = $request->input('businessDistrict');
            $secAddressFirm->province = $request->input('businessProvince');
            $secAddressFirm->country = 'SriLanka';
            $secAddressFirm->postcode = $request->input('businessPostCode');
            $secAddressFirm->gn_division = $request->input('bgnDivision');
            $secAddressFirm->save();

            $loggedUserEmail = $request->input('loggedInUser');
            $loggedUserId = User::where('email', $loggedUserEmail)->value('id');

            if(isset($loggedUserId)){
            $secFirminfo = new SecretaryFirm();
            $secFirminfo->registration_no = $request->input('registrationNumber');
            $secFirminfo->name = $request->input('name');
            $secFirminfo->email = $request->input('email');
            $secFirminfo->mobile = $request->input('mobile');
            $secFirminfo->telphone = $request->input('tel');
            $secFirminfo->address_id = $secAddressFirm->id;
            $secFirminfo->is_undertake_secretary_work = $request->input('isUndertakeSecWork');
            $secFirminfo->is_unsound_mind = $request->input('isUnsoundMind');
            $secFirminfo->is_insolvent_or_bankrupt = $request->input('isInsolventOrBankrupt');
            $secFirminfo->reason = $request->input('reason1');
            $secFirminfo->is_competent_court = $request->input('isCompetentCourt');
            $secFirminfo->competent_court_type = $request->input('reason2');
            $secFirminfo->is_existing_secretary_firm = $request->input('isExistSec');
            $secFirminfo->status = $this->settings('SECRETARY_PROCESSING','key')->id;
            $secFirminfo->type = $request->input('type');
            $secFirminfo->firm_type = $request->input('firmType');
            $secFirminfo->created_by = $loggedUserId;
            $secFirminfo->name_si = $request->input('sinName');
            $secFirminfo->name_ta = $request->input('tamName');
            $secFirminfo->save();
            }else {
                return response()->json([
            'message' => 'logged in user invalid!!!',
            'status' =>false,
            ], 200);
            }
            $isExistSec = $request->input('isExistSec');
                if($isExistSec == 1){
                    $secCertificate = new SecretaryCertificate();
                    $secCertificate->firm_id = $secFirminfo->id;
                    $secCertificate->certificate_no = $request->input('certificateNo');
                    $secCertificate->status = $this->settings('COMMON_STATUS_ACTIVE','key')->id;
                    $secCertificate->save();
                }
            $partnerDetails = $request->input('firmPartners');
            foreach($partnerDetails as $partner){
                if(!empty($partner)){
                $secFirmPartners = new SecretaryFirmPartner();
                $secFirmPartners->firm_id =  $secFirminfo->id;
                $secFirmPartners->nic =  $partner['id'];
                $secFirmPartners->name = $partner['name'];
                $secFirmPartners->address = $partner['residentialAddress'];
                $secFirmPartners->citizenship = $partner['citizenship'];
                $secFirmPartners->which_qualified = $partner['whichQualified'];
                $secFirmPartners->professional_qualifications = $partner['pQualification'];
                $secFirmPartners->save();
                }
            }
        
            $id = $secFirminfo->id;   
                return response()->json([
                    'message' => 'successfully inserted!!!',
                    'status' =>true,
                    'firmId' => $id,
                ], 200);
            }else {
                $firmStatus = SecretaryFirm::leftJoin('settings','secretary_firm.status','=','settings.id')
                                              ->where('secretary_firm.registration_no', $regNum)
                                              ->get(['settings.key as statusKey']);

                $loggedUserEmail = $request->input('loggedInUser');
                $loggedUserId = User::where('email', $loggedUserEmail)->value('id');
                $firmCreatedById = SecretaryFirm::where('registration_no', $regNum)->value('created_by');

                if(($firmStatus[0]['statusKey'] === 'SECRETARY_PROCESSING') && ($firmCreatedById == $loggedUserId)){
                        $firmId = SecretaryFirm::where('registration_no', $regNum)->value('id');
                        $firmAddressId = SecretaryFirm::where('id', $firmId)->value('address_id');
                        Address::where('id',$firmAddressId)
                                    ->update(['address1' => $request->input('businessLocalAddress1'),
                                            'address2' => $request->input('businessLocalAddress2'),
                                            'province' => $request->input('businessProvince'), 
                                            'district' => $request->input('businessDistrict'), 
                                            'city' => $request->input('businessCity'), 
                                            'postcode' => $request->input('businessPostCode'), 
                                            'gn_division' => $request->input('bgnDivision'),     
                                            ]);

                        SecretaryFirm::where('id', $firmId)
                                    ->update(['name' => $request->input('name'), 
                                            'registration_no' => $request->input('registrationNumber'),  
                                            'is_unsound_mind' => $request->input('isUnsoundMind'),
                                            'is_insolvent_or_bankrupt' => $request->input('isInsolventOrBankrupt'),
                                            'reason' => $request->input('reason1'),
                                            'is_competent_court' => $request->input('isCompetentCourt'),
                                            'is_existing_secretary_firm' => $request->input('isExistSec'),
                                            'firm_type' => $request->input('firmType'),
                                            'competent_court_type' => $request->input('reason2'),
                                            'is_undertake_secretary_work' => $request->input('isUndertakeSecWork'),
                                            'status' => $this->settings('SECRETARY_PROCESSING','key')->id,
                                            'name_si' => $request->input('sinName'),
                                            'name_ta' => $request->input('tamName'),
                                            'email' => $request->input('email'),
                                            'telphone' => $request->input('tel'),
                                            'mobile' => $request->input('mobile'),
                                            ]);
                        $isExistSec = $request->input('isExistSec');
                        if($isExistSec == 1){
                                $certificate = SecretaryCertificate::updateOrCreate(
                                                [
                                                'firm_id' =>  $firmId
                                                ],
                                                [
                                                'certificate_no' => $request->input('certificateNo'),
                                                'status' => $this->settings('COMMON_STATUS_ACTIVE','key')->id,                                
                                                ]);
                        }else if($isExistSec == 0){
                                $removeCertificate = SecretaryCertificate::where('firm_id', $firmId)->delete();
                        }

                        $removePartners = SecretaryFirmPartner::where('firm_id', $firmId)->delete();
                        $partnerDetails = $request->input('firmPartners');
                        foreach($partnerDetails as $partner){
                            if(!empty($partner)){
                            $secFirmPartners = new SecretaryFirmPartner();
                            $secFirmPartners->firm_id =  $firmId;
                            $secFirmPartners->nic =  $partner['id'];
                            $secFirmPartners->name = $partner['name'];
                            $secFirmPartners->address = $partner['residentialAddress'];
                            $secFirmPartners->citizenship = $partner['citizenship'];
                            $secFirmPartners->which_qualified = $partner['whichQualified'];
                            $secFirmPartners->professional_qualifications = $partner['pQualification'];
                            $secFirmPartners->save();
                            }
                        }
                        return response()->json([
                            'message' => 'successfully updated!!!',
                            'status' =>true,
                            'firmId' => $firmId,
                        ], 200);
                }else{
                    return response()->json([
                        'message' => 'firm is already registered!!!',
                        'status' =>false,
                    ], 200);
                }
            }
        }else{
            return response()->json([
                'status' =>false,
            ], 200);
        }
    }

    // load name, address, citizenship, data of firm and pvt limited members... using nic
    public function loadSecretaryFirmPartnerData(Request $request){
        if(!$request->nic){

            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false
            ], 200);
        }
        if(isset($request->nic)){
            $secNic = strtoupper($request->nic);
            $partnerDetails = Secretary::where('nic','ILIKE', "$secNic")
                                       ->where('status',$this->settings('SECRETARY_APPROVED','key')->id)->first();

            if($partnerDetails){
            $partnerAddressId = $partnerDetails->address_id;
            $partnerAddress = Address::where('id', $partnerAddressId)->first();
            $certificateNum = SecretaryCertificate::where('secretary_id', $partnerDetails->id)->value('certificate_no');
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true,
                'data'   => array(
                                
                                'partner'     => $partnerDetails,
                                'partnerAddress'=> $partnerAddress,
                                'certificateNum'     => $certificateNum, 

                            )
            ], 200);
            }else{
                return response()->json([
                    'message' => 'We can \'t find a User.',
                    'status' =>false
                ], 200);
            }
        }
    }


    // for upload secretary individual pdf...
    public function secretaryIndividualUploadPdf(Request $request){

        if(isset($request)){

        $fileName =  uniqid().'.pdf';
        $token = md5(uniqid());

        $secId = $request->secId;
        $docType = $request->docType;
        $pdfName = $request->filename;

        $description = $request->description;
        if($description=='undefined'){
            $description=NULL;
        }

        $path = 'secretary/'.$secId;
        $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');


        $docId;
        if($docType=='applicationUpload'){
            $docIdArray = DocumentsGroup::leftJoin('documents','document_groups.id','=','documents.document_group_id')
                                           ->where('document_groups.request_type','SECRETARY')		
						                   ->where('documents.key','SECRETARY_APPLICATION')
                                           ->get(['documents.id']);
        $docId = $docIdArray[0]['id'];
        }elseif($docType=='eCertificateUpload'){
            $docIdArray = DocumentsGroup::leftJoin('documents','document_groups.id','=','documents.document_group_id')
                                           ->where('document_groups.request_type','SECRETARY')		
						                   ->where('documents.key','SECRETARY_EDUCATIONAL_CERTIFICATE')
                                           ->get(['documents.id']);
        $docId = $docIdArray[0]['id'];   
        }elseif($docType=='pCertificateUpload'){
            $docIdArray = DocumentsGroup::leftJoin('documents','document_groups.id','=','documents.document_group_id')
                                           ->where('document_groups.request_type','SECRETARY')		
						                   ->where('documents.key','SECRETARY_PROFESSIONAL_CERTIFICATE')
                                           ->get(['documents.id']);
        $docId = $docIdArray[0]['id'];  
        }elseif($docType=='experienceUpload'){
            $docIdArray = DocumentsGroup::leftJoin('documents','document_groups.id','=','documents.document_group_id')
                                           ->where('document_groups.request_type','SECRETARY')		
						                   ->where('documents.key','SECRETARY_EXPERIENCE_CERTIFICATE')
                                           ->get(['documents.id']);
        $docId = $docIdArray[0]['id'];  
        }elseif($docType=='evidenceUpload'){
            $docIdArray = DocumentsGroup::leftJoin('documents','document_groups.id','=','documents.document_group_id')
                                           ->where('document_groups.request_type','SECRETARY')		
						                   ->where('documents.key','SECRETARY_EVIDENCE_CERTIFICATE')
                                           ->get(['documents.id']);
        $docId = $docIdArray[0]['id'];  
        }elseif($docType=='regCertificateUpload'){
            $docIdArray = DocumentsGroup::leftJoin('documents','document_groups.id','=','documents.document_group_id')
                                           ->where('document_groups.request_type','SECRETARY')		
						                   ->where('documents.key','SECRETARY_CERTIFICATE')
                                           ->get(['documents.id']);
        $docId = $docIdArray[0]['id'];  
        }elseif($docType=='PracticeCertificateUpload'){
            $docIdArray = DocumentsGroup::leftJoin('documents','document_groups.id','=','documents.document_group_id')
                                           ->where('document_groups.request_type','SECRETARY')		
						                   ->where('documents.key','SECRETARY_CERTIFICATE_TO_PRACTICE')
                                           ->get(['documents.id']);
        $docId = $docIdArray[0]['id'];  
        }       
       
        $secDoc = new SecretaryDocument;
        $secDoc->document_id = $docId;
        $secDoc->secretary_id = $secId;
        $secDoc->name = $pdfName;
        $secDoc->description = $description;
        $secDoc->file_token = $token;
        $secDoc->path = $path;
        $secDoc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
        $secDoc->save();
        
        $secdocId = $secDoc->id;

          return response()->json([
            'message' => 'File uploaded successfully.',
            'status' =>true,
            'name' =>basename($path),
            'doctype' =>$docType,
            'docid' =>$secdocId, // for delete pdf...
            'token' =>$token,
            'pdfname' =>$pdfName,
            ], 200);

        }

    }

    // for upload secretary firm pdf...
    public function secretaryFirmUploadPdf(Request $request){

        if(isset($request)){

        $fileName =  uniqid().'.pdf';
        $token = md5(uniqid());

        $firmId = $request->firmId;
        $docType = $request->docType;
        $pdfName = $request->filename;

        $path = 'secretaryfirm/'.$firmId;
        $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');

        $docId;
    if($docType=='applicationUpload'){
        $docIdArray = Documents::where('key','SECRETARY_APPLICATION')->select('id')->first();
    $docId = $docIdArray->id;
    }elseif($docType=='form1Upload'){
        $docIdArray = Documents::where('key','SECRETARY_FORM_01')->select('id')->first();
    $docId = $docIdArray->id;   
    }elseif($docType=='articleUpload'){
        $docIdArray = Documents::where('key','SECRETARY_ARTICLE')->select('id')->first();
    $docId = $docIdArray->id;  
    }elseif($docType=='regCertificateUpload'){
        $docIdArray = Documents::where('key','SECRETARY_CERTIFICATE')->select('id')->first();
    $docId = $docIdArray->id;  
    }elseif($docType=='otherUpload'){
        $docIdArray = Documents::where('key','EXTRA_DOCUMENT')->select('id')->first();
    $docId = $docIdArray->id;  
    }
        
        // $docIdArray = DocumentsGroup::leftJoin('documents','document_groups.id','=','documents.document_group_id')
        //                                    ->where('document_groups.request_type','SECRETARY')		
		// 				                   ->where('documents.key','SECRETARY_APPLICATION')
        //                                    ->get(['documents.id']);
        // $docId = $docIdArray[0]['id'];                                  
        
        $secDoc = new SecretaryDocument;
        $secDoc->document_id = $docId;
        $secDoc->firm_id = $firmId;
        $secDoc->name = $pdfName;
        $secDoc->description = $docType;
        $secDoc->file_token = $token;
        $secDoc->path = $path;
        $secDoc->status =  $this->settings('DOCUMENT_PENDING','key')->id;
        $secDoc->save();
        
        $secdocId = $secDoc->id;

          return response()->json([
            'message' => 'File uploaded successfully.',
            'status' =>true,
            'name' =>basename($path),
            'doctype' =>$docType,
            'docid' =>$secdocId, // for delete pdf...
            'token' =>$token,
            'pdfname' =>$pdfName,
        ], 200);

        }

    }

    // for delete secretary individual and firm pdf files...
    function deleteSecretaryPdf(Request $request){
        if(isset($request)){
        $docId = $request->documentId;
        if($docId){
            $document = SecretaryDocument::where('id', $docId)->first();
            $delete = Storage::disk('sftp')->delete($document->path);
            $remove = SecretaryDocument::where('id', $docId)->delete();
        }
        return response()->json([
            'message' => 'File removed successfully.',
            'status' =>true,
        ], 200);
        }
    }

    // for load individual and firm registered secretary data to profile card...
    public function loadRegisteredSecretaryData(Request $request){
        if($request){
            $loggedUserEmail = $request->input('loggedInUser');
            $loggedUserId = User::where('email', $loggedUserEmail)->value('id');

            $secretary = Secretary::leftJoin('settings','secretaries.status','=','settings.id')
                                  ->leftJoin('secretary_certificates','secretaries.id','=','secretary_certificates.secretary_id')
                                     ->where('secretaries.created_by',$loggedUserId)
                                       ->get(['secretaries.id','secretaries.first_name','secretaries.last_name','secretaries.nic','secretaries.which_applicant_is_qualified','secretary_certificates.certificate_no as certificate_no','secretaries.created_at','settings.key as status','settings.value as value']);
          
            $secretaryFirm = SecretaryFirm::leftJoin('settings','secretary_firm.status','=','settings.id')
                                          ->leftJoin('secretary_certificates','secretary_firm.id','=','secretary_certificates.firm_id')
                                             ->where('secretary_firm.created_by',$loggedUserId)
                                               ->get(['secretary_firm.id','secretary_firm.registration_no','secretary_certificates.certificate_no as certificate_no','secretary_firm.name','secretary_firm.created_at','secretary_firm.type','settings.key as status','settings.value as value']);
           
            

            $secretary_arr = array();

            if(count($secretary)) {
                foreach($secretary as $sec ) {
                     $secretory_id = $sec['id'];
                     $change = SecretaryChnagesIndividual::where('secretory_id', $secretory_id)->first();

                     $secretaryDelisting = SecretaryChangeRequestItem::where('secretary_id', $secretory_id)
                                    ->where('request_type',$this->settings('SECRETARY_DELISTING', 'key')->id)
                                    ->where('secretary_type', $this->settings( 'MODULE_SECRETARY','key')->id)
                                    ->select('status')
                                    ->first();

                     $change_exist = false;
                     $change_arr = array();
                     if(isset($change->id)) {

                        $change_arr = array(
                            'status' => $this->settings($change->status,'id')->key,
                            'value'  =>  $this->settings($change->status,'id')->value
                        );
                        $change_exist = true;
                     }

                    $created_at =  new Carbon($sec['created_at']);
                    $created_at = $created_at->toDateTimeString();

                     $row = array();
                     $row['id'] = $sec['id'];
                     $row['certificate_no'] = $sec['certificate_no'];
                     $row['created_at'] = $created_at;
                     $row['first_name'] = $sec['first_name'];
                     $row['last_name'] = $sec['last_name'];
                     $row['nic'] = $sec['nic'];
                     $row['status'] = $sec['status'];
                     $row['value'] = $sec['value'];
                     $row['which_applicant_is_qualified'] = $sec['which_applicant_is_qualified'];
                     $row['change_exist'] = $change_exist;
                     $row['change_info'] = $change_arr;
                     $row['secretaryDelisting'] = (isset($secretaryDelisting->status) && $secretaryDelisting->status) ? $this->settings($secretaryDelisting->status,'id')->key : '';
                     $secretary_arr[] = $row;
                }
            }


            $secretary_firm_arr = array();

            if(count($secretaryFirm)) {
                foreach($secretaryFirm as $sec ) {
                     $secretory_id = $sec['id'];
                     $change = SecretaryChnagesFirm::where('secretory_id', $secretory_id)->first();

                     $secretaryDelisting = SecretaryChangeRequestItem::where('secretary_id', $secretory_id)
                                    ->where('request_type',$this->settings('SECRETARY_DELISTING', 'key')->id)
                                    ->where('secretary_type', $this->settings( 'MODULE_SECRETARY_FIRM','key')->id)
                                    ->select('status')
                                    ->first();

                     $change_exist = false;
                     $change_arr = array();
                     if(isset($change->id)) {
      
                        $change_arr = array(
                            'status' => $this->settings($change->status,'id')->key,
                            'value'  =>  $this->settings($change->status,'id')->value
                        );
                        $change_exist = true;
                     }
                     
                     $created_at =  new Carbon($sec['created_at']);
                     $created_at = $created_at->toDateTimeString();
                     $row = array();
                     $row['id'] = $sec['id'];
                     $row['certificate_no'] = $sec['certificate_no'];
                     $row['registration_no'] = $sec['registration_no'];
                     $row['created_at'] = $created_at;
                     $row['name'] = $sec['name'];
                     $row['status'] = $sec['status'];
                     $row['value'] = $sec['value'];
                     $row['type'] = $sec['type'];
                     $row['change_exist'] = $change_exist;
                     $row['change_info'] = $change_arr;
                     $row['secretaryDelisting'] = $secretaryDelisting;
                     $secretary_firm_arr[] = $row;
                }
            }
            
            
            
            if($secretary_arr || $secretary_firm_arr){
                return response()->json([
                    'message' => 'Sucess!!!',
                    'status' =>true,
                    'data'   => array(
                                    'secretary'     => $secretary_arr,
                                    'secretaryfirm'     => $secretary_firm_arr,
                                )
                ], 200);
            }
        }
    }

    // for individual and firm secretary payments...
    public function secretaryPay(Request $request){
        if(isset($request)){
            if($request->secType=='individual'){
            $secId = $request->secId;
            $secretaryPay =  array(
                'status'    => $this->settings('SECRETARY_PENDING','key')->id
            );
            Secretary::where('id',  $secId)->update($secretaryPay);
            }
            elseif($request->secType=='firm'){
            $firmId = $request->secId;
            $secretaryPay =  array(
                'status'    => $this->settings('SECRETARY_PENDING','key')->id
            );
            SecretaryFirm::where('id',  $firmId)->update($secretaryPay);
            }
            return response()->json([
                'message' => 'Payment Successful.',
                'status' =>true,
            ], 200);
        }
    }

    // for load secretary uploaded files...
    public function secretaryFile(Request $request){
        if(isset($request)){
            if($request->type=='individual'){
            $secId = $request->secId;
            $uploadedPdf = SecretaryDocument::leftJoin('documents','secretary_documents.document_id','=','documents.id')
                                       ->where('secretary_documents.secretary_id',$secId)
                                         ->get(['secretary_documents.id','secretary_documents.name','secretary_documents.file_token','documents.key as dockey']);
            if(isset($uploadedPdf)){
                return response()->json([
                    'file' => $uploadedPdf,
                    'status' =>true,
                    'data'   => array(
                        'file'     => $uploadedPdf,
                    )
                ], 200);

                }
            }
            elseif($request->type=='firm'){
                $secId = $request->secId;
                $uploadedPdf =  SecretaryDocument::leftJoin('documents','secretary_documents.document_id','=','documents.id')
                ->where('secretary_documents.firm_id', $secId)->get(['secretary_documents.id','secretary_documents.name','secretary_documents.file_token','documents.key as dockey']);
                if(isset($uploadedPdf)){
                    return response()->json([
                        'file' => $uploadedPdf,
                        'status' =>true,
                        'data'   => array(
                            'file'     => $uploadedPdf,
                        )
                    ], 200);
                }
            }

        }else{
            return response()->json([
                'status' =>false,
            ], 200);
        }

    }

    // for check secretary pvt partner registered sec...
    public function isPartnerRegSec(Request $request){
        if(isset($request)){
           
            $isReg = Secretary::whereIn('nic',$request->id)
                                ->where('status',$this->settings('SECRETARY_APPROVED','key')->id)->get();
                               
                if(isset($isReg)){
                    if(sizeof($isReg) > 0){
                        return response()->json([
                            'status' =>true,
                                     ], 200);
                    }else {
                        return response()->json([
                            'status' =>false,
                                     ], 200);
                        }
                }else{
                    return response()->json([
                             'status' =>false,
                                    ], 200);
                }
        }
    }

     // to generate secretary individual pdf ...
     public function generateSecretaryPDF (Request $request){
        if(isset($request->secid)){
            $secretaryDetails = Secretary::where('id',$request->secid)->first();

            $fname = $secretaryDetails['first_name'];
            $lname = $secretaryDetails['last_name'];
            $fullName =  $fname .' '. $lname  ;

            $rAddressId = $secretaryDetails['address_id'];
            $bAddressId = $secretaryDetails['business_address_id'];
            $rAddress = Address::where('id',$rAddressId)->first();
            $bAddress = Address::where('id',$bAddressId)->first();

            if(isset($rAddress)){
                $address1 = $rAddress['address1'];
                $address2 = $rAddress['address2'];
                $city = $rAddress['city'];
                $resAddress = $address1 .' '. $address2 .' '. $city ;
            }
            if(isset($bAddress)){
                $address1 = $bAddress['address1'];
                $address2 = $bAddress['address2'];
                $city = $bAddress['city'];
                $bussAddress = $address1 .' '. $address2 .' '. $city ;
            }else{
                $bussAddress = '';
            }

            $businessName = $secretaryDetails['business_name'];
            $subClauseQualified = $secretaryDetails['which_applicant_is_qualified'];
            $pQualification = $secretaryDetails['professional_qualifications'];
            $eQualification = $secretaryDetails['educational_qualifications'];
            $wExperience = $secretaryDetails['work_experience'];
            $isUnsoundMind = $secretaryDetails['is_unsound_mind'];
            $isInsolventOrBankrupt = $secretaryDetails['is_insolvent_or_bankrupt'];
            $reason1 = $secretaryDetails['reason'];
            $isCompetentCourt = $secretaryDetails['is_competent_court'];
            $reason2 = $secretaryDetails['competent_court_type'];
            $otherDetails = $secretaryDetails['other_details'];

            $workHistory = SecretaryWorkingHistory::where('secretary_id',$request->secid)->get();            

            $secId = $request->secid;
            $createdAt = $secretaryDetails['created_at'];
            $createdDate = date("Y/m/d", strtotime($createdAt));
            $year = Translations::where('key',date("Y", strtotime($createdAt)))->first();
            $month = Translations::where('key',"M".date("m", strtotime($createdAt)))->first();
            $date = Translations::where('key',date("d", strtotime($createdAt)))->first();

            $data = [
                'secid' => $secId,
                'createdDate' => $createdDate,
                'name' => $fullName,
                'bname' =>  $businessName,
                'baddress' => $bussAddress,
                'raddress' => $resAddress,
                'sublauseualified' => $subClauseQualified,
                'pqualification' =>  $pQualification,
                'equalification' => $eQualification,
                'wexperience' => $wExperience,
                'isunsoundMind' => $isUnsoundMind,
                'isinsolventorbankrupt' => $isInsolventOrBankrupt,
                'reason1' => $reason1,
                'iscompetentcourt' => $isCompetentCourt,
                'reason2' => $reason2,
                'other' => $otherDetails,
                'workhistory' => $workHistory,
                'year' => $year,
                'month' => $month,
                'date' => $date
            ];           
            $pdf = PDF::loadView('secretary-forms/form1', $data);
            return $pdf->stream('form1.pdf');
        }else{            
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false,
            ], 200);
        }
    }

    // to generate secretary firm and pvt pdf ...
    public function generateSecretaryFirmPDF (Request $request){

        if(isset($request->firmid)){
            $secretaryFirmDetails = SecretaryFirm::where('id',$request->firmid)->first();
            
            $name = $secretaryFirmDetails['name'];
            $regnum = $secretaryFirmDetails['registration_no'];

            $bAddressId = $secretaryFirmDetails['address_id'];
            $bAddress = Address::where('id',$bAddressId)->first();           
            if(isset($bAddress)){
                $address1 = $bAddress['address1'];
                $address2 = $bAddress['address2'];
                $city = $bAddress['city'];
                $bussAddress = $address1 .' '. $address2 .' '. $city ;
            }

            $isUndertakeSecWork = $secretaryFirmDetails['is_undertake_secretary_work'];
            $isUnsoundMind = $secretaryFirmDetails['is_unsound_mind'];
            $isInsolventOrBankrupt = $secretaryFirmDetails['is_insolvent_or_bankrupt'];
            $reason1 = $secretaryFirmDetails['reason'];
            $isCompetentCourt = $secretaryFirmDetails['is_competent_court'];
            $reason2 = $secretaryFirmDetails['competent_court_type'];

            $partner = SecretaryFirmPartner::where('firm_id',$request->firmid)->get();            

            $firmId = $request->firmid;
            $createdAtF = $secretaryFirmDetails['created_at'];
            $createdDateF = date("Y/m/d", strtotime($createdAtF));
            $data = [
                'firmid' => $firmId,
                'date' => $createdDateF,
                'name' => $name,
                'regnum' =>  $regnum,
                'baddress' => $bussAddress,
                'isUndertakeSecWork' => $isUndertakeSecWork,
                'isunsoundMind' => $isUnsoundMind,
                'isinsolventorbankrupt' => $isInsolventOrBankrupt,
                'reason1' => $reason1,
                'iscompetentcourt' => $isCompetentCourt,
                'reason2' => $reason2,
                'partner' => $partner,
            ];
            $pdf = PDF::loadView('secretary-forms/form2', $data);   
            return $pdf->stream('form2.pdf');
        }else{            
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false,
            ], 200);
        }
    }    

    // load secretary firm data(firm info and members info) to resubmit...
    public function getSecretaryFirmData(Request $request){
        if(isset($request->firmId)){

            $firmDetails = SecretaryFirm::where('id',$request->firmId)->first();
            $firmAddressId = SecretaryFirm::where('id',$request->firmId)->value('address_id');
            $firmAddress = Address::where('id',$firmAddressId)->first();
            $certificateNum = SecretaryCertificate::where('firm_id', $request->firmId)->value('certificate_no');

            $partnerDetails = SecretaryFirmPartner::where('firm_id',$request->firmId)->get();
            return response()->json([
                'message' => 'Sucess',
                'status' =>true,
                'data'   => array(
                    'partnerDetails'     => $partnerDetails,
                    'firmAddress'  => $firmAddress,
                    'firmDetails'     => $firmDetails,
                    'certificateNumber'  => $certificateNum,
                )
            ], 200);

        }else{            
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false,
            ], 200);
        }
    }

    // update secretary firm data for resubmit ...
    public function updateSecretaryFirmData(Request $request){
        if(isset($request->firmId)){

            $firmAddressId = SecretaryFirm::where('id',$request->firmId)->value('address_id');
            Address::where('id',$firmAddressId)
                        ->update(['address1' => $request->input('businessLocalAddress1'),
                                  'address2' => $request->input('businessLocalAddress2'),
                                  'province' => $request->input('businessProvince'), 
                                  'district' => $request->input('businessDistrict'), 
                                  'city' => $request->input('businessCity'), 
                                  'postcode' => $request->input('businessPostCode'), 
                                  'gn_division' => $request->input('bgnDivision'),     
                                 ]);

            SecretaryFirm::where('id', $request->firmId)
                        ->update(['name' => $request->input('firmName'), 
                                  'registration_no' => $request->input('registrationNumber'),  
                                  'is_unsound_mind' => $request->input('isUnsoundMind'),
                                  'is_insolvent_or_bankrupt' => $request->input('isInsolventOrBankrupt'),
                                  'reason' => $request->input('reason1'),
                                  'is_competent_court' => $request->input('isCompetentCourt'),
                                  'is_existing_secretary_firm' => $request->input('isExistSec'),
                                  'firm_type' => $request->input('firmType'),
                                  'competent_court_type' => $request->input('reason2'),
                                  'is_undertake_secretary_work' => $request->input('isUndertakeSecWork'),
                                  'status' => $this->settings('SECRETARY_REQUEST_TO_RESUBMIT','key')->id,
                                  'name_si' => $request->input('sinName'),
                                  'name_ta' => $request->input('tamName'),
                                  'telphone' => $request->input('tel'),
                                  'mobile' => $request->input('mobile'),
                                  'email' => $request->input('email'),
                                 ]);

            $isExistSec = $request->input('isExistSec');
                    if($isExistSec == 1){
                        SecretaryCertificate::where('firm_id',$request->firmId)
                        ->update(['certificate_no' => $request->input('certificateNo')     
                                 ]);
                                     }
                       
            return response()->json([
                'message' => 'Sucessfully updated',
                'status' =>true,
                'firmId' => $request->firmId,
            ], 200);
        }else{            
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false,
            ], 200);
        }
    }

    // load secretary individual data to resubmit...
    public function getSecretaryData(Request $request){
        if(isset($request->secId)){

            $secretaryDetails = Secretary::where('id',$request->secId)->first();
            $secResAddressId = Secretary::where('id',$request->secId)->value('address_id');
            $secBusAddressId = Secretary::where('id',$request->secId)->value('business_address_id');
            $secResAddress = Address::where('id',$secResAddressId)->first();
            $secBusAddress = Address::where('id',$secBusAddressId)->first();
            $certificateNum = SecretaryCertificate::where('secretary_id', $request->secId)->value('certificate_no');

            $secretaryWorkHistory = SecretaryWorkingHistory::where('secretary_id',$request->secId)->get();

            return response()->json([
                'message' => 'Sucess',
                'status' =>true,
                'data'   => array(
                    'secretary'     => $secretaryDetails,
                    'secResAddress'  => $secResAddress,
                    'secBusAddress'  => $secBusAddress,
                    'secretaryWorkHistory'  => $secretaryWorkHistory,
                    'certificateNumber'  => $certificateNum,
                )
            ], 200);

        }else{            
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false,
            ], 200);
        }
    }

    // update secretary individual data for resubmit ...
    public function updateSecretaryData(Request $request){
        if(isset($request->id)){

            $secResAddressId = Secretary::where('id',$request->id)->value('address_id');
            if(isset($secResAddressId)){
            Address::where('id',$secResAddressId)
                        ->update(['address1' => $request->input('residentialLocalAddress1'),
                                  'address2' => $request->input('residentialLocalAddress2'),
                                  'province' => $request->input('residentialProvince'), 
                                  'district' => $request->input('residentialDistrict'), 
                                  'city' => $request->input('residentialCity'), 
                                  'postcode' => $request->input('residentialPostCode'), 
                                  'gn_division' => $request->input('rgnDivision'),     
                                 ]);
            }

            $secBusAddressId = Secretary::where('id',$request->id)->value('business_address_id');
            if(isset($secBusAddressId)){            
            Address::where('id',$secBusAddressId)
                        ->update(['address1' => $request->input('businessLocalAddress1'),
                                  'address2' => $request->input('businessLocalAddress2'),
                                  'province' => $request->input('businessProvince'), 
                                  'district' => $request->input('businessDistrict'), 
                                  'city' => $request->input('businessCity'), 
                                  'postcode' => $request->input('businessPostCode'), 
                                  'gn_division' => $request->input('bgnDivision'),     
                                 ]);
            }else{
                   
                $secAddressBusiness = new Address();
                $bAddress = $request->input('businessName');
                if(!empty($bAddress)){
                $secAddressBusiness->address1 = $request->input('businessLocalAddress1');
                $secAddressBusiness->address2 = $request->input('businessLocalAddress2');
                $secAddressBusiness->city = $request->input('businessCity');
                $secAddressBusiness->district = $request->input('businessDistrict');
                $secAddressBusiness->province = $request->input('businessProvince');
                $secAddressBusiness->country = 'Sri Lanka';
                $secAddressBusiness->postcode = $request->input('businessPostCode');
                $secAddressBusiness->gn_division = $request->input('bgnDivision');
                $secAddressBusiness->save();
                }
                $bAddressID = $secAddressBusiness->id;
                         Secretary::where('id', $request->id)
                                 ->update(['business_address_id' => $bAddressID,  
                                          ]);
            }

            Secretary::where('id', $request->id)
                        ->update(['title' => $request->input('title'),  
                                  'first_name' => $request->input('firstname'),
                                  'last_name' => $request->input('lastname'),
                                  'other_name' => $request->input('othername'),
                                  'business_name' => $request->input('businessName'),
                                  'professional_qualifications' => $request->input('pQualification'),
                                  'educational_qualifications' => $request->input('eQualification'),
                                  'work_experience' => $request->input('wExperience'),
                                  'is_unsound_mind' => $request->input('isUnsoundMind'),
                                  'is_insolvent_or_bankrupt' => $request->input('isInsolventOrBankrupt'),
                                  'reason' => $request->input('reason1'),
                                  'is_competent_court' => $request->input('isCompetentCourt'),
                                  'competent_court_type' => $request->input('reason2'),
                                  'which_applicant_is_qualified' => $request->input('subClauseQualified'),
                                  'other_details' => $request->input('otherDetails'),
                                  'is_existing_secretary' => $request->input('isExistSec'),
                                  'status' => $this->settings('SECRETARY_REQUEST_TO_RESUBMIT','key')->id,
                                  'name_si' => $request->input('sinFullName'),
                                  'name_ta' => $request->input('tamFullName'),
                                  'email' => $request->input('email'),
                                  'mobile' => $request->input('mobile'),
                                  'telephone' => $request->input('tel'),
                                 ]);

            $isExistSec = $request->input('isExistSec');
                if($isExistSec == 1){
                    $certificate = SecretaryCertificate::updateOrCreate(
                                    [
                                        'secretary_id' =>  $request->id
                                    ],
                                    [
                                        'certificate_no' => $request->input('certificateNo'),
                                        'status' => $this->settings('COMMON_STATUS_ACTIVE','key')->id,                                
                                    ]);
                }else if($isExistSec == 0){
                    $removeCertificate = SecretaryCertificate::where('secretary_id', $request->id)->delete();
                }
            $workHis = $request->input('workHis');
            foreach($workHis as $history){
                if(!empty($history)){
                    
                    SecretaryWorkingHistory::where('id',$history['id'])
                            ->update(['company_name' => $history['companyName'],  
                                      'position' => $history['position'],
                                      'from' => $history['from'],
                                      'to' =>  $history['to'],
                                    ]);
                }
            }

            return response()->json([
                'message' => 'Sucessfully updated',
                'status' =>true,
                'secId' => $request->id,
            ], 200);

        }else{            
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false,
            ], 200);
        }
    }

    // update secretary individual and firm status ...
    public function updateSecretaryStatus(Request $request){
        if(isset($request->secId)){
            if($request->type==='individual'){
                Secretary::where('id', $request->secId)
                        ->update([
                                'status' => $this->settings('SECRETARY_RESUBMITTED','key')->id,
                                ]);
                return response()->json([
                'message' => 'sucessfully updated',
                'status' =>true,
                ], 200);            

            }elseif($request->type==='firm'){
                SecretaryFirm::where('id', $request->secId)
                            ->update([
                                    'status' => $this->settings('SECRETARY_RESUBMITTED','key')->id,
                                    ]);
                return response()->json([
                'message' => 'sucessfully updated',
                'status' =>true,
                ], 200);
            }else if($request->type==='isExisting'){
                Secretary::where('id', $request->secId)
                        ->update([
                                'status' => $this->settings('SECRETARY_PENDING','key')->id,
                                ]);
                return response()->json([
                'message' => 'sucessfully updated',
                'status' =>true,
                ], 200);   
            }else if($request->type==='isExistingFirm'){
                SecretaryFirm::where('id', $request->secId)
                        ->update([
                                'status' => $this->settings('SECRETARY_PENDING','key')->id,
                                ]);
                return response()->json([
                'message' => 'sucessfully updated',
                'status' =>true,
                ], 200);   
            }else{            
                return response()->json([
                    'message' => 'error',
                    'status' =>false,
                ], 200);
            }
        }else{            
            return response()->json([
                'message' => 'can \'t find a User.',
                'status' =>false,
            ], 200);
        }
    }

    // load secretary documents comments to resubmit...
    public function getSecretaryDocComments(Request $request){
        if(isset($request->secId)){
            if($request->type==='individual'){
                 $secretaryDoc = SecretaryDocument::leftJoin('secretary_document_status','secretary_documents.id','=','secretary_document_status.secretary_document_id')
                                              ->leftJoin('documents','secretary_documents.document_id','=','documents.id')
                                                 ->where('secretary_documents.secretary_id',$request->secId)
                                                 ->where('secretary_document_status.comment_type', $this->settings('COMMENT_EXTERNAL','key')->id)
                                                   ->get(['documents.key as key','secretary_documents.document_id','secretary_document_status.status as status','secretary_document_status.comments as comments']);

            return response()->json([
                'message' => 'Sucess',
                'status' =>true,
                'data'   => array(
                    'secretaryDoc'     => $secretaryDoc,
                )
            ], 200);

            }elseif($request->type==='firm'){
             $secretaryFirmDoc = SecretaryDocument::leftJoin('secretary_document_status','secretary_documents.id','=','secretary_document_status.secretary_document_id')
                                              ->leftJoin('documents','secretary_documents.document_id','=','documents.id')
                                                 ->where('secretary_documents.firm_id',$request->secId)
                                                 ->where('secretary_document_status.comment_type', $this->settings('COMMENT_EXTERNAL','key')->id)
                                                   ->get(['documents.key as key','secretary_documents.document_id','secretary_document_status.status as status','secretary_document_status.comments as comments']);


            return response()->json([
                'message' => 'Sucess',
                'status' =>true,
                'data'   => array(
                    'secretaryFirmDoc'     => $secretaryFirmDoc,
                )
            ], 200);

            }  
        }else{            
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false,
            ], 200);
        }
    }

    // load secretary general comments to resubmit...
    public function getSecretaryComments(Request $request){
        if(isset($request->secId)){
            if($request->type==='individual'){
                 $secretaryComment = SecretaryComment::where('secretary_id',$request->secId) 
                                                     ->where('comment_type', $this->settings('COMMENT_EXTERNAL','key')->id) 
                                                       ->get(['id','comments','created_at']);

            return response()->json([
                'message' => 'Sucess',
                'status' =>true,
                'data'   => array(
                    'secretaryComment'     => $secretaryComment,
                )
            ], 200);

            }elseif($request->type==='firm'){
             $secretaryComment = SecretaryComment::where('firm_id',$request->secId) 
                                                 ->where('comment_type', $this->settings('COMMENT_EXTERNAL','key')->id) 
                                                   ->get(['id','comments','created_at']);

            return response()->json([
                'message' => 'Sucess',
                'status' =>true,
                'data'   => array(
                    'secretaryComment'     => $secretaryComment,
                )
            ], 200);

            }  
        }else{            
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' =>false,
            ], 200);
        }
    }

    // for update uploaded secretary pdf to resubmit...
    public function secretaryUpdateUploadedPdf(Request $request){

        if(isset($request)){

            $fileName =  uniqid().'.pdf';
            $token = md5(uniqid());
    
            $secId = $request->secId;
            $firmId = $request->firmId;
            $docType = $request->docType;
            $pdfName = $request->filename;
    
            if(isset($secId)){
                $path = 'secretary/'.$secId;
            }elseif (isset($firmId)) {
                $path = 'secretaryfirm/'.$firmId;
            }        
            $path=  $request->file('uploadFile')->storeAs($path,$fileName,'sftp');               
           
            SecretaryDocument::where('id', $request->docId)
                            ->update(['status' => $this->settings('DOCUMENT_PENDING','key')->id,
                            'name' => $pdfName,
                            'description' => $request->description,
                            'file_token' => $token,
                            'path' => $path]); 
    
              return response()->json([
                'message' => 'File uploaded successfully.',
                'status' =>true,
                'name' =>basename($path),
                'doctype' =>$docType,
                'token' =>$token,
                'pdfname' =>$pdfName,
                ], 200);
    
        }

    } 
     
    // for load secretary uploaded files with resumit comments...
    public function secretaryFileLoad(Request $request){
        if(isset($request)){
            if($request->type=='individual'){
            $secId = $request->secId;            
            $uploadedPdf = SecretaryDocument::leftJoin('documents','secretary_documents.document_id','=','documents.id')
                                            ->leftJoin('secretary_document_status', function ($join) {
                                             $join->on('secretary_documents.id', '=', 'secretary_document_status.secretary_document_id')
                                               ->where('secretary_document_status.comment_type', $this->settings('COMMENT_EXTERNAL','key')->id);})
                                            ->leftJoin('settings','secretary_documents.status','=','settings.id')
                                               ->where('secretary_documents.secretary_id',$secId)                                               
                                               ->where('secretary_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                                 ->get(['secretary_documents.id','secretary_documents.name','secretary_documents.file_token','documents.key as dockey','secretary_documents.document_id','secretary_document_status.status as status','secretary_document_status.comments as comments','settings.value as value','settings.key as setkey']);
                                                
            if(isset($uploadedPdf)){
                return response()->json([
                    'file' => $uploadedPdf,
                    'status' =>true,
                    'data'   => array(
                        'file'     => $uploadedPdf,
                    )
                ], 200);

                }
            }
            elseif($request->type=='firm'){
                $secId = $request->secId;
                $uploadedPdf = SecretaryDocument::leftJoin('documents','secretary_documents.document_id','=','documents.id')
                                                ->leftJoin('secretary_document_status', function ($join) {
                                                 $join->on('secretary_documents.id', '=', 'secretary_document_status.secretary_document_id')
                                                   ->where('secretary_document_status.comment_type', $this->settings('COMMENT_EXTERNAL','key')->id);})
                                                ->leftJoin('settings','secretary_documents.status','=','settings.id')
                                                   ->where('secretary_documents.firm_id',$secId)                                               
                                                   ->where('secretary_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                                                     ->get(['secretary_documents.id','secretary_documents.name','secretary_documents.file_token','documents.key as dockey','secretary_documents.document_id','secretary_document_status.status as status','secretary_document_status.comments as comments','settings.value as value','settings.key as setkey']);
                if(isset($uploadedPdf)){
                    return response()->json([
                        'file' => $uploadedPdf,
                        'status' =>true,
                        'data'   => array(
                            'file'     => $uploadedPdf,
                        )
                    ], 200);
                }
            }
        }else{
            return response()->json([
                'status' =>false,
            ], 200);
        }

    }

    // for delete secretary pdf files in resubmit process...
    function deleteSecretaryPdfUpdate(Request $request){
        if(isset($request)){
        $docId = $request->documentId;
        $type = $request->type;
        $docstatusid = SecretaryDocumentStatus::where('secretary_document_id', $docId)->first();

        if($docstatusid){

            if($type =='additionalUpload'){

                $document = SecretaryDocument::where('id', $docId)->first();
                if($docstatusid->status == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id){

                    $delete = Storage::disk('sftp')->delete($document->path);
                SecretaryDocument::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);

                }
                else{

                    $delete = Storage::disk('sftp')->delete($document->path);
                SecretaryDocument::where('id', $docId)
                ->update(['status' => $this->settings('DOCUMENT_REQUESTED','key')->id,
                'name' => NULL,
                'file_token' => NULL,
                'path' => NULL]);

                }          
            }
            else{

                $document = SecretaryDocument::where('id', $docId)->first();
            $delete = Storage::disk('sftp')->delete($document->path);

            SecretaryDocument::where('id', $docId)
                          ->update(['status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT','key')->id,
                                    'name' => NULL,
                                    'file_token' => NULL,
                                    'path' => NULL]);
            }

        }
        else{
            $document = SecretaryDocument::where('id', $docId)->first();
            $delete = Storage::disk('sftp')->delete($document->path);
            $remove = SecretaryDocument::where('id', $docId)->delete();
        }
        return response()->json([
            'message' => 'File removed successfully.',
            'status' =>true,
        ], 200);
        }
    }


    // secretary certificate request
    function secretaryCertificateRequest(Request $request){
        if(isset($request)){
            $type = $request->type;
            $quantity = $request->quantity;
            $secID = $request->secID;
            $user = User::where('email', $request->input('email'))->first();

            if(!$request->reqID){
                if($type=="induvidual"){

                    $secCerReq = new SecretaryCertificateRequest();
                    $secCerReq->secretary_id = $secID;
                    $secCerReq->secretary_type = $this->settings('MODULE_SECRETARY','key')->id;
                    $secCerReq->request_by = $user->id;
                    $secCerReq->status = $this->settings('PRINT_PENDING','key')->id;
                    $secCerReq->no_of_copies= $quantity;
                    $secCerReq->save();
    
                    $secCerReqId = $secCerReq->id;
    
                    return response()->json([
                        'message' => 'successfully',
                        'status' =>true,
                        'secCerReqId' => $secCerReqId,
                    ], 200);
    
                }elseif($type=="firm"){
                    $secCerReq = new SecretaryCertificateRequest();
                    $secCerReq->secretary_id = $secID;
                    $secCerReq->secretary_type = $this->settings('MODULE_SECRETARY_FIRM','key')->id;
                    $secCerReq->request_by = $user->id;
                    $secCerReq->status = $this->settings('PRINT_PENDING','key')->id;
                    $secCerReq->no_of_copies= $quantity;
                    $secCerReq->save();
    
                    $secCerReqId = $secCerReq->id;
    
                    return response()->json([
                        'message' => 'successfully',
                        'status' =>true,
                        'secCerReqId' => $secCerReqId,
                    ], 200);
                }else{
                    return response()->json([
                        'message' => 'We can \'t find a Secretary.',
                        'status' =>false,
                        'issec' => false,
                    ], 200);
                }
            }else{
                $reqID=$request->reqID;
                
                if($type=="induvidual"){
                    SecretaryCertificateRequest::where('id', $reqID)
                        ->update(['no_of_copies' => $quantity]);
    
                    $secCerReqId = $reqID;
    
                    return response()->json([
                        'message' => 'successfully',
                        'status' =>true,
                        'secCerReqId' => $secCerReqId,
                    ], 200);
    
                }elseif($type=="firm"){
                    SecretaryCertificateRequest::where('id', $reqID)
                        ->update(['no_of_copies' => $quantity]);
    
                    $secCerReqId = $reqID;
    
                    return response()->json([
                        'message' => 'successfully',
                        'status' =>true,
                        'secCerReqId' => $secCerReqId,
                    ], 200);
                }else{
                    return response()->json([
                        'message' => 'We can \'t find a Secretary.',
                        'status' =>false,
                        'issec' => false,
                    ], 200);
                } 

            }
            
            
        }
    }

    

    //Load previous approved debentures record  using company id number...
    public function loadPreSecretaryCertificateRequest(Request $request){

        if(!$request->secID){
            return response()->json([
                'message' => 'We can \'t find a Secretary.',
                'status' =>false,
                'issec' => false,
            ], 200);
        }

        $type = $request->type;

        if($type=="induvidual"){
            $requestRecord = SecretaryCertificateRequest::where('secretary_id',$request->secID)
                                ->where('secretary_type', $this->settings('MODULE_SECRETARY','key')->id)
                                ->leftJoin('settings','secretary_certificate_requests.status','=','settings.id')
                                ->orderBy('secretary_certificate_requests.id','DESC')
                                ->limit(1)
                                ->get(['secretary_certificate_requests.id','secretary_certificate_requests.secretary_id','settings.key as status','secretary_certificate_requests.no_of_copies']);
            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'preSecCerRequest'    => $requestRecord,                           
            ], 200);
        }elseif($type=="firm"){
            $requestRecord = SecretaryCertificateRequest::where('secretary_id',$request->secID)
                                ->where('secretary_type', $this->settings('MODULE_SECRETARY_FIRM','key')->id)
                                ->leftJoin('settings','secretary_certificate_requests.status','=','settings.id')
                                ->orderBy('secretary_certificate_requests.id','DESC')
                                ->limit(1)
                                ->get(['secretary_certificate_requests.id','secretary_certificate_requests.secretary_id','settings.key as status','secretary_certificate_requests.no_of_copies']);
            
            return response()->json([
                'message' => 'Sucess!!!',
                'status' =>true, // to load data from eroc company...
                'preSecCerRequest'    => $requestRecord,                           
            ], 200);
        }else{
            return response()->json([
                'message' => 'no previous record',
                'status' =>false,                           
            ], 200);
        }   
    }
  
    
}
