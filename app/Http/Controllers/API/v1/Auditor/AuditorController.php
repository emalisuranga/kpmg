<?php

namespace App\Http\Controllers\API\v1\Auditor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helper\_helper;
use App\User;
use App\People;
use App\Auditor;
use App\Address;
use App\Setting;
use App\Documents;
use App\AuditorFirm;
use App\DocumentsGroup;
use App\AuditorComment;
use App\AuditorRenewal;
use App\AuditorCertificate;
use App\AuditorDocumentStatus;
use App\AuditorDocument;
use App\AuditorFirmPartner;
use App\AuditorFirmPartnerChange;
use App\AuditorChangeRequestItem;
use App\AuditorItemChange;
use App\AuditorChangeType;
use Storage;
use App;
use URL;
use PDF;

class AuditorController extends Controller
{
    use _helper;

    // save individual auditor sl and nonsl data to database...
    public function saveAuditorData(Request $request)
    {
        if (isset($request->nic) xor isset($request->passport)) {

            $isAlreadyAuditor;
            $nic = strtoupper($request->nic);
            if (isset($nic)) {
                $isAlreadyAuditor = Auditor::where('nic', 'ILIKE', "$nic")->value('id');
            } else if ($request->passport) {
                $isAlreadyAuditor = Auditor::where('passport_no', $request->passport)->value('id');
            }
            if (!isset($isAlreadyAuditor)) {

                $audAddressResidential = new Address();
                $audAddressResidential->address1 = $request->input('residentialLocalAddress1');
                $audAddressResidential->address2 = $request->input('residentialLocalAddress2');
                $audAddressResidential->city = $request->input('residentialCity');
                $audAddressResidential->district = $request->input('residentialDistrict');
                $audAddressResidential->province = $request->input('residentialProvince');
                $audAddressResidential->postcode = $request->input('residentialPostCode');
                $audAddressResidential->gn_division = $request->input('rgnDivision');
                $audAddressResidential->country = 'SriLanka';
                $audAddressResidential->save();

                $audAddressBusiness = new Address();
                $bAddress = $request->input('businessName');
                if (!empty($bAddress)) {
                    $audAddressBusiness->address1 = $request->input('businessLocalAddress1');
                    $audAddressBusiness->address2 = $request->input('businessLocalAddress2');
                    $audAddressBusiness->city = $request->input('businessCity');
                    $audAddressBusiness->district = $request->input('businessDistrict');
                    $audAddressBusiness->province = $request->input('businessProvince');
                    $audAddressBusiness->postcode = $request->input('businessPostCode');
                    $audAddressBusiness->gn_division = $request->input('gnDivision');
                    $audAddressBusiness->country = 'SriLanka';
                    $audAddressBusiness->save();
                }

                $regUser = $request->input('registeredUser');

                $people = new People();
                if ($regUser == false) {
                    // if not a registered user, bellow details insert into people table...
                    $people->title = $request->input('title');
                    $people->first_name = $request->input('firstname');
                    $people->email = $request->input('email');
                    $people->mobile = $request->input('mobile');
                    $people->telephone = $request->input('tel');
                    $people->last_name = $request->input('lastname');
                    $people->nic = $request->input('nic');
                    $people->passport_no = $request->input('passport');
                    $people->address_id = $audAddressResidential->id;
                    $people->save();
                }

                // if applicant already roc user... 
                $audNic = '';
                if (isset($request->nic)) {
                    $audNic = strtoupper($request->nic);
                    $peopleId = People::where('nic', 'ILIKE', "$audNic")->value('id');
                } else if (isset($request->passport)) {
                    $audPassport = $request->input('passport');
                    $peopleId = People::where('passport_no', $audPassport)->value('id');
                }

                $loggedUserEmail = $request->input('loggedInUser');
                $loggedUserId = User::where('email', $loggedUserEmail)->value('id');

                $audinfo = new Auditor();
                $audinfo->title = $request->input('title');
                $audinfo->first_name = $request->input('firstname');
                $audinfo->last_name = $request->input('lastname');
                $audinfo->business_name = $request->input('businessName');
                $audinfo->email = $request->input('email');
                $audinfo->mobile = $request->input('mobile');
                $audinfo->telephone = $request->input('tel');
                $audinfo->nic = $audNic;
                $audinfo->passport_no = $request->input('passport');
                $audinfo->dob = $request->input('birthDay');
                $audinfo->nationality = $request->input('nationality');
                $audinfo->race = $request->input('race');
                $audinfo->where_domiciled = $request->input('whereDomiciled');
                $audinfo->from_residence_in_srilanka = $request->input('dateTakeResidenceInSrilanka');
                $audinfo->continuously_residence_in_srilanka = $request->input('dateConResidenceInSrilanka');
                $audinfo->particulars_of_immovable_property = $request->input('ownedProperty');
                $audinfo->other_facts_to_the_srilanka_domicile = $request->input('otherFacts');
                $audinfo->address_id = $audAddressResidential->id;
                $audinfo->business_address_id = $audAddressBusiness->id;
                $audinfo->professional_qualifications = $request->input('pQualification');
                $audinfo->is_unsound_mind = $request->input('isUnsoundMind');
                $audinfo->is_insolvent_or_bankrupt = $request->input('isInsolventOrBankrupt');
                $audinfo->reason = $request->input('reason1');
                $audinfo->is_competent_court = $request->input('isCompetentCourt');
                $audinfo->competent_court_type = $request->input('reason2');
                $audinfo->other_details = $request->input('otherDetails');
                $audinfo->which_applicant_is_qualified = $request->input('subClauseQualified');
                $audinfo->status = $this->settings('AUDITOR_PROCESSING', 'key')->id;
                $audinfo->created_by = $loggedUserId;
                $audinfo->is_existing_auditor = $request->input('isExistAud');
                $audinfo->name_si = $request->input('sinFullName');
                $audinfo->name_ta = $request->input('tamFullName');
                $audinfo->address_si = $request->input('sinAd');
                $audinfo->address_ta = $request->input('tamAd');
                if ($regUser == false) {
                    $audinfo->people_id = $people->id;   // if applicant is not a roc user... 
                } else {
                    $audinfo->people_id = $peopleId;  // if applicant already roc user... 
                }
                $audinfo->save();
                $isExistAud = $request->input('isExistAud');
                if ($isExistAud == 1) {
                    $audCertificate = new AuditorCertificate();
                    $audCertificate->auditor_id = $audinfo->id;
                    $audCertificate->certificate_no = $request->input('certificateNo');
                    $audCertificate->status = $this->settings('COMMON_STATUS_ACTIVE', 'key')->id;
                    $audCertificate->save();
                }

                $audId = $audinfo->id;
                return response()->json([
                    'message' => 'successfully inserted!!!',
                    'status' => true,
                    'audId' => $audId,
                ], 200);
            } else {

                $audStatus;
                $audIdUpdate;

                if (isset($nic)) {
                    $audStatus = Auditor::leftJoin('settings', 'auditors.status', '=', 'settings.id')
                        ->where('auditors.nic', 'ILIKE', "$nic")
                        ->get(['settings.key as statusKey']);
                    $audIdUpdate = Auditor::where('nic', 'ILIKE', "$nic")->value('id');
                } else if ($request->passport) {
                    $audStatus = Auditor::leftJoin('settings', 'auditors.status', '=', 'settings.id')
                        ->where('auditors.passport_no', $request->passport)
                        ->get(['settings.key as statusKey']);
                    $audIdUpdate = Auditor::where('passport_no', $request->passport)->value('id');
                }

                if ($audStatus[0]['statusKey'] === 'AUDITOR_PROCESSING') {

                    $audAddressId = Auditor::where('id', $audIdUpdate)->value('address_id');
                    if (isset($audAddressId)) {
                        Address::where('id', $audAddressId)
                            ->update([
                                'address1' => $request->input('residentialLocalAddress1'),
                                'address2' => $request->input('residentialLocalAddress2'),
                                'province' => $request->input('residentialProvince'),
                                'district' => $request->input('residentialDistrict'),
                                'city' => $request->input('residentialCity'),
                                'postcode' => $request->input('residentialPostCode'),
                                'gn_division' => $request->input('rgnDivision'),
                            ]);
                    }

                    $businessAddressId = Auditor::where('id', $audIdUpdate)->value('business_address_id');
                    if (isset($businessAddressId)) {
                        Address::where('id', $businessAddressId)
                            ->update([
                                'address1' => $request->input('businessLocalAddress1'),
                                'address2' => $request->input('businessLocalAddress2'),
                                'province' => $request->input('businessProvince'),
                                'district' => $request->input('businessDistrict'),
                                'city' => $request->input('businessCity'),
                                'postcode' => $request->input('businessPostCode'),
                                'gn_division' => $request->input('gnDivision'),
                            ]);
                    } else {

                        $audAddressBusiness = new Address();
                        $bAddress = $request->input('businessName');
                        if (!empty($bAddress)) {
                            $audAddressBusiness->address1 = $request->input('businessLocalAddress1');
                            $audAddressBusiness->address2 = $request->input('businessLocalAddress2');
                            $audAddressBusiness->city = $request->input('businessCity');
                            $audAddressBusiness->district = $request->input('businessDistrict');
                            $audAddressBusiness->province = $request->input('businessProvince');
                            $audAddressBusiness->country = 'SriLanka';
                            $audAddressBusiness->postcode = $request->input('businessPostCode');
                            $audAddressBusiness->gn_division = $request->input('gnDivision');
                            $audAddressBusiness->save();
                        }
                        $bAddressID = $audAddressBusiness->id;
                        Auditor::where('id', $audIdUpdate)
                            ->update([
                                'business_address_id' => $bAddressID,
                            ]);
                    }

                    Auditor::where('id', $audIdUpdate)
                        ->update([
                            'title' => $request->input('title'),
                            'first_name' => $request->input('firstname'),
                            'last_name' => $request->input('lastname'),
                            'business_name' => $request->input('businessName'),
                            'dob' => $request->input('birthDay'),
                            'nationality' => $request->input('nationality'),
                            'race' => $request->input('race'),
                            'where_domiciled' => $request->input('whereDomiciled'),
                            'from_residence_in_srilanka' => $request->input('dateTakeResidenceInSrilanka'),
                            'continuously_residence_in_srilanka' => $request->input('dateConResidenceInSrilanka'),
                            'particulars_of_immovable_property' => $request->input('ownedProperty'),
                            'other_facts_to_the_srilanka_domicile' => $request->input('otherFacts'),
                            'professional_qualifications' => $request->input('pQualification'),
                            'is_unsound_mind' => $request->input('isUnsoundMind'),
                            'is_insolvent_or_bankrupt' => $request->input('isInsolventOrBankrupt'),
                            'reason' => $request->input('reason1'),
                            'is_competent_court' => $request->input('isCompetentCourt'),
                            'competent_court_type' => $request->input('reason2'),
                            'other_details' => $request->input('otherDetails'),
                            'is_existing_auditor' => $request->input('isExistAud'),
                            'which_applicant_is_qualified' => $request->input('subClauseQualified'),
                            'status' => $this->settings('AUDITOR_PROCESSING', 'key')->id,
                            'name_si' => $request->input('sinFullName'),
                            'name_ta' => $request->input('tamFullName'),
                            'address_si' => $request->input('sinAd'),
                            'address_ta' => $request->input('tamAd'),
                            'email' => $request->input('email'),
                            'telephone' => $request->input('tel'),
                            'mobile' => $request->input('mobile'),
                        ]);

                    $isExistAud = $request->input('isExistAud');
                    if ($isExistAud == 1) {
                        $certificate = AuditorCertificate::updateOrCreate(
                            [
                                'auditor_id' =>  $audIdUpdate
                            ],
                            [
                                'certificate_no' => $request->input('certificateNo'),
                                'status' => $this->settings('COMMON_STATUS_ACTIVE', 'key')->id,
                            ]
                        );
                    } else if ($isExistAud == 0) {
                        $removeCertificate = AuditorCertificate::where('auditor_id', $audIdUpdate)->delete();
                    }

                    return response()->json([
                        'message' => 'successfully updated!!!',
                        'status' => true,
                        'audId' => $audIdUpdate,
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'auditor is already registered!!!',
                        'status' => false,
                    ], 200);
                }
            }
        } else {
            return response()->json([
                'message' => 'please insert valid nic number!!!',
                'status' => false,
            ], 200);
        }
    }

