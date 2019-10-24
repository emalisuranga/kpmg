<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::group(['prefix' => '/v1', 'namespace' => 'API\v1'], function () {
    Route::post('eroc/login', 'Auth\LoginController@authenticate')->name('auth.login'); //User Login
    Route::post('eroc/register', 'Auth\RegisterController@register')->name('auth.register'); //User Register
    Route::post('eroc/migrate/register', 'Auth\RegisterController@setMigrateRegister'); //User Register
    Route::post('eroc/refresh', 'Auth\LoginController@refresh')->name('auth.refresh'); //User refresh token
    Route::put('eroc/user/verification', 'Auth\RegisterController@verifyAccount')->name('auth.verifyAccount');
    Route::put('eroc/user/migrate/verification', 'Auth\RegisterController@verifyMigrateAccount');
    Route::get('eroc/user/exists', 'Auth\RegisterController@checkExisitsEmail');
    Route::get('eroc/request/link/{email}', 'Auth\RegisterController@requestLinkWithToken');

    Route::get('eroc/company/type', 'General\GeneralController@getCompanyType');
    Route::get('eroc/member/title', 'General\GeneralController@getMemberTitle');
    Route::get('eroc/get/gnDivisionAndCity', 'General\GeneralController@getGnandCity');
    Route::get('eroc/get/country', 'General\GeneralController@getCountry');
    Route::get('eroc/country/details', 'General\GeneralController@getCountryDetails');
    Route::post('eroc/valid/token', 'General\GeneralController@validSecToken');
    Route::post('eroc/get/city', 'General\GeneralController@getCity');

    Route::post('eroc/admin-signup/check-company', 'General\GeneralController@checkCompanyByRegNumber');

    Route::post('forgot/email/link', 'Auth\ResetPasswordController@sendLink');
    Route::put('forgot/password/reset', 'Auth\ResetPasswordController@resetMyPassword');

    // Change Password
    Route::get('eroc/changePassword', 'Auth\ResetChangePassword@isCheckOldPassword');
    Route::put('eroc/changePassword', 'Auth\ResetChangePassword@changePassword')->name('changePassword');

    /** tenders - non auth requests ***/
    Route::post('/get-tenders', 'Tender\TenderController@getTenders');
    Route::post('/get-tender', 'Tender\TenderController@getTender');
    Route::post('/get-close-tender', 'Tender\TenderController@getCloseTender');

    Route::post('/apply-tender', 'Tender\TenderController@submitTender');
    Route::post('/tender-file-upload', 'Tender\TenderController@upload');
    Route::post('tender-file-upload_resubmit_other', 'Tender\TenderController@uploadOtherResubmittedDocs');
    Route::post('/tender-other-file-upload', 'Tender\TenderController@uploadOtherDocs');
    Route::post('/tender-file-remove', 'Tender\TenderController@removeFile');
    Route::post('/tender-other-file-remove', 'Tender\TenderController@removeOtherDoc');
    Route::post('/tender-apply-pay', 'Tender\TenderController@tenderApplyPay');

    Route::post('/resubmit-tender', 'Tender\TenderController@resubmitTender');
    Route::post('/check-already-applied-items', 'Tender\TenderController@checkAlreadyApplied');

    Route::post('/get-resubmitted-tender', 'Tender\TenderController@getResubmttedTender');
    Route::post('/tender-resubmitted', 'Tender\TenderController@tenderResbumit');

    // tender - awarding
    Route::post('/get-awording-tender', 'Tender\TenderController@getAwordingTender');
    Route::post('/tender-aword-file-upload', 'Tender\TenderController@uploadForAwording');
    Route::post('/tender-aword-other-file-upload', 'Tender\TenderController@uploadAwardingOtherDocs');
    Route::post('tender-aword-upload-resubmit-other', 'Tender\TenderController@uploadAwordOtherResubmittedDocs');
    Route::post('/tender-aword-file-remove', 'Tender\TenderController@removeAworddingFile');
    Route::post('/tender-aword-other-file-remove', 'Tender\TenderController@removeAwardingOtherDoc');
    Route::post('/tender-awarded', 'Tender\TenderController@tenderAwarded');
    Route::post('/tender-award-resubmitted', 'Tender\TenderController@tenderAwardingResubmitted');
    Route::post('/tender-upate-contact-details', 'Tender\TenderController@tenderAwardedUpdateContract');
    Route::post('/tender-aword-signing-party-detail', 'Tender\TenderController@updateAwardingSigningPartyDetails');

    Route::post('/tender-publisher-other-file-upload', 'Tender\TenderController@uploadPublisherOtherDocs');
    Route::post('/tender-publihser-other-file-remove', 'Tender\TenderController@removePublisherOtherDoc');

    // tender - renewal and reregistration
    Route::post('/renewal-tender', 'Tender\TenderRenewalRegistrationController@getRenewalApplication');
    Route::post('/renewal-tender-file-upload', 'Tender\TenderRenewalRegistrationController@upload_renwal');
    Route::post('/renewal-tender-file-remove', 'Tender\TenderRenewalRegistrationController@removeTenderRenewalFile');
    Route::post('/renewal-resubmission-tender', 'Tender\TenderRenewalRegistrationController@getRenewalResubmission');
    Route::post('/rereg-tender', 'Tender\TenderRenewalRegistrationController@getReRegistrationApplication');
    Route::post('/rereg-tender-file-upload', 'Tender\TenderRenewalRegistrationController@upload_reregistration');
    Route::post('/rereg-tender-file-remove', 'Tender\TenderRenewalRegistrationController@removeTenderReregisterFile');
    Route::post('/rereg-resubmission-tender', 'Tender\TenderRenewalRegistrationController@getReRegistrationResubmission');
    Route::post('/renewal-rereg-tender-update-pca7', 'Tender\TenderRenewalRegistrationController@submitPCA7details');
    Route::post('/renewal-resubmitted', 'Tender\TenderRenewalRegistrationController@renewalResubmitted');
    Route::post('/rereg-resubmitted', 'Tender\TenderRenewalRegistrationController@reregResubmitted');

    Route::post('/rereg-new-record', 'Tender\TenderRenewalRegistrationController@renewalReregNewRequest');

   // Company Verification
    Route::post('/verify-company', 'Incorporation\CompanyVerificationController@checkCompanyVerification');

    // Name Search
    Route::post('eroc/name/search', 'Search\SEOController@showName')->name('search');

    Route::get('eroc/payment/{ref_no}/{convenience_fee}/{gateway_name}/{transection_status}', 'Payment\PaymentController@paymentForName');

    // Payment
    Route::post('eroc/cipher/token', 'Payment\PaymentController@payMyProduct');
    Route::post('eroc/name/payment', 'Payment\PaymentController@paymentForName');

    Route::middleware('auth:api')->group(function () {
        Route::get('eroc/set/Company', 'General\GeneralController@setCompany');

        // Name onResavation
        Route::post('eroc/name/receive', 'Search\ReservationController@setName');
        Route::get('eroc/name/fix/has', 'Search\ReservationController@isGetfix');
        Route::post('eroc/name/receive/files/upload', 'Search\ReservationController@uploadFile');
        Route::post('eroc/name/receive/files/upload_other', 'Search\ReservationController@uploadOtherDocs');
        Route::post('eroc/name/receive/files/upload_resubmit_other', 'Search\ReservationController@uploadOtherResubmittedDocs');
        Route::post('eroc/name/receive/files/upload_other_for_name', 'Search\ReservationController@uploadOtherDocsForName');
        Route::post('eroc/name/receive/files/get_other_uploaded_docs', 'Search\ReservationController@files_for_other_docs');
        Route::post('eroc/name/receive/files/get_other_uploaded_docs_for_reservation', 'Search\ReservationController@files_for_other_docs_for_name_reservation');
        Route::post('eroc/name/receive/files/remoe_other_doc', 'Search\ReservationController@removeOtherDoc');
        Route::post('eroc/name/has/reSubmit', 'Search\ReservationController@hasNameReSubmit');
        Route::post('eroc/name/cancel', 'Search\ReservationController@nameCancel');

        Route::post('eroc/files/upload', 'Search\ReservationController@uploadFileRequest');

        Route::post('eroc/get/change/name/form/fill', 'NameChange\NameChangeController@getNameChangeformWithfill');
        Route::post('eroc/get/change/name/form/is-date-resolution-gap-exeed', 'NameChange\NameChangeController@getResolutionDateGapisExeed');
        Route::post('eroc/set/change/name/resubmit', 'NameChange\NameChangeController@setResubmit');
        Route::post('eroc/set/change/name/update_court_order_details', 'NameChange\NameChangeController@updateCourtInfo');
        Route::post('eroc/get/change/name/form/is-set-resolution-date', 'NameChange\NameChangeController@checkHasSetResolutionDate');

        // Dashboard
        Route::get('eroc/name/search', 'Search\ReservationController@getSearchResult');
        Route::get('eroc/name/reservation/data', 'Search\ReservationController@getNameReservationData');
        Route::post('eroc/data/reservation/resubmit/file', 'Search\ReservationController@getResubmitDoc');
        Route::post('eroc/name/received', 'Search\ReservationController@getUserData');
        Route::put('eroc/name/re/submit', 'Search\ReservationController@setNameReSubmit');
        Route::post('eroc/avater', 'Auth\UserController@getAvater');

        // user
        Route::get('eroc/users', 'Auth\UserController@getUser');
        Route::post('eroc/edit/user', 'Auth\UserController@updateUserProfile');

        // logout
        Route::get('eroc/logout', 'Auth\LoginController@logout'); //system logout

        // General Setting
        Route::post('eroc/status/count', 'General\GeneralController@getStatusCount');
        Route::post('eroc/document/feild', 'General\GeneralController@getdocDynamic');
        Route::get('eroc/company/sub/category', 'General\GeneralController@getSubCompanyType');
        Route::post('eroc/get/member', 'General\GeneralController@getMember');
        Route::post('eroc/get/setting', 'General\GeneralController@getSetting');

        // Documnet Download
        Route::post('eroc/document/download', 'General\GeneralController@getDocument')->name('download-document');
        Route::post('eroc/document/name', 'General\GeneralController@getDocName')->name('Get-document-Name');
        Route::delete('eroc/document/destroy', 'General\GeneralController@isFileDestroy')->name('file-destroy');
        Route::delete('eroc/resubmit/document/destroy', 'General\GeneralController@isResubmitFileDestroy')->name('ReSubmit-destroy');

        // Reduction Capital
        Route::post('eroc/get/reduc/data', 'Capital\ReductionCapitalController@index');
        Route::post('eroc/get/reduc', 'Capital\ReductionCapitalController@getShareData');
        Route::post('eroc/eroc/continue', 'Capital\ReductionCapitalController@Continue');
        Route::post('eroc/set/reduc', 'Capital\ReductionCapitalController@setReduRaw');
        Route::post('eroc/update/reduc', 'Capital\ReductionCapitalController@updateReduRaw');
        Route::post('eroc/get/capital/form/fill', 'Capital\ReductionCapitalController@getForm');
        Route::put('eroc/set/Resubmit', 'Capital\ReductionCapitalController@setResubmit');
        Route::post('eroc/get/reduc/is-date-resolution-gap-exeed', 'Capital\ReductionCapitalController@getPanaltyCharge');


        //Reduction
        Route::post('/form8-data', 'Capital\ReductionStatedCapitalController@loadData')->name('form8-data');
        Route::post('/form8-submit', 'Capital\ReductionStatedCapitalController@submitRecords')->name('form8-submit');
        Route::post('/form8-upload-docs', 'Capital\ReductionStatedCapitalController@upload')->name('form8-upload-docs');
        Route::post('/form8-upload-other-docs', 'Capital\ReductionStatedCapitalController@uploadOtherDocs')->name('form8-upload-other-docs');
        Route::post('/form8-upload-other-resubmitted-docs', 'Capital\ReductionStatedCapitalController@uploadOtherResubmittedDocs')->name('form8-upload-other-resubmitted-docs');
        Route::post('/form8-remove-docs', 'Capital\ReductionStatedCapitalController@removeDoc')->name('form8-remove-docs');
        Route::post('/form8-remove-other-docs', 'Capital\ReductionStatedCapitalController@removeOtherDoc')->name('form8-remove-other-docs');
        Route::post('/form8-resubmit', 'Capital\ReductionStatedCapitalController@resubmit')->name('form8-resubmit');
        Route::post('/form8-update-court-record', 'Capital\ReductionStatedCapitalController@updateCourtRecords')->name('form8-update-court-record');
        
        //special resolution - form 39
         //Reduction
         Route::post('/form39-data', 'specialResolution\SpecialResolutionController@loadData')->name('form39-data');
         Route::post('/form39-submit', 'specialResolution\SpecialResolutionController@submitRecords')->name('form39-submit');
         Route::post('/form39-upload-docs', 'specialResolution\SpecialResolutionController@upload')->name('form39-upload-docs');
         Route::post('/form39-upload-other-docs', 'specialResolution\SpecialResolutionController@uploadOtherDocs')->name('form39-upload-other-docs');
         Route::post('/form39-upload-other-resubmitted-docs', 'specialResolution\SpecialResolutionController@uploadOtherResubmittedDocs')->name('form39-upload-other-resubmitted-docs');
         Route::post('/form39-remove-docs', 'specialResolution\SpecialResolutionController@removeDoc')->name('form39-remove-docs');
         Route::post('/form39-remove-other-docs', 'specialResolution\SpecialResolutionController@removeOtherDoc')->name('form39-remove-other-docs');
         Route::post('/form39-resubmit', 'specialResolution\SpecialResolutionController@resubmit')->name('form39-resubmit');
         Route::post('/form39-update-court-record', 'specialResolution\SpecialResolutionController@updateCourtRecords')->name('form39-update-court-record');
        /* ---------------------- Udara Madushan -------------------------*/
        Route::post('/company-incorporation-data', 'Incorporation\IncorporationController@loadData')->name('incorporation-data');
        Route::post('/company-incorporation-heavy-data', 'Incorporation\IncorporationController@loadHeavyData')->name('incorporation-heavy-data');
        Route::post('/company-incorporation-data-step1', 'Incorporation\IncorporationController@submitStep1')->name('company-incorporation-data-step1');
        Route::post('/company-incorporation-data-step2', 'Incorporation\IncorporationController@submitStep2')->name('company-incorporation-data-step2');
        Route::post('/company-incorporation-check-nic', 'Incorporation\IncorporationController@checkNic');
        Route::post('/company-incorporation-delete-stakeholder', 'Incorporation\IncorporationController@removeStakeHolder');
        Route::post('/generate-pdf', 'Incorporation\IncorporationController@generatePDF');
        Route::post('/file-upload', 'Incorporation\IncorporationController@upload');
        Route::post('/file-remove', 'Incorporation\IncorporationController@removeDoc');
        Route::post('/save-doc-copies', 'Incorporation\IncorporationController@saveNoOfCopies');

        Route::post('/pay', 'Incorporation\IncorporationController@submitPay');
        Route::post('/re-submit', 'Incorporation\IncorporationController@resubmit');
        Route::post('/foreign-company-request-approval', 'Incorporation\IncorporationController@requestApprovalForForeign');

        Route::post('/remove-director-sec-position', 'Incorporation\IncorporationController@removeSecForDirector');
        Route::post('/remove-director-sh-position', 'Incorporation\IncorporationController@removeShForDirector');
        Route::post('/remove-sec-sh-position', 'Incorporation\IncorporationController@removeShForSec');
        Route::post('/remove-secfirm-shfirm-position', 'Incorporation\IncorporationController@removeShForSecFirm');
        Route::post('/remove-sh-firm', 'Incorporation\IncorporationController@removeShFirm');
        Route::post('/remove-sec-firm', 'Incorporation\IncorporationController@removeSecFirm');


        Route::post('/get-companies', 'Incorporation\CertifiedCopiesController@getCompnanies');
        Route::post('/company-certified-incorporation-heavy-data', 'Incorporation\CertifiedCopiesController@loadHeavyData')->name('company-certified-incorporation-heavy-data');
        Route::post('/company-certified-incorporation-data', 'Incorporation\CertifiedCopiesController@loadData')->name('incorporation-data');
        Route::post('/save-pub-doc-copies', 'Incorporation\CertifiedCopiesController@saveNoOfPublicCopies');
        Route::post('/save-ird-info', 'Incorporation\IncorporationController@saveIRDInfo');
        Route::post('/save-labour-info', 'Incorporation\IncorporationController@saveLabourInfo');
        Route::post('/upload-ird-drector-nic', 'Incorporation\IncorporationController@upload_ird_nic');
        Route::post('/remove-ird-drector-nic', 'Incorporation\IncorporationController@removeIRDNICDoc');
        

        /** Tenders - auth requests **/
        Route::post('/get-publications', 'Tender\TenderController@tenderPublications');
        Route::post('/get-user-tenders', 'Tender\TenderController@getUserTenders');
        Route::post('/add-tender', 'Tender\TenderController@createTender');
        Route::post('/add-tender-items', 'Tender\TenderController@createTenderItems');
        Route::post('/tender-document-upload', 'Tender\TenderController@upload_tender_document');
        Route::post('/tender-document-remove', 'Tender\TenderController@removeTenderDoc');

        Route::post('/tender-aword-by-publisher', 'Tender\TenderController@awordForApplicant');
        Route::post('/tender-change-closing-date-publisher', 'Tender\TenderController@notifyTenderItemDateChange');

        Route::post('/tender-user-applications', 'Tender\TenderController@getAppliedTenders');


        // Bulk socity//
        Route::post('/bulk-societies', 'societyBulk\SocietyBulkController@getBulkSocietiesList');
        Route::post('/upload-bulk-societies', 'societyBulk\SocietyBulkController@uploadBulkSocieties');
        Route::post('/upload-bulk-societies-file', 'societyBulk\SocietyBulkController@upload');
        Route::post('/remove-societies-file', 'societyBulk\SocietyBulkController@removeFile');
        Route::post('/remove-pending-societies-all', 'societyBulk\SocietyBulkController@removeSocietiesAction');
        Route::post('update-optional', 'societyBulk\SocietyBulkController@updateSocietyOptional');
        Route::post('remove-society', 'societyBulk\SocietyBulkController@removeSociety');

        // Annual Return
        Route::post('/company-annual-data', 'AnnualReturn\AnnualReturnController@loadData')->name('company-annual-data');
        Route::post('/company-annual-heavy-data', 'AnnualReturn\AnnualReturnController@loadHeavyData')->name('company-annual-heavy-data');
        Route::post('/company-annual-check-nic', 'AnnualReturn\AnnualReturnController@checkNic');
        Route::post('/company-annual-submit-step1', 'AnnualReturn\AnnualReturnController@submitStep1')->name('company-annual-submit-step1');
        Route::post('/company-annual-submit-directors', 'AnnualReturn\AnnualReturnController@submitDirectors')->name('company-annual-submit-directors');
        Route::post('/company-annual-submit-secretories', 'AnnualReturn\AnnualReturnController@submitSecretories')->name('company-annual-submit-secretories');
        Route::post('/company-annual-submit-shareholders', 'AnnualReturn\AnnualReturnController@submitShareolders')->name('company-annual-submit-shareholders');
        Route::post('/company-annual-submit-share-register', 'AnnualReturn\AnnualReturnController@submitShareReisterRecords')->name('company-annual-submit-share-register');
        Route::post('/company-annual-submit-annual-records', 'AnnualReturn\AnnualReturnController@submitAnnualRecords')->name('company-annual-submit-annual-records');
        Route::post('/company-annual-submit-annual-auditor-records', 'AnnualReturn\AnnualReturnController@submitAnnualAuditorRecords')->name('company-annual-submit-annual-auditor-records');
        Route::post('/company-annual-submit-annual-charges-records', 'AnnualReturn\AnnualReturnController@submitAnnualCharges')->name('company-annual-submit-annual-charges-records');
        Route::post('/company-annual-submit-share-records', 'AnnualReturn\AnnualReturnController@submitShareRecords')->name('company-annual-submit-share-records');
        Route::post('/company-annual-upload-docs', 'AnnualReturn\AnnualReturnController@upload')->name('company-annual-upload-docs');
        Route::post('/company-annual-remove-docs', 'AnnualReturn\AnnualReturnController@removeDoc')->name('company-annual-remove-docs'); 
        Route::post('/company-annual-resubmit', 'AnnualReturn\AnnualReturnController@resubmit')->name('company-annual-resubmit');
        Route::post('/company-annual-submit-bulk-shareholder-csv', 'AnnualReturn\AnnualReturnController@uploadShareholderByCSV')->name('company-annual-submit-bulk-shareholder-csv');
        Route::post('/company-annual-submit-bulk-ceased-shareholder-csv', 'AnnualReturn\AnnualReturnController@uploadCeasedShareholderByCSV')->name('company-annual-submit-bulk-ceased-shareholder-csv');
        Route::post('/company-annual-submit-share-transfers', 'AnnualReturn\AnnualReturnController@submitShareholderTransfers')->name('company-annual-submit-share-transfers');
        
        Route::post('/company-annual-upload-other-docs', 'AnnualReturn\AnnualReturnController@uploadOtherDocs')->name('company-annual-upload-other-docs');
        Route::post('/company-annual-upload-other-resubmitted-docs', 'AnnualReturn\AnnualReturnController@uploadOtherResubmittedDocs')->name('ccompany-annual-upload-other-resubmitted-docs');
        Route::post('/company-annual-remove-other-docs', 'AnnualReturn\AnnualReturnController@removeOtherDoc')->name('company-annual-remove-other-docs');
        Route::post('/company-annual-update-court-record', 'AnnualReturn\AnnualReturnController@updateCourtRecords')->name('company-annual-update-court-record');

        // Register of Charges
        Route::post('/company-register-charges-data', 'RegisterOfCharges\RegisterOfChargesController@loadData')->name('company-register-charges-data');
        Route::post('/company-register-charges-submit', 'RegisterOfCharges\RegisterOfChargesController@submitRecords')->name('company-register-charges-submit');
        Route::post('/company-register-charges-upload-docs', 'RegisterOfCharges\RegisterOfChargesController@upload')->name('company-register-charges-upload-docs');
        Route::post('/company-register-charges-remove-docs', 'RegisterOfCharges\RegisterOfChargesController@removeDoc')->name('company-register-charges-remove-docs');
        Route::post('/company-register-charges-resubmit', 'RegisterOfCharges\RegisterOfChargesController@resubmit')->name('company-register-charges-resubmit');


        //issue of shares
        Route::post('/company-issue-of-shares-data', 'IssueofShares\IssueOfSharesController@loadData')->name('company-issue-of-shares-data');
        Route::post('/company-issue-of-shares-submit', 'IssueofShares\IssueOfSharesController@submitShareCallRecords')->name('company-issue-of-shares-submit');
        Route::post('/company-issue-of-shares-remove-record', 'IssueofShares\IssueOfSharesController@removeShareClassRecord')->name('company-issue-of-shares-remove-record');
        Route::post('/company-issue-of-shares-upload-docs', 'IssueofShares\IssueOfSharesController@upload')->name('company-issue-of-shares-upload-docs');
        Route::post('/company-issue-of-shares-remove-docs', 'IssueofShares\IssueOfSharesController@removeDoc')->name('company-issue-of-shares-remove-docs');
        Route::post('/company-issue-of-shares-resubmit', 'IssueofShares\IssueOfSharesController@resubmit')->name('company-issue-of-shares-resubmit');
        Route::post('/company-issue-of-shares-upload-other-docs', 'IssueofShares\IssueOfSharesController@uploadOtherDocs')->name('company-issue-of-shares-upload-other-docs');
        Route::post('/company-issue-of-shares-upload-other-resubmitted-docs', 'IssueofShares\IssueOfSharesController@uploadOtherResubmittedDocs')->name('company-issue-of-shares-upload-other-resubmitted-docs');
        Route::post('/company-issue-of-shares-remove-other-docs', 'IssueofShares\IssueOfSharesController@removeOtherDoc')->name('company-calls-issue-of-remove-other-docs');
        Route::post('/company-issue-of-shares-update-court-record', 'IssueofShares\IssueOfSharesController@updateCourtRecords')->name('company-issue-of-shares-update-court-record');
        Route::post('/company-issue-of-shares-submit-shareholders', 'IssueofShares\IssueOfSharesController@submitShareolders')->name('company-issue-of-shares-submit-shareholders');
        Route::post('/company-issue-of-shares-submit-new-shareholder', 'IssueofShares\IssueOfSharesController@submitNewShareHolder')->name('company-issue-of-shares-submit-new-shareholder');
        Route::post('/company-issue-of-shares-submit-exist-shareholder', 'IssueofShares\IssueOfSharesController@submitExisitingShareholder')->name('company-issue-of-shares-submit-exist-shareholder');
        Route::post('/company-issue-of-shares-remove-shareholder', 'IssueofShares\IssueOfSharesController@removeShareHolder')->name('company-issue-of-shares-remove-shareholder');
        Route::post('/company-issue-of-shares-check-nic', 'IssueofShares\IssueOfSharesController@checkNic');
        Route::post('/upload-shareholder-csv', 'IssueofShares\IssueOfSharesController@uploadShareholderByCSV');
       
        //calls on shares
        Route::post('/company-calls-on-shares-data', 'CallOnShares\CallOnSharesController@loadData')->name('company-calls-on-shares-data');
        Route::post('/company-calls-on-shares-submit', 'CallOnShares\CallOnSharesController@submitShareCallRecords')->name('company-calls-on-shares-submit');
        Route::post('/company-calls-on-shares-upload-docs', 'CallOnShares\CallOnSharesController@upload')->name('company-calls-on-shares-upload-docs');
        Route::post('/company-calls-on-shares-remove-docs', 'CallOnShares\CallOnSharesController@removeDoc')->name('company-calls-on-shares-remove-docs');
        Route::post('/company-calls-on-shares-resubmit', 'CallOnShares\CallOnSharesController@resubmit')->name('company-calls-on-shares-resubmit');

    
        Route::post('/company-calls-on-shares-upload-other-docs', 'CallOnShares\CallOnSharesController@uploadOtherDocs')->name('company-calls-on-shares-upload-other-docs');
        Route::post('/company-calls-on-shares-upload-other-resubmitted-docs', 'CallOnShares\CallOnSharesController@uploadOtherResubmittedDocs')->name('company-calls-on-shares-upload-other-resubmitted-docs');
        Route::post('/company-calls-on-shares-remove-other-docs', 'CallOnShares\CallOnSharesController@removeOtherDoc')->name('company-calls-on-shares-remove-other-docs');
        Route::post('/company-calls-on-shares-update-court-record', 'CallOnShares\CallOnSharesController@updateCourtRecords')->name('company-calls-on-shares-update-court-record');

          // Charges Registration
          Route::post('/company-charges-registration-data', 'Charges\ChargesRegistrationController@loadData')->name('company-charges-registration-data');
          Route::post('/company-charges-registration-submit', 'Charges\ChargesRegistrationController@submitRecords')->name('company-charges-registration-submit');
          Route::post('/company-charges-registration-upload-docs', 'Charges\ChargesRegistrationController@upload')->name('company-charges-registration-upload-docs');
          Route::post('/company-charges-registration-remove-docs', 'Charges\ChargesRegistrationController@removeDoc')->name('company-charges-registration-remove-docs');
          Route::post('/company-charges-registration-resubmit', 'Charges\ChargesRegistrationController@resubmit')->name('company-charges-registration-resubmit');
          Route::post('/company-charges-items-submit', 'Charges\ChargesRegistrationController@submitDeedItems')->name('company-charges-items-submit');
          Route::post('/company-charges-persons-submit', 'Charges\ChargesRegistrationController@submitEntitledPersons')->name('company-charges-persons-submit');
          Route::post('/company-charges-persons-submit', 'Charges\ChargesRegistrationController@submitEntitledPersons')->name('company-charges-persons-submit');
          Route::post('/company-charges-remove-deed', 'Charges\ChargesRegistrationController@removeDeedItem')->name('ompany-charges-remove-deed');
          Route::post('/company-charges-remove-person', 'Charges\ChargesRegistrationController@removeEntitledPersonItem')->name('ompany-charges-remove-person');

          Route::post('/company-charges-registration-upload-other-docs', 'Charges\ChargesRegistrationController@uploadOtherDocs')->name('company-charges-registration-upload-other-docs');
          Route::post('/company-charges-registration-upload-other-resubmitted-docs', 'Charges\ChargesRegistrationController@uploadOtherResubmittedDocs')->name('company-charges-registration-upload-other-resubmitted-docs');
          Route::post('/company-charges-registration-remove-other-docs', 'Charges\ChargesRegistrationController@removeOtherDoc')->name('company-charges-registration-remove-other-docs');
          Route::post('/company-charges-update-court-record', 'Charges\ChargesRegistrationController@updateCourtRecords')->name('company-charges-update-court-record');

          Route::post('company-charges-check-company', 'Charges\ChargesRegistrationController@checkCompanyByRegNumber');
          Route::post('company-charges-add-company', 'Charges\ChargesRegistrationController@addCompaniesToLawyerBankerProfile');
          
          // Appointment of Admin - form 34
            Route::post('/form34-data', 'AppointmentOfAdmin\AppointmentOfAdminController@loadData')->name('form34-data');
            Route::post('/form34-submit', 'AppointmentOfAdmin\AppointmentOfAdminController@submitRecords')->name('form34-submit');
            Route::post('/form34-upload-docs', 'AppointmentOfAdmin\AppointmentOfAdminController@upload')->name('form34-upload-docs');
            Route::post('/form34-remove-docs', 'AppointmentOfAdmin\AppointmentOfAdminController@removeDoc')->name('form34-remove-docs');
            Route::post('/form34-resubmit', 'AppointmentOfAdmin\AppointmentOfAdminController@resubmit')->name('form34-resubmit');

            Route::post('/form34-upload-other-docs', 'AppointmentOfAdmin\AppointmentOfAdminController@uploadOtherDocs')->name('form34-upload-other-docs');
            Route::post('/form34-upload-other-resubmitted-docs', 'AppointmentOfAdmin\AppointmentOfAdminController@uploadOtherResubmittedDocs')->name('form34-upload-other-resubmitted-docs');
            Route::post('/form34-remove-other-docs', 'AppointmentOfAdmin\AppointmentOfAdminController@removeOtherDoc')->name('form34-remove-other-docs');
            Route::post('/form34-update-court-record', 'AppointmentOfAdmin\AppointmentOfAdminController@updateCourtRecords')->name('form34-update-court-record');
            Route::post('form34-check-company', 'AppointmentOfAdmin\AppointmentOfAdminController@checkCompanyByRegNumber');
            Route::post('form34-add-company', 'AppointmentOfAdmin\AppointmentOfAdminController@addCompaniesToAdminProfile');
           
            // Company Notices - form 22
           Route::post('/company-notices-data', 'Notices\CompanyNoticesController@loadData')->name('company-notices-data');
           Route::post('/company-notices-submit', 'Notices\CompanyNoticesController@submitRecords')->name('company-notices-submit');
           Route::post('/company-notices-upload-docs', 'Notices\CompanyNoticesController@upload')->name('company-notices-upload-docs');
           Route::post('/company-notices-remove-docs', 'Notices\CompanyNoticesController@removeDoc')->name('company-notices-remove-docs');
           Route::post('/company-notices-resubmit', 'Notices\CompanyNoticesController@resubmit')->name('company-notices-resubmit');
        
        //name change notices of overseas
        Route::post('/overseas-name-change-data', 'NameChange\NoticeOfNameChangeOfOverseasController@loadData')->name('overseas-name-change-data');
        Route::post('/overseas-name-change-submit', 'NameChange\NoticeOfNameChangeOfOverseasController@submitRecords')->name('overseas-name-change-submit');
        Route::post('/overseas-name-change-upload-docs', 'NameChange\NoticeOfNameChangeOfOverseasController@upload')->name('overseas-name-change-upload-docs');
        Route::post('/overseas-name-change-remove-docs', 'NameChange\NoticeOfNameChangeOfOverseasController@removeDoc')->name('overseas-name-change-remove-docs');
        Route::post('/overseas-name-change-resubmit', 'NameChange\NoticeOfNameChangeOfOverseasController@resubmit')->name('overseas-name-change-resubmit');
        Route::post('/overseas-name-change-upload-other-docs', 'NameChange\NoticeOfNameChangeOfOverseasController@uploadOtherDocs')->name('overseas-name-change-upload-other-docs');
        Route::post('/overseas-name-change-remove-other-docs', 'NameChange\NoticeOfNameChangeOfOverseasController@removeOtherDoc')->name('overseas-name-change-remove-other-docs');
        Route::post('/overseas-name-change-resubmitted-docs', 'NameChange\NoticeOfNameChangeOfOverseasController@uploadOtherResubmittedDocs')->name('overseas-name-change-resubmitted-docs');
        Route::post('/overseas-name-change-update-court-record', 'NameChange\NoticeOfNameChangeOfOverseasController@updateCourtRecords')->name('overseas-name-change-update-court-record');


         // Overseas Company Alterations - Form 35
         Route::post('/company-overseas-annual-data', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@loadData')->name('company-annual-data');
         Route::post('/company-overseas-annual-heavy-data', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@loadHeavyData')->name('company-overseas-annual-heavy-data');
         Route::post('/company-overseas-annual-check-nic', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@checkNic');
         Route::post('/company-overseas-annual-submit-step1', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@submitStep1')->name('company-overseas-annual-submit-step1');
         Route::post('/company-overseas-annual-submit-directors', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@submitDirectors')->name('company-overseas-annual-submit-directors');
         Route::post('/company-overseas-annual-submit-secretories', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@submitSecretories')->name('company-overseas-annual-submit-secretories');
         Route::post('/company-overseas-annual-submit-shareholders', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@submitShareolders')->name('company-overseas-annual-submit-shareholders');
         Route::post('/company-overseas-annual-submit-share-register', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@submitShareReisterRecords')->name('company-overseas-annual-submit-share-register');
         Route::post('/company-overseas-annual-submit-annual-records', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@submitAnnualRecords')->name('company-overseas-annual-submit-annual-records');
         Route::post('/company-overseas-annual-submit-annual-auditor-records', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@submitAnnualAuditorRecords')->name('company-overseas-annual-submit-annual-auditor-records');
         Route::post('/company-overseas-annual-submit-annual-charges-records', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@submitAnnualCharges')->name('company-overseas-annual-submit-annual-charges-records');
         Route::post('/company-overseas-annual-submit-share-records', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@submitShareRecords')->name('company-overseas-annual-submit-share-records');
         Route::post('/company-overseas-annual-upload-docs', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@upload')->name('company-overseas-annual-upload-docs');
         Route::post('/company-overseas-annual-remove-docs', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@removeDoc')->name('company-overseas-annual-remove-docs'); 
         Route::post('/company-overseas-annual-resubmit', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@resubmit')->name('company-overseas-annual-resubmit');
         Route::post('/company-overseas-annual-submit-bulk-shareholder-csv', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@uploadShareholderByCSV')->name('company-overseas-annual-submit-bulk-shareholder-csv');
         Route::post('/company-overseas-annual-submit-bulk-ceased-shareholder-csv', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@uploadCeasedShareholderByCSV')->name('company-overseas-annual-submit-bulk-ceased-shareholder-csv');
         
         Route::post('/company-overseas-remove-existing-director', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@removeExistingDirector')->name('company-overseas-remove-existing-director');
         Route::post('/company-overseas-remove-change-director', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@removeChangeDirector')->name('company-overseas-remove-change-director');
         Route::post('/company-overseas-update-existing-director', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@updateExistingDirector')->name('company-overseas-update-existing-director');
         Route::post('/company-overseas-add-new-director', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@addNewDirector')->name('company-overseas-add-new-director');
         Route::post('/company-overseas-remove-existing-sec', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@removeExistingSec')->name('company-overseas-remove-existing-sec');
         Route::post('/company-overseas-update-existing-sec', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@updateExistingSec')->name('company-overseas-update-existing-sec');
         Route::post('/company-overseas-remove-change-sec', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@removeChangeSec')->name('company-overseas-remove-change-sec');
         Route::post('/company-overseas-update-existing-secfirm', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@updateExistingSecFirm')->name('company-overseas-update-existing-secfirm');
         Route::post('/company-overseas-add-new-sec', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@addNewSecretory')->name('company-overseas-add-new-sec');
         Route::post('/company-overseas-add-new-secfirm', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@addNewSecretoryFirm')->name('company-overseas-add-new-secfirm');
         Route::post('/company-overseas-update-othe-doc-date', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@updateOtherDocsChangeDate')->name('company-overseas-update-othe-doc-date');

         Route::post('/company-overseas-update-alter-type', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@updateAlterationType')->name('company-overseas-update-alter-type');
        
         Route::post('/company-overseas-upload-other-docs', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@uploadOtherDocs')->name('company-overseas-upload-other-docs');
         Route::post('/company-overseas-upload-other-resubmitted-docs', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@uploadOtherResubmittedDocs')->name('company-overseas-upload-other-resubmitted-docs');
         Route::post('/company-overseas-remove-other-docs', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@removeOtherDoc')->name('company-overseas-remove-other-docs');
         Route::post('/company-overseas-update-court-record', 'foreignCompanyAlterations\OverseasCompanyAlterationsController@updateCourtRecords')->name('company-overseas-update-court-record');
        
        
        
         // Overseas Company Alterations - Form 23
        Route::post('/company-offshore-annual-data', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@loadData')->name('company-offshore-annual-data');
         Route::post('/company-offshore-annual-heavy-data', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@loadHeavyData')->name('company-offshore-annual-heavy-data');
         Route::post('/company-offshore-annual-check-nic', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@checkNic');
         Route::post('/company-offshore-annual-submit-step1', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@submitStep1')->name('company-offshore-annual-submit-step1');
         Route::post('/company-offshore-annual-submit-directors', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@submitDirectors')->name('company-offshore-annual-submit-directors');
         Route::post('/company-offshore-annual-submit-secretories', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@submitSecretories')->name('company-offshore-annual-submit-secretories');
         Route::post('/company-offshore-annual-submit-shareholders', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@submitShareolders')->name('company-offshore-annual-submit-shareholders');
         Route::post('/company-offshore-annual-submit-share-register', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@submitShareReisterRecords')->name('company-offshore-annual-submit-share-register');
         Route::post('/company-offshore-annual-submit-annual-records', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@submitAnnualRecords')->name('company-offshore-annual-submit-annual-records');
         Route::post('/company-offshore-annual-submit-annual-auditor-records', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@submitAnnualAuditorRecords')->name('company-offshore-annual-submit-annual-auditor-records');
         Route::post('/company-offshore-offshore-annual-submit-annual-charges-records', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@submitAnnualCharges')->name('company-offshore-annual-submit-annual-charges-records');
         Route::post('/company-offshore-annual-submit-share-records', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@submitShareRecords')->name('company-offshore-annual-submit-share-records');
         Route::post('/company-offshore-annual-upload-docs', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@upload')->name('company-offshore-annual-upload-docs');
         Route::post('/company-offshore-annual-remove-docs', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@removeDoc')->name('company-offshore-annual-remove-docs'); 
         Route::post('/company-offshore-annual-resubmit', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@resubmit')->name('company-offshore-annual-resubmit');
         Route::post('/company-offshore-annual-submit-bulk-shareholder-csv', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@uploadShareholderByCSV')->name('company-offshore-annual-submit-bulk-shareholder-csv');
         Route::post('/company-offshore-annual-submit-bulk-ceased-shareholder-csv', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@uploadCeasedShareholderByCSV')->name('company-offshore-annual-submit-bulk-ceased-shareholder-csv');
 
         Route::post('/company-offshore-remove-existing-director', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@removeExistingDirector')->name('company-offshore-remove-existing-director');
         Route::post('/company-offshore-remove-change-director', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@removeChangeDirector')->name('company-offshore-remove-change-director');
         Route::post('/company-offshore-update-existing-director', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@updateExistingDirector')->name('company-offshore-update-existing-director');
         Route::post('/company-offshore-add-new-director', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@addNewDirector')->name('company-offshore-add-new-director');
         Route::post('/company-offshore-remove-existing-sec', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@removeExistingSec')->name('company-offshore-remove-existing-sec');
         Route::post('/company-offshore-update-existing-sec', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@updateExistingSec')->name('company-offshore-update-existing-sec');
         Route::post('/company-offshore-remove-change-sec', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@removeChangeSec')->name('company-offshore-remove-change-sec');
         Route::post('/company-offshore-update-existing-secfirm', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@updateExistingSecFirm')->name('company-offshore-update-existing-secfirm');
         Route::post('/company-offshore-add-new-sec', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@addNewSecretory')->name('company-offshore-add-new-sec');
         Route::post('/company-offshore-add-new-secfirm', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@addNewSecretoryFirm')->name('company-offshore-add-new-secfirm');
         Route::post('/company-offshore-update-othe-doc-date', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@updateOtherDocsChangeDate')->name('company-offshore-update-othe-doc-date');
        
         Route::post('/company-offshore-update-alter-type', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@updateAlterationType')->name('company-offshore-update-alter-type');

         Route::post('/company-offshore-upload-other-docs', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@uploadOtherDocs')->name('company-offshore-upload-other-docs');
         Route::post('/company-offshore-upload-other-resubmitted-docs', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@uploadOtherResubmittedDocs')->name('company-offshore-upload-other-resubmitted-docs');
         Route::post('/company-offshore-remove-other-docs', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@removeOtherDoc')->name('company-offshore-remove-other-docs');
         Route::post('/company-offshore-update-court-record', 'foreignCompanyAlterations\OffshoreCompanyAlterationsController@updateCourtRecords')->name('company-offshore-update-court-record');
        
        // Form 9
        Route::post('/company-form9-data', 'CallOnShares\Form9Controller@loadData')->name('company-form9-data');
        Route::post('/company-form9-submit', 'CallOnShares\Form9Controller@submitShareCallRecords')->name('company-form9-submit');
        Route::post('/company-form9-upload-docs', 'CallOnShares\Form9Controller@upload')->name('company-form9-upload-docs');
        Route::post('/company-form9-remove-docs', 'CallOnShares\Form9Controller@removeDoc')->name('company-form9-remove-docs');
        Route::post('/company-form9-resubmit', 'CallOnShares\Form9Controller@resubmit')->name('company-form9-resubmit');
        Route::post('/company-form9-upload-other-docs', 'CallOnShares\Form9Controller@uploadOtherDocs')->name('company-form9-upload-other-docs');
        Route::post('/company-form9-upload-other-resubmitted-docs', 'CallOnShares\Form9Controller@uploadOtherResubmittedDocs')->name('company-form9-upload-other-resubmitted-docs');
        Route::post('/company-form9-remove-other-docs', 'CallOnShares\Form9Controller@removeOtherDoc')->name('company-form9-remove-other-docs');
        
        //Prospectus of Registration
        Route::post('/prospectus-data', 'Workflows\ProspectusController@loadData')->name('prospectus-data');
        Route::post('/prospectus-submit', 'Workflows\ProspectusController@submitRecords')->name('prospectus-submit');
        Route::post('/prospectus-upload-docs', 'Workflows\ProspectusController@upload')->name('prospectus-upload-docs');
        Route::post('/prospectus-upload-other-docs', 'Workflows\ProspectusController@uploadOtherDocs')->name('prospectus-upload-other-docs');
        Route::post('/prospectus-upload-other-resubmitted-docs', 'Workflows\ProspectusController@uploadOtherResubmittedDocs')->name('prospectus-upload-other-resubmitted-docs');
        Route::post('/prospectus-remove-docs', 'Workflows\ProspectusController@removeDoc')->name('prospectus-remove-docs');
        Route::post('/prospectus-remove-other-docs', 'Workflows\ProspectusController@removeOtherDoc')->name('prospectus-remove-other-docs');
        Route::post('/prospectus-resubmit', 'Workflows\ProspectusController@resubmit')->name('prospectus-resubmit');

        //Annual Accounts
        Route::post('/annual-account-data', 'Workflows\AnnualAccountsController@loadData')->name('annual-account-data');
        Route::post('/annual-account-submit', 'Workflows\AnnualAccountsController@submitRecords')->name('annual-account-submit');
        Route::post('/annual-account-upload-docs', 'Workflows\AnnualAccountsController@upload')->name('annual-account-upload-docs');
        Route::post('/annual-account-upload-other-docs', 'Workflows\AnnualAccountsController@uploadOtherDocs')->name('annual-account-upload-other-docs');
        Route::post('/annual-account-upload-other-resubmitted-docs', 'Workflows\AnnualAccountsController@uploadOtherResubmittedDocs')->name('annual-account-upload-other-resubmitted-docs');
        Route::post('/annual-account-remove-docs', 'Workflows\AnnualAccountsController@removeDoc')->name('annual-account-remove-docs');
        Route::post('/annual-account-remove-other-docs', 'Workflows\AnnualAccountsController@removeOtherDoc')->name('annual-account-remove-other-docs');
        Route::post('/annual-account-resubmit', 'Workflows\AnnualAccountsController@resubmit')->name('annual-account-resubmit');
        Route::post('/annual-account-check-previous-record', 'Workflows\AnnualAccountsController@checkHasPreviousYearRecord')->name('annual-account-check-previous-record');
        Route::post('/annual-account-pay-for-private', 'Workflows\AnnualAccountsController@payForPrivateCompanies')->name('annual-account-pay-for-private');
        
         // Correspondence
         Route::post('/corr-data', 'Correspondence\CorrespondenceController@loadData')->name('corr-data');
         Route::post('/corr-submit', 'Correspondence\CorrespondenceController@submitRecords')->name('corr-submit');
         Route::post('/corr-upload-docs', 'Correspondence\CorrespondenceController@upload')->name('corr-upload-docs');
         Route::post('/corr-remove-docs', 'Correspondence\CorrespondenceController@removeDoc')->name('corr-remove-docs');
         Route::post('/corr-submitt-request', 'Correspondence\CorrespondenceController@submit')->name('corr-submitt-request');
         Route::post('/corr-resubmit', 'Correspondence\CorrespondenceController@resubmit')->name('corr-resubmit');
         Route::post('/corr-upload-other-docs', 'Correspondence\CorrespondenceController@uploadOtherDocs')->name('corr-upload-other-docs');
         Route::post('/corr-remove-other-docs', 'Correspondence\CorrespondenceController@removeOtherDoc')->name('corr-remove-other-docs');
         Route::post('/corr-get-companies', 'Correspondence\SearchCompaniesController@getCompnanies');

         Route::post('/corr-get-user-requests', 'Correspondence\SearchCompaniesController@getUserCorrespondenceRequests');
        /* ---------------------- Udara Madushan -------------------------*/

        /* ---------------------- Ravihansa -------------------------*/
        Route::post('/secretary-data-submit', 'Secretary\SecretaryController@saveSecretaryData')->name('secretary-data-submit');
        Route::post('/secretary-data-load', 'Secretary\SecretaryController@loadSecretaryData')->name('secretary-data-load');
        Route::post('/secretary-firm-data-submit', 'Secretary\SecretaryController@saveSecretaryFirmData')->name('secretary-firm-data-submit');
        Route::post('/secretary-firm-data-load', 'Secretary\SecretaryController@loadSecretaryFirmPartnerData')->name('secretary-firm-data-load');
        Route::post('/secretary-natural-upload-pdf', 'Secretary\SecretaryController@secretaryIndividualUploadPdf')->name('secretary-natural-upload-pdf');
        Route::post('/secretary-firm-upload-pdf', 'Secretary\SecretaryController@secretaryFirmUploadPdf')->name('secretary-firm-upload-pdf');
        Route::post('/secretary-delete-pdf', 'Secretary\SecretaryController@deleteSecretaryPdf')->name('secretary-delete-pdf');
        Route::post('/secretary-profile-load', 'Secretary\SecretaryController@loadRegisteredSecretaryData')->name('secretary-profile-load');
        Route::post('/secretary-pay', 'Secretary\SecretaryController@secretaryPay')->name('secretary-pay');
        Route::post('/secretary-file', 'Secretary\SecretaryController@secretaryFile')->name('secretary-file');
        Route::post('/secretary-is-reg', 'Secretary\SecretaryController@isPartnerRegSec')->name('secretary-is-reg');
        Route::post('/secretary-individual-pdf', 'Secretary\SecretaryController@generateSecretaryPDF')->name('secretary-individual-pdf');
        Route::post('/secretary-firm-pdf', 'Secretary\SecretaryController@generateSecretaryFirmPDF')->name('secretary-firm-pdf');
        Route::post('/secretary-firm-data-load-resubmit', 'Secretary\SecretaryController@getSecretaryFirmData')->name('secretary-firm-data-load-resubmit');
        Route::post('/secretary-firm-data-update', 'Secretary\SecretaryController@updateSecretaryFirmData')->name('secretary-firm-data-update');
        Route::post('/secretary-data-load-resubmit', 'Secretary\SecretaryController@getSecretaryData')->name('secretary-data-load-resubmit');
        Route::post('/secretary-data-update', 'Secretary\SecretaryController@updateSecretaryData')->name('secretary-data-update');
        Route::post('/secretary-doc-comments', 'Secretary\SecretaryController@secretaryFileLoad')->name('secretary-doc-comments');
        Route::post('/secretary-general-comments', 'Secretary\SecretaryController@getSecretaryComments')->name('secretary-general-comments');
        Route::post('/secretary-update-uploaded-pdf', 'Secretary\SecretaryController@secretaryUpdateUploadedPdf')->name('secretary-update-uploaded-pdf');
        Route::post('/secretary-delete-pdf-resubmited', 'Secretary\SecretaryController@deleteSecretaryPdfUpdate')->name('secretary-delete-pdf-resubmited');
        Route::post('/secretary-status-update-resubmit', 'Secretary\SecretaryController@updateSecretaryStatus')->name('secretary-status-update-resubmit');
        //Route::post('/secretary-firm-status-update-resubmit', 'Secretary\SecretaryController@updateSecretaryFirmStatus')->name('secretary-firm-status-update-resubmit');
        Route::post('/secretary-certificate-request', 'Secretary\SecretaryController@secretaryCertificateRequest')->name('secretary-certificate-request');
        Route::post('/load-secretary-certificate-request', 'Secretary\SecretaryController@loadPreSecretaryCertificateRequest')->name('load-secretary-certificate-request');
        /* ---------------------- Ravihansa -------------------------*/

        /* ---------------------- Ravihansa -------------------------*/
        Route::post('/auditor-data-submit', 'Auditor\AuditorController@saveAuditorData')->name('auditor-data-submit');
        Route::post('/auditor-data-load-sl', 'Auditor\AuditorController@loadAuditorDataSL')->name('auditor-data-load-sl');
        Route::get('/auditor-data-load-ind-card', 'Auditor\AuditorController@loadAuditorDataIndCard')->name('auditor-data-load-ind-card');
        Route::get('/auditor-data-load-firm-card', 'Auditor\AuditorController@loadAuditorDataFirmCard')->name('auditor-data-load-firm-card');
        Route::post('/auditor-data-load-nonsl', 'Auditor\AuditorController@loadAuditorDataNonSL')->name('auditor-data-load-nonsl');
        Route::post('/auditor-individual-pdf', 'Auditor\AuditorController@generateAuditorPDF')->name('auditor-individual-pdf');
        Route::post('/auditor-upload-pdf', 'Auditor\AuditorController@auditorUploadPdf')->name('auditor-upload-pdf');
        Route::post('/auditor-delete-pdf', 'Auditor\AuditorController@deleteAuditorPdf')->name('auditor-delete-pdf');
        Route::post('/auditor-pay', 'Auditor\AuditorController@auditorPay')->name('auditor-pay');
        Route::post('/auditor-profile-load', 'Auditor\AuditorController@loadRegisteredAuditorData')->name('auditor-profile-load');
        Route::post('/auditor-file', 'Auditor\AuditorController@auditorFile')->name('auditor-file');
        Route::post('/auditor-firm-data-submit', 'Auditor\AuditorController@saveAuditorFirmData')->name('auditor-firm-data-submit');
        Route::post('/auditor-firm-get-audid', 'Auditor\AuditorController@getAuditorFirmPartners')->name('auditor-firm-get-audid');
        Route::post('/auditor-firm-pdf', 'Auditor\AuditorController@generateAuditorFirmPDF')->name('auditor-firm-pdf');
        Route::post('/auditor-firm-data-load', 'Auditor\AuditorController@getAuditorFirmData')->name('auditor-firm-data-load');
        Route::post('/auditor-firm-data-update', 'Auditor\AuditorController@updateAuditorFirmData')->name('auditor-firm-data-update');
        Route::post('/auditor-data-load', 'Auditor\AuditorController@getAuditorData')->name('auditor-data-load');
        ////////////////////////////
        Route::post('/auditor-change-status-update', 'Auditor\AuditorController@updateAuditorChangeStatus')->name('auditor-change-status-update');
        Route::post('/auditor-changeupload-pdf', 'Auditor\AuditorController@auditorChangeUploadPdf')->name('auditor-changeupload-pdf');
        Route::post('/auditor-changeupdateupload-pdf', 'Auditor\AuditorController@auditorChangeUpdateUploadedPdf')->name('auditor-changeupdateupload-pdf');
        Route::post('/auditor-changeupdateupload-delete-pdf', 'Auditor\AuditorController@deleteAuditorChangePdfUpdate')->name('auditor-changeupdateupload-delete-pdf');
        Route::post('/auditor-data-loadforchange', 'Auditor\AuditorController@getAuditorDataForChange')->name('auditor-data-loadforchange');
        Route::post('/auditor-changetype-submit', 'Auditor\AuditorController@AuditorChangeTypeSubmit')->name('auditor-changetype-submit');
        Route::post('/auditor-changedata-submit', 'Auditor\AuditorController@AuditorChangeDataSubmit')->name('auditor-changedata-submit');
        Route::post('/auditor-changefile', 'Auditor\AuditorController@auditorChangeFileLoad')->name('auditor-changefile');
        ////////////////////////////
        Route::post('/auditor-firm-data-loadforchange', 'Auditor\AuditorController@getAuditorFirmChangeData')->name('auditor-firm-data-loadforchange');
        Route::post('/auditor-firm-changetype-submit', 'Auditor\AuditorController@AuditorFirmChangeTypeSubmit')->name('auditor-firm-changetype-submit');
        Route::post('/auditor-firm-changedata-submit', 'Auditor\AuditorController@AuditorFirmChangeDataSubmit')->name('auditor-firm-changedata-submit');
        Route::post('/auditor-firm-changeupload-pdf', 'Auditor\AuditorController@auditorFirmChangeUploadPdf')->name('auditor-firm-changeupload-pdf');
        Route::post('/auditor-firm-changeupdateupload-pdf', 'Auditor\AuditorController@auditorChangeUpdateUploadedPdf')->name('auditor-firm-changeupdateupload-pdf');
        Route::post('/auditor-firm-changeupdateupload-delete-pdf', 'Auditor\AuditorController@deleteAuditorFirmChangePdfUpdate')->name('auditor-firm-changeupdateupload-delete-pdf');
        ////////////////////////////
        Route::post('/auditor-data-update', 'Auditor\AuditorController@updateAuditorData')->name('auditor-data-update');
        Route::post('/auditor-doc-comments', 'Auditor\AuditorController@auditorFileLoad')->name('auditor-doc-comments');
        Route::post('/auditor-general-comments', 'Auditor\AuditorController@getAuditorComments')->name('auditor-general-comments');
        Route::post('/auditor-update-uploaded-pdf', 'Auditor\AuditorController@auditorUpdateUploadedPdf')->name('auditor-update-uploaded-pdf');
        Route::post('/auditor-delete-pdf-resubmited', 'Auditor\AuditorController@deleteAuditorPdfUpdate')->name('auditor-delete-pdf-resubmited');
        Route::post('/auditor-individual-renewal-pdf', 'Auditor\AuditorController@generateAuditorRenewalPDF')->name('auditor-individual-renewal-pdf');
        Route::post('/auditor-individual-renewal-isreg', 'Auditor\AuditorController@auditorIsReg')->name('auditor-individual-renewal-isreg');
        Route::post('/auditor-firm-renewal-pdf', 'Auditor\AuditorController@generateAuditorFirmRenewalPDF')->name('auditor-firm-renewal-pdf');
        Route::post('/auditor-firm-renewal-isreg', 'Auditor\AuditorController@auditorFirmIsReg')->name('auditor-firm-renewal-isreg');
        Route::post('/auditor-status-update-resubmit', 'Auditor\AuditorController@updateAuditorStatus')->name('auditor-status-update-resubmit');
        Route::post('/auditorrenewal-status-update-resubmit', 'Auditor\AuditorController@updateAuditorRenewalStatus')->name('auditorrenewal-status-update-resubmit');
        /* ---------------------- Ravihansa -------------------------*/

        /* ---------------------- Ravihansa -------------------------*/
        Route::post('/company-member-data-load', 'MemberChange\DirectorSecretaryController@loadMemberData')->name('company-member-data-load');
        Route::post('/company-member-changed-data-submit', 'MemberChange\DirectorSecretaryController@saveMemberData')->name('company-member-changed-data-submit');
        Route::post('/company-member-changed-data-edit', 'MemberChange\DirectorSecretaryController@editMemberData')->name('company-member-changed-data-edit');
        // Route::post('/company-member-changed-data-checkregno', 'MemberChange\DirectorSecretaryController@checkRegno')->name('company-member-changed-data-checkregno');
        Route::post('/company-member-changed-data-revert', 'MemberChange\DirectorSecretaryController@revertMemberData')->name('company-member-changed-data-revert');
        Route::post('/company-member-changed-data-inputsignby', 'MemberChange\DirectorSecretaryController@inputSignby')->name('company-member-changed-data-inputsignby');
        Route::post('/company-member-changed-data-courtdatasubmit', 'MemberChange\DirectorSecretaryController@saveCourtData')->name('company-member-changed-data-courtdatasubmit');
        Route::post('/company-member-change-pdf', 'MemberChange\DirectorSecretaryController@generateMemberPDF')->name('company-member-change-pdf');
        Route::post('/company-member-change-remove', 'MemberChange\DirectorSecretaryController@removeJustAddedMember')->name('company-member-change-remove');
        Route::post('/company-member-upload-pdf', 'MemberChange\DirectorSecretaryController@memberUploadPdf')->name('company-member-upload-pdf');
        Route::post('/company-member-upload-pdf-update', 'MemberChange\DirectorSecretaryController@memberUploadUpdatePdf')->name('company-member-upload-pdf-update');
        Route::post('/company-member-delete-pdf', 'MemberChange\DirectorSecretaryController@deleteMemberPdf')->name('company-member-delete-pdf');
        Route::post('/company-member-load-pdf', 'MemberChange\DirectorSecretaryController@memberFileLoad')->name('company-member-load-pdf');
        Route::post('/company-member-data-resubmit', 'MemberChange\DirectorSecretaryController@resubmit')->name('company-member-data-resubmit');
        
        /* ---------------------- Ravihansa -------------------------*/

        /* ---------------------- sahani-------------------------*/
        Route::post('eroc/name/search/society', 'Search\SEOController@showNameSociety')->name('search');
        /* ---------------------- thilan -------------------------*/
        Route::post('/society-data-submit', 'Society\SocietyController@saveSocietyData')->name('society-data-submit');
        Route::post('/society-profile-load', 'Society\SocietyController@loadRegisteredSocietyData')->name('society-profile-load');
        Route::post('/society-pay', 'Society\SocietyController@societyPay')->name('society-pay');
        Route::post('/society-upload-pdf', 'Society\SocietyController@societyUploadPdf')->name('society-upload-pdf');
        Route::post('/society-member-data-load', 'Society\SocietyController@societyMemberLoad')->name('society-member-data-load');
        Route::post('/society-file', 'Society\SocietyController@societyFile')->name('society-file');
        Route::post('/society-data-submit-update', 'Society\SocietyController@updateSocietyData')->name('society-data-submit-update');
        Route::post('/society-data-resubmit', 'Society\SocietyController@resubmitSociety')->name('society-data-resubmit');
        Route::post('/society-getpath', 'Society\SocietyController@getPathConstitution')->name('society-getpath');
        Route::post('/society-member-datawithaddress-load', 'Society\SocietyController@societyMemberLoadWithAddress')->name('society-member-datawithaddress-load');
        Route::post('/society-file-comment', 'Society\SocietyController@societyFileComment')->name('society-file-comment');
        Route::post('/society-data-load', 'Society\SocietyController@loadSocietyData')->name('society-data-load');
        Route::post('/society-comments-load', 'Society\SocietyController@loadSocietyComments')->name('society-comments-load');
        Route::post('/society-deleteupdate-pdf', 'Society\SocietyController@deleteSocietyPdfUpdate')->name('society-deleteupdate-pdf');
        Route::post('/society-update-upload-pdf', 'Society\SocietyController@societyUpdateUploadPdf')->name('society-update-upload-pdf');

        Route::post('/company-address-load', 'AddressChange\AddressChangeController@loadCompanyAddress')->name('company-address-load');
        Route::post('/company-newaddress-submit', 'AddressChange\AddressChangeController@submitNewCompanyAddress')->name('company-newaddress-submit');
        Route::post('/company-addresschangeapplication-document', 'AddressChange\AddressChangeController@generate_App_pdf')->name('company-addresschangeapplication-document');
        Route::post('/company-addresschangeupload-pdf', 'AddressChange\AddressChangeController@addresschangeUploadPdf')->name('company-addresschangeupload-pdf');
        Route::post('/company-addresschange-file', 'AddressChange\AddressChangeController@addresschangeFile')->name('company-addresschange-file');
        Route::post('/company-addresschange-delete-pdf', 'AddressChange\AddressChangeController@deleteAddresschangePdf')->name('company-addresschange-delete-pdf');
        Route::post('/company-newaddress-resubmit', 'AddressChange\AddressChangeController@resubmitNewCompanyAddress')->name('company-newaddress-resubmit');
        Route::post('/company-addresschange-update-upload-pdf', 'AddressChange\AddressChangeController@addresschangeUpdateUploadPdf')->name('company-addresschange-update-upload-pdf');
        Route::post('/company-addresschange-deleteupdate-pdf', 'AddressChange\AddressChangeController@deleteAddresschangePdfUpdate')->name('company-addresschange-deleteupdate-pdf');
        Route::post('/company-addresschange-data-resubmit', 'AddressChange\AddressChangeController@resubmitAddresschange')->name('company-addresschange-data-resubmit');

        Route::post('/company-accounting-address-load', 'AccountingAddressChange\AccountingAddressChangeController@loadCompanyAddress')->name('company-accounting-address-load');
        Route::post('/company-accounting-address-submit', 'AccountingAddressChange\AccountingAddressChangeController@saveData')->name('company-accounting-address-submit');
        Route::post('/company-accounting-address-courtdata-submit', 'AccountingAddressChange\AccountingAddressChangeController@saveCourtData')->name('company-accounting-address-courtdata-submit');
        Route::post('/company-accounting-address-view-document', 'AccountingAddressChange\AccountingAddressChangeController@generate_pdf')->name('company-accounting-address-view-document');
        Route::post('/company-accountingaddresschangeupload-pdf', 'AccountingAddressChange\AccountingAddressChangeController@acaddresschangeUploadPdf')->name('company-accountingaddresschangeupload-pdf');
        Route::post('/company-accountingaddresschange-file', 'AccountingAddressChange\AccountingAddressChangeController@accountingaddresschangeFile')->name('company-accountingaddresschange-file');
        Route::post('/company-accountingaddresschange-delete-pdf', 'AccountingAddressChange\AccountingAddressChangeController@deleteAcAddresschangePdf')->name('company-accountingaddresschange-delete-pdf');
        Route::post('/company-accounting-address-update', 'AccountingAddressChange\AccountingAddressChangeController@updateData')->name('company-accounting-address-update');
        Route::post('/company-accountingaddresschange-update-upload-pdf', 'AccountingAddressChange\AccountingAddressChangeController@accountingaddresschangeUpdateUploadPdf')->name('company-accountingaddresschange-update-upload-pdf');
        Route::post('/company-accountingaddresschange-deleteupdate-pdf', 'AccountingAddressChange\AccountingAddressChangeController@deleteAcAddresschangePdfUpdate')->name('company-accountingaddresschange-deleteupdate-pdf');
        Route::post('/company-accountingaddresschange-data-resubmit', 'AccountingAddressChange\AccountingAddressChangeController@resubmitAcAddresschange')->name('company-accountingaddresschange-data-resubmit');

        Route::post('/company-bsd-dataload', 'BalanceSheetDateChange\BalanceSheetDateChangeController@loadCompanybsdData')->name('company-bsd-dataload');
        Route::post('/company-bsd-submit', 'BalanceSheetDateChange\BalanceSheetDateChangeController@saveData')->name('company-bsd-submit');
        Route::post('/company-bsd-view-document', 'BalanceSheetDateChange\BalanceSheetDateChangeController@generate_pdf')->name('company-bsd-view-document');
        Route::post('/company-bsdupload-pdf', 'BalanceSheetDateChange\BalanceSheetDateChangeController@bsdUploadPdf')->name('company-bsdupload-pdf');
        Route::post('/company-bsd-file', 'BalanceSheetDateChange\BalanceSheetDateChangeController@bsdFile')->name('company-bsd-file');
        Route::post('/company-bsd-delete-pdf', 'BalanceSheetDateChange\BalanceSheetDateChangeController@deletebsdPdf')->name('company-bsd-delete-pdf');
        Route::post('/company-bsd-Resubmit', 'BalanceSheetDateChange\BalanceSheetDateChangeController@saveDataResubmit')->name('company-bsd-Resubmit');
        Route::post('/company-bsd-update-upload-pdf', 'BalanceSheetDateChange\BalanceSheetDateChangeController@bsdUpdateUploadPdf')->name('company-bsd-update-upload-pdf');
        Route::post('/company-bsd-deleteupdate-pdf', 'BalanceSheetDateChange\BalanceSheetDateChangeController@deletebsdPdfUpdate')->name('company-bsd-deleteupdate-pdf');
        Route::post('/company-bsd-data-resubmit', 'BalanceSheetDateChange\BalanceSheetDateChangeController@resubmitbsd')->name('company-bsd-data-resubmit');

        Route::post('/company-rr-address-load', 'RecordsRegisters\RecordsRegistersController@loadCompanyAddress')->name('company-rr-address-load');
        Route::post('/company-rr-address-submit', 'RecordsRegisters\RecordsRegistersController@saveData')->name('company-rr-address-submit');
        Route::post('/company-rr-address-delete', 'RecordsRegisters\RecordsRegistersController@deleteData')->name('company-rr-address-delete');
        Route::post('/company-rr-address-revert', 'RecordsRegisters\RecordsRegistersController@revertData')->name('company-rr-address-revert');
        Route::post('/company-rr-address-change', 'RecordsRegisters\RecordsRegistersController@changeData')->name('company-rr-address-change');
        Route::post('/company-rr-address-court-submit', 'RecordsRegisters\RecordsRegistersController@saveCourtData')->name('company-rr-address-court-submit');
        Route::post('/company-rr-view-document', 'RecordsRegisters\RecordsRegistersController@generate_pdf')->name('company-rr-view-document');
        Route::post('/company-rrupload-pdf', 'RecordsRegisters\RecordsRegistersController@rrUploadPdf')->name('company-rrupload-pdf');
        Route::post('/company-rr-file', 'RecordsRegisters\RecordsRegistersController@rrFile')->name('company-rr-file');
        Route::post('/company-rr-delete-pdf', 'RecordsRegisters\RecordsRegistersController@deleterrPdf')->name('company-rr-delete-pdf');
        Route::post('/company-rr-update-upload-pdf', 'RecordsRegisters\RecordsRegistersController@rrUpdateUploadPdf')->name('company-rr-update-upload-pdf');
        Route::post('/company-rr-deleteupdate-pdf', 'RecordsRegisters\RecordsRegistersController@deleterrPdfUpdate')->name('company-rr-deleteupdate-pdf');
        Route::post('/company-rr-data-resubmit', 'RecordsRegisters\RecordsRegistersController@resubmitrr')->name('company-rr-data-resubmit');

        Route::post('/company-sc-dataload', 'SatisCharge\SatisChargeController@loadCompanyScData')->name('company-sc-dataload');
        Route::post('/company-sc-submit', 'SatisCharge\SatisChargeController@saveData')->name('company-sc-submit');
        Route::post('/company-sc-view-document', 'SatisCharge\SatisChargeController@generate_pdf')->name('company-sc-view-document');
        Route::post('/company-scupload-pdf', 'SatisCharge\SatisChargeController@scUploadPdf')->name('company-scupload-pdf');
        Route::post('/company-sc-file', 'SatisCharge\SatisChargeController@scFile')->name('company-sc-file');
        Route::post('/company-sc-delete-pdf', 'SatisCharge\SatisChargeController@deletescPdf')->name('company-sc-delete-pdf');
        Route::post('/company-sc-update-upload-pdf', 'SatisCharge\SatisChargeController@scUpdateUploadPdf')->name('company-sc-update-upload-pdf');
        Route::post('/company-sc-deleteupdate-pdf', 'SatisCharge\SatisChargeController@deletescPdfUpdate')->name('company-sc-deleteupdate-pdf');
        Route::post('/company-sc-data-resubmit', 'SatisCharge\SatisChargeController@resubmitsc')->name('company-sc-data-resubmit');

        /* ---------------------- heshan-------------------------*/
        Route::post('/society-view-document', 'Society\SocietyController@generate_pdf')->name('society-view-document');
        Route::post('/society-application-document', 'Society\SocietyController@generate_App_pdf')->name('society-application-document');
        Route::post('/society-delete-pdf', 'Society\SocietyController@deleteSocietyPdf')->name('society-delete-pdf');

        /* ---------------------- Issue of shares-------------------------*/
        Route::post('/company-name-load', 'IssueofShares\IssueofShareController@loadCompanyName')->name('company-name-load');
        Route::post('/processing-list-load', 'IssueofShares\IssueofShareController@loadProcessingList')->name('processing-list-load');
        Route::post('/company-shares-submit', 'IssueofShares\IssueofShareController@submitCompanyShares')->name('company-shares-submit');
        Route::get('/company-shares-type', 'IssueofShares\IssueofShareController@getSharesTypes')->name('company-shares-type');
        Route::get('/load-two-csvs', 'IssueofShares\IssueofShareController@getTwoCSVs')->name('load-two-csvs');
        Route::post('/bulk-shareholder-csv', 'IssueofShares\IssueofShareController@uploadShareholderByCSV')->name('bulk-shareholder-csv');
        Route::post('/issue-of-share-form-download', 'IssueofShares\IssueofShareController@generate_App_pdf')->name('issue-of-share-form-download');
        Route::post('/current-shareholders-details-form-download', 'IssueofShares\IssueofShareController@generateCurrentShareholdersDetailspdf')->name('current-shareholders-details-form-download');
        Route::post('/company-issueofshare-upload-pdf', 'IssueofShares\IssueofShareController@issueofsharesUploadPdf')->name('company-issueofshare-upload-pdf');
        Route::post('/company-issueofshares-delete-pdf', 'IssueofShares\IssueofShareController@deleteIssueofSharesPdf')->name('company-issueofshares-delete-pdf');
        Route::post('/company-issueofshares-file', 'IssueofShares\IssueofShareController@issueofsharesFile')->name('company-issueofshares-file');
        Route::post('/company-excell-data-load', 'IssueofShares\IssueofShareController@excellDataLoad')->name('company-excell-data-load');
        Route::post('/company-shareholders-record-reset', 'IssueofShares\IssueofShareController@resetShareholdersExcellData')->name('company-shareholders-record-reset');
        Route::post('/company-shares-resubmit', 'IssueofShares\IssueofShareController@resubmitCompanyShares')->name('company-shares-resubmit');
        Route::post('/company-issueofshares-delete-updated-pdf', 'IssueofShares\IssueofShareController@deleteUpdatedIssueofSharesPdf')->name('company-issueofshares-delete-updated-pdf'); 
        Route::post('/company-issueofshare-upload-updated-pdf', 'IssueofShares\IssueofShareController@issueofsharesUploadUpdatedPdf')->name('company-issueofshare-upload-updated-pdf');
        Route::post('/company-issueofshare-confirm-resubmit', 'IssueofShares\IssueofShareController@issueofsharesReSubmit')->name('company-issueofshare-confirm-resubmit');

        /* ---------------------- Issue of debentures-------------------------*/
        Route::post('/pre-approved-record-load', 'Debentures\IssueofDebenturesController@loadPreApproved')->name('pre-approved-record-load');
        Route::post('/procesing-list-load', 'Debentures\IssueofDebenturesController@loadProcesingList')->name('procesing-list-load');
        Route::post('/company-debentures-submit', 'Debentures\IssueofDebenturesController@submitCompanyDebentures')->name('company-debentures-submit');
        Route::post('/issue-of-Debentures-form-download', 'Debentures\IssueofDebenturesController@generate_App_pdf')->name('issue-of-Debentures-form-download');
        Route::post('/company-issueofdebenture-upload-pdf', 'Debentures\IssueofDebenturesController@issueofDebentureUploadPdf')->name('company-issueofdebenture-upload-pdf');
        Route::post('/company-issueofdebentures-delete-pdf', 'Debentures\IssueofDebenturesController@deleteIssueofDebenturesPdf')->name('company-issueofdebentures-delete-pdf');
        Route::post('/company-issueofdebentures-file', 'Debentures\IssueofDebenturesController@issueofdebenturesFile')->name('company-issueofdebentures-file');
        Route::post('/company-issueofdebentures-delete-updated-pdf', 'Debentures\IssueofDebenturesController@deleteUpdatedIssueofDebenturesPdf')->name('company-issueofdebentures-delete-updated-pdf'); 
        Route::post('/company-issueofdebentures-upload-updated-pdf', 'Debentures\IssueofDebenturesController@issueofdebenturesUploadUpdatedPdf')->name('company-issueofdebentures-upload-updated-pdf');
        Route::post('/company-issueofdebentures-confirm-resubmit', 'Debentures\IssueofDebenturesController@issueofdebenturesReSubmit')->name('company-issueofdebentures-confirm-resubmit');

        /* ---------------------- Other Court Order-------------------------*/
        Route::post('/other-court-order', 'OtherCourtOrder\otherCourtOrderController@loadData')->name('other-court-order');
        Route::post('/others-court-upload-docs', 'OtherCourtOrder\otherCourtOrderController@upload')->name('others-court-upload-docs');
        Route::post('/other-court-order-remove-docs', 'OtherCourtOrder\otherCourtOrderController@removeDoc')->name('other-court-order-remove-docs');
        Route::post('/other-court-order-upload-other-docs', 'OtherCourtOrder\otherCourtOrderController@uploadOtherDocs')->name('other-court-order-upload-other-docs');
        Route::post('/other-court-order-remove-other-docs', 'OtherCourtOrder\otherCourtOrderController@removeOtherDoc')->name('other-court-order-remove-other-docs');
        Route::post('/other-court-order-resubmit', 'OtherCourtOrder\otherCourtOrderController@resubmit')->name('other-court-order-resubmit');
        Route::post('/other-court-order-resubmitted-other-docs', 'OtherCourtOrder\otherCourtOrderController@uploadOtherResubmittedDocs')->name('other-court-order-resubmitted-other-docs');
        Route::post('/other-court-order-resubmitted-docs', 'OtherCourtOrder\otherCourtOrderController@uploadResubmittedDocs')->name('other-court-order-resubmitted-docs');
        Route::post('/other-court-order-list', 'OtherCourtOrder\otherCourtOrderController@getOthersCourtOrderList')->name('other-court-order-list');
        Route::post('/court-order-get-company', 'OtherCourtOrder\otherCourtOrderController@getCompnanies')->name('court-order-get-company');
        Route::post('/remove-court-order', 'OtherCourtOrder\otherCourtOrderController@removeList')->name('remove-court-order');
        
        

         /* ---------------------- Prior Approval-------------------------*/
        Route::post('/prior-approval', 'PriorApproval\PriorApprovalController@loadData')->name('prior-approval');
        Route::post('/prior-approval-resubmit', 'PriorApproval\PriorApprovalController@resubmit')->name('prior-approval-resubmit');
        Route::post('/prior-submit', 'PriorApproval\PriorApprovalController@submitRecords')->name('prior-submit');
        Route::post('/prior-approval-upload-other-docs', 'PriorApproval\PriorApprovalController@uploadOtherDocs')->name('prior-approval-upload-other-docs');
        Route::post('/prior-approval-remove-other-docs', 'PriorApproval\PriorApprovalController@removeOtherDoc')->name('prior-approval-remove-other-docs');
        Route::post('/prior-approval-submit', 'PriorApproval\PriorApprovalController@submit')->name('prior-approval-submit');
        Route::post('/list-prior-approval', 'PriorApproval\PriorApprovalController@getListPriorApproval')->name('list-prior-approval');
        Route::post('/remove-prior-list', 'PriorApproval\PriorApprovalController@removeList')->name('remove-prior-list');
        Route::post('/prior-approval-resubmitted-docs', 'PriorApproval\PriorApprovalController@uploadOtherResubmittedDocs')->name('prior-approval-resubmitted-docs');
        Route::post('/prior-approval-uplode', 'PriorApproval\PriorApprovalController@upload')->name('prior-approval-uplode');

        /* ---------------------- Statement Of Affairs-------------------------*/
        Route::post('/statement-of-affairs', 'StatementOfAffairs\StatementOfAffairsController@loadData')->name('statement-of-affairs');
        Route::post('/statement-of-affairs-uplode', 'StatementOfAffairs\StatementOfAffairsController@upload')->name('statement-of-affairs-uplode');
        Route::post('/statement-of-affairs-document', 'StatementOfAffairs\StatementOfAffairsController@generate_App_pdf')->name('statement-of-affairs-document');
        Route::post('/statement-of-affairs-remove-docs', 'StatementOfAffairs\StatementOfAffairsController@removeDoc')->name('statement-of-affairs-remove-docs');
        Route::post('/statement-of-affairs-upload-other-docs', 'StatementOfAffairs\StatementOfAffairsController@uploadOtherDocs')->name('statement-of-affairs-upload-other-docs');
        Route::post('/statement-of-affairs-remove-other-docs', 'StatementOfAffairs\StatementOfAffairsController@removeOtherDoc')->name('statement-of-affairs-remove-other-docs');
        Route::post('/statement-of-affairs-resubmit', 'StatementOfAffairs\StatementOfAffairsController@resubmit')->name('statement-of-affairs-resubmit');
        Route::post('/statement-of-affairs-resubmitted-docs', 'StatementOfAffairs\StatementOfAffairsController@uploadOtherResubmittedDocs')->name('statement-of-affairs-resubmitted-docs');
        

        //secretory changes - Individual
        Route::post('/secretory-changes-data', 'Secretary\Changes\SecretaryChangesController@loadData');
        Route::post('/secretory-changes-heavy-data', 'Secretary\Changes\SecretaryChangesController@loadHeavyData');
        Route::post('/secretory-update-altertypes', 'Secretary\Changes\SecretaryChangesController@updateAlterationTypes');
        Route::post('/secretory-update-name', 'Secretary\Changes\SecretaryChangesController@updateNameSection');
        Route::post('/secretory-update-address', 'Secretary\Changes\SecretaryChangesController@updateAddressSection');
        Route::post('/secretory-update-email', 'Secretary\Changes\SecretaryChangesController@updateEmailSection');
        Route::post('/secretory-update-contact', 'Secretary\Changes\SecretaryChangesController@updateContactSection');
        Route::post('/secretory-upload-other-docs', 'Secretary\Changes\SecretaryChangesController@uploadOtherDocs');
        Route::post('/secretory-upload-other-resubmitted-docs', 'Secretary\Changes\SecretaryChangesController@uploadOtherResubmittedDocs');
        Route::post('/secretory-remove-other-docs', 'Secretary\Changes\SecretaryChangesController@removeOtherDoc');
        Route::post('/secretory-submit', 'Secretary\Changes\SecretaryChangesController@submit');
        Route::post('/secretory-resubmit', 'Secretary\Changes\SecretaryChangesController@resubmit');

        //secretory changes - Firm
        Route::post('/secretory-firm-changes-data', 'Secretary\Changes\SecretaryFirmChangesController@loadData');
        Route::post('/secretory-firm-changes-heavy-data', 'Secretary\Changes\SecretaryFirmChangesController@loadHeavyData');
        Route::post('/secretory-firm-update-altertypes', 'Secretary\Changes\SecretaryFirmChangesController@updateAlterationTypes');
        Route::post('/secretory-firm-update-name', 'Secretary\Changes\SecretaryFirmChangesController@updateNameSection');
        Route::post('/secretory-firm-update-address', 'Secretary\Changes\SecretaryFirmChangesController@updateAddressSection');
        Route::post('/secretory-firm-update-email', 'Secretary\Changes\SecretaryFirmChangesController@updateEmailSection');
        Route::post('/secretory-firm-update-contact', 'Secretary\Changes\SecretaryFirmChangesController@updateContactSection');
        Route::post('/secretory-firm-upload-other-docs', 'Secretary\Changes\SecretaryFirmChangesController@uploadOtherDocs');
        Route::post('/secretory-firm-upload-other-resubmitted-docs', 'Secretary\Changes\SecretaryFirmChangesController@uploadOtherResubmittedDocs');
        Route::post('/secretory-firm-remove-other-docs', 'Secretary\Changes\SecretaryFirmChangesController@removeOtherDoc');
        Route::post('/secretory-firm-partner-add', 'Secretary\Changes\SecretaryFirmChangesController@addPartner');
        Route::post('/secretory-firm-partner-update', 'Secretary\Changes\SecretaryFirmChangesController@editPartner');
        Route::post('/secretory-firm-partner-remove', 'Secretary\Changes\SecretaryFirmChangesController@removePartner');
        Route::post('/secretory-firm-partner-check-nic-record', 'Secretary\Changes\SecretaryFirmChangesController@checkNic');
        Route::post('/secretory-firm-submit', 'Secretary\Changes\SecretaryFirmChangesController@submit');
        Route::post('/secretory-firm-resubmit', 'Secretary\Changes\SecretaryFirmChangesController@resubmit');
        
          /* ---------------------- Offshore Strike Off-------------------------*/
        Route::post('/offshore-strike-off', 'StrikeOff\StrikeOffController@loadData')->name('offshore-strike-off');
        Route::post('/offshore-strike-off-uplode', 'StrikeOff\StrikeOffController@upload')->name('offshore-strike-off-uplode');
        Route::post('/offshore-strike-off-document', 'StrikeOff\StrikeOffController@generate_App_pdf')->name('offshore-strike-off-document');
        Route::post('/offshore-strike-off-remove-docs', 'StrikeOff\StrikeOffController@removeDoc')->name('offshore-strike-off-remove-docs');
        Route::post('/offshore-strike-off-upload-other-docs', 'StrikeOff\StrikeOffController@uploadOtherDocs')->name('offshore-strike-off-upload-other-docs');
        Route::post('/offshore-strike-off-remove-other-docs', 'StrikeOff\StrikeOffController@removeOtherDoc')->name('offshore-strike-off-remove-other-docs');
        Route::post('/offshore-strike-off-resubmit', 'StrikeOff\StrikeOffController@resubmit')->name('offshore-strike-off-resubmit');
        Route::post('/offshore-strike-off-resubmitted-docs', 'StrikeOff\StrikeOffController@uploadOtherResubmittedDocs')->name('offshore-strike-off-resubmitted-docs');
        Route::post('/offshore-strike-off-uplode-data',  'StrikeOff\StrikeOffController@uplodeTable')->name('offshore-strike-off-uplode-data');


        /* ---------------------- Overseas Strike Off-------------------------*/
        Route::post('/overseas-strike-off', 'OverseasStrikeOff\OverseasStrikeOffController@loadData')->name('overseas-strike-off');
        Route::post('/overseas-strike-off-uplode', 'OverseasStrikeOff\OverseasStrikeOffController@upload')->name('overseas-strike-off-uplode');
        Route::post('/overseas-strike-off-document', 'OverseasStrikeOff\OverseasStrikeOffController@generate_App_pdf')->name('overseas-strike-off-document');
        Route::post('/overseas-strike-off-remove-docs', 'OverseasStrikeOff\OverseasStrikeOffController@removeDoc')->name('overseas-strike-off-remove-docs');
        Route::post('/overseas-strike-off-upload-other-docs', 'OverseasStrikeOff\OverseasStrikeOffController@uploadOtherDocs')->name('overseas-strike-off-upload-other-docs');
        Route::post('/overseas-strike-off-remove-other-docs', 'OverseasStrikeOff\OverseasStrikeOffController@removeOtherDoc')->name('overseas-strike-off-remove-other-docs');
        Route::post('/overseas-strike-off-resubmit', 'OverseasStrikeOff\OverseasStrikeOffController@resubmit')->name('overseas-strike-off-resubmit');
        Route::post('/overseas-strike-off-resubmitted-docs', 'OverseasStrikeOff\OverseasStrikeOffController@uploadOtherResubmittedDocs')->name('overseas-strike-off-resubmitted-docs');
        Route::post('/overseas-strike-off-uplode-data',  'OverseasStrikeOff\OverseasStrikeOffController@uplodeTable')->name('overseas-strike-off-uplode-data');

         /* ---------------------- Auditor Strike Off-------------------------*/
        Route::post('/auditor-strike-off', 'AuditorStrikeOff\AuditorStrikeOffController@loadData')->name('auditor-strike-off');
        Route::post('/auditor-strike-off-uplode', 'AuditorStrikeOff\AuditorStrikeOffController@upload')->name('auditor-strike-off-uplode');
        Route::post('/auditor-strike-off-document', 'AuditorStrikeOff\AuditorStrikeOffController@generate_App_pdf')->name('auditor-strike-off-document');
        Route::post('/auditor-strike-off-remove-docs', 'AuditorStrikeOff\AuditorStrikeOffController@removeDoc')->name('auditor-strike-off-remove-docs');
        Route::post('/auditor-strike-off-upload-other-docs', 'AuditorStrikeOff\AuditorStrikeOffController@uploadOtherDocs')->name('auditor-strike-off-upload-other-docs');
        Route::post('/auditor-strike-off-remove-other-docs', 'AuditorStrikeOff\AuditorStrikeOffController@removeOtherDoc')->name('auditor-strike-off-remove-other-docs');
        Route::post('/auditor-strike-off-resubmit', 'AuditorStrikeOff\AuditorStrikeOffController@resubmit')->name('auditor-strike-off-resubmit');
        Route::post('/auditor-strike-off-resubmitted-docs', 'AuditorStrikeOff\AuditorStrikeOffController@uploadOtherResubmittedDocs')->name('auditor-strike-off-resubmitted-docs');
        Route::post('/auditor-strike-off-submit',  'AuditorStrikeOff\AuditorStrikeOffController@submit')->name('auditor-strike-off-submit');
        Route::post('/auditor-strike-off-uplode-data',  'AuditorStrikeOff\AuditorStrikeOffController@dataUplode')->name('auditor-strike-off-uplode-data');

              /* ---------------------- Secretary Delisting -------------------------*/
        Route::post('/secretary-delisting', 'SecretaryDelisting\SecretaryDelistingController@loadData')->name('secretary-delisting');
        Route::post('/secretary-delisting-uplode', 'SecretaryDelisting\SecretaryDelistingController@upload')->name('secretary-delisting-uplode');
        Route::post('/secretary-delisting-document', 'SecretaryDelisting\SecretaryDelistingController@generate_App_pdf')->name('secretary-delisting-document');
        Route::post('/secretary-delisting-remove-docs', 'SecretaryDelisting\SecretaryDelistingController@removeDoc')->name('secretary-delisting-remove-docs');
        Route::post('/secretary-delisting-upload-other-docs', 'SecretaryDelisting\SecretaryDelistingController@uploadOtherDocs')->name('secretary-delisting-upload-other-docs');
        Route::post('/secretary-delisting-remove-other-docs', 'SecretaryDelisting\SecretaryDelistingController@removeOtherDoc')->name('secretary-delisting-remove-other-docs');
        Route::post('/secretary-delisting-resubmit', 'SecretaryDelisting\SecretaryDelistingController@resubmit')->name('secretary-delisting-resubmit');
        Route::post('/secretary-delisting-resubmitted-docs', 'SecretaryDelisting\SecretaryDelistingController@uploadOtherResubmittedDocs')->name('secretary-delisting-resubmitted-docs');
        Route::post('/secretary-delisting-submit',  'SecretaryDelisting\SecretaryDelistingController@submit')->name('secretary-delisting-submit');
        Route::post('/secretary-delisting-uplode-data',  'SecretaryDelisting\SecretaryDelistingController@dataUplode')->name('secretary-delisting-uplode-data');
        
       
        
    });
});


Route::get('/hash/{text}', 'Hash\HashController@hashText');