    public function loadAuditorDataFirmCard(Request $request)
    {

        try {
            if (!($request->id)) {
                return response()->json([
                    'message' => 'We can \'t find a Firm.',
                    'status' => false,
                    'addNew' => false,
                    'isauditor' => false,
                    'isPros' => false,
                ], 200);
            } elseif ($request->id) {

                $auditorfirm = AuditorFirm::leftJoin('addresses', 'auditor_firms.address_id', '=', 'addresses.id')
                    ->leftJoin('settings', 'settings.id', '=', 'auditor_firms.status')
                    ->where('auditor_firms.id', '=', $request->id)
                    ->select(
                        'auditor_firms.id',
                        'auditor_firms.name',
                        'auditor_firms.name_si',
                        'auditor_firms.name_ta',
                        'auditor_firms.email',
                        'auditor_firms.created_at',
                        'auditor_firms.updated_at',
                        'addresses.address1',
                        'addresses.address2',
                        'addresses.city',
                        'addresses.district',
                        'addresses.province',
                        'addresses.country',
                        'addresses.postcode',
                        'settings.value as status',
                        'settings.key'
                    )->first();


                $auditorfirmchangedetails = AuditorChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'auditor_change_requests.status')
                    ->where('auditor_change_requests.auditor_id', $request->id)
                    ->where('auditor_change_requests.request_type', $this->settings('AUDITOR_FIRM_CHANGE', 'key')->id)
                    ->where('auditor_change_requests.table_type', $this->settings('AUDITOR_FIRMS', 'key')->id)
                    ->where('auditor_change_requests.status', '!=', $this->settings('AUDITOR_CHANGE_APPROVED', 'key')->id)
                    ->where('auditor_change_requests.status', '!=', $this->settings('AUDITOR_CHANGE_REJECTED', 'key')->id)
                    ->orderBy('auditor_change_requests.created_at', 'DESC')
                    ->limit(1)
                    ->get(['auditor_change_requests.id', 'auditor_change_requests.table_type', 'settings.value as value', 'settings.key as setKey']);

                if (count($auditorfirmchangedetails) != 1) {
                    $i = 0;
                    $pages_arrayac[] = (object) array('changeReqID' => null, 'setKey' => '', 'setValue' => '');

                    $auditorfirmchangedetails = $pages_arrayac;
                }

                if ($auditorfirm) {
                    $response = [
                        'auditorfirminfo' => $auditorfirm,
                        'auditorfirmchangedetails' => $auditorfirmchangedetails,
                    ];
                    return response()->json($response, 200);
                } else {

                    return response()->json(['error' => 'No auditor firm details'], 200);
                }
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function loadAuditorDataIndCard(Request $request)
    {

        try {
            if (!($request->id)) {
                return response()->json([
                    'message' => 'We can \'t find a User.',
                    'status' => false,
                    'addNew' => false,
                    'isauditor' => false,
                    'isPros' => false,
                ], 200);
            } elseif ($request->id) {

                $auditor = Auditor::leftJoin('addresses', 'auditors.address_id', '=', 'addresses.id')
                    ->leftJoin('settings', 'settings.id', '=', 'auditors.status')
                    ->leftJoin('addresses as s1', 's1.id', '=', 'auditors.business_address_id')
                    ->where('auditors.id', '=', $request->id)
                    ->select(
                        'auditors.id',
                        'auditors.first_name',
                        'auditors.last_name',
                        'auditors.name_si',
                        'auditors.name_ta',
                        'auditors.email',
                        'auditors.created_at',
                        'auditors.updated_at',
                        'addresses.address1',
                        'addresses.address2',
                        'addresses.city',
                        'addresses.district',
                        'addresses.province',
                        'addresses.country',
                        'addresses.postcode',
                        'settings.value as status',
                        's1.address1 as baddress1',
                        's1.address2 as baddress2',
                        's1.city as bcity',
                        's1.district as bdistrict',
                        's1.province as bprovince',
                        's1.country as bcountry',
                        's1.postcode as bpostcode',
                        'settings.key'
                    )->first();


                $auditorchangedetails = AuditorChangeRequestItem::leftJoin('settings', 'settings.id', '=', 'auditor_change_requests.status')
                    ->where('auditor_change_requests.auditor_id', $request->id)
                    ->where('auditor_change_requests.request_type', $this->settings('AUDITOR_CHANGE', 'key')->id)
                    ->where('auditor_change_requests.table_type', $this->settings('AUDITORS', 'key')->id)
                    ->where('auditor_change_requests.status', '!=', $this->settings('AUDITOR_CHANGE_APPROVED', 'key')->id)
                    ->where('auditor_change_requests.status', '!=', $this->settings('AUDITOR_CHANGE_REJECTED', 'key')->id)
                    ->orderBy('auditor_change_requests.created_at', 'DESC')
                    ->limit(1)
                    ->get(['auditor_change_requests.id', 'auditor_change_requests.table_type', 'settings.value as value', 'settings.key as setKey']);

                if (count($auditorchangedetails) != 1) {
                    $i = 0;
                    $pages_arrayac[] = (object) array('changeReqID' => null, 'setKey' => '', 'setValue' => '');

                    $auditorchangedetails = $pages_arrayac;
                }

                if ($auditor) {
                    $response = [
                        'auditorinfo' => $auditor,
                        'auditorchangedetails' => $auditorchangedetails,
                    ];
                    return response()->json($response, 200);
                } else {

                    return response()->json(['error' => 'No auditor details'], 200);
                }
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // load individual auditor srilankan data using nic number...
    public function loadAuditorDataSL(Request $request)
    {
        // return $request;

        if (!($request->nic)) {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
                'addNew' => false,
                'isauditor' => false,
                'isPros' => false,
            ], 200);
        } elseif ($request->nic) {
            $nic = strtoupper($request->nic);
            // to check applicant already registered as a auditor...
            $isAlreadyAuditor = Auditor::where('nic', 'ILIKE', "$nic")->value('id');
            if (!$isAlreadyAuditor) {
                $auditorDetails = People::where('nic', 'ILIKE', "$nic")->first();

                if (isset($auditorDetails)) {
                    return response()->json([
                        'message' => 'Sucess!!!',
                        'user' => true, // to check applicant already registered as roc user...  
                        'status' => true, // to load data from eroc people...
                        'isauditor' => false,
                        'addNew' => true,
                        'isPros' => false,
                        'data'   => array(
                            'auditor'     => $auditorDetails,
                        )
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'We can \'t find a Registered User.',
                        'user' => false,
                        'status' => false,
                        'isauditor' => false,
                        'addNew' => true,
                        'isPros' => false,
                    ], 200);
                }
            } else if ($isAlreadyAuditor) {
                $isProsAuditorID = Auditor::where('nic', 'ILIKE', "$nic")
                    ->where('status', $this->settings('AUDITOR_PROCESSING', 'key')->id)->value('id');

                $isApprovedAuditor = Auditor::where('nic', 'ILIKE', "$nic")
                    ->where('status', $this->settings('AUDITOR_APPROVED', 'key')->id);
                if ($request->qualification == 'CM') {
                    $isApprovedAuditor = $isApprovedAuditor->where('which_applicant_is_qualified', '=', '5(1)a')->first();
                } else {
                    // return $request;
                    $isApprovedAuditor = $isApprovedAuditor->where('which_applicant_is_qualified', '!=', '5(1)a')->first();
                }

                if (isset($isProsAuditorID)) {
                    return response()->json([
                        'message' => 'auditor processing',
                        'isauditor' => false,
                        // 'audID' => $isProsAuditorID,  
                        'audID' => array('auddata' => $isApprovedAuditor,),
                        'status' => false,
                        'addNew' => false,
                        'isPros' => true,
                    ], 200);
                } else if (isset($isApprovedAuditor)) {
                    return response()->json([
                        'message' => 'approved auditor',
                        'isauditor' => true,
                        'status' => false,
                        'data'   => array('auddata' => $isApprovedAuditor,),
                        'addNew' => false,
                        'isPros' => false,
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'still not approved auditor',
                        'isauditor' => false,
                        'status' => false,
                        'addNew' => false,
                        'isPros' => false,
                    ], 200);
                }
            }
        }
    }

    // load individual auditor non srilankan data using passport number...
    public function loadAuditorDataNonSL(Request $request)
    {

        if (!($request->passport)) {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
                'isauditor' => false,
            ], 200);
        } elseif ($request->passport) {
            // to check applicant already registered as a auditor...
            $isAlreadyAuditor = Auditor::where('passport_no', $request->passport)
                ->where('status', $this->settings('AUDITOR_APPROVED', 'key')->id)->first();
            if (!$isAlreadyAuditor) {
                $auditorDetails = People::where('passport_no', $request->passport)->first();

                if (isset($auditorDetails)) {
                    return response()->json([
                        'message' => 'Sucess!!!',
                        'user' => true, // to check applicant already registered as roc user...  
                        'status' => true, // to load data from eroc people...
                        'isauditor' => false,
                        'data'   => array(
                            'auditor'     => $auditorDetails,
                        )
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'We can \'t find a Registered User.',
                        'user' => false,
                        'status' => false,
                        'isauditor' => false,
                    ], 200);
                }
            } else if ($isAlreadyAuditor) {
                return response()->json([
                    'message' => 'Sorry you can not register again',
                    'isauditor' => true,
                    'data'   => array('auddata' => $isAlreadyAuditor,),
                    'status' => false,
                ], 200);
            }
        }
    }

    // for upload auditor individual and firm pdf...
    public function auditorUploadPdf(Request $request)
    {

        if (isset($request)) {

            $fileName =  uniqid() . '.pdf';
            $token = md5(uniqid());

            $audId = $request->audId;
            $firmId = $request->firmId;
            $docType = $request->docType;
            $pdfName = $request->filename;

            $description = $request->description;
            if ($description == 'undefined') {
                $description = NULL;
            }

            if (isset($audId)) {
                $path = 'auditor/' . $audId;
            } elseif (isset($firmId)) {
                $path = 'auditorfirm/' . $firmId;
            }
            $path =  $request->file('uploadFile')->storeAs($path, $fileName, 'sftp');


            $docId;
            if ($docType == 'applicationUpload') {
                $docIdArray = DocumentsGroup::leftJoin('documents', 'document_groups.id', '=', 'documents.document_group_id')
                    ->where('document_groups.request_type', 'AUDITOR')
                    ->where('documents.key', 'AUDITOR_APPLICATION')
                    ->get(['documents.id']);
                $docId = $docIdArray[0]['id'];
            } elseif ($docType == 'pCertificateUpload') {
                $docIdArray = DocumentsGroup::leftJoin('documents', 'document_groups.id', '=', 'documents.document_group_id')
                    ->where('document_groups.request_type', 'AUDITOR')
                    ->where('documents.key', 'AUDITOR_PROFESSIONAL_QUALIFICATION')
                    ->get(['documents.id']);
                $docId = $docIdArray[0]['id'];
            } elseif ($docType == 'renewalFormUpload') {
                $docIdArray = DocumentsGroup::leftJoin('documents', 'document_groups.id', '=', 'documents.document_group_id')
                    ->where('document_groups.request_type', 'AUDITOR')
                    ->where('documents.key', 'AUDITOR_RENEWAL_APPLICATION')
                    ->get(['documents.id']);
                $docId = $docIdArray[0]['id'];
            } elseif ($docType == 'renewalPQUpload') {
                $docIdArray = DocumentsGroup::leftJoin('documents', 'document_groups.id', '=', 'documents.document_group_id')
                    ->where('document_groups.request_type', 'AUDITOR')
                    ->where('documents.key', 'AUDITOR_RENEWAL_PROF_QUALIFICATION')
                    ->get(['documents.id']);
                $docId = $docIdArray[0]['id'];
            } elseif ($docType == 'regCertificateUpload') {
                $docIdArray = DocumentsGroup::leftJoin('documents', 'document_groups.id', '=', 'documents.document_group_id')
                    ->where('document_groups.request_type', 'AUDITOR')
                    ->where('documents.key', 'AUDITOR_CERTIFICATE')
                    ->get(['documents.id']);
                $docId = $docIdArray[0]['id'];
            } elseif ($docType == 'PracticeCertificateUpload') {
                $docIdArray = DocumentsGroup::leftJoin('documents', 'document_groups.id', '=', 'documents.document_group_id')
                    ->where('document_groups.request_type', 'AUDITOR')
                    ->where('documents.key', 'AUDITOR_CERTIFICATE_TO_PRACTICE')
                    ->get(['documents.id']);
                $docId = $docIdArray[0]['id'];
            }

            $audDoc = new AuditorDocument;
            $audDoc->document_id = $docId;
            $audDoc->auditor_id = $audId;
            $audDoc->firm_id = $firmId;
            $audDoc->name = $pdfName;
            $audDoc->description = $description;
            $audDoc->file_token = $token;
            $audDoc->path = $path;
            $audDoc->status =  $this->settings('DOCUMENT_PENDING', 'key')->id;
            $audDoc->save();

            $auddocId = $audDoc->id;

            return response()->json([
                'message' => 'File uploaded successfully.',
                'status' => true,
                'name' => basename($path),
                'doctype' => $docType,
                'docid' => $auddocId, // for delete pdf...
                'token' => $token,
                'pdfname' => $pdfName,
            ], 200);
        }
    }

    // for delete auditor pdf files...
    function deleteAuditorPdf(Request $request)
    {
        if (isset($request)) {
            $docId = $request->documentId;
            if ($docId) {
                $document = AuditorDocument::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                $remove = AuditorDocument::where('id', $docId)->delete();
            }
            return response()->json([
                'message' => 'File removed successfully.',
                'status' => true,
            ], 200);
        }
    }

    // for individual and firm auditor payments...
    public function auditorPay(Request $request)
    {
        if (isset($request)) {
            if ($request->audType == 'individual') {
                $audId = $request->audId;
                $audPay =  array(
                    'status'    => $this->settings('AUDITOR_PENDING', 'key')->id
                );
                Auditor::where('id',  $audId)->update($audPay);
            } elseif ($request->audType == 'firm') {
                $firmId = $request->audId;
                $audPay =  array(
                    'status'    => $this->settings('AUDITOR_PENDING', 'key')->id
                );
                AuditorFirm::where('id',  $firmId)->update($audPay);
            }
            return response()->json([
                'message' => 'Payment Successful.',
                'status' => true,
            ], 200);
        }
    }

    // for load individual and firm registered auditor data to profile card...
    public function loadRegisteredAuditorData(Request $request)
    {
        if ($request) {
            $loggedUserEmail = $request->input('loggedInUser');
            $loggedUserId = User::where('email', $loggedUserEmail)->value('id');

            $auditors = Auditor::leftJoin('auditor_certificates', 'auditors.id', '=', 'auditor_certificates.auditor_id')
                ->leftJoin('settings as s1', 'auditors.status', '=', 's1.id')
                ->where('auditors.created_by', $loggedUserId)
                ->where('auditors.status', '!=', $this->settings('COMMON_STATUS_EDIT', 'key')->id)
                ->get(['auditors.id', 'auditors.first_name', 'auditors.last_name', 'auditors.nic', 'auditor_certificates.certificate_no as certificate_no', 'auditors.passport_no', 'auditors.created_at', 's1.key as status', 's1.value as value']);

            $auds = array();
            foreach ($auditors as $auditor) {
                $renewal = AuditorRenewal::where('auditor_id', $auditor->id)
                    ->orderBy('id', 'DESC')
                    ->first();

                $strike_off = AuditorChangeRequestItem::where('auditor_id', $auditor->id)
                                ->where('request_type',$this->settings('AUDITOR_DELISTING', 'key')->id)
                                ->where('table_type', $this->settings( 'MODULE_AUDITOR','key')->id)
                                // ->where('auditor_type','individual')
                                ->select('status')
                                ->first();

                $created_at = $auditor->created_at;

                $aud = array(
                    'first_name' => $auditor->first_name,
                    'id' => $auditor->id,
                    'last_name' => $auditor->last_name,
                    'nic' => $auditor->nic,
                    'certificate_no' => $auditor->certificate_no,
                    'passport_no' => $auditor->passport_no,
                    'created_at' => $created_at,
                    'token' => (isset($renewal->token) && $renewal->token) ? $renewal->token : null,
                    'status' => $auditor->status,
                    'value' => $auditor->value,
                    'Renewstatus' => (isset($renewal->status) && $renewal->status) ? $this->settings($renewal->status, 'id')->key : '',
                    'Renewvalue' => (isset($renewal->status) && $renewal->status) ? $this->settings($renewal->status, 'id')->value : '',
                    'strike_off' => (isset($strike_off->status) && $strike_off->status) ? $this->settings($strike_off->status, 'id')->key : '',

                );
                $auds[] = $aud;
            }

            $auditorFirms = AuditorFirm::leftJoin('auditor_certificates', 'auditor_firms.id', '=', 'auditor_certificates.firm_id')
                //  ->leftJoin('auditor_renewal','auditor_firms.id','=','auditor_renewal.firm_id')
                ->leftJoin('settings as s1', 'auditor_firms.status', '=', 's1.id')
                // ->leftJoin('settings as s2','auditor_renewal.status','=','s2.id')
                ->where('auditor_firms.created_by', $loggedUserId)
                ->where('auditor_firms.status', '!=', $this->settings('COMMON_STATUS_EDIT', 'key')->id)
                ->get(['auditor_firms.id', 'auditor_firms.name', 'auditor_certificates.certificate_no as certificate_no', 'auditor_firms.created_at', 's1.key as status', 's1.value as value']);

            $audfirms = array();
            foreach ($auditorFirms as $auditor_firm) {
                $renewal = AuditorRenewal::where('firm_id', $auditor_firm->id)
                    ->orderBy('id', 'DESC')
                    ->first();

                $created_at = $auditor_firm->created_at;

                $strike_off = AuditorChangeRequestItem::where('auditor_id', $auditor->id)
                                ->where('request_type',$this->settings('AUDITOR_DELISTING', 'key')->id)
                                ->where('table_type', $this->settings( 'MODULE_AUDITOR_FIRM','key')->id)
                                // ->where('auditor_type','firm')
                                ->select('status')
                                ->first();


                $audfirm = array(
                    'name' => $auditor_firm->name,
                    'id' => $auditor_firm->id,
                    'certificate_no' => $auditor_firm->certificate_no,
                    'passport_no' => $auditor_firm->passport_no,
                    'created_at' => $created_at,
                    'token' => (isset($renewal->token) && $renewal->token) ? $renewal->token : null,
                    'status' => $auditor_firm->status,
                    'value' => $auditor_firm->value,
                    'Renewstatus' => (isset($renewal->status) && $renewal->status) ? $this->settings($renewal->status, 'id')->key : '',
                    'Renewvalue' => (isset($renewal->status) && $renewal->status) ? $this->settings($renewal->status, 'id')->value : '',
                    'strike_off' => (isset($strike_off->status) && $strike_off->status) ? $this->settings($strike_off->status, 'id')->key : '',

                );
                $audfirms[] = $audfirm;
            }



            if ($auditorFirms || $auditors) {
                return response()->json([
                    'message' => 'Sucess!!!',
                    'status' => true,
                    'data'   => array(
                        'auditor'     => $auds,
                        'auditorfirm'     => $audfirms,
                    )
                ], 200);
            }
        }
    }

    // for load auditor uploaded files...
    public function auditorFile(Request $request)
    {
        if (isset($request)) {
            if ($request->type == 'individual') {
                $audId = $request->audId;
                $uploadedPdf = AuditorDocument::leftJoin('documents', 'auditor_documents.document_id', '=', 'documents.id')
                    ->leftJoin('settings', 'auditor_documents.status', '=', 'settings.id')
                    ->where('auditor_documents.auditor_id', $audId)
                    ->get(['auditor_documents.id', 'auditor_documents.name', 'auditor_documents.file_token', 'documents.key as dockey', 'settings.key as setkey']);
                if (isset($uploadedPdf)) {
                    return response()->json([
                        'file' => $uploadedPdf,
                        'status' => true,
                        'data'   => array(
                            'file'     => $uploadedPdf,
                        )
                    ], 200);
                }
            } elseif ($request->type == 'firm') {
                $audId = $request->audId;
                $uploadedPdf = AuditorDocument::leftJoin('documents', 'auditor_documents.document_id', '=', 'documents.id')
                    ->leftJoin('settings', 'auditor_documents.status', '=', 'settings.id')
                    ->where('auditor_documents.firm_id', $audId)
                    ->get(['auditor_documents.id', 'auditor_documents.name', 'auditor_documents.file_token', 'documents.key as dockey', 'settings.key as setkey']);
                if (isset($uploadedPdf)) {
                    return response()->json([
                        'file' => $uploadedPdf,
                        'status' => true,
                        'data'   => array(
                            'file'     => $uploadedPdf,
                        )
                    ], 200);
                }
            }
        } else {
            return response()->json([
                'status' => false,
            ], 200);
        }
    }


    // save auditor firm data(firm info and members info) to database...
    public function saveAuditorFirmData(Request $request)
    {
        if (isset($request)) {

            $firmName =  $request->input('firmName');
            $firmID =  $request->input('firmId');
            $isRegisteredFirm = AuditorFirm::where('name', 'ILIKE', "$firmName")->value('id');

            if ($firmID != 0) {
                $firmStatus = AuditorFirm::leftJoin('settings', 'auditor_firms.status', '=', 'settings.id')
                    ->where('auditor_firms.id', $firmID)
                    ->get(['settings.key as statusKey']);

                if ($firmStatus[0]['statusKey'] === 'AUDITOR_PROCESSING') {

                    $firmAddressId = AuditorFirm::where('id', $firmID)->value('address_id');
                    Address::where('id', $firmAddressId)
                        ->update([
                            'address1' => $request->input('businessLocalAddress1'),
                            'address2' => $request->input('businessLocalAddress2'),
                            'province' => $request->input('businessProvince'),
                            'district' => $request->input('businessDistrict'),
                            'city' => $request->input('businessCity'),
                            'postcode' => $request->input('businessPostCode'),
                            'gn_division' => $request->input('gnDivision'),
                        ]);

                    AuditorFirm::where('id', $firmID)
                        ->update([
                            'name' => $request->input('firmName'),
                            'name_si' => $request->input('sinFirmName'),
                            'name_ta' => $request->input('tamFirmName'),
                            'address_si' => $request->input('sinFirmAd'),
                            'address_ta' => $request->input('tamFirmAd'),
                            'email' => $request->input('email'),
                            'telephone' => $request->input('tel'),
                            'mobile' => $request->input('mobile'),
                            'is_existing_auditor_firm' => $request->input('isExistAud'),
                            'status' => $this->settings('AUDITOR_PROCESSING', 'key')->id,
                        ]);

                    $isExistAud = $request->input('isExistAud');
                    if ($isExistAud == 1) {
                        $certificate = AuditorCertificate::updateOrCreate(
                            [
                                'firm_id' =>  $firmID
                            ],
                            [
                                'certificate_no' => $request->input('certificateNo'),
                                'status' => $this->settings('COMMON_STATUS_ACTIVE', 'key')->id,
                            ]
                        );
                    } else if ($isExistAud == 0) {
                        $removeCertificate = AuditorCertificate::where('firm_id', $firmID)->delete();
                    }

                    $removePartners = AuditorFirmPartner::where('firm_id', $firmID)->delete();
                    $partnerDetails = $request->input('firmPartner');
                    foreach ($partnerDetails as $partner) {
                        if (!empty($partner)) {
                            $secFirmPartners = new AuditorFirmPartner();
                            $secFirmPartners->firm_id =  $firmID;
                            $secFirmPartners->auditor_id =  $partner['audId'];
                            $secFirmPartners->other_state = $partner['otherState'];
                            $secFirmPartners->save();
                        }
                    }
                    return response()->json([
                        'message' => 'successfully updated!!!',
                        'status' => true,
                        'firmId' => $firmID,
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'firm is already registered!!!',
                        'status' => false,
                    ], 200);
                }
            } else if ($firmID == 0) {
                if (isset($isRegisteredFirm)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'name is already taken',
                    ], 200);
                }

                $audAddressFirm = new Address();
                $audAddressFirm->address1 = $request->input('businessLocalAddress1');
                $audAddressFirm->address2 = $request->input('businessLocalAddress2');
                $audAddressFirm->city = $request->input('businessCity');
                $audAddressFirm->district = $request->input('businessDistrict');
                $audAddressFirm->province = $request->input('businessProvince');
                $audAddressFirm->postcode = $request->input('businessPostCode');
                $audAddressFirm->gn_division = $request->input('gnDivision');
                $audAddressFirm->country = 'SriLanka';
                $audAddressFirm->save();

                $loggedUserEmail = $request->input('loggedInUser');
                $loggedUserId = User::where('email', $loggedUserEmail)->value('id');

                if (isset($audAddressFirm->id) && isset($loggedUserId)) {
                    $audFirminfo = new AuditorFirm();
                    $audFirminfo->name = $request->input('firmName');
                    $audFirminfo->name_si = $request->input('sinFirmName');
                    $audFirminfo->name_ta = $request->input('tamFirmName');
                    $audFirminfo->email = $request->input('email');
                    $audFirminfo->mobile = $request->input('mobile');
                    $audFirminfo->telephone = $request->input('tel');
                    $audFirminfo->is_existing_auditor_firm = $request->input('isExistAud');
                    $audFirminfo->address_si = $request->input('sinFirmAd');
                    $audFirminfo->address_ta = $request->input('tamFirmAd');
                    $audFirminfo->address_id = $audAddressFirm->id;
                    $audFirminfo->status = $this->settings('AUDITOR_PROCESSING', 'key')->id;
                    $audFirminfo->created_by = $loggedUserId;
                    $audFirminfo->qualification = $request->input('qualification');
                    $audFirminfo->save();

                    $isExistAud = $request->input('isExistAud');
                    if ($isExistAud == 1) {
                        $audCertificate = new AuditorCertificate();
                        $audCertificate->firm_id = $audFirminfo->id;
                        $audCertificate->certificate_no = $request->input('certificateNo');
                        $audCertificate->status = $this->settings('COMMON_STATUS_ACTIVE', 'key')->id;
                        $audCertificate->save();
                    }
                }
                if (isset($audFirminfo->id)) {
                    $auditorIDs = array();
                    $partnerDetails = $request->input('firmPartner');
                    foreach ($partnerDetails as $partner) {
                        if (!empty($partner)) {
                            $audFirmPartnerinfo = new AuditorFirmPartner();
                            $audFirmPartnerinfo->firm_id = $audFirminfo->id;
                            $audFirmPartnerinfo->auditor_id = $partner['audId'];
                            $audFirmPartnerinfo->other_state = $partner['otherState'];
                            $audFirmPartnerinfo->save();
                        }
                        $auditorIDs[] = $partner['audId'];
                    }
                }
                $firmId = $audFirminfo->id;
                return response()->json([
                    'status' => true,
                    'firmId' => $firmId,
                    'auditorIDs' => $auditorIDs,
                    'message' => 'successfully inserted!!!',
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
            ], 200);
        }
    }

    // to get auditor individual id list from firm id...
    public function getAuditorFirmPartners(Request $request)
    {
        if (isset($request->firmId)) {

            $audIdList = AuditorFirmPartner::where('firm_id', $request->firmId)->get(['auditor_id']);
            return response()->json([
                'message' => 'Sucess!',
                'status' => true,
                'audidlist' => $audIdList,
            ], 200);
        } else {
            return response()->json([
                'message' => 'We can \'t find a Partner.',
                'status' => false,
            ], 200);
        }
    }

    // to generate auditor individual pdf ...
    public function generateAuditorPDF(Request $request)
    {

        if (isset($request->audid)) {
            $auditorDetails = Auditor::where('id', $request->audid)->first();

            $fname = $auditorDetails['first_name'];
            $lname = $auditorDetails['last_name'];
            $fullName =  $fname . ' ' . $lname;
            $addressId = $auditorDetails['address_id'];
            $address = Address::where('id', $addressId)->first();
            if (isset($address)) {
                $address1 = $address['address1'];
                $address2 = $address['address2'];
                $city = $address['city'];
                $rAddress = $address1 . ' ' . $address2 . ' ' . $city;
            } else {
                $rAddress = '';
            }

            $businessAddressId = $auditorDetails['business_address_id'];
            $busAddress = Address::where('id', $businessAddressId)->first();
            if (isset($busAddress)) {
                $baddress1 = $busAddress['address1'];
                $baddress2 = $busAddress['address2'];
                $bcity = $busAddress['city'];
                $bAddress = $baddress1 . ' ' . $baddress2 . ' ' . $bcity;
            } else {
                $bAddress = '';
            }

            $data = [
                'name' => $fullName,
                'raddress' =>  $rAddress,
                'bname' =>  $auditorDetails['business_name'],
                'baddress' =>  $bAddress,
                'dob' => $auditorDetails['dob'],
                'pqualification' =>  $auditorDetails['professional_qualifications'],
                'nationality' => $auditorDetails['nationality'],
                'race' => $auditorDetails['race'],
                'whereDomiciled' => $auditorDetails['where_domiciled'],
                'dateTakeResidenceInSrilanka' => $auditorDetails['from_residence_in_srilanka'],
                'dateConResidenceInSrilanka' => $auditorDetails['continuously_residence_in_srilanka'],
                'ownedProperty' => $auditorDetails['particulars_of_immovable_property'],
                'otherFacts' => $auditorDetails['other_facts_to_the_srilanka_domicile'],
                'isunsoundMind' => $auditorDetails['is_unsound_mind'],
                'isinsolventorbankrupt' => $auditorDetails['is_insolvent_or_bankrupt'],
                'reason1' => $auditorDetails['reason'],
                'iscompetentcourt' => $auditorDetails['is_competent_court'],
                'reason2' => $auditorDetails['competent_court_type'],
                'otherDetails' => $auditorDetails['other_details'],
                'sublauseualified' => $auditorDetails['which_applicant_is_qualified'],
            ];
            $pdf = PDF::loadView('auditor-forms/form1', $data);
            return $pdf->stream('form1.pdf');
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // to generate auditor firm pdf ...
    public function generateAuditorFirmPDF(Request $request)
    {
        if (isset($request->firmid) && isset($request->audid)) {

            $firmName = AuditorFirm::where('id', $request->firmid)->value('name');
            $firmAddressId = AuditorFirm::where('id', $request->firmid)->value('address_id');
            $firmAddress = Address::where('id', $firmAddressId)->first();
            if (isset($firmAddress)) {
                $address1 = $firmAddress['address1'];
                $address2 = $firmAddress['address2'];
                $city = $firmAddress['city'];
                $fAddress = $address1 . ' ' . $address2 . ' ' . $city;
            }

            $auditorDetails = Auditor::where('id', $request->audid)->first();
            $fname = $auditorDetails['first_name'];
            $lname = $auditorDetails['last_name'];
            $fullName =  $fname . ' ' . $lname;
            $auditorAddress = Address::where('id', $auditorDetails->address_id)->first();
            $auditaddress1 = $auditorAddress['address1'];
            $auditaddress2 = $auditorAddress['address2'];
            $auditcity = $auditorAddress['city'];
            $auditAddress = $auditaddress1 . ' ' . $auditaddress2 . ' ' . $auditcity;

            $otherDetails = AuditorFirmPartner::where('firm_id', $request->firmid)->where('auditor_id', $request->audid)->value('other_state');

            $data = [
                'name' => $fullName,
                'auditAddress' => $auditAddress,
                'bname' =>  $firmName,
                'baddress' =>  $fAddress,
                'dob' => $auditorDetails['dob'],
                'pqualification' =>  $auditorDetails['professional_qualifications'],
                'nationality' => $auditorDetails['nationality'],
                'race' => $auditorDetails['race'],
                'whereDomiciled' => $auditorDetails['where_domiciled'],
                'dateTakeResidenceInSrilanka' => $auditorDetails['from_residence_in_srilanka'],
                'dateConResidenceInSrilanka' => $auditorDetails['continuously_residence_in_srilanka'],
                'ownedProperty' => $auditorDetails['particulars_of_immovable_property'],
                'otherFacts' => $auditorDetails['other_facts_to_the_srilanka_domicile'],
                'isunsoundMind' => $auditorDetails['is_unsound_mind'],
                'isinsolventorbankrupt' => $auditorDetails['is_insolvent_or_bankrupt'],
                'reason1' => $auditorDetails['reason'],
                'iscompetentcourt' => $auditorDetails['is_competent_court'],
                'reason2' => $auditorDetails['competent_court_type'],
                'otherDetails' => $otherDetails,
            ];
            $pdf = PDF::loadView('auditor-forms/form2', $data);
            return $pdf->stream('form2.pdf');
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // load auditor firm data(firm info and members info) to resubmit...
    public function getAuditorFirmData(Request $request)
    {
        if (isset($request->firmId)) {

            $firmDetails = AuditorFirm::where('id', $request->firmId)->first();
            $firmAddressId = AuditorFirm::where('id', $request->firmId)->value('address_id');
            $firmAddress = Address::where('id', $firmAddressId)->first();

            $certificateNum = AuditorCertificate::where('firm_id', $request->firmId)->value('certificate_no');

            $auditorDetails = AuditorFirmPartner::leftJoin('auditors', 'auditor_firm_partners.auditor_id', '=', 'auditors.id')
                ->where('auditor_firm_partners.firm_id', $request->firmId)
                ->get(['auditor_firm_partners.other_state', 'auditors.first_name as fname', 'auditors.last_name as lname', 'auditors.id as id', 'auditors.nic as nic', 'auditors.passport_no as passport']);
            return response()->json([
                'message' => 'Sucess',
                'status' => true,
                'data'   => array(
                    'auditors'     => $auditorDetails,
                    'firmaddress'  => $firmAddress,
                    'firm'     => $firmDetails,
                    'certificateNumber'  => $certificateNum,
                )
            ], 200);
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // update auditor firm data(firm info and members info) for resubmit ...
    public function updateAuditorFirmData(Request $request)
    {
        if (isset($request->firmId)) {

            $firmAddressId = AuditorFirm::where('id', $request->firmId)->value('address_id');
            Address::where('id', $firmAddressId)
                ->update([
                    'address1' => $request->input('businessLocalAddress1'),
                    'address2' => $request->input('businessLocalAddress2'),
                    'province' => $request->input('businessProvince'),
                    'district' => $request->input('businessDistrict'),
                    'city' => $request->input('businessCity'),
                    'postcode' => $request->input('businessPostCode'),
                    'gn_division' => $request->input('gnDivision'),
                ]);

            AuditorFirm::where('id', $request->firmId)
                ->update([
                    'name' => $request->input('firmName'),
                    'name_si' => $request->input('sinFirmName'),
                    'name_ta' => $request->input('tamFirmName'),
                    'address_si' => $request->input('sinFirmAd'),
                    'address_ta' => $request->input('tamFirmAd'),
                    'email' => $request->input('email'),
                    'telephone' => $request->input('tel'),
                    'mobile' => $request->input('mobile'),
                    'is_existing_auditor_firm' => $request->input('isExistAud'),
                    'status' => $this->settings('AUDITOR_REQUEST_TO_RESUBMIT', 'key')->id,
                ]);

            $isExistAud = $request->input('isExistAud');
            if ($isExistAud == 1) {
                $certificate = AuditorCertificate::updateOrCreate(
                    [
                        'firm_id' =>  $request->firmId
                    ],
                    [
                        'certificate_no' => $request->input('certificateNo'),
                        'status' => $this->settings('COMMON_STATUS_ACTIVE', 'key')->id,
                    ]
                );
            } else if ($isExistAud == 0) {
                $removeCertificate = AuditorCertificate::where('firm_id', $request->firmId)->delete();
            }

            return response()->json([
                'message' => 'Sucessfully updated',
                'status' => true,
                'firmId' => $request->firmId,
            ], 200);
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }
    /////////////////////////////////////// Auditor Firm Change /////////////////////
    function deleteAuditorFirmChangePdfUpdate(Request $request)
    {
        if (isset($request)) {
            $docId = $request->documentId;
            $type = $request->type;
            $docstatusid = AuditorDocumentStatus::where('auditor_document_id', $docId)->first();
            if ($docstatusid) {

                if ($type == 'additionalUpload') {

                    $document = AuditorDocument::where('id', $docId)->first();
                    if ($docstatusid->status == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id) {

                        $delete = Storage::disk('sftp')->delete($document->path);
                        AuditorDocument::where('id', $docId)
                            ->update([
                                'status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                                'name' => NULL,
                                'file_token' => NULL,
                                'path' => NULL
                            ]);
                    } else {

                        $delete = Storage::disk('sftp')->delete($document->path);
                        AuditorDocument::where('id', $docId)
                            ->update([
                                'status' => $this->settings('DOCUMENT_REQUESTED', 'key')->id,
                                'name' => NULL,
                                'file_token' => NULL,
                                'path' => NULL
                            ]);
                    }
                } else {

                    $document = AuditorDocument::where('id', $docId)->first();
                    $delete = Storage::disk('sftp')->delete($document->path);

                    AuditorDocument::where('id', $docId)
                        ->update([
                            'status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                            'name' => NULL,
                            'file_token' => NULL,
                            'path' => NULL
                        ]);
                }
            } else {
                $document = AuditorDocument::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                $remove = AuditorDocument::where('id', $docId)->delete();
            }
            return response()->json([
                'message' => 'File removed successfully.',
                'status' => true,
            ], 200);
        }
    }
    public function auditorFirmChangeUpdateUploadedPdf(Request $request)
    {

        if (isset($request)) {

            $fileName =  uniqid() . '.pdf';
            $token = md5(uniqid());

            $audId = $request->audId;
            $firmId = $request->firmId;
            $docType = $request->docType;
            $pdfName = $request->filename;

            if (isset($firmId)) {
                $path = 'auditorfirm/' . $firmId;
            }
            // elseif (isset($firmId)) {
            //     $path = 'auditorfirm/'.$firmId;
            // }        
            $path =  $request->file('uploadFile')->storeAs($path, $fileName, 'sftp');

            AuditorDocument::where('id', $request->docId)
                ->update([
                    'status' => $this->settings('DOCUMENT_PENDING', 'key')->id,
                    'name' => $pdfName,
                    //'description' => $request->description,
                    'file_token' => $token,
                    'path' => $path
                ]);

            return response()->json([
                'message' => 'File uploaded successfully.',
                'status' => true,
                'name' => basename($path),
                'doctype' => $docType,
                'token' => $token,
                'pdfname' => $pdfName,
            ], 200);
        }
    }
    public function auditorFirmChangeUploadPdf(Request $request)
    {

        if (isset($request)) {

            $fileName =  uniqid() . '.pdf';
            $token = md5(uniqid());

            $audId = $request->audId;
            $reqid = $request->reqid;
            $firmId = $request->firmId;
            $docType = $request->docType;
            $pdfName = $request->filename;

            $description = $request->description;
            if ($description == 'undefined') {
                $description = NULL;
            }

            if (isset($audId)) {
                $path = 'auditor/' . $audId;
            } elseif (isset($firmId)) {
                $path = 'auditorfirm/' . $firmId;
            }
            $path =  $request->file('uploadFile')->storeAs($path, $fileName, 'sftp');


            $docId;
            if ($docType == 'extraUpload') {
                $docIdArray = DocumentsGroup::leftJoin('documents', 'document_groups.id', '=', 'documents.document_group_id')
                    ->where('document_groups.request_type', 'AUDITOR')
                    ->where('documents.key', 'AUDITOR_EXTRA_DOCUMENT')
                    ->get(['documents.id']);
                $docId = $docIdArray[0]['id'];
            }

            $audDoc = new AuditorDocument;
            $audDoc->document_id = $docId;
            $audDoc->firm_id = $firmId;
            $audDoc->request_id = $reqid;
            $audDoc->name = $pdfName;
            $audDoc->description = $description;
            $audDoc->file_token = $token;
            $audDoc->path = $path;
            $audDoc->status =  $this->settings('DOCUMENT_PENDING', 'key')->id;
            $audDoc->save();

            $auddocId = $audDoc->id;

            return response()->json([
                'message' => 'File uploaded successfully.',
                'status' => true,
                'name' => basename($path),
                'doctype' => $docType,
                'docid' => $auddocId, // for delete pdf...
                'token' => $token,
                'pdfname' => $pdfName,
            ], 200);
        }
    }
    public function AuditorFirmChangeDataSubmit(Request $request)
    {
        if (isset($request->reqid)) {
            $reqid = $request->reqid;
            $audId = $request->firmId;
            $newaudId = $request->newid;
            $auditor_request = AuditorChangeRequestItem::where('id', $reqid)->first();
            if ($request) {
                $changetype = json_decode($auditor_request->change_type);

                $old_auditor = AuditorFirm::find($audId);
                $isNewAuditor;
                if (intval($newaudId)) {
                    $new_auditor = AuditorFirm::find($newaudId);
                    $isNewAuditor = false;
                } else {
                    $new_auditor = $old_auditor->replicate();
                    $isNewAuditor = true;
                    //  $new_auditor->save();
                }

                if (!empty($changetype)) {
                    if (in_array('NAME_CHANGE', $changetype)) {
                        $new_auditor->name = $request->input('firmName');
                        $new_auditor->name_si = $request->input('sinFirmName');
                        $new_auditor->name_ta = $request->input('tamFirmName');
                        $new_auditor->save();
                    }
                    if (in_array('EMAIL_CHANGE', $changetype)) {
                        $new_auditor->email = $request->input('email');
                        $new_auditor->save();
                    }
                    if (in_array('TEL_CHANGE', $changetype)) {
                        $new_auditor->telephone = $request->input('tel');
                        $new_auditor->mobile = $request->input('mobile');
                        $new_auditor->save();
                    }
                    if (in_array('ADDRESS_CHANGE', $changetype)) {

                        $audAddressResidential = new Address();
                        $audAddressResidential->address1 = $request->input('businessLocalAddress1');
                        $audAddressResidential->address2 = $request->input('businessLocalAddress2');
                        $audAddressResidential->city = $request->input('businessCity');
                        $audAddressResidential->district = $request->input('businessDistrict');
                        $audAddressResidential->province = $request->input('businessProvince');
                        $audAddressResidential->postcode = $request->input('businessPostCode');
                        $audAddressResidential->gn_division = $request->input('gnDivision');
                        $audAddressResidential->country = 'SriLanka';
                        $audAddressResidential->save();


                        $new_auditor->address_id = $audAddressResidential->id;
                        $new_auditor->address_si = $request->input('sinFirmAd');
                        $new_auditor->address_ta = $request->input('tamFirmAd');
                        $new_auditor->save();
                    }
                    if (in_array('PARTNER_CHANGE', $changetype)) {

                        $partnerDetails = $request->input('firmPartner');
                        foreach ($partnerDetails as $partner) {
                            if (!empty($partner)) {
                                $secFirmPartners = new AuditorFirmPartnerChange();
                                $secFirmPartners->firm_id =  $new_auditor->id;
                                $secFirmPartners->auditor_id =  $partner['audId'];
                                $secFirmPartners->other_state = $partner['otherState'];
                                $secFirmPartners->save();
                            }
                        }

                        $new_auditor->qualification = $request->input('qualification');
                        $new_auditor->save();
                    }

                    $new_auditor->status = $this->settings('COMMON_STATUS_EDIT', 'key')->id;
                    $new_auditor->save();

                    if ($isNewAuditor) {
                        $itemChange = new AuditorItemChange;
                        $itemChange->request_id = $reqid;
                        $itemChange->changes_type = $this->settings('EDIT', 'key')->id;
                        $itemChange->item_id =  $new_auditor->id;
                        $itemChange->old_record_id =  $old_auditor->id;
                        $itemChange->item_table_type =  $this->settings('AUDITOR_FIRMS', 'key')->id;
                        $itemChange->save();
                    }

                    return response()->json([
                        'message' => 'Success new auditor details added',
                        'status' => true,
                        'data'   => array(
                            'newid'     => $new_auditor->id,
                        )
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'empty change type.',
                        'status' => false,
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'We can \'t find a request.',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a request id.',
                'status' => false,
            ], 200);
        }
    }

    public function getAuditorFirmChangeData(Request $request)
    {
        if (isset($request->firmId) && isset($request->email)) {
            $loggedUserEmail = $request->email;
            $loggedUserId = User::where('email', $loggedUserEmail)->value('id');
            $createdUserId = AuditorFirm::where('id', $request->firmId)->value('created_by');

            // $firmDetails = AuditorFirm::where('id',$request->firmId)->first();
            // $firmAddressId = AuditorFirm::where('id',$request->firmId)->value('address_id');
            // $firmAddress = Address::where('id',$firmAddressId)->first();

            // $certificateNum = AuditorCertificate::where('firm_id', $request->firmId)->value('certificate_no');

            // $auditorDetails = AuditorFirmPartner::leftJoin('auditors','auditor_firm_partners.auditor_id','=','auditors.id')
            //                                     ->where('auditor_firm_partners.firm_id',$request->firmId)
            //                                     ->get(['auditor_firm_partners.other_state','auditors.first_name as fname','auditors.last_name as lname','auditors.id as id','auditors.nic as nic','auditors.passport_no as passport']);


            if ($loggedUserId === $createdUserId) {

                $auditorchangerequest = AuditorChangeRequestItem::where('auditor_change_requests.auditor_id', $request->firmId)
                    ->where('auditor_change_requests.table_type', $this->settings('AUDITOR_FIRMS', 'key')->id)
                    ->where('auditor_change_requests.request_type', $this->settings('AUDITOR_FIRM_CHANGE', 'key')->id)
                    ->where(function ($query) {
                        $query->where('auditor_change_requests.status', '=', $this->settings('AUDITOR_CHANGE_PROCESSING', 'key')->id)
                            ->orWhere('auditor_change_requests.status', '=', $this->settings('AUDITOR_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id);
                    })
                    ->first();

                if (!$auditorchangerequest) {

                    $firmDetails = AuditorFirm::where('id', $request->firmId)->first();

                    $firmAddressId = AuditorFirm::where('id', $request->firmId)->value('address_id');
                    $firmAddress = Address::where('id', $firmAddressId)->first();

                    // $certificateNum = AuditorCertificate::where('firm_id', $request->firmId)->value('certificate_no');

                    $auditorPartDetails = AuditorFirmPartner::leftJoin('auditors', 'auditor_firm_partners.auditor_id', '=', 'auditors.id')
                        ->where('auditor_firm_partners.firm_id', $request->firmId)
                        ->get(['auditor_firm_partners.other_state', 'auditors.first_name as fname', 'auditors.last_name as lname', 'auditors.id as id', 'auditors.nic as nic', 'auditors.passport_no as passport']);


                    $changetypes = AuditorChangeType::all();

                    return response()->json([
                        'message' => 'Sucess',
                        'status' => true,
                        'data'   => array(
                            'firm'     => $firmDetails,
                            'firmaddress'  => $firmAddress,
                            'auditors'  => $auditorPartDetails,
                            'changetypes'  => $changetypes,
                            'processStatus'  => null,
                        )
                    ], 200);
                } else {
                    if ($auditorchangerequest->status == $this->settings('AUDITOR_CHANGE_PROCESSING', 'key')->id) {

                        $auditorDetails2 = AuditorFirm::where('id', $request->firmId)->first();
                        $change_type_pro = json_decode($auditorchangerequest->change_type);
                        $reqid = $auditorchangerequest->id;


                        if ($auditorDetails2) {

                            $auditorPartDetails1 = AuditorFirmPartner::leftJoin('auditors', 'auditor_firm_partners.auditor_id', '=', 'auditors.id')
                                ->where('auditor_firm_partners.firm_id', $request->firmId)
                                ->get(['auditor_firm_partners.other_state', 'auditors.first_name as fname', 'auditors.last_name as lname', 'auditors.id as id', 'auditors.nic as nic', 'auditors.passport_no as passport']);


                            $isAuditorEdited = AuditorItemChange::where('request_id', $auditorchangerequest->id)
                                ->where('changes_type', $this->settings('EDIT', 'key')->id)
                                ->where('item_table_type', $this->settings('AUDITOR_FIRMS', 'key')->id)
                                ->where('old_record_id', $auditorDetails2->id)
                                ->first();

                            $auditorDetails2->newid = null;
                            if ($isAuditorEdited) {

                                $newEditedAuditor = AuditorFirm::where('id', $isAuditorEdited->item_id)->first();

                                $auditorDetails2 =   $newEditedAuditor;
                                $audID =   $isAuditorEdited->old_record_id;
                                $newaudID =   $newEditedAuditor->id;

                                $auditorDetails2->id = $audID;
                                $auditorDetails2->newid = $newaudID;

                                $auditor_partners = AuditorFirmPartnerChange::leftJoin('auditors', 'auditor_firm_partners_changes.auditor_id', '=', 'auditors.id')
                                    ->where('auditor_firm_partners_changes.firm_id', $newaudID)
                                    ->get(['auditor_firm_partners_changes.other_state', 'auditors.first_name as fname', 'auditors.last_name as lname', 'auditors.id as id', 'auditors.nic as nic', 'auditors.passport_no as passport']);
                                if (count($auditor_partners) > 0) {
                                    $auditorPartDetails1 = $auditor_partners;
                                }
                            }



                            $audaddresspro = null;
                            if ($auditorDetails2->address_id) {
                                $audaddresspro = Address::where('id', $auditorDetails2->address_id)->first();
                            }

                            $changetypes = AuditorChangeType::all();

                            return response()->json([
                                'message' => 'Sucess',
                                'status' => true,
                                'data'   => array(
                                    'firm'     => $auditorDetails2,
                                    'reqid'     => $reqid,
                                    'firmaddress'  => $audaddresspro,
                                    'changetypes'  => $changetypes,
                                    'changetype'  => $change_type_pro,
                                    'auditors'  => $auditorPartDetails1,
                                    'processStatus'  => 'AUDITOR_CHANGE_PROCESSING',
                                )
                            ], 200);
                        } else {
                            return response()->json([
                                'message' => 'We can \'t find a Auditor Firm.',
                                'status' => false,
                            ], 200);
                        }
                    } elseif ($auditorchangerequest->status == $this->settings('AUDITOR_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id) {

                        $auditorDetails3 = AuditorFirm::where('id', $request->firmId)->first();
                        $change_type_re = json_decode($auditorchangerequest->change_type);
                        $reqid = $auditorchangerequest->id;


                        if ($auditorDetails3) {

                            $auditorPartDetails2 = AuditorFirmPartner::leftJoin('auditors', 'auditor_firm_partners.auditor_id', '=', 'auditors.id')
                                ->where('auditor_firm_partners.firm_id', $request->firmId)
                                ->get(['auditor_firm_partners.other_state', 'auditors.first_name as fname', 'auditors.last_name as lname', 'auditors.id as id', 'auditors.nic as nic', 'auditors.passport_no as passport']);


                            $isAuditorEdited = AuditorItemChange::where('request_id', $auditorchangerequest->id)
                                ->where('changes_type', $this->settings('EDIT', 'key')->id)
                                ->where('item_table_type', $this->settings('AUDITORS', 'key')->id)
                                ->where('old_record_id', $auditorDetails3->id)
                                ->first();

                            $auditorDetails3->newid = null;
                            if ($isAuditorEdited) {

                                $newEditedAuditor = AuditorFirm::where('id', $isAuditorEdited->item_id)->first();

                                $auditorDetails3 =   $newEditedAuditor;
                                $audID =   $isAuditorEdited->old_record_id;
                                $newaudID =   $newEditedAuditor->id;

                                $auditorDetails3->id = $audID;
                                $auditorDetails3->newid = $newaudID;

                                $auditor_partners1 = AuditorFirmPartnerChange::leftJoin('auditors', 'auditor_firm_partners_changes.auditor_id', '=', 'auditors.id')
                                    ->where('auditor_firm_partners_changes.firm_id', $newaudID)
                                    ->get(['auditor_firm_partners_changes.other_state', 'auditors.first_name as fname', 'auditors.last_name as lname', 'auditors.id as id', 'auditors.nic as nic', 'auditors.passport_no as passport']);
                                if (count($auditor_partners1) > 0) {
                                    $auditorPartDetails2 = $auditor_partners1;
                                }
                            }



                            $audaddressresub = null;
                            if ($auditorDetails3->address_id) {
                                $audaddressresub = Address::where('id', $auditorDetails3->address_id)->first();
                            }

                            $changetypes = AuditorChangeType::all();

                            $external_comment_key_id = $this->settings('COMMENT_EXTERNAL', 'key')->id;

                            $external_comment_query = AuditorComment::where('firm_id', $request->firmId)
                                ->where('comment_type', $external_comment_key_id)
                                ->where('request_id', $reqid)
                                ->orderBy('id', 'DESC')
                                ->first();
                            $external_global_comment = (isset($external_comment_query->comments) && $external_comment_query->comments)
                                ?  $external_comment_query->comments
                                : '';

                            return response()->json([
                                'message' => 'Sucess',
                                'status' => true,
                                'data'   => array(
                                    'firm'     => $auditorDetails3,
                                    'reqid'     => $reqid,
                                    'firmaddress'  => $audaddressresub,
                                    'external_global_comment' => $external_global_comment,
                                    'changetypes'  => $changetypes,
                                    'changetype'  => $change_type_re,
                                    'auditors'  => $auditorPartDetails2,
                                    'processStatus'  => 'AUDITOR_CHANGE_REQUEST_TO_RESUBMIT',
                                )
                            ], 200);
                        } else {
                            return response()->json([
                                'message' => 'We can \'t find a Auditor Firm.',
                                'status' => false,
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'message' => 'We can \'t find a status.',
                            'status' => false,
                        ], 200);
                    }
                }
            } else {
                return response()->json([
                    'message' => 'Unauthorized user is trying a auditor change',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    public function AuditorFirmChangeTypeSubmit(Request $request)
    {
        if (isset($request->audId)) {
            $userId = User::where('email', $request->input('email'))->value('id');
            $audid = $request->audId;
            $reqid = $request->requestId;
            $changetype = $request->changetype;
            if (!isset($changetype)) {
                return response()->json([
                    'message' => 'change type array not passed.',
                    'status' => false,
                ], 200);
            }

            if (intval($reqid)) {
                $req = AuditorChangeRequestItem::find($reqid);
            } else {
                $req = new AuditorChangeRequestItem;
                $req->auditor_id = $audid;
                $req->request_by = $userId;
                $req->table_type = $this->settings('AUDITOR_FIRMS', 'key')->id;
                $req->request_type = $this->settings('AUDITOR_FIRM_CHANGE', 'key')->id;
                $req->status = $this->settings('AUDITOR_CHANGE_PROCESSING', 'key')->id;
            }

            $req->change_type = json_encode($changetype);
            $req->save();


            $auditor_item = AuditorItemChange::where('request_id', $req->id)->first();
            if ($auditor_item) {
                $new_auditor_id = $auditor_item->item_id;
                $old_auditor_id = $auditor_item->old_record_id;

                ///// ////
                $new_auditor = AuditorFirm::find($new_auditor_id);
                $old_auditor = AuditorFirm::where('id', $old_auditor_id)->first();

                if (!empty($changetype)) {
                    if (!in_array('NAME_CHANGE', $changetype)) {
                        $new_auditor->name = $old_auditor->name;
                        $new_auditor->name_si = $old_auditor->name_si;
                        $new_auditor->name_ta = $old_auditor->name_ta;
                        $new_auditor->save();
                    }
                    if (!in_array('EMAIL_CHANGE', $changetype)) {
                        $new_auditor->email = $old_auditor->email;
                        $new_auditor->save();
                    }
                    if (!in_array('TEL_CHANGE', $changetype)) {
                        $new_auditor->telephone = $old_auditor->telephone;
                        $new_auditor->mobile = $old_auditor->mobile;
                        $new_auditor->save();
                    }
                    if (!in_array('ADDRESS_CHANGE', $changetype)) {
                        $new_auditor->address_id = $old_auditor->address_id;
                        $new_auditor->address_si = $old_auditor->address_si;
                        $new_auditor->address_ta = $old_auditor->address_ta;
                        $new_auditor->save();
                    }
                    if (!in_array('PARTNER_CHANGE', $changetype)) {
                        $new_auditor->qualification = $old_auditor->qualification;
                        $new_auditor->save();
                        $auditor_partners_delete = AuditorFirmPartnerChange::where('auditor_firm_partners_changes.firm_id', $new_auditor_id)
                            ->delete();
                    }
                } else {
                    $new_auditor_delete = AuditorFirm::where('id', $new_auditor_id)->delete();
                    $auditor_item_delete = AuditorItemChange::where('request_id', $req->id)->delete();
                }
            }

            return response()->json([
                'message' => 'Success pro done',
                'status' => true,
                'data'   => array(
                    'reqId'     => $req->id,
                )
            ], 200);
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    /////////////////////////////////////// Auditor Change /////////////////////

    public function getAuditorDataForChange(Request $request)
    {
        if (isset($request->audId) && isset($request->email)) {
            $loggedUserEmail = $request->email;
            $loggedUserId = User::where('email', $loggedUserEmail)->value('id');
            $createdUserId = Auditor::where('id', $request->audId)->value('created_by');

            if ($loggedUserId === $createdUserId) {

                $auditorchangerequest = AuditorChangeRequestItem::where('auditor_change_requests.auditor_id', $request->audId)
                    ->where('auditor_change_requests.table_type', $this->settings('AUDITORS', 'key')->id)
                    ->where('auditor_change_requests.request_type', $this->settings('AUDITOR_CHANGE', 'key')->id)
                    ->where(function ($query) {
                        $query->where('auditor_change_requests.status', '=', $this->settings('AUDITOR_CHANGE_PROCESSING', 'key')->id)
                            ->orWhere('auditor_change_requests.status', '=', $this->settings('AUDITOR_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id);
                    })
                    ->first();

                if (!$auditorchangerequest) {

                    $auditorDetails = Auditor::where('id', $request->audId)->first();

                    $audAddressId = Auditor::where('id', $request->audId)->value('address_id');
                    $businessAddressId = Auditor::where('id', $request->audId)->value('business_address_id');

                    $audAddress = Address::where('id', $audAddressId)->first();
                    $businessAddress = Address::where('id', $businessAddressId)->first();

                    $certificateNum = AuditorCertificate::where('auditor_id', $request->audId)->value('certificate_no');

                    $changetypes = AuditorChangeType::where('setting_type_id', 1)->get();

                    return response()->json([
                        'message' => 'Sucess',
                        'status' => true,
                        'data'   => array(
                            'auditor'     => $auditorDetails,
                            'audaddress'  => $audAddress,
                            'businessaddress'  => $businessAddress,
                            'certificateNumber'  => $certificateNum,
                            'changetypes'  => $changetypes,
                            'processStatus'  => null,
                        )
                    ], 200);
                } else {
                    if ($auditorchangerequest->status == $this->settings('AUDITOR_CHANGE_PROCESSING', 'key')->id) {

                        $auditorDetails2 = Auditor::where('id', $request->audId)->first();
                        $change_type_pro = json_decode($auditorchangerequest->change_type);
                        $reqid = $auditorchangerequest->id;


                        if ($auditorDetails2) {

                            $isAuditorEdited = AuditorItemChange::where('request_id', $auditorchangerequest->id)
                                ->where('changes_type', $this->settings('EDIT', 'key')->id)
                                ->where('item_table_type', $this->settings('AUDITORS', 'key')->id)
                                ->where('old_record_id', $auditorDetails2->id)
                                ->first();

                            $auditorDetails2->newid = null;
                            if ($isAuditorEdited) {

                                $newEditedAuditor = Auditor::where('id', $isAuditorEdited->item_id)->first();

                                $auditorDetails2 =   $newEditedAuditor;
                                $audID =   $isAuditorEdited->old_record_id;
                                $newaudID =   $newEditedAuditor->id;

                                $auditorDetails2->id = $audID;
                                $auditorDetails2->newid = $newaudID;
                            }



                            $audaddresspro = null;
                            $businessAddresspro = null;
                            if ($auditorDetails2->address_id) {
                                $audaddresspro = Address::where('id', $auditorDetails2->address_id)->first();
                            }
                            if ($auditorDetails2->business_address_id) {
                                $businessAddresspro = Address::where('id', $auditorDetails2->business_address_id)->first();
                            }

                            $certificateNum2 = AuditorCertificate::where('auditor_id', $request->audId)->value('certificate_no');
                            $changetypes = AuditorChangeType::where('setting_type_id', 1)->get();

                            return response()->json([
                                'message' => 'Sucess',
                                'status' => true,
                                'data'   => array(
                                    'auditor'     => $auditorDetails2,
                                    'reqid'     => $reqid,
                                    'audaddress'  => $audaddresspro,
                                    'businessaddress'  => $businessAddresspro,
                                    'certificateNumber'  => $certificateNum2,
                                    'changetypes'  => $changetypes,
                                    'changetype'  => $change_type_pro,
                                    'processStatus'  => 'AUDITOR_CHANGE_PROCESSING',
                                )
                            ], 200);
                        } else {
                            return response()->json([
                                'message' => 'We can \'t find a Auditor.',
                                'status' => false,
                            ], 200);
                        }
                    } elseif ($auditorchangerequest->status == $this->settings('AUDITOR_CHANGE_REQUEST_TO_RESUBMIT', 'key')->id) {

                        $auditorDetails3 = Auditor::where('id', $request->audId)->first();
                        $change_type_re = json_decode($auditorchangerequest->change_type);
                        $reqid = $auditorchangerequest->id;


                        if ($auditorDetails3) {

                            $isAuditorEdited = AuditorItemChange::where('request_id', $auditorchangerequest->id)
                                ->where('changes_type', $this->settings('EDIT', 'key')->id)
                                ->where('item_table_type', $this->settings('AUDITORS', 'key')->id)
                                ->where('old_record_id', $auditorDetails3->id)
                                ->first();

                            $auditorDetails3->newid = null;
                            if ($isAuditorEdited) {

                                $newEditedAuditor = Auditor::where('id', $isAuditorEdited->item_id)->first();

                                $auditorDetails3 =   $newEditedAuditor;
                                $audID =   $isAuditorEdited->old_record_id;
                                $newaudID =   $newEditedAuditor->id;

                                $auditorDetails3->id = $audID;
                                $auditorDetails3->newid = $newaudID;
                            }



                            $audaddressresub = null;
                            $businessAddressresub = null;
                            if ($auditorDetails3->address_id) {
                                $audaddressresub = Address::where('id', $auditorDetails3->address_id)->first();
                            }
                            if ($auditorDetails3->business_address_id) {
                                $businessAddressresub = Address::where('id', $auditorDetails3->business_address_id)->first();
                            }

                            $certificateNum2 = AuditorCertificate::where('auditor_id', $request->audId)->value('certificate_no');
                            $changetypes = AuditorChangeType::where('setting_type_id', 1)->get();

                            $external_comment_key_id = $this->settings('COMMENT_EXTERNAL', 'key')->id;

                            $external_comment_query = AuditorComment::where('auditor_id', $request->audId)
                                ->where('comment_type', $external_comment_key_id)
                                ->where('request_id', $reqid)
                                ->orderBy('id', 'DESC')
                                ->first();
                            $external_global_comment = (isset($external_comment_query->comments) && $external_comment_query->comments)
                                ?  $external_comment_query->comments
                                : '';

                            return response()->json([
                                'message' => 'Sucess',
                                'status' => true,
                                'data'   => array(
                                    'auditor'     => $auditorDetails3,
                                    'reqid'     => $reqid,
                                    'audaddress'  => $audaddressresub,
                                    'external_global_comment' => $external_global_comment,
                                    'businessaddress'  => $businessAddresspro,
                                    'certificateNumber'  => $certificateNum2,
                                    'changetypes'  => $changetypes,
                                    'changetype'  => $change_type_re,
                                    'processStatus'  => 'AUDITOR_CHANGE_REQUEST_TO_RESUBMIT',
                                )
                            ], 200);
                        } else {
                            return response()->json([
                                'message' => 'We can \'t find a Auditor.',
                                'status' => false,
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'message' => 'We can \'t find a sfvfv.',
                            'status' => false,
                        ], 200);
                    }
                }
            } else {
                return response()->json([
                    'message' => 'Unauthorized user is trying a auditor change',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    public function auditorChangeFileLoad(Request $request)
    {
        if (isset($request)) {
            if ($request->type == 'individual') {
                $audId = $request->audId;
                $reqid = $request->reqid;
                // $uploadedPdf = AuditorDocument::leftJoin('documents','auditor_documents.document_id','=','documents.id')
                //                               ->leftJoin('auditor_document_status', function ($join) {
                //                                $join->on('auditor_documents.id', '=', 'auditor_document_status.auditor_document_id')
                //                                  ->where('auditor_document_status.comment_type', $this->settings('COMMENT_EXTERNAL','key')->id);})
                //                               ->leftJoin('settings','auditor_documents.status','=','settings.id')
                //                                  ->where('auditor_documents.auditor_id',$audId)                                               
                //                                  ->where('auditor_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                //                                    ->get(['auditor_documents.id','auditor_documents.name','auditor_documents.file_token','documents.key as dockey','auditor_documents.document_id','auditor_document_status.status as status','auditor_document_status.comments as comments','settings.value as value','settings.key as setkey']);

                $uploadedPdf = AuditorDocument::leftJoin('documents', 'auditor_documents.document_id', '=', 'documents.id')
                    ->leftJoin('auditor_document_status', function ($join) {
                        $join->on('auditor_documents.id', '=', 'auditor_document_status.auditor_document_id')
                            ->where(function ($query) {
                                $query->where('auditor_document_status.status', '=', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id)
                                    ->orWhere('auditor_document_status.status', '=', $this->settings('DOCUMENT_REQUESTED', 'key')->id);
                            })
                            ->where('auditor_document_status.comment_type', '=', $this->settings('COMMENT_EXTERNAL', 'key')->id);
                    })
                    ->leftJoin('settings', 'auditor_documents.status', '=', 'settings.id')
                    ->where('auditor_documents.auditor_id', $audId)
                    ->where('auditor_documents.request_id', $reqid)
                    ->where('auditor_documents.status', '!=', $this->settings('DOCUMENT_DELETED', 'key')->id)
                    ->get(['auditor_documents.id', 'auditor_documents.name', 'auditor_documents.file_token', 'auditor_documents.description', 'settings.value as value', 'settings.key as setkey', 'documents.key as dockey', 'auditor_document_status.comments as document_comment', 'auditor_document_status.status as document_status', 'auditor_document_status.comment_type as document_comment_type']);

                if (isset($uploadedPdf)) {
                    return response()->json([
                        'file' => $uploadedPdf,
                        'status' => true,
                        'data'   => array(
                            'file'     => $uploadedPdf,
                            'resubmission_status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                            'request_status' => $this->settings('DOCUMENT_REQUESTED', 'key')->id,
                            'external_comment_type' => $this->settings('COMMENT_EXTERNAL', 'key')->id,
                        )
                    ], 200);
                }
            } elseif ($request->type == 'firm') {
                $audId = $request->audId;
                $reqid = $request->reqid;
                // $uploadedPdf = AuditorDocument::leftJoin('documents','auditor_documents.document_id','=','documents.id')
                //                               ->leftJoin('auditor_document_status', function ($join) {
                //                                $join->on('auditor_documents.id', '=', 'auditor_document_status.auditor_document_id')
                //                                  ->where('auditor_document_status.comment_type', $this->settings('COMMENT_EXTERNAL','key')->id);})
                //                               ->leftJoin('settings','auditor_documents.status','=','settings.id')
                //                                  ->where('auditor_documents.auditor_id',$audId)                                               
                //                                  ->where('auditor_documents.status','!=',$this->settings('DOCUMENT_DELETED', 'key')->id)
                //                                    ->get(['auditor_documents.id','auditor_documents.name','auditor_documents.file_token','documents.key as dockey','auditor_documents.document_id','auditor_document_status.status as status','auditor_document_status.comments as comments','settings.value as value','settings.key as setkey']);

                $uploadedPdf = AuditorDocument::leftJoin('documents', 'auditor_documents.document_id', '=', 'documents.id')
                    ->leftJoin('auditor_document_status', function ($join) {
                        $join->on('auditor_documents.id', '=', 'auditor_document_status.auditor_document_id')
                            ->where(function ($query) {
                                $query->where('auditor_document_status.status', '=', $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id)
                                    ->orWhere('auditor_document_status.status', '=', $this->settings('DOCUMENT_REQUESTED', 'key')->id);
                            })
                            ->where('auditor_document_status.comment_type', '=', $this->settings('COMMENT_EXTERNAL', 'key')->id);
                    })
                    ->leftJoin('settings', 'auditor_documents.status', '=', 'settings.id')
                    ->where('auditor_documents.firm_id', $audId)
                    ->where('auditor_documents.request_id', $reqid)
                    ->where('auditor_documents.status', '!=', $this->settings('DOCUMENT_DELETED', 'key')->id)
                    ->get(['auditor_documents.id', 'auditor_documents.name', 'auditor_documents.file_token', 'auditor_documents.description', 'settings.value as value', 'settings.key as setkey', 'documents.key as dockey', 'auditor_document_status.comments as document_comment', 'auditor_document_status.status as document_status', 'auditor_document_status.comment_type as document_comment_type']);

                if (isset($uploadedPdf)) {
                    return response()->json([
                        'file' => $uploadedPdf,
                        'status' => true,
                        'data'   => array(
                            'file'     => $uploadedPdf,
                            'resubmission_status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                            'request_status' => $this->settings('DOCUMENT_REQUESTED', 'key')->id,
                            'external_comment_type' => $this->settings('COMMENT_EXTERNAL', 'key')->id,
                        )
                    ], 200);
                }
            }
        } else {
            return response()->json([
                'status' => false,
            ], 200);
        }
    }

    public function auditorChangeUploadPdf(Request $request)
    {

        if (isset($request)) {

            $fileName =  uniqid() . '.pdf';
            $token = md5(uniqid());

            $audId = $request->audId;
            $reqid = $request->reqid;
            $firmId = $request->firmId;
            $docType = $request->docType;
            $pdfName = $request->filename;

            $description = $request->description;
            if ($description == 'undefined') {
                $description = NULL;
            }

            if (isset($audId)) {
                $path = 'auditor/' . $audId;
            } elseif (isset($firmId)) {
                $path = 'auditorfirm/' . $firmId;
            }
            $path =  $request->file('uploadFile')->storeAs($path, $fileName, 'sftp');


            $docId;
            if ($docType == 'extraUpload') {
                $docIdArray = DocumentsGroup::leftJoin('documents', 'document_groups.id', '=', 'documents.document_group_id')
                    ->where('document_groups.request_type', 'AUDITOR')
                    ->where('documents.key', 'AUDITOR_EXTRA_DOCUMENT')
                    ->get(['documents.id']);
                $docId = $docIdArray[0]['id'];
            }

            $audDoc = new AuditorDocument;
            $audDoc->document_id = $docId;
            $audDoc->auditor_id = $audId;
            $audDoc->request_id = $reqid;
            $audDoc->name = $pdfName;
            $audDoc->description = $description;
            $audDoc->file_token = $token;
            $audDoc->path = $path;
            $audDoc->status =  $this->settings('DOCUMENT_PENDING', 'key')->id;
            $audDoc->save();

            $auddocId = $audDoc->id;

            return response()->json([
                'message' => 'File uploaded successfully.',
                'status' => true,
                'name' => basename($path),
                'doctype' => $docType,
                'docid' => $auddocId, // for delete pdf...
                'token' => $token,
                'pdfname' => $pdfName,
            ], 200);
        }
    }

    public function auditorChangeUpdateUploadedPdf(Request $request)
    {

        if (isset($request)) {

            $fileName =  uniqid() . '.pdf';
            $token = md5(uniqid());

            $audId = $request->audId;
            $firmId = $request->firmId;
            $docType = $request->docType;
            $pdfName = $request->filename;

            if (isset($audId)) {
                $path = 'auditor/' . $audId;
            }
            // elseif (isset($firmId)) {
            //     $path = 'auditorfirm/'.$firmId;
            // }        
            $path =  $request->file('uploadFile')->storeAs($path, $fileName, 'sftp');

            AuditorDocument::where('id', $request->docId)
                ->update([
                    'status' => $this->settings('DOCUMENT_PENDING', 'key')->id,
                    'name' => $pdfName,
                    //'description' => $request->description,
                    'file_token' => $token,
                    'path' => $path
                ]);

            return response()->json([
                'message' => 'File uploaded successfully.',
                'status' => true,
                'name' => basename($path),
                'doctype' => $docType,
                'token' => $token,
                'pdfname' => $pdfName,
            ], 200);
        }
    }

    function deleteAuditorChangePdfUpdate(Request $request)
    {
        if (isset($request)) {
            $docId = $request->documentId;
            $type = $request->type;
            $docstatusid = AuditorDocumentStatus::where('auditor_document_id', $docId)->first();
            if ($docstatusid) {

                if ($type == 'additionalUpload') {

                    $document = AuditorDocument::where('id', $docId)->first();
                    if ($docstatusid->status == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id) {

                        $delete = Storage::disk('sftp')->delete($document->path);
                        AuditorDocument::where('id', $docId)
                            ->update([
                                'status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                                'name' => NULL,
                                'file_token' => NULL,
                                'path' => NULL
                            ]);
                    } else {

                        $delete = Storage::disk('sftp')->delete($document->path);
                        AuditorDocument::where('id', $docId)
                            ->update([
                                'status' => $this->settings('DOCUMENT_REQUESTED', 'key')->id,
                                'name' => NULL,
                                'file_token' => NULL,
                                'path' => NULL
                            ]);
                    }
                } else {

                    $document = AuditorDocument::where('id', $docId)->first();
                    $delete = Storage::disk('sftp')->delete($document->path);

                    AuditorDocument::where('id', $docId)
                        ->update([
                            'status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                            'name' => NULL,
                            'file_token' => NULL,
                            'path' => NULL
                        ]);
                }
            } else {
                $document = AuditorDocument::where('id', $docId)->first();
                $delete = Storage::disk('sftp')->delete($document->path);
                $remove = AuditorDocument::where('id', $docId)->delete();
            }
            return response()->json([
                'message' => 'File removed successfully.',
                'status' => true,
            ], 200);
        }
    }

    public function updateAuditorChangeStatus(Request $request)
    {
        if (isset($request->reqid)) {
            if ($request->type === 'individual') {
                AuditorChangeRequestItem::where('id', $request->reqid)
                    ->update([
                        'status' => $this->settings('AUDITOR_CHANGE_PENDING', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } elseif ($request->type === 'individualResubmit') {
                AuditorChangeRequestItem::where('id', $request->reqid)
                    ->update([
                        'status' => $this->settings('AUDITOR_CHANGE_RESUBMITTED', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } elseif ($request->type === 'firm') {
                AuditorChangeRequestItem::where('id', $request->reqid)
                    ->update([
                        'status' => $this->settings('AUDITOR_CHANGE_PENDING', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } elseif ($request->type === 'firmResubmit') {
                AuditorChangeRequestItem::where('id', $request->reqid)
                    ->update([
                        'status' => $this->settings('AUDITOR_CHANGE_RESUBMITTED', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'error',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'can \'t find a request id.',
                'status' => false,
            ], 200);
        }
    }

    public function AuditorChangeDataSubmit(Request $request)
    {
        if (isset($request->reqid)) {
            $reqid = $request->reqid;
            $audId = $request->id;
            $newaudId = $request->newid;
            $auditor_request = AuditorChangeRequestItem::where('id', $reqid)->first();
            if ($request) {
                $changetype = json_decode($auditor_request->change_type);

                $old_auditor = Auditor::find($audId);
                $isNewAuditor;
                if (intval($newaudId)) {
                    $new_auditor = Auditor::find($newaudId);
                    $isNewAuditor = false;
                } else {
                    $new_auditor = $old_auditor->replicate();
                    $isNewAuditor = true;
                    //  $new_auditor->save();
                }

                if (!empty($changetype)) {
                    if (in_array('NAME_CHANGE', $changetype)) {
                        $new_auditor->title = $request->input('title');
                        $new_auditor->first_name = $request->input('firstname');
                        $new_auditor->last_name = $request->input('lastname');
                        $new_auditor->name_si = $request->input('sinFullName');
                        $new_auditor->name_ta = $request->input('tamFullName');
                        $new_auditor->save();
                    }
                    if (in_array('EMAIL_CHANGE', $changetype)) {
                        $new_auditor->email = $request->input('email');
                        $new_auditor->save();
                    }
                    if (in_array('TEL_CHANGE', $changetype)) {
                        $new_auditor->telephone = $request->input('tel');
                        $new_auditor->mobile = $request->input('mobile');
                        $new_auditor->save();
                    }
                    if (in_array('ADDRESS_CHANGE', $changetype)) {

                        $audAddressResidential = new Address();
                        $audAddressResidential->address1 = $request->input('residentialLocalAddress1');
                        $audAddressResidential->address2 = $request->input('residentialLocalAddress2');
                        $audAddressResidential->city = $request->input('residentialCity');
                        $audAddressResidential->district = $request->input('residentialDistrict');
                        $audAddressResidential->province = $request->input('residentialProvince');
                        $audAddressResidential->postcode = $request->input('residentialPostCode');
                        $audAddressResidential->gn_division = $request->input('rgnDivision');
                        $audAddressResidential->country = 'SriLanka';
                        $audAddressResidential->save();

                        $audAddressBusiness = new Address();
                        $bAddress = $request->input('businessName');
                        if (!empty($bAddress)) {
                            $audAddressBusiness->address1 = $request->input('businessLocalAddress1');
                            $audAddressBusiness->address2 = $request->input('businessLocalAddress2');
                            $audAddressBusiness->city = $request->input('businessCity');
                            $audAddressBusiness->district = $request->input('businessDistrict');
                            $audAddressBusiness->province = $request->input('businessProvince');
                            $audAddressBusiness->postcode = $request->input('businessPostCode');
                            $audAddressBusiness->gn_division = $request->input('gnDivision');
                            $audAddressBusiness->country = 'SriLanka';
                            $audAddressBusiness->save();
                        }


                        $new_auditor->business_name = $request->input('businessName');
                        $new_auditor->address_id = $audAddressResidential->id;
                        $new_auditor->address_si = $request->input('sinAd');
                        $new_auditor->address_ta = $request->input('tamAd');
                        $new_auditor->business_address_id = empty($audAddressBusiness->id) ? null : $audAddressBusiness->id;
                        $new_auditor->save();
                    }

                    $new_auditor->status = $this->settings('COMMON_STATUS_EDIT', 'key')->id;
                    $new_auditor->save();

                    if ($isNewAuditor) {
                        $itemChange = new AuditorItemChange;
                        $itemChange->request_id = $reqid;
                        $itemChange->changes_type = $this->settings('EDIT', 'key')->id;
                        $itemChange->item_id =  $new_auditor->id;
                        $itemChange->old_record_id =  $old_auditor->id;
                        $itemChange->item_table_type =  $this->settings('AUDITORS', 'key')->id;
                        $itemChange->save();
                    }

                    return response()->json([
                        'message' => 'Success new auditor details added',
                        'status' => true,
                        'data'   => array(
                            'newid'     => $new_auditor->id,
                        )
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'empty change type.',
                        'status' => false,
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'We can \'t find a request.',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a request id.',
                'status' => false,
            ], 200);
        }
    }

    public function AuditorChangeTypeSubmit(Request $request)
    {
        if (isset($request->audId)) {
            $userId = User::where('email', $request->input('email'))->value('id');
            $audid = $request->audId;
            $reqid = $request->requestId;
            $changetype = $request->changetype;
            if (!isset($changetype)) {
                return response()->json([
                    'message' => 'change type array not passed.',
                    'status' => false,
                ], 200);
            }

            if (intval($reqid)) {
                $req = AuditorChangeRequestItem::find($reqid);
            } else {
                $req = new AuditorChangeRequestItem;
                $req->auditor_id = $audid;
                $req->request_by = $userId;
                $req->table_type = $this->settings('AUDITORS', 'key')->id;
                $req->request_type = $this->settings('AUDITOR_CHANGE', 'key')->id;
                $req->status = $this->settings('AUDITOR_CHANGE_PROCESSING', 'key')->id;
            }

            $req->change_type = json_encode($changetype);
            $req->save();


            $auditor_item = AuditorItemChange::where('request_id', $req->id)->first();
            if ($auditor_item) {
                $new_auditor_id = $auditor_item->item_id;
                $old_auditor_id = $auditor_item->old_record_id;

                ///// ////
                $new_auditor = Auditor::find($new_auditor_id);
                $old_auditor = Auditor::where('id', $old_auditor_id)->first();

                if (!empty($changetype)) {
                    if (!in_array('NAME_CHANGE', $changetype)) {
                        $new_auditor->title = $old_auditor->title;
                        $new_auditor->first_name = $old_auditor->first_name;
                        $new_auditor->last_name = $old_auditor->last_name;
                        $new_auditor->name_si = $old_auditor->name_si;
                        $new_auditor->name_ta = $old_auditor->name_ta;
                        $new_auditor->save();
                    }
                    if (!in_array('EMAIL_CHANGE', $changetype)) {
                        $new_auditor->email = $old_auditor->email;
                        $new_auditor->save();
                    }
                    if (!in_array('TEL_CHANGE', $changetype)) {
                        $new_auditor->telephone = $old_auditor->telephone;
                        $new_auditor->mobile = $old_auditor->mobile;
                        $new_auditor->save();
                    }
                    if (!in_array('ADDRESS_CHANGE', $changetype)) {
                        $new_auditor->business_name = $old_auditor->business_name;
                        $new_auditor->address_id = $old_auditor->address_id;
                        $new_auditor->address_si = $old_auditor->address_si;
                        $new_auditor->address_ta = $old_auditor->address_ta;
                        $new_auditor->business_address_id = $old_auditor->business_address_id;
                        $new_auditor->save();
                    }
                } else {
                    $new_auditor_delete = Auditor::where('id', $new_auditor_id)->delete();
                    $auditor_item_delete = AuditorItemChange::where('request_id', $req->id)->delete();
                }
            }

            return response()->json([
                'message' => 'Success pro done',
                'status' => true,
                'data'   => array(
                    'reqId'     => $req->id,
                )
            ], 200);
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // load auditor individual data(sl and nonsl) to resubmit...
    public function getAuditorData(Request $request)
    {
        if (isset($request->audId)) {

            $auditorDetails = Auditor::where('id', $request->audId)->first();

            $audAddressId = Auditor::where('id', $request->audId)->value('address_id');
            $audAddress = Address::where('id', $audAddressId)->first();

            $businessAddressId = Auditor::where('id', $request->audId)->value('business_address_id');
            $businessAddress = Address::where('id', $businessAddressId)->first();

            $certificateNum = AuditorCertificate::where('auditor_id', $request->audId)->value('certificate_no');

            return response()->json([
                'message' => 'Sucess',
                'status' => true,
                'data'   => array(
                    'auditor'     => $auditorDetails,
                    'audaddress'  => $audAddress,
                    'businessaddress'  => $businessAddress,
                    'certificateNumber'  => $certificateNum,
                )
            ], 200);
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // update auditor individual data for resubmit ...
    public function updateAuditorData(Request $request)
    {
        if (isset($request->id)) {

            $audAddressId = Auditor::where('id', $request->id)->value('address_id');
            if (isset($audAddressId)) {
                Address::where('id', $audAddressId)
                    ->update([
                        'address1' => $request->input('residentialLocalAddress1'),
                        'address2' => $request->input('residentialLocalAddress2'),
                        'province' => $request->input('residentialProvince'),
                        'district' => $request->input('residentialDistrict'),
                        'city' => $request->input('residentialCity'),
                        'postcode' => $request->input('residentialPostCode'),
                        'gn_division' => $request->input('rgnDivision'),
                    ]);
            }

            $businessAddressId = Auditor::where('id', $request->id)->value('business_address_id');
            if (isset($businessAddressId)) {
                Address::where('id', $businessAddressId)
                    ->update([
                        'address1' => $request->input('businessLocalAddress1'),
                        'address2' => $request->input('businessLocalAddress2'),
                        'province' => $request->input('businessProvince'),
                        'district' => $request->input('businessDistrict'),
                        'city' => $request->input('businessCity'),
                        'postcode' => $request->input('businessPostCode'),
                        'gn_division' => $request->input('gnDivision'),
                    ]);
            } else {

                $audAddressBusiness = new Address();
                $bAddress = $request->input('businessName');
                if (!empty($bAddress)) {
                    $audAddressBusiness->address1 = $request->input('businessLocalAddress1');
                    $audAddressBusiness->address2 = $request->input('businessLocalAddress2');
                    $audAddressBusiness->city = $request->input('businessCity');
                    $audAddressBusiness->district = $request->input('businessDistrict');
                    $audAddressBusiness->province = $request->input('businessProvince');
                    $audAddressBusiness->country = 'SriLanka';
                    $audAddressBusiness->postcode = $request->input('businessPostCode');
                    $audAddressBusiness->gn_division = $request->input('gnDivision');
                    $audAddressBusiness->save();
                }
                $bAddressID = $audAddressBusiness->id;
                Auditor::where('id', $request->id)
                    ->update([
                        'business_address_id' => $bAddressID,
                    ]);
            }

            Auditor::where('id', $request->id)
                ->update([
                    'title' => $request->input('title'),
                    'first_name' => $request->input('firstname'),
                    'name_si' => $request->input('sinFullName'),
                    'name_ta' => $request->input('tamFullName'),
                    'address_si' => $request->input('sinAd'),
                    'address_ta' => $request->input('tamAd'),
                    'email' => $request->input('email'),
                    'mobile' => $request->input('mobile'),
                    'telephone' => $request->input('tel'),
                    'last_name' => $request->input('lastname'),
                    'business_name' => $request->input('businessName'),
                    'dob' => $request->input('birthDay'),
                    'nationality' => $request->input('nationality'),
                    'race' => $request->input('race'),
                    'where_domiciled' => $request->input('whereDomiciled'),
                    'from_residence_in_srilanka' => $request->input('dateTakeResidenceInSrilanka'),
                    'continuously_residence_in_srilanka' => $request->input('dateConResidenceInSrilanka'),
                    'particulars_of_immovable_property' => $request->input('ownedProperty'),
                    'other_facts_to_the_srilanka_domicile' => $request->input('otherFacts'),
                    'professional_qualifications' => $request->input('pQualification'),
                    'is_unsound_mind' => $request->input('isUnsoundMind'),
                    'is_insolvent_or_bankrupt' => $request->input('isInsolventOrBankrupt'),
                    'reason' => $request->input('reason1'),
                    'is_competent_court' => $request->input('isCompetentCourt'),
                    'competent_court_type' => $request->input('reason2'),
                    'other_details' => $request->input('otherDetails'),
                    'is_existing_auditor' => $request->input('isExistAud'),
                    'which_applicant_is_qualified' => $request->input('subClauseQualified'),
                    'status' => $this->settings('AUDITOR_REQUEST_TO_RESUBMIT', 'key')->id,
                ]);
            $isExistAud = $request->input('isExistAud');
            if ($isExistAud == 1) {
                $certificate = AuditorCertificate::updateOrCreate(
                    [
                        'auditor_id' =>  $request->id
                    ],
                    [
                        'certificate_no' => $request->input('certificateNo'),
                        'status' => $this->settings('COMMON_STATUS_ACTIVE', 'key')->id,
                    ]
                );
            } else if ($isExistAud == 0) {
                $removeCertificate = AuditorCertificate::where('auditor_id', $request->id)->delete();
            }

            return response()->json([
                'message' => 'Sucessfully updated',
                'status' => true,
                'audId' => $request->id,
            ], 200);
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // update auditor individual and firm status end at resubmit ...
    public function updateAuditorStatus(Request $request)
    {
        if (isset($request->audId)) {
            if ($request->type === 'individual') {
                Auditor::where('id', $request->audId)
                    ->update([
                        'status' => $this->settings('AUDITOR_RESUBMITTED', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } elseif ($request->type === 'firm') {
                AuditorFirm::where('id', $request->audId)
                    ->update([
                        'status' => $this->settings('AUDITOR_RESUBMITTED', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } else if ($request->type === 'isExisting') {
                Auditor::where('id', $request->audId)
                    ->update([
                        'status' => $this->settings('AUDITOR_PENDING', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } else if ($request->type === 'isExistingFirm') {
                AuditorFirm::where('id', $request->audId)
                    ->update([
                        'status' => $this->settings('AUDITOR_PENDING', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'error',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // load auditor documents comments to resubmit...(not used)
    public function getAuditorDocComments(Request $request)
    {
        if (isset($request->audId)) {
            if ($request->type === 'individual') {
                $auditorDoc = AuditorDocument::leftJoin('auditor_document_status', 'auditor_documents.id', '=', 'auditor_document_status.auditor_document_id')
                    ->leftJoin('documents', 'auditor_documents.document_id', '=', 'documents.id')
                    ->where('auditor_documents.auditor_id', $request->audId)
                    ->where('auditor_document_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
                    ->get(['documents.key as key', 'auditor_documents.id', 'auditor_documents.document_id', 'auditor_document_status.status as status', 'auditor_document_status.comments as comments']);

                return response()->json([
                    'message' => 'Sucess',
                    'status' => true,
                    'data'   => array(
                        'auditorDoc'     => $auditorDoc,
                    )
                ], 200);
            } elseif ($request->type === 'firm') {
                $auditorFirmDoc = AuditorDocument::leftJoin('auditor_document_status', 'auditor_documents.id', '=', 'auditor_document_status.auditor_document_id')
                    ->leftJoin('documents', 'auditor_documents.document_id', '=', 'documents.id')
                    ->where('auditor_documents.firm_id', $request->audId)
                    ->where('auditor_document_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
                    ->get(['documents.key as key', 'auditor_documents.id', 'auditor_documents.document_id', 'auditor_document_status.status as status', 'auditor_document_status.comments as comments']);


                return response()->json([
                    'message' => 'Sucess',
                    'status' => true,
                    'data'   => array(
                        'auditorFirmDoc'     => $auditorFirmDoc,
                    )
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // load auditor general comments to resubmit...
    public function getAuditorComments(Request $request)
    {
        if (isset($request->audId)) {
            if ($request->type === 'individual') {
                $auditorComment = AuditorComment::leftJoin('settings', 'auditor_statuses.status', '=', 'settings.id')
                    ->where('auditor_id', $request->audId)
                    ->where('comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
                    ->get(['auditor_statuses.id', 'auditor_statuses.comments', 'auditor_statuses.created_at', 'settings.key as status']);

                return response()->json([
                    'message' => 'Sucess',
                    'status' => true,
                    'data'   => array(
                        'auditorComment'     => $auditorComment,
                    )
                ], 200);
            } elseif ($request->type === 'firm') {
                $auditorComment = AuditorComment::leftJoin('settings', 'auditor_statuses.status', '=', 'settings.id')
                    ->where('firm_id', $request->audId)
                    ->where('comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id)
                    ->get(['auditor_statuses.id', 'auditor_statuses.comments', 'auditor_statuses.created_at', 'settings.key as status']);

                return response()->json([
                    'message' => 'Sucess',
                    'status' => true,
                    'data'   => array(
                        'auditorComment'     => $auditorComment,
                    )
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // for update uploaded auditor pdf to resubmit...
    public function auditorUpdateUploadedPdf(Request $request)
    {

        if (isset($request)) {

            $fileName =  uniqid() . '.pdf';
            $token = md5(uniqid());

            $audId = $request->audId;
            $firmId = $request->firmId;
            $docType = $request->docType;
            $pdfName = $request->filename;

            if (isset($audId)) {
                $path = 'auditor/' . $audId;
            } elseif (isset($firmId)) {
                $path = 'auditorfirm/' . $firmId;
            }
            $path =  $request->file('uploadFile')->storeAs($path, $fileName, 'sftp');

            AuditorDocument::where('id', $request->docId)
                ->update([
                    'status' => $this->settings('DOCUMENT_PENDING', 'key')->id,
                    'name' => $pdfName,
                    'description' => $request->description,
                    'file_token' => $token,
                    'path' => $path
                ]);

            return response()->json([
                'message' => 'File uploaded successfully.',
                'status' => true,
                'name' => basename($path),
                'doctype' => $docType,
                'token' => $token,
                'pdfname' => $pdfName,
            ], 200);
        }
    }


    // for load auditor uploaded files with resumit comments...
    public function auditorFileLoad(Request $request)
    {
        if (isset($request)) {
            if ($request->type == 'individual') {
                $audId = $request->audId;
                $uploadedPdf = AuditorDocument::leftJoin('documents', 'auditor_documents.document_id', '=', 'documents.id')
                    ->leftJoin('auditor_document_status', function ($join) {
                        $join->on('auditor_documents.id', '=', 'auditor_document_status.auditor_document_id')
                            ->where('auditor_document_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id);
                    })
                    ->leftJoin('settings', 'auditor_documents.status', '=', 'settings.id')
                    ->where('auditor_documents.auditor_id', $audId)
                    ->where('auditor_documents.status', '!=', $this->settings('DOCUMENT_DELETED', 'key')->id)
                    ->get(['auditor_documents.id', 'auditor_documents.name', 'auditor_documents.file_token', 'documents.key as dockey', 'auditor_documents.document_id', 'auditor_document_status.status as status', 'auditor_document_status.comments as comments', 'settings.value as value', 'settings.key as setkey']);

                if (isset($uploadedPdf)) {
                    return response()->json([
                        'file' => $uploadedPdf,
                        'status' => true,
                        'data'   => array(
                            'file'     => $uploadedPdf,
                        )
                    ], 200);
                }
            } elseif ($request->type == 'firm') {
                $audId = $request->audId;
                $uploadedPdf = AuditorDocument::leftJoin('documents', 'auditor_documents.document_id', '=', 'documents.id')
                    ->leftJoin('auditor_document_status', function ($join) {
                        $join->on('auditor_documents.id', '=', 'auditor_document_status.auditor_document_id')
                            ->where('auditor_document_status.comment_type', $this->settings('COMMENT_EXTERNAL', 'key')->id);
                    })
                    ->leftJoin('settings', 'auditor_documents.status', '=', 'settings.id')
                    ->where('auditor_documents.firm_id', $audId)
                    ->where('auditor_documents.status', '!=', $this->settings('DOCUMENT_DELETED', 'key')->id)
                    ->get(['auditor_documents.id', 'auditor_documents.name', 'auditor_documents.file_token', 'documents.key as dockey', 'auditor_documents.document_id', 'auditor_document_status.status as status', 'auditor_document_status.comments as comments', 'settings.value as value', 'settings.key as setkey']);
                if (isset($uploadedPdf)) {
                    return response()->json([
                        'file' => $uploadedPdf,
                        'status' => true,
                        'data'   => array(
                            'file'     => $uploadedPdf,
                        )
                    ], 200);
                }
            }
        } else {
            return response()->json([
                'status' => false,
            ], 200);
        }
    }

    // for delete auditor pdf files in resubmit process...
    function deleteAuditorPdfUpdate(Request $request)
    {
        if (isset($request)) {
            $docId = $request->documentId;
            $type = $request->type;
            $docstatusid = AuditorDocumentStatus::where('auditor_document_id', $docId)->first();
            if ($docstatusid) {

                if ($type == 'additionalUpload') {

                    $document = AuditorDocument::where('id', $docId)->first();
                    if ($docstatusid->status == $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id) {

                        $delete = Storage::disk('sftp')->delete($document->path);
                        AuditorDocument::where('id', $docId)
                            ->update([
                                'status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                                'name' => NULL,
                                'file_token' => NULL,
                                'path' => NULL
                            ]);
                    } else {

                        $delete = Storage::disk('sftp')->delete($document->path);
                        AuditorDocument::where('id', $docId)
                            ->update([
                                'status' => $this->settings('DOCUMENT_REQUESTED', 'key')->id,
                                'name' => NULL,
                                'file_token' => NULL,
                                'path' => NULL
                            ]);
                    }
                } else {

                    $document = AuditorDocument::where('id', $docId)->first();
                    $delete = Storage::disk('sftp')->delete($document->path);

                    AuditorDocument::where('id', $docId)
                        ->update([
                            'status' => $this->settings('DOCUMENT_REQUEST_TO_RESUBMIT', 'key')->id,
                            'name' => NULL,
                            'file_token' => NULL,
                            'path' => NULL
                        ]);
                }
            }
            return response()->json([
                'message' => 'File removed successfully.',
                'status' => true,
            ], 200);
        }
    }

    public function updateAuditorRenewalStatus(Request $request)
    {
        if (isset($request->audId)) {
            if ($request->type === 'individual') {
                AuditorRenewal::where('auditor_id', $request->audId)
                    ->update([
                        'status' => $this->settings('AUDITOR_RENEWAL_RESUBMITTED', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } elseif ($request->type === 'firm') {
                AuditorRenewal::where('firm_id', $request->audId)
                    ->update([
                        'status' => $this->settings('AUDITOR_RENEWAL_RESUBMITTED', 'key')->id,
                    ]);
                return response()->json([
                    'message' => 'sucessfully updated',
                    'status' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'error',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // to generate auditor individual renewal pdf ...
    public function generateAuditorRenewalPDF(Request $request)
    {

        if (isset($request->token)) {
            $auditorStatus = AuditorRenewal::leftJoin('settings', 'auditor_renewal.status', '=', 'settings.id')
                ->where('auditor_renewal.token', $request->token)
                ->get(['settings.key as status']);
            if (sizeof($auditorStatus) > 0) {
                if ($auditorStatus[0]['status'] == 'AUDITOR_RENEWAL_PROCESSING' or $auditorStatus[0]['status'] == 'AUDITOR_RENEWAL_REQUEST_TO_RESUBMIT') {
                    $auditor = Auditor::leftJoin('auditor_renewal', 'auditors.id', '=', 'auditor_renewal.auditor_id')
                        ->leftJoin('auditor_certificates', 'auditors.id', '=', 'auditor_certificates.auditor_id')
                        ->where('auditor_renewal.token', $request->token)
                        ->get(['auditors.id', 'auditors.telephone', 'auditors.email', 'auditors.address_id', 'auditor_certificates.certificate_no as certificate_no', 'auditors.first_name', 'auditors.last_name']);

                    $address = Address::where('id', $auditor[0]['address_id'])->first();

                    $todayDate = date("Y-m-d");

                    $day1 = date('d', strtotime($todayDate));
                    $month1 = date('m', strtotime($todayDate));
                    $year1 = date('Y', strtotime($todayDate));

                    if (isset($auditor)) {
                        $fname = $auditor[0]['first_name'];
                        $lname = $auditor[0]['last_name'];
                        $fullName =  $fname . ' ' . $lname;
                        $regnum = $auditor[0]['certificate_no'];
                        $telephone = $auditor[0]['telephone'];
                        $email = $auditor[0]['email'];
                        $data = [
                            'name' => $fullName,
                            'telephone' => $telephone,
                            'email' => $email,
                            'address' => $address,
                            'regnum' =>  $regnum,
                            'year1' => $year1,
                        ];
                        $pdf = PDF::loadView('auditor-forms/renewal', $data);
                        return $pdf->stream('renewal.pdf');
                    } else {
                        return response()->json([
                            'message' => 'We can \'t find a User.',
                            'status' => false,
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'message' => 'We can \'t find a User.',
                        'status' => false,
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'Invalid Token',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // to check individual auditor is registered to renewal...
    public function auditorIsReg(Request $request)
    {

        if (isset($request->token) && isset($request->email)) {
            $loggedUserEmail = $request->email;
            $loggedUserId = User::where('email', $loggedUserEmail)->value('id');
            $auditorDetails = AuditorRenewal::where('token', $request->token)->first();
            if ($auditorDetails) {
                $createdUserId = Auditor::where('id', $auditorDetails->auditor_id)->value('created_by');
                if ($loggedUserId === $createdUserId) {
                    $auditorDetails = AuditorRenewal::where('token', $request->token)->first();

                    if (($auditorDetails->status == $this->settings('AUDITOR_RENEWAL_PROCESSING', 'key')->id) || ($auditorDetails->status == $this->settings('AUDITOR_RENEWAL_REQUEST_TO_RESUBMIT', 'key')->id)) {
                        $audId = $auditorDetails['auditor_id'];
                        return response()->json([
                            'message' => 'Registered Auditor.',
                            'status' => true,
                            'audId' => $audId,
                        ], 200);
                    } else {
                        return response()->json([
                            'message' => 'Not Registered as a Auditor.',
                            'status' => false,
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'message' => 'Unauthorized User',
                        'status' => false,
                        '1' => $loggedUserId,
                        '2' => $createdUserId,
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'Invalid Token',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // to generate auditor firm renewal pdf...
    public function generateAuditorFirmRenewalPDF(Request $request)
    {

        if (isset($request->token)) {
            $auditorStatus = AuditorRenewal::leftJoin('settings', 'auditor_renewal.status', '=', 'settings.id')
                ->where('auditor_renewal.token', $request->token)
                ->get(['settings.key as status']);
            if (sizeof($auditorStatus) > 0) {
                if ($auditorStatus[0]['status'] == 'AUDITOR_RENEWAL_PROCESSING' or $auditorStatus[0]['status'] == 'AUDITOR_RENEWAL_REQUEST_TO_RESUBMIT') {
                    $auditorFirmDetails = AuditorFirm::leftJoin('auditor_renewal', 'auditor_firms.id', '=', 'auditor_renewal.firm_id')
                        ->leftJoin('auditor_certificates', 'auditor_firms.id', '=', 'auditor_certificates.firm_id')
                        ->where('auditor_renewal.token', $request->token)
                        ->get(['auditor_firms.address_id', 'auditor_certificates.certificate_no as certificate_no', 'auditor_firms.name', 'auditor_firms.telephone', 'auditor_firms.email']);
                    $address = Address::where('id', $auditorFirmDetails[0]['address_id'])->first();

                    $todayDate = date("Y-m-d");

                    $day1 = date('d', strtotime($todayDate));
                    $month1 = date('m', strtotime($todayDate));
                    $year1 = date('Y', strtotime($todayDate));

                    if (isset($auditorFirmDetails)) {
                        $name = $auditorFirmDetails[0]['name'];
                        $regnum = $auditorFirmDetails[0]['certificate_no'];
                        $telephone = $auditorFirmDetails[0]['telephone'];
                        $email = $auditorFirmDetails[0]['email'];
                        $data = [
                            'name' => $name,
                            'regnum' =>  $regnum,
                            'telephone' => $telephone,
                            'email' => $email,
                            'address' => $address,
                            'year1' => $year1,
                        ];
                        $pdf = PDF::loadView('auditor-forms/renewalfirm', $data);
                        return $pdf->stream('renewal.pdf');
                    } else {
                        return response()->json([
                            'message' => 'We can \'t find a User.',
                            'status' => false,
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'message' => 'We can \'t find a User.',
                        'status' => false,
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'Invalid Token',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }

    // to check auditor firm is registered to renewal...
    public function auditorFirmIsReg(Request $request)
    {

        if (isset($request->token) && isset($request->email)) {
            $loggedUserEmail = $request->email;
            $loggedUserId = User::where('email', $loggedUserEmail)->value('id');
            $auditorFirmDetails = AuditorRenewal::where('token', $request->token)->first();
            if ($auditorFirmDetails) {
                $createdUserId = AuditorFirm::where('id', $auditorFirmDetails->firm_id)->value('created_by');
                if ($loggedUserId === $createdUserId) {
                    $auditorFirmDetails = AuditorRenewal::where('token', $request->token)->first();

                    if (($auditorFirmDetails->status == $this->settings('AUDITOR_RENEWAL_PROCESSING', 'key')->id) || ($auditorFirmDetails->status == $this->settings('AUDITOR_RENEWAL_REQUEST_TO_RESUBMIT', 'key')->id)) {
                        $firmId = $auditorFirmDetails['firm_id'];
                        return response()->json([
                            'message' => 'Registered Auditor Firm.',
                            'status' => true,
                            'firmId' => $firmId,
                        ], 200);
                    } else {
                        return response()->json([
                            'message' => 'Not Registered as a Auditor Firm.',
                            'status' => false,
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'message' => 'Unauthorized User',
                        'status' => false,
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'Invalid Token',
                    'status' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'We can \'t find a User.',
                'status' => false,
            ], 200);
        }
    }
}
