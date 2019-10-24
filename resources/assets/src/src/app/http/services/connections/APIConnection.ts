import { environment } from 'src/environments/environment';

export class APIConnection {
  _getStatusCountUrl: string;
  _getgetPaymentURL: string;
  _getComsubData: string;
  _getReceivedUrl: string;
  _getauthRequestLinkURL: string;
  _getuploadURL: string;
  _getcheckEmail: string;
  _getdocAPI: string;
  _getauthUser: string;
  _apiUrl: string;
  _getauthLogin: string;
  _getauthRegister: string;
  _getMigrateUser: string;
  _getauthlogout: string;
  _getSEUrlPages: string;
  _getcTypeUrl: string;
  _getcReceUrl: string;
  _getauActivationUrl: string;
  _getauMigrateActivationUrl: string;

  _getGnDivisionAndCityUrl: string;
  _getCountryUrl: string;

  _checkCompanyForAdmin: string;

  _getCompanies: string;
  _getuploadFileURL: string;

  // Name Change
  _getChangeNameDataUrl: string;
  _getChangeNameFormUrl: string;
  _getChResubmitDataUrl: string;
  _geteuploadotherFileURL: string;
  _geteuploadotherForNameFileURL: string;
  _getOtherFilesList: string;
  _getOtherFilesListForName: string;
  _removeOtherDoc: string;
  _isResolutionDateExeed: string;
  _updateCourtOrderDetails: string;
  _isSetResDate: string;

  // Payment
  _getCrTokenURL: string;
  _getPyamentSuccessURL: string;

  _getValidTokenUrl: string;

  _getMemberTitleUrl: string;
  _getCountryDetailsUrl: string;

  _getNameReservationDataUrl: string;
  _getfileResubmitDataUrl: string;
  _getNameCancelUrl: string;
  _getResubmitDataUrl: string;
  _getForeignRequestForApprovelURL: string;
  _getCheckFoxDataUrl: string;
  _getDownloadUrl: string;
  _getDocNameUrl: string;
  _getAvaterUrl: string;
  _getCheckSamePasswordUrl: string;
  _getReSubmitFileDestroyUrl: string;
  _getCheckNameReSubmitUrl: string;
  _getUserUpdateURL: string;
  _getMemberUrl: string;

  _getPasswordResetLinkURL: string;
  _getNewPasswordResetURL: string;
  _getReduRowURL: string;
  _getResucValueURL: string;
  _getUpdateAndContinueURL: string;
  _getSettingURL: string;
  _setCapResubmitURL: string;


  /* ---------- Udara Madushan -----------*/

  _incorporationData: string;
  _incorporationDataStep1Submit: string;
  _incorporationDataStep2Submit: string;
  _incorporationCheckNic: string;
  _incorporationDeleteStakeholder: string;
  _incorporationFileUpload: string;
  _incorporationPay: string;
  _getFileDestroyUrl: string;


  // 2018-09-03 Updated
  _incorporationSecForDirDelete: string;
  _incorporationShForDirDelete: string;
  _incorporationShForSecDelete: string;
  _incorporationShFirmDelete: string;

  // 2018-09-06 Updated
  _getResubmitURL: string;

  // 2018-09-11 Updated
  _incorporationFileRemove: string;
  _incorporationShFirmForSecFirmDelete: string;
  _incorporationSecFirmDelete: string;

  // 2018-10-24 Updated
  _incorporationHeavyData: string;
  _certifiedCopyHeavyData: string;

  // 2018-12-05  Updated
  _incorporationSaveDocCopies: string;
  _incorporationSavePubDocCopies: string;
  _certifiedCopiesLoadData: string;

  // 2019-01-10
  _incorporationSaveIRDInfo: string;
  _irdDirectorNicUpload: string;
  _irdDirectorNicRemove: string;

  _incorporationSaveLabourInfo: string;

  // 2019-01-16
  _companyVerify: string;

  /* ---------- Udara Madushan -----------*/

  /* ---------- Udara Madushan -----------*/

  /* ---------- ravihansa 20180919-----------*/
  _secretaryData: string;
  _secretaryDataSubmit: string;
  _secretaryFirmDataSubmit: string;
  _secretaryFirmPartnerData: string;
  _secretaryNaturalUpload: string;
  _secretaryFirmUpload: string;
  _secretaryFileUploadedDelete: string;
  _secretaryProfileData: string;
  _secretaryPay: string;
  _secretaryFile: string;
  _secretaryIsReg: string;
  _secretaryPDF: string;
  _secretaryFirmPDF: string;
  _secretaryFirmDataResubmit: string;
  _secretaryFirmDataUpdate: string;
  _secretaryDataResubmit: string;
  _secretaryDataUpdate: string;
  _secretaryDocComments: string;
  _secretaryComments: string;
  _secretaryUpdateUploaded: string;
  _secretaryFileUploadedDeleteResubmited: string;
  _secretaryUpdateStatus: string;
  _secretaryCertificate: string;
  _loadsecretaryCertificate: string;
  // _secretaryFirmUpdateStatus: string;

  _audIndcardload: string;
  _audFirmcardload: string;
  _auditorDataSubmit: string;
  /////////////////////
  _auditorChangeStatusUpdate: string;
  _auditorChangeFileUploadedDeleteResubmited: string;
  _auditorChangeUpload: string;
  _auditorChangeUpdateUploaded: string;
  _auditorChangeTypeSubmit: string;
  _auditorChangeDataSubmit: string;
  _auditorChangeFile: string;
  ///////////////////
  _auditorFirmDataForChange: string;
  _auditorFirmChangeTypeSubmit: string;
  _auditorFirmChangeDataSubmit: string;
  _auditorFirmChangeUpload: string;
  _auditorFirmChangeUpdateUploaded: string;
  _auditorFirmChangeFileUploadedDeleteResubmited: string;
  ///////////////////
  _auditorDataSL: string;
  _auditorDataNonSL: string;
  _auditorPDF: string;
  _auditorUpload: string;
  _auditorFileUploadedDelete: string;
  _auditorPay: string;
  _auditorProfileData: string;
  _auditorFile: string;
  _auditorFirmDataSubmit: string;
  _auditorID: string;
  _auditorFirmPDF: string;
  _auditorFirmData: string;
  _auditorFirmDataUpdate: string;
  _auditorData: string;
  _auditorDataForChange: string;
  _auditorDataUpdate: string;
  _auditorDocComments: string;
  _auditorComments: string;
  _auditorUpdateUploaded: string;
  _auditorFileUploadedDeleteResubmited: string;
  _auditorRenewalPDF: string;
  _auditorIsReg: string;
  _auditorFirmRenewalPDF: string;
  _auditorFirmIsReg: string;
  _auditorUpdateStatus: string;
  _auditorRenewalReSubmit: string;

  _companyMemberData: string;
  _companyMemberDataSubmit: string;
  _companyMemberDataEdit: string;
  _companyMemberDataCheckRegno: string;
  _companyMemberDataRevert: string;
  _companyinputSignby: string;
  _companyform20CourtDataSubmit: string;
  _companyMemberPDF: string;
  _companyMemberRemove: string;
  _companyMemberUpload: string;
  _companyMemberUploadUpdate: string;
  _companyMemberFileUploadedDelete: string;
  _companyMemberFile: string;
  _resubmitMemberRecord: string;

  /* ---------- ravihansa -----------*/

  /* ---------- thilan 20181010-----------*/
  _societyDataSubmit: string;
  _societyProfileData: string;
  _societyPay: string;
  _societyUpload: string;
  _societyMemberData: string;
  _societyFile: string;
  _societyDataSubmitUpdate: string;
  _societyReSubmit: string;
  _getPathCon: string;
  _societyMemberDataAddress: string;
  _societyFileComment: string;
  _societyData: string;
  _societyComments: string;
  _societyUpdateUpload: string;
  _societyFileUploadedDeleteUpdate: string;

  /* ---------- thilan 20181116 address change-----------*/
  _companyAddress: string;
  _companyAddressSubmit: string;
  _companyAddressReSubmit: string;
  _addressChangeApplicantGetDownloadUrl: string;
  _addresschangeUpload: string;
  _addresschangeFile: string;
  _addresschangeFileUploadedDelete: string;
  _addresschangeUpdateUpload: string;
  _addresschangeFileUploadedDeleteUpdate: string;
  _addresschangeReSubmit: string;

  /* ---------- thilan 20190107 accounting address change-----------*/
  _companyAccountingAddress: string;
  _companyAccountingAddressDataSubmit: string;
  _companyAccountingAddressCourtDataSubmit: string;
  _accDocGetDownloadUrl: string;
  _accountingaddresschangeUpload: string;
  _accountingaddresschangeFile: string;
  _accountingaddresschangeFileUploadedDelete: string;
  _companyAccountingAddressDataUpdate: string;
  _accountingaddresschangeUpdateUpload: string;
  _accountingaddresschangeFileUploadedDeleteUpdate: string;
  _accountingaddresschangeReSubmit: string;

  /* ---------- thilan 20190107 bsd change-----------*/
  _companyBsdData: string;
  _companyBsdDataSubmit: string;
  _bsdDocGetDownloadUrl: string;
  _bsdUpload: string;
  _bsdFile: string;
  _bsdFileUploadedDelete: string;
  _companyBsdDataReSubmit: string;
  _bsdUpdateUpload: string;
  _bsdUploadedDeleteUpdate: string;
  _bsdReSubmit: string;

  /* ---------- thilan 20190107 rr address change-----------*/
  _companyRRAddress: string;
  _companyRRAddressDataSubmit: string;
  _companyRRAddressDelete: string;
  _companyRRAddressRevert: string;
  _companyRRAddressChange: string;
  _companyRRAddressCourtDataSubmit: string;
  _rrDocGetDownloadUrl: string;
  _rrUpload: string;
  _rrFile: string;
  _rrFileUploadedDelete: string;
  _rrUpdateUpload: string;
  _rrUploadedDeleteUpdate: string;
  _rrReSubmit: string;

  /* ---------- thilan 20190107 sc change-----------*/
  _companyScData: string;
  _companyScDataSubmit: string;
  _scDocGetDownloadUrl: string;
  _scUpload: string;
  _scFile: string;
  _scFileUploadedDelete: string;
  _scUpdateUpload: string;
  _scUploadedDeleteUpdate: string;
  _scReSubmit: string;

  /*---------------heshan------------------*/
  _societyGetDownloadUrl: string;
  _societyApplicantGetDownloadUrl: string;
  _societyFileUploadedDelete: string;

  /* ---------- Issue of shares-----------*/
  _companyName: string;
  _processingList: string;
  _sharesSubmit: string;
  _getShareTypeUrl: string;
  _twocsvs: string;
  _shareholderBulkUpload: string;
  _IssueofShareApplicantGetDownloadUrl: string;
  _currentShareholdersDetailsPDFGetDownloadUrl: string;
  _issueofShareUpload: string;
  _issueofSharesFileUploadedDelete: string;
  _issueofSharesFile: string;
  _excelDataLoad: string;
  _resetShareholdersRecord: string;
  _sharesReSubmit: string;
  _issueofSharesFileUploadedDeleteUpdate: string;
  _issueofShareUploadUpdate: string;
  _issueofsharesresubmit: string;

  /* ---------- Issue of debentures-----------*/
  _previousApproved: string;
  _procesinglist: string;
  _debenturesSubmit: string;
  _IssueofDebenturesApplicantGetDownloadUrl: string;
  _issueofDebentureUpload: string;
  _issueofDebenturesFileUploadedDelete: string;
  _issueofDebenturesFile: string;
  _issueofDebenturesFileUploadedDeleteUpdate: string;
  _issueofDebenturesUploadUpdate: string;
  _issueofdebenturesresubmit: string;

  /* ------------sahani------------*/
  _getSocietySEUrlPages: string;

  _getReduURL: string;
  _getUpdateReduURL: string;
  _getCapitalFormURL: string;

  _getReduPenalty: string;
  _geteuploadotherResubmitFileURL: string;

  constructor() {
    // this._apiUrl = `http://220.247.219.173/frontend/API/eRoc/public/`;
    // this._apiUrl = `http://220.247.219.173/frontend/beta/staging_dev/API/public`;
    this._apiUrl = environment.apiUrl;
    // this._apiUrl = 'http://220.247.219.173/frontend/beta/staging_v17/API/public';
    // this._apiUrl = `http://220.247.219.173/frontend/API/eRoc_v31/public/`;
    // this._apiUrl = `http://220.247.219.173/frontend/staging/eRoc/public/`;
    // this._apiUrl = `http://localhost/front-end/public/`;

    this._getauthLogin = `${this._apiUrl}/api/v1/eroc/login`;
    this._getauthRegister = `${this._apiUrl}/api/v1/eroc/register`;
    this._getMigrateUser = `${this._apiUrl}/api/v1/eroc/migrate/register`;
    this._getauthRequestLinkURL = `${this._apiUrl}/api/v1/eroc/request/link`;
    this._getauthlogout = `${this._apiUrl}/api/v1/eroc/logout`;
    this._getauthUser = `${this._apiUrl}/api/v1/eroc/users`;
    this._getSEUrlPages = `${this._apiUrl}/api/v1/eroc/name/search`;
    this._getcTypeUrl = `${this._apiUrl}/api/v1/eroc/company/type`;
    this._getcReceUrl = `${this._apiUrl}/api/v1/eroc/name/receive`;
    this._getauActivationUrl = `${this._apiUrl}/api/v1/eroc/user/verification`;
    this._getauMigrateActivationUrl = `${this._apiUrl}/api/v1/eroc/user/migrate/verification`;
    this._getdocAPI = `${this._apiUrl}/api/v1/eroc/document/feild`;
    this._getcheckEmail = `${this._apiUrl}/api/v1/eroc/user/exists`;
    this._getuploadURL = `${this._apiUrl}/api/v1/eroc/name/receive/files/upload`;
    this._getuploadFileURL = `${this._apiUrl}/api/v1/eroc/files/upload`;
    this._geteuploadotherFileURL = `${this._apiUrl}/api/v1/eroc/name/receive/files/upload_other`;
    this._geteuploadotherResubmitFileURL = `${this._apiUrl}/api/v1/eroc/name/receive/files/upload_resubmit_other`;
    this._geteuploadotherForNameFileURL = `${this._apiUrl}/api/v1/eroc/name/receive/files/upload_other_for_name`;
    this._getOtherFilesList = `${this._apiUrl}/api/v1/eroc/name/receive/files/get_other_uploaded_docs`;
    this._getOtherFilesListForName = `${this._apiUrl}/api/v1/eroc/name/receive/files/get_other_uploaded_docs_for_reservation`;
    this._updateCourtOrderDetails = `${this._apiUrl}/api/v1/eroc/set/change/name/update_court_order_details`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/eroc/name/receive/files/remoe_other_doc`;
    this._getReceivedUrl = `${this._apiUrl}/api/v1/eroc/name/received?page=`;
    this._getCheckFoxDataUrl = `${this._apiUrl}/api/v1/eroc/name/fix/has`;
    this._getAvaterUrl = `${this._apiUrl}/api/v1/eroc/avater`;
    this._getComsubData = `${this._apiUrl}/api/v1/eroc/company/sub/category?id=`;
    this._getgetPaymentURL = `${this._apiUrl}/api/v1/eroc/name/payment`;
    this._getStatusCountUrl = `${this._apiUrl}/api/v1/eroc/status/count`;
    this._getNameCancelUrl = `${this._apiUrl}/api/v1/eroc/name/cancel`;
    this._getMemberUrl = `${this._apiUrl}/api/v1/eroc/get/member`;

    this._getGnDivisionAndCityUrl = `${this._apiUrl}/api/v1/eroc/get/gnDivisionAndCity`;
    this._getCountryUrl = `${this._apiUrl}/api/v1/eroc/get/country`;

    this._checkCompanyForAdmin = `${this._apiUrl}/api/v1/eroc/admin-signup/check-company`;

    this._getReduRowURL = `${this._apiUrl}/api/v1/eroc/get/reduc/data`;
    this._getReduURL = `${this._apiUrl}/api/v1/eroc/set/reduc`;
    this._getUpdateReduURL = `${this._apiUrl}/api/v1/eroc/update/reduc`;
    this._getUpdateAndContinueURL = `${this._apiUrl}/api/v1/eroc/continue`;
    this._getCapitalFormURL = `${this._apiUrl}/api/v1/eroc/get/capital/form/fill`;
    this._getSettingURL = `${this._apiUrl}/api/v1/eroc/get/setting`;
    this._setCapResubmitURL = `${this._apiUrl}/api/v1/eroc/set/Resubmit`;
    this._getReduPenalty = `${this._apiUrl}/api/v1/eroc/get/reduc/is-date-resolution-gap-exeed`;

    // Name Change
    this._getChangeNameDataUrl = `${this._apiUrl}/api/v1/eroc/name/change/data`;
    this._getChangeNameFormUrl = `${this._apiUrl}/api/v1/eroc/get/change/name/form/fill`;
    this._getChResubmitDataUrl = `${this._apiUrl}/api/v1/eroc/set/change/name/resubmit`;
    this._isResolutionDateExeed = `${this._apiUrl}/api/v1/eroc/get/change/name/form/is-date-resolution-gap-exeed`;
    this._isSetResDate = `${this._apiUrl}/api/v1/eroc/get/change/name/form/is-set-resolution-date`;

    this._getValidTokenUrl = `${this._apiUrl}/api/v1/eroc/valid/token`;

    this._getCrTokenURL = `${this._apiUrl}/api/v1/eroc/cipher/token`;
    this._getPyamentSuccessURL = `${this._apiUrl}/api/v1/eroc/payment/success`;

    this._getMemberTitleUrl = `${this._apiUrl}/api/v1/eroc/member/title`;
    this._getCountryDetailsUrl = `${this._apiUrl}/api/v1/eroc/country/details`;

    this._getNameReservationDataUrl = `${this._apiUrl}/api/v1/eroc/name/reservation/data?id=`;
    this._getfileResubmitDataUrl = `${this._apiUrl}/api/v1/eroc/data/reservation/resubmit/file`;
    this._getResubmitDataUrl = `${this._apiUrl}/api/v1/eroc/name/re/submit`;
    this._getDownloadUrl = `${this._apiUrl}/api/v1/eroc/document/download`;
    this._getDocNameUrl = `${this._apiUrl}/api/v1/eroc/document/name`;
    this._getFileDestroyUrl = `${this._apiUrl}/api/v1/eroc/document/destroy`;
    this._getReSubmitFileDestroyUrl = `${this._apiUrl}/api/v1/eroc/resubmit/document/destroy`;
    this._getCheckSamePasswordUrl = `${this._apiUrl}/api/v1/eroc/changePassword`;
    this._getCheckNameReSubmitUrl = `${this._apiUrl}/api/v1/eroc/name/has/reSubmit`;
    this._getUserUpdateURL = `${this._apiUrl}/api/v1/eroc/edit/user`;

    this._getPasswordResetLinkURL = `${this._apiUrl}/api/v1/forgot/email/link`;
    this._getNewPasswordResetURL = `${this._apiUrl}/api/v1/forgot/password/reset`;
    /* ---------- Udara Madushan -----------*/
    this._incorporationData = `${this._apiUrl}/api/v1/company-incorporation-data`;
    this._incorporationDataStep1Submit = `${this._apiUrl}/api/v1/company-incorporation-data-step1`;
    this._incorporationDataStep2Submit = `${this._apiUrl}/api/v1/company-incorporation-data-step2`;
    this._incorporationDeleteStakeholder = `${this._apiUrl}/api/v1/company-incorporation-delete-stakeholder`;
    this._incorporationCheckNic = `${this._apiUrl}/api/v1/company-incorporation-check-nic`;
    this._incorporationFileUpload = `${this._apiUrl}/api/v1/file-upload`;
    this._incorporationPay = `${this._apiUrl}/api/v1/pay`;

    // 2018-09-03 Updated
    this._incorporationSecForDirDelete = `${this._apiUrl}/api/v1/remove-director-sec-position`;
    this._incorporationShForDirDelete = `${this._apiUrl}/api/v1/remove-director-sh-position`;
    this._incorporationShForSecDelete = `${this._apiUrl}/api/v1/remove-sec-sh-position`;
    this._incorporationShFirmForSecFirmDelete = `${this._apiUrl}/api/v1/remove-secfirm-shfirm-position`;
    this._incorporationShFirmDelete = `${this._apiUrl}/api/v1/remove-sh-firm`;
    this._incorporationSecFirmDelete = `${this._apiUrl}/api/v1/remove-sec-firm`;
    // 2018-09-06 Updated
    this._getResubmitURL = `${this._apiUrl}/api/v1/re-submit`;
    this._getForeignRequestForApprovelURL = `${this._apiUrl}/api/v1/foreign-company-request-approval`;
    this._getResucValueURL = `${this._apiUrl}/api/v1/eroc/get/reduc`;

    // 2018-09-11 updated
    this._incorporationFileRemove = `${this._apiUrl}/api/v1/file-remove`;

    // 2018-10-24 updated
    this._incorporationHeavyData = `${this._apiUrl}/api/v1/company-incorporation-heavy-data`;
    this._certifiedCopyHeavyData =  `${this._apiUrl}/api/v1/company-certified-incorporation-heavy-data`;
    /* ---------- Udara Madushan -----------*/

    // 2018-12-05 updated
    this._incorporationSaveDocCopies = `${this._apiUrl}/api/v1/save-doc-copies`;

    // 2018-12-10 updated
    this._getCompanies = `${this._apiUrl}/api/v1/get-companies`;
    this._incorporationSavePubDocCopies = `${this._apiUrl}/api/v1/save-pub-doc-copies`;

    // 2019-01-10
    this._incorporationSaveIRDInfo = `${this._apiUrl}/api/v1/save-ird-info`;
    this._irdDirectorNicUpload = `${this._apiUrl}/api/v1/upload-ird-drector-nic`;
    this._irdDirectorNicRemove = `${this._apiUrl}/api/v1/remove-ird-drector-nic`;

    this._incorporationSaveLabourInfo = `${this._apiUrl}/api/v1/save-labour-info`;

    // 2019-01-16
    this._companyVerify = `${this._apiUrl}/api/v1/verify-company`;

    this._certifiedCopiesLoadData = `${this._apiUrl}/api/v1/company-certified-incorporation-data`;

    /* ---------- Udara Madushan -----------*/

    /* ---------- Ravihansa -----------*/
    this._secretaryData = `${this._apiUrl}/api/v1/secretary-data-load`;
    this._secretaryDataSubmit = `${this._apiUrl}/api/v1/secretary-data-submit`;
    this._secretaryFirmDataSubmit = `${this._apiUrl}/api/v1/secretary-firm-data-submit`;
    this._secretaryFirmPartnerData = `${this._apiUrl}/api/v1/secretary-firm-data-load`;
    this._secretaryNaturalUpload = `${this._apiUrl}/api/v1/secretary-natural-upload-pdf`;
    this._secretaryFileUploadedDelete = `${this._apiUrl}/api/v1/secretary-delete-pdf`;
    this._secretaryProfileData = `${this._apiUrl}/api/v1/secretary-profile-load`;
    this._secretaryFirmUpload = `${this._apiUrl}/api/v1/secretary-firm-upload-pdf`;
    this._secretaryPay = `${this._apiUrl}/api/v1/secretary-pay`;
    this._secretaryFile = `${this._apiUrl}/api/v1/secretary-file`;
    this._secretaryIsReg = `${this._apiUrl}/api/v1/secretary-is-reg`;
    this._secretaryPDF = `${this._apiUrl}/api/v1/secretary-individual-pdf`;
    this._secretaryFirmPDF = `${this._apiUrl}/api/v1/secretary-firm-pdf`;
    this._secretaryFirmDataResubmit = `${this._apiUrl}/api/v1/secretary-firm-data-load-resubmit`;
    this._secretaryFirmDataUpdate = `${this._apiUrl}/api/v1/secretary-firm-data-update`;
    this._secretaryDataResubmit = `${this._apiUrl}/api/v1/secretary-data-load-resubmit`;
    this._secretaryDataUpdate = `${this._apiUrl}/api/v1/secretary-data-update`;
    this._secretaryDocComments = `${this._apiUrl}/api/v1/secretary-doc-comments`;
    this._secretaryComments = `${this._apiUrl}/api/v1/secretary-general-comments`;
    this._secretaryUpdateUploaded = `${this._apiUrl}/api/v1/secretary-update-uploaded-pdf`;
    this._secretaryFileUploadedDeleteResubmited = `${this._apiUrl}/api/v1/secretary-delete-pdf-resubmited`;
    this._secretaryUpdateStatus = `${this._apiUrl}/api/v1/secretary-status-update-resubmit`;
    // this._secretaryFirmUpdateStatus = `${this._apiUrl}/api/v1/secretary-firm-status-update-resubmit`;
    this._secretaryCertificate = `${this._apiUrl}/api/v1/secretary-certificate-request`;
    this._loadsecretaryCertificate = `${this._apiUrl}/api/v1/load-secretary-certificate-request`;
    /* ---------- Ravihansa -----------*/

    /* ---------- Ravihansa 20181018-----------*/
    this._audIndcardload = `${this._apiUrl}/api/v1/auditor-data-load-ind-card?id=`;
    this._audFirmcardload = `${this._apiUrl}/api/v1/auditor-data-load-firm-card?id=`;
    this._auditorDataForChange = `${this._apiUrl}/api/v1/auditor-data-loadforchange`;
    this._auditorChangeTypeSubmit = `${this._apiUrl}/api/v1/auditor-changetype-submit`;
    this._auditorChangeDataSubmit = `${this._apiUrl}/api/v1/auditor-changedata-submit`;
    this._auditorChangeFile = `${this._apiUrl}/api/v1/auditor-changefile`;
    this._auditorChangeUpload = `${this._apiUrl}/api/v1/auditor-changeupload-pdf`;
    this._auditorChangeUpdateUploaded = `${this._apiUrl}/api/v1/auditor-changeupdateupload-pdf`;
    this._auditorChangeFileUploadedDeleteResubmited = `${this._apiUrl}/api/v1/auditor-changeupdateupload-delete-pdf`;
    this._auditorChangeStatusUpdate = `${this._apiUrl}/api/v1/auditor-change-status-update`;
    ////
    this._auditorFirmDataForChange = `${this._apiUrl}/api/v1/auditor-firm-data-loadforchange`;
    this._auditorFirmChangeTypeSubmit = `${this._apiUrl}/api/v1/auditor-firm-changetype-submit`;
    this._auditorFirmChangeDataSubmit = `${this._apiUrl}/api/v1/auditor-firm-changedata-submit`;
    this._auditorFirmChangeUpload = `${this._apiUrl}/api/v1/auditor-firm-changeupload-pdf`;
    this._auditorFirmChangeUpdateUploaded = `${this._apiUrl}/api/v1/auditor-firm-changeupdateupload-pdf`;
    this._auditorFirmChangeFileUploadedDeleteResubmited = `${this._apiUrl}/api/v1/auditor-firm-changeupdateupload-delete-pdf`;
    ////
    this._auditorDataSubmit = `${this._apiUrl}/api/v1/auditor-data-submit`;
    this._auditorDataSL = `${this._apiUrl}/api/v1/auditor-data-load-sl`;
    this._auditorDataNonSL = `${this._apiUrl}/api/v1/auditor-data-load-nonsl`;
    this._auditorPDF = `${this._apiUrl}/api/v1/auditor-individual-pdf`;
    this._auditorUpload = `${this._apiUrl}/api/v1/auditor-upload-pdf`;
    this._auditorFileUploadedDelete = `${this._apiUrl}/api/v1/auditor-delete-pdf`;
    this._auditorPay = `${this._apiUrl}/api/v1/auditor-pay`;
    this._auditorProfileData = `${this._apiUrl}/api/v1/auditor-profile-load`;
    this._auditorFile = `${this._apiUrl}/api/v1/auditor-file`;
    this._auditorFirmDataSubmit = `${this._apiUrl}/api/v1/auditor-firm-data-submit`;
    this._auditorID = `${this._apiUrl}/api/v1/auditor-firm-get-audid`;
    this._auditorFirmPDF = `${this._apiUrl}/api/v1/auditor-firm-pdf`;
    this._auditorFirmData = `${this._apiUrl}/api/v1/auditor-firm-data-load`;
    this._auditorFirmDataUpdate = `${this._apiUrl}/api/v1/auditor-firm-data-update`;
    this._auditorData = `${this._apiUrl}/api/v1/auditor-data-load`;
    this._auditorDataUpdate = `${this._apiUrl}/api/v1/auditor-data-update`;
    this._auditorDocComments = `${this._apiUrl}/api/v1/auditor-doc-comments`;
    this._auditorComments = `${this._apiUrl}/api/v1/auditor-general-comments`;
    this._auditorUpdateUploaded = `${this._apiUrl}/api/v1/auditor-update-uploaded-pdf`;
    this._auditorFileUploadedDeleteResubmited = `${this._apiUrl}/api/v1/auditor-delete-pdf-resubmited`;
    this._auditorRenewalPDF = `${this._apiUrl}/api/v1/auditor-individual-renewal-pdf`;
    this._auditorIsReg = `${this._apiUrl}/api/v1/auditor-individual-renewal-isreg`;
    this._auditorFirmRenewalPDF = `${this._apiUrl}/api/v1/auditor-firm-renewal-pdf`;
    this._auditorFirmIsReg = `${this._apiUrl}/api/v1/auditor-firm-renewal-isreg`;
    this._auditorUpdateStatus = `${this._apiUrl}/api/v1/auditor-status-update-resubmit`;
    this._auditorRenewalReSubmit = `${this._apiUrl}/api/v1/auditorrenewal-status-update-resubmit`;

    /* ---------- Ravihansa -----------*/

    /* ---------- Ravihansa 20190106-----------*/
    this._companyMemberData = `${this._apiUrl}/api/v1/company-member-data-load`;
    this._resubmitMemberRecord = `${this._apiUrl}/api/v1/company-member-data-resubmit`;
    this._companyMemberDataSubmit = `${this._apiUrl}/api/v1/company-member-changed-data-submit`;
    this._companyMemberDataEdit = `${this._apiUrl}/api/v1/company-member-changed-data-edit`;
    this._companyMemberDataCheckRegno = `${this._apiUrl}/api/v1/company-member-changed-data-checkregno`;
    this._companyMemberDataRevert = `${this._apiUrl}/api/v1/company-member-changed-data-revert`;
    this._companyinputSignby = `${this._apiUrl}/api/v1/company-member-changed-data-inputsignby`;
    this._companyform20CourtDataSubmit = `${this._apiUrl}/api/v1/company-member-changed-data-courtdatasubmit`;
    this._companyMemberPDF = `${this._apiUrl}/api/v1/company-member-change-pdf`;
    this._companyMemberRemove = `${this._apiUrl}/api/v1/company-member-change-remove`;
    this._companyMemberUpload = `${this._apiUrl}/api/v1/company-member-upload-pdf`;
    this._companyMemberUploadUpdate = `${this._apiUrl}/api/v1/company-member-upload-pdf-update`;
    this._companyMemberFileUploadedDelete = `${this._apiUrl}/api/v1/company-member-delete-pdf`;
    this._companyMemberFile = `${this._apiUrl}/api/v1/company-member-load-pdf`;

    /* ---------- Ravihansa -----------*/

    /* ---------- thilan -----------*/
    this._societyDataSubmit = `${this._apiUrl}/api/v1/society-data-submit`;
    this._societyProfileData = `${this._apiUrl}/api/v1/society-profile-load`;
    this._societyPay = `${this._apiUrl}/api/v1/society-pay`;
    this._societyUpload = `${this._apiUrl}/api/v1/society-upload-pdf`;
    this._societyMemberData = `${this._apiUrl}/api/v1/society-member-data-load`;
    this._societyFile = `${this._apiUrl}/api/v1/society-file`;
    this._societyDataSubmitUpdate = `${this._apiUrl}/api/v1/society-data-submit-update`;
    this._societyReSubmit = `${this._apiUrl}/api/v1/society-data-resubmit`;
    this._getPathCon = `${this._apiUrl}/api/v1/society-getpath`;
    this._societyMemberDataAddress = `${this._apiUrl}/api/v1/society-member-datawithaddress-load`;
    this._societyFileComment = `${this._apiUrl}/api/v1/society-file-comment`;
    this._societyData = `${this._apiUrl}/api/v1/society-data-load`;
    this._societyComments = `${this._apiUrl}/api/v1/society-comments-load`;
    this._societyUpdateUpload = `${this._apiUrl}/api/v1/society-update-upload-pdf`;
    this._societyFileUploadedDeleteUpdate = `${this._apiUrl}/api/v1/society-deleteupdate-pdf`;

    /* ---------- thilan address change-----------*/
    this._companyAddress = `${this._apiUrl}/api/v1/company-address-load`;
    this._companyAddressSubmit = `${this._apiUrl}/api/v1/company-newaddress-submit`;
    this._companyAddressReSubmit = `${this._apiUrl}/api/v1/company-newaddress-resubmit`;
    this._addressChangeApplicantGetDownloadUrl = `${this._apiUrl}/api/v1/company-addresschangeapplication-document`;
    this._addresschangeUpload = `${this._apiUrl}/api/v1/company-addresschangeupload-pdf`;
    this._addresschangeFile = `${this._apiUrl}/api/v1/company-addresschange-file`;
    this._addresschangeFileUploadedDelete = `${this._apiUrl}/api/v1/company-addresschange-delete-pdf`;
    this._addresschangeUpdateUpload = `${this._apiUrl}/api/v1/company-addresschange-update-upload-pdf`;
    this._addresschangeFileUploadedDeleteUpdate = `${this._apiUrl}/api/v1/company-addresschange-deleteupdate-pdf`;
    this._addresschangeReSubmit = `${this._apiUrl}/api/v1/company-addresschange-data-resubmit`;

    /* ---------- thilan accounting address change-----------*/
    this._companyAccountingAddress = `${this._apiUrl}/api/v1/company-accounting-address-load`;
    this._companyAccountingAddressDataSubmit = `${this._apiUrl}/api/v1/company-accounting-address-submit`;
    this._companyAccountingAddressCourtDataSubmit = `${this._apiUrl}/api/v1/company-accounting-address-courtdata-submit`;
    this._accDocGetDownloadUrl = `${this._apiUrl}/api/v1/company-accounting-address-view-document`;
    this._accountingaddresschangeUpload = `${this._apiUrl}/api/v1/company-accountingaddresschangeupload-pdf`;
    this._accountingaddresschangeFile = `${this._apiUrl}/api/v1/company-accountingaddresschange-file`;
    this._accountingaddresschangeFileUploadedDelete = `${this._apiUrl}/api/v1/company-accountingaddresschange-delete-pdf`;
    this._companyAccountingAddressDataUpdate = `${this._apiUrl}/api/v1/company-accounting-address-update`;
    this._accountingaddresschangeUpdateUpload = `${this._apiUrl}/api/v1/company-accountingaddresschange-update-upload-pdf`;
    this._accountingaddresschangeFileUploadedDeleteUpdate = `${this._apiUrl}/api/v1/company-accountingaddresschange-deleteupdate-pdf`;
    this._accountingaddresschangeReSubmit = `${this._apiUrl}/api/v1/company-accountingaddresschange-data-resubmit`;

    /* ---------- thilan balance sheet date change-----------*/
    this._companyBsdData = `${this._apiUrl}/api/v1/company-bsd-dataload`;
    this._companyBsdDataSubmit = `${this._apiUrl}/api/v1/company-bsd-submit`;
    this._bsdDocGetDownloadUrl = `${this._apiUrl}/api/v1/company-bsd-view-document`;
    this._bsdUpload = `${this._apiUrl}/api/v1/company-bsdupload-pdf`;
    this._bsdFile = `${this._apiUrl}/api/v1/company-bsd-file`;
    this._bsdFileUploadedDelete = `${this._apiUrl}/api/v1/company-bsd-delete-pdf`;
    this._companyBsdDataReSubmit = `${this._apiUrl}/api/v1/company-bsd-Resubmit`;
    this._bsdUpdateUpload = `${this._apiUrl}/api/v1/company-bsd-update-upload-pdf`;
    this._bsdUploadedDeleteUpdate = `${this._apiUrl}/api/v1/company-bsd-deleteupdate-pdf`;
    this._bsdReSubmit = `${this._apiUrl}/api/v1/company-bsd-data-resubmit`;

    /* ---------- thilan RR address change-----------*/
    this._companyRRAddress = `${this._apiUrl}/api/v1/company-rr-address-load`;
    this._companyRRAddressDataSubmit = `${this._apiUrl}/api/v1/company-rr-address-submit`;
    this._companyRRAddressDelete = `${this._apiUrl}/api/v1/company-rr-address-delete`;
    this._companyRRAddressRevert = `${this._apiUrl}/api/v1/company-rr-address-revert`;
    this._companyRRAddressChange = `${this._apiUrl}/api/v1/company-rr-address-change`;
    this._companyRRAddressCourtDataSubmit = `${this._apiUrl}/api/v1/company-rr-address-court-submit`;
    this._rrDocGetDownloadUrl = `${this._apiUrl}/api/v1/company-rr-view-document`;
    this._rrUpload = `${this._apiUrl}/api/v1/company-rrupload-pdf`;
    this._rrFile = `${this._apiUrl}/api/v1/company-rr-file`;
    this._rrFileUploadedDelete = `${this._apiUrl}/api/v1/company-rr-delete-pdf`;
    this._rrUpdateUpload = `${this._apiUrl}/api/v1/company-rr-update-upload-pdf`;
    this._rrUploadedDeleteUpdate = `${this._apiUrl}/api/v1/company-rr-deleteupdate-pdf`;
    this._rrReSubmit = `${this._apiUrl}/api/v1/company-rr-data-resubmit`;

    /* ---------- thilan satis charge change-----------*/
    this._companyScData = `${this._apiUrl}/api/v1/company-sc-dataload`;
    this._companyScDataSubmit = `${this._apiUrl}/api/v1/company-sc-submit`;
    this._scDocGetDownloadUrl = `${this._apiUrl}/api/v1/company-sc-view-document`;
    this._scUpload = `${this._apiUrl}/api/v1/company-scupload-pdf`;
    this._scFile = `${this._apiUrl}/api/v1/company-sc-file`;
    this._scFileUploadedDelete = `${this._apiUrl}/api/v1/company-sc-delete-pdf`;
    this._scUpdateUpload = `${this._apiUrl}/api/v1/company-sc-update-upload-pdf`;
    this._scUploadedDeleteUpdate = `${this._apiUrl}/api/v1/company-sc-deleteupdate-pdf`;
    this._scReSubmit = `${this._apiUrl}/api/v1/company-sc-data-resubmit`;

    /*-------heshan-----------------*/
    this._societyGetDownloadUrl = `${this._apiUrl}/api/v1/society-view-document`;
    this._societyApplicantGetDownloadUrl = `${this._apiUrl}/api/v1/society-application-document`;
    this._societyFileUploadedDelete = `${this._apiUrl}/api/v1/society-delete-pdf`;

    /* ---------- Issue of shares-----------*/
    this._companyName = `${this._apiUrl}/api/v1/company-name-load`;
    this._processingList = `${this._apiUrl}/api/v1/processing-list-load`;
    this._sharesSubmit = `${this._apiUrl}/api/v1/company-shares-submit`;
    this._getShareTypeUrl = `${this._apiUrl}/api/v1/company-shares-type`;
    this._twocsvs = `${this._apiUrl}/api/v1/load-two-csvs`;
    this._shareholderBulkUpload = `${this._apiUrl}/api/v1/bulk-shareholder-csv`;
    this._IssueofShareApplicantGetDownloadUrl =  `${this._apiUrl}/api/v1/issue-of-share-form-download`;
    this._currentShareholdersDetailsPDFGetDownloadUrl = `${this._apiUrl}/api/v1/current-shareholders-details-form-download`;
    this._issueofShareUpload = `${this._apiUrl}/api/v1/company-issueofshare-upload-pdf`;
    this._issueofSharesFileUploadedDelete = `${this._apiUrl}/api/v1/company-issueofshares-delete-pdf`;
    this._issueofSharesFile = `${this._apiUrl}/api/v1/company-issueofshares-file`;
    this._excelDataLoad = `${this._apiUrl}/api/v1/company-excell-data-load`;
    this._resetShareholdersRecord = `${this._apiUrl}/api/v1/company-shareholders-record-reset`;
    this._sharesReSubmit = `${this._apiUrl}/api/v1/company-shares-resubmit`;
    this._issueofSharesFileUploadedDeleteUpdate = `${this._apiUrl}/api/v1/company-issueofshares-delete-updated-pdf`;
    this._issueofShareUploadUpdate = `${this._apiUrl}/api/v1/company-issueofshare-upload-updated-pdf`;
    this._issueofsharesresubmit = `${this._apiUrl}/api/v1/company-issueofshare-confirm-resubmit`;

    /* ---------- Issue of debentures-----------*/
    this._previousApproved = `${this._apiUrl}/api/v1/pre-approved-record-load`;
    this._procesinglist = `${this._apiUrl}/api/v1/procesing-list-load`;
    this._debenturesSubmit = `${this._apiUrl}/api/v1/company-debentures-submit`;
    this._IssueofDebenturesApplicantGetDownloadUrl =  `${this._apiUrl}/api/v1/issue-of-Debentures-form-download`;
    this._issueofDebentureUpload = `${this._apiUrl}/api/v1/company-issueofdebenture-upload-pdf`;
    this._issueofDebenturesFileUploadedDelete = `${this._apiUrl}/api/v1/company-issueofdebentures-delete-pdf`;
    this._issueofDebenturesFile = `${this._apiUrl}/api/v1/company-issueofdebentures-file`;
    this._issueofDebenturesFileUploadedDeleteUpdate = `${this._apiUrl}/api/v1/company-issueofdebentures-delete-updated-pdf`;
    this._issueofDebenturesUploadUpdate = `${this._apiUrl}/api/v1/company-issueofdebentures-upload-updated-pdf`;
    this._issueofdebenturesresubmit = `${this._apiUrl}/api/v1/company-issueofdebentures-confirm-resubmit`;

    /* ---------- sahani -----------*/
    this._getSocietySEUrlPages = `${this._apiUrl}/api/v1/eroc/name/search/society?page=`;
    /* ---------- sahani -----------*/
  }

  public getLoginAPI(): string {
    return this._getauthLogin;
  }
  public getRegisterAPI(): string {
    return this._getauthRegister;
  }
  public getMigrateUserRegisterAPI(): string {
    return this._getMigrateUser;
  }

  public getauthRequestLinkURL(): string {
    return this._getauthRequestLinkURL;
  }

  public getLogoutAPI(): string {
    return this._getauthlogout;
  }

  public getCheckSamePasswordAPI(): string {
    return this._getCheckSamePasswordUrl;
  }

  public getResultAPI(): string {
    return this._getSEUrlPages;
  }
  public getCompanyTypeAPI(): string {
    return this._getcTypeUrl;
  }
  public getNameReceiveAPI(): string {
    return this._getcReceUrl;
  }

  public getActivationAPI(): string {
    return this._getauActivationUrl;
  }
  public getMigrateActivationAPI(): string {
    return this._getauMigrateActivationUrl;
  }

  public getUserAPI(): string {
    return this._getauthUser;
  }

  public getDocFeildAPI(): string {
    return this._getdocAPI;
  }

  public checkEmailAPI(): string {
    return this._getcheckEmail;
  }

  public setfileUploadAPI(): string {
    return this._getuploadURL;
  }

  public getNameReceived(): string {
    return this._getReceivedUrl;
  }

  public getChangeNameDataAPI(): string {
    return this._getChangeNameDataUrl;
  }

  public getChangeNameFormAPI(): string {
    return this._getChangeNameFormUrl;
  }
  public getReSubmitDataAPI(): string {
    return this._getResubmitDataUrl;
  }

  public getForeignRequestApprovalAPI(): string {
    return this._getForeignRequestForApprovelURL;
  }

  public getNameCancelAPI(): string {
    return this._getNameCancelUrl;
  }

  public getDocumentDownloadAPI(): string {
    return this._getDownloadUrl;
  }

  public getDocNameAPI(): string {
    return this._getDocNameUrl;
  }

  public getAvaterAPI(): string {
    return this._getAvaterUrl;
  }

  public getCountryAPI(): string {
    return this._getCountryUrl;
  }

  public getFileDestroyAPI(): string {
    return this._getFileDestroyUrl;
  }

  public getReSubmitFileDestroyAPI(): string {
    return this._getReSubmitFileDestroyUrl;
  }

  public getCheckFixDataAPI(): string {
    return this._getCheckFoxDataUrl;
  }

  public getSubdataAPI(): string {
    return this._getComsubData;
  }

  public getCityAndGnAPI(): string {
    return this._getGnDivisionAndCityUrl;
  }

  public getReduURL() {
    return this._getResucValueURL;
  }

  public getCapitalFormAPI() {
    return this._getCapitalFormURL;
  }

  // Payment
  public getPaymentAPI(): string {
    return this._getgetPaymentURL;
  }

  public getCrTokenAPI(): string {
    return this._getCrTokenURL;
  }
  public getPyamentSuccessAPI(): string {
    return this._getPyamentSuccessURL;
  }

  // check valid token
  public getValidTokenAPI(): string {
    return this._getValidTokenUrl;
  }
  public getMemberAPI(): string {
    return this._getMemberUrl;
  }

  public getStatusCountAPI(): string {
    return this._getStatusCountUrl;
  }

  public getMemberTitleAPI(): string {
    return this._getMemberTitleUrl;
  }

  public getCountryDetailsAPI(): string {
    return this._getCountryDetailsUrl;
  }

  public getNameReservationDataAPI(): string {
    return this._getNameReservationDataUrl;
  }

  public getChReSubmitDataAPI(): string {
    return this._getChResubmitDataUrl;
  }

  public getPasswordResetLinkAPI() {
    return this._getPasswordResetLinkURL;
  }

  public getNewPasswordResetAPI() {
    return this._getNewPasswordResetURL;
  }

  public isResolutionDateExeedURL(){
    return this._isResolutionDateExeed;
  }

  public isSetResDateURL(){
    return this._isSetResDate;
  }

  /* ---------- Udara Madushan -----------*/
  public getIncorporationData() {
    return this._incorporationData;
  }
  public getIncorporationDataStep1Submit() {
    return this._incorporationDataStep1Submit;
  }
  public getIncorporationDataStep2Submit() {
    return this._incorporationDataStep2Submit;
  }
  public getIncorporationNICCheckURL() {
    return this._incorporationCheckNic;
  }

  public getIncorporationRemoveStakeholderURL() {
    return this._incorporationDeleteStakeholder;
  }
  public getFileUploadURL() {
    return this._incorporationFileUpload;
  }

  public incorparationPay() {
    return this._incorporationPay;
  }

  // 2018-09-03 updated
  public incorparationSecForDirDeleteURL() {
    return this._incorporationSecForDirDelete;
  }
  public incorparationShForDirDeleteURL() {
    return this._incorporationShForDirDelete;
  }

  public incorparationShForSecDeleteURL() {
    return this._incorporationShForSecDelete;
  }

  public incorparationShFirmForSecFirmDeleteURL() {
    return this._incorporationShFirmForSecFirmDelete;
  }


  public incorparationSecFirmDeleteURL() {
    return this._incorporationSecFirmDelete;
  }

  // 2018-09-06 update
  public incorparationResubmitURL() {
    return this._getResubmitURL;
  }

  // 2018-09-11 update
  public incorparationFileRemoveURL() {
    return this._incorporationFileRemove;
  }

  public incorparationDeleteShFirmURL() {

    return this._incorporationShFirmDelete;
  }

  // 2018-10-24

  public incorparationHeavyDataURL() {
    return this._incorporationHeavyData;
  }
  public certifiedCopiesHeavyDataURL() {
    return this._certifiedCopyHeavyData;
  }

  // 2018-12-05
  public incorporationSaveDocCopiesURL() {
    return this._incorporationSaveDocCopies;
  }

  public getCompaniesURL() {
    return this._getCompanies;
  }
  public incorporationSavePubDocCopiesURL() {
    return this._incorporationSavePubDocCopies;
  }

  public incorporationSaveIRDInfoURL() {
    return this._incorporationSaveIRDInfo;
  }

  public incorporationSaveLabourInfoURL() {
    return this._incorporationSaveLabourInfo;
  }

  public irdDirectorNICUploadURL() {
    return this._irdDirectorNicUpload;
  }
  public irdDirectorNICremoveURL() {
    return this._irdDirectorNicRemove;
  }

  public verfiyCompanyURL() {
    return this._companyVerify;
  }

  public getCertifiedCopyDataURL() {
    return this._certifiedCopiesLoadData;
  }

  public _getReduRowAPI() {
    return this._getReduRowURL;
  }
  public _getReduAPI() {
    return this._getReduURL;
  }
  public _getUpdateReduAPI() {
    return this._getUpdateReduURL;
  }

  public _getUpdateAndContinueAPI() {
    return this._getUpdateAndContinueURL;
  }

  /* ---------- Udara Madushan -----------*/

  public getCheckNameReSubmitAPI() {
    return this._getCheckNameReSubmitUrl;
  }

  public getUserUpdateAPI(): string {
    return this._getUserUpdateURL;
  }

  /* ---------- Ravihansa 20180919-----------*/
  public getSecretaryData() {
    return this._secretaryData;
  }
  public getSecretaryDataSubmit() {
    return this._secretaryDataSubmit;
  }
  public getSecretaryFirmDataSubmit() {
    return this._secretaryFirmDataSubmit;
  }
  public getSecretaryFirmPartnerData() {
    return this._secretaryFirmPartnerData;
  }
  public getSecretaryNaturalFileUploadUrl() {
    return this._secretaryNaturalUpload;
  }
  public getSecretaryFirmFileUploadUrl() {
    return this._secretaryFirmUpload;
  }
  public getSecretaryFileUploadedDelete() {
    return this._secretaryFileUploadedDelete;
  }
  public getSecretaryProfileData() {
    return this._secretaryProfileData;
  }
  public getSecretaryPay() {
    return this._secretaryPay;
  }
  public getSecretaryFile() {
    return this._secretaryFile;
  }
  public getSecretaryRegistered() {
    return this._secretaryIsReg;
  }
  public getSecretaryPDF() {
    return this._secretaryPDF;
  }
  public getSecretaryFirmPDF() {
    return this._secretaryFirmPDF;
  }
  public getSecretaryDataResubmit() {
    return this._secretaryDataResubmit;
  }
  public getSecretaryFirmDataResubmit() {
    return this._secretaryFirmDataResubmit;
  }
  public getSecretaryFirmDataUpdate() {
    return this._secretaryFirmDataUpdate;
  }
  public getSecretaryDataUpdate() {
    return this._secretaryDataUpdate;
  }
  public getSecretaryDocComments() {
    return this._secretaryDocComments;
  }
  public getSecretaryComments() {
    return this._secretaryComments;
  }
  public getSecretaryFileUpdateUploadedUrl() {
    return this._secretaryUpdateUploaded;
  }
  public getSecretaryFileUploadedDeleteResubmited() {
    return this._secretaryFileUploadedDeleteResubmited;
  }
  public getSecretaryStatusUpdate() {
    return this._secretaryUpdateStatus;
  }
  // public getSecretaryFirmStatusUpdate() {
  //   return this._secretaryFirmUpdateStatus;
  // }
  public audIndcardload(): string {
    return this._audIndcardload;
  }
  public audFirmcardload(): string {
    return this._audFirmcardload;
  }
  public secretaryCertificateRequest() {
    return this._secretaryCertificate;
  }
  public loadsecretaryCertificateRequest() {
    return this._loadsecretaryCertificate;
  }
  //////////////
  public getAuditorChangeStatusUpdate() {
    return this._auditorChangeStatusUpdate;
  }
  public getAuditorChangeFileUploadedDeleteResubmited() {
    return this._auditorChangeFileUploadedDeleteResubmited;
  }
  public getAuditorChangeFileUpdateUploadedUrl() {
    return this._auditorChangeUpdateUploaded;
  }
  public getAuditorChangeFileUploadUrl() {
    return this._auditorChangeUpload;
  }
  public getAuditorChangeTypeSubmit() {
    return this._auditorChangeTypeSubmit;
  }
  public getAuditorChangeDataSubmit() {
    return this._auditorChangeDataSubmit;
  }
  public getAuditorChangeFile() {
    return this._auditorChangeFile;
  }
  ///////////////////
  public getAuditorFirmChangeData() {
    return this._auditorFirmDataForChange;
  }
  public getAuditorFirmChangeTypeSubmit() {
    return this._auditorFirmChangeTypeSubmit;
  }
  public getAuditorFirmChangeDataSubmit() {
    return this._auditorFirmChangeDataSubmit;
  }
  public getAuditorFirmChangeFileUploadUrl() {
    return this._auditorFirmChangeUpload;
  }
  public getAuditorFirmChangeFileUpdateUploadedUrl() {
    return this._auditorFirmChangeUpdateUploaded;
  }
  public getAuditorFirmChangeFileUploadedDeleteResubmited() {
    return this._auditorFirmChangeFileUploadedDeleteResubmited;
  }
  ///////////////////
  public getAuditorDataSubmit() {
    return this._auditorDataSubmit;
  }
  public getAuditorDataSL() {
    return this._auditorDataSL;
  }
  public getAuditorDataNonSL() {
    return this._auditorDataNonSL;
  }
  public getAuditorPDF() {
    return this._auditorPDF;
  }
  public getAuditorFileUploadUrl() {
    return this._auditorUpload;
  }
  public getAuditorFileUploadedDelete() {
    return this._auditorFileUploadedDelete;
  }
  public getAuditorPay() {
    return this._auditorPay;
  }
  public getAuditorProfileData() {
    return this._auditorProfileData;
  }
  public getAuditorFile() {
    return this._auditorFile;
  }
  public getAuditorFirmDataSubmit() {
    return this._auditorFirmDataSubmit;
  }
  public getAuditorID() {
    return this._auditorID;
  }
  public getAuditorFirmPDF() {
    return this._auditorFirmPDF;
  }
  public getAuditorFirmData() {
    return this._auditorFirmData;
  }
  public getAuditorFirmDataUpdate() {
    return this._auditorFirmDataUpdate;
  }
  public getAuditorData() {
    return this._auditorData;
  }
  public getAuditorDataForChange() {
    return this._auditorDataForChange;
  }
  public getAuditorDataUpdate() {
    return this._auditorDataUpdate;
  }
  public getAuditorDocComments() {
    return this._auditorDocComments;
  }
  public getAuditorComments() {
    return this._auditorComments;
  }
  public getAuditorFileUpdateUploadedUrl() {
    return this._auditorUpdateUploaded;
  }
  public getAuditorFileUploadedDeleteResubmited() {
    return this._auditorFileUploadedDeleteResubmited;
  }
  public getAuditorRenewalPDF() {
    return this._auditorRenewalPDF;
  }
  public getauditorRenewalReSubmit() {
    return this._auditorRenewalReSubmit;
  }
  public getAuditorIsReg() {
    return this._auditorIsReg;
  }
  public getAuditorFirmRenewalPDF() {
    return this._auditorFirmRenewalPDF;
  }
  public getAuditorFirmIsReg() {
    return this._auditorFirmIsReg;
  }
  public getAuditorStatusUpdate() {
    return this._auditorUpdateStatus;
  }

  public getCompanyMemberData() {
    return this._companyMemberData;
  }
  public getCompanyMemberResubmitURL(){
    return this._resubmitMemberRecord;
  }
  public getMemberChangeDataSubmit() {
    return this._companyMemberDataSubmit;
  }
  public getMemberChangeDataEdit() {
    return this._companyMemberDataEdit;
  }
  public checkRegno() {
    return this._companyMemberDataCheckRegno;
  }
  public getMemberChangeDataRevert() {
    return this._companyMemberDataRevert;
  }
  public inputSignby() {
    return this._companyinputSignby;
  }
  public form20CourtDataSubmit() {
    return this._companyform20CourtDataSubmit;
  }
  public getCompanyMemberPDF() {
    return this._companyMemberPDF;
  }
  public getRemoveMemberURL() {
    return this._companyMemberRemove;
  }
  public getCompanyMemberFileUploadUrl() {
    return this._companyMemberUpload;
  }
  public getCompanyMemberFileUpdateUploadUrl() {
    return this._companyMemberUploadUpdate;
  }
  public getMemberFileUploadedDelete() {
    return this._companyMemberFileUploadedDelete;
  }
  public getmemberFile() {
    return this._companyMemberFile;
  }



  /* ---------- Ravihansa 20180919-----------*/



  /* ---------- thilan 20181010-----------*/
  public getSocietyDataSubmit() {
    return this._societyDataSubmit;
  }

  public getSocietyProfileData() {
    return this._societyProfileData;
  }

  public getSocietyPay() {
    return this._societyPay;
  }

  public getSocietyFileUploadUrl() {
    return this._societyUpload;
  }

  public getSocietyMemberData() {
    return this._societyMemberData;
  }

  public getSocietyFile() {
    return this._societyFile;
  }
  public getSocietyDataSubmitUpdate() {
    return this._societyDataSubmitUpdate;
  }

  public getSocietyReSubmit() {
    return this._societyReSubmit;
  }

  public getPathCon() {
    return this._getPathCon;
  }

  public getSocietyMemberDataWithAddress() {
    return this._societyMemberDataAddress;
  }

  public getSocietyFileComment() {
    return this._societyFileComment;
  }

  public getSocietyData() {
    return this._societyData;
  }
  public getSocietyComments() {
    return this._societyComments;
  }
  public getSocietyFileUpdateUploadUrl() {
    return this._societyUpdateUpload;
  }

  public getSocietyFileUploadedDeleteUpdate() {
    return this._societyFileUploadedDeleteUpdate;
  }

  // ---------------Address-Change-thilan-------------------------//

  public getCompanyAddress() {
    return this._companyAddress;
  }
  public addressSubmit() {
    return this._companyAddressSubmit;
  }

  public addressReSubmit() {
    return this._companyAddressReSubmit;
  }

  public getAddressChangeApplicationDownloadAPI() {
    return this._addressChangeApplicantGetDownloadUrl;
  }

  public getAddressChangeFileUploadUrl() {
    return this._addresschangeUpload;
  }
  public getAddresschangeFile() {
    return this._addresschangeFile;
  }
  public getAddresschangeFileUploadedDelete() {
    return this._addresschangeFileUploadedDelete;
  }

  public getAddresschangeFileUpdateUploadUrl() {
    return this._addresschangeUpdateUpload;
  }

  public getAddresschangeFileUploadedDeleteUpdate() {
    return this._addresschangeFileUploadedDeleteUpdate;
  }

  public getAddresschangeReSubmit() {
    return this._addresschangeReSubmit;
  }



  /* ---------- heshan -----------*/
  public getSocietyDocumentDownloadAPI() {
    return this._societyGetDownloadUrl;
  }

  public getSocietyApplicationDownloadAPI() {
    return this._societyApplicantGetDownloadUrl;
  }

  public getSocietyFileUploadedDelete() {
    return this._societyFileUploadedDelete;
  }

  /* ---------- Issue of debentures -----------*/
  // Load previous approved records...
  public getPreApproved() {
    return this._previousApproved;
  }

  // Load procesing list...
  public getProcesingList() {
    return this._procesinglist;
  }

  // Submit details
  public debenturesSubmit() {
    return this._debenturesSubmit;
  }

  // Form 10A download
  public getIssueofDebenturesApplicationDownloadAPI() {
    return this._IssueofDebenturesApplicantGetDownloadUrl;
  }

  // File upload
  public getIssueofDebentureFileUploadUrl() {
    return this._issueofDebentureUpload;
  }

  // File delete
  public getIssueofDebenturesFileUploadedDelete() {
    return this._issueofDebenturesFileUploadedDelete;
  }

  // After delete pdf to view remaining pdf files
  public getIssueofDebenturesFile() {
    return this._issueofDebenturesFile;
  }

  // to delete updated uploaded issue-of-debentures pdf files...
  public getIssueofDebenturesFileUploadedDeleteUpdate() {
    return this._issueofDebenturesFileUploadedDeleteUpdate;
  }

  // Updated file upload
  public getIssueofDebenturesUpdatedFileUploadUrl() {
    return this._issueofDebenturesUploadUpdate;
  }

  // complete resubmit process...
  public getissueofDebenturesReSubmitAPI() {
    return this._issueofdebenturesresubmit;
  }
  /* ---------- Issue of debentures -end -----------*/

   /* ---------- Issue of shares -----------*/
   public getCompanyName() {
    return this._companyName;
  }

  public getProcessingList(){
    return this._processingList;
  }

  public sharesSubmit() {
    return this._sharesSubmit;
  }

  public getShareTypeAPI(): string {
    return this._getShareTypeUrl;
  }

  public getCSVsAPI(){
    return this._twocsvs;
  }

  public shareholderBulkUploadURL() {
    return this._shareholderBulkUpload;
  }

  // Form 6 download
  public getIssueofShareApplicationDownloadAPI() {
    return this._IssueofShareApplicantGetDownloadUrl;
  }

  public getCurrentShareholdersDetailsPDFDownloadAPI(){
    return this._currentShareholdersDetailsPDFGetDownloadUrl;
  }

  // File upload
  public getIssueofShareFileUploadUrl() {
    return this._issueofShareUpload;
  }

  public getIssueofSharesFileUploadedDelete() {
    return this._issueofSharesFileUploadedDelete;
  }
  // After delete pdf to view remaining pdf files
  public getIssueofSharesFile() {
    return this._issueofSharesFile;
  }

  // Load excell data when ISSUE OF SHARES PROCESSING
  public getExcellDataloadAPI() {
    return this._excelDataLoad;
  }

  // Reset shareholders record from database
  public getShareholdersRecordResetAPI(){
    return this._resetShareholdersRecord;
  }


  // Shares Resubmit
  public sharesReSubmit() {
    return this._sharesReSubmit;
  }

    // to delete updated uploaded issue-of-shares pdf files...
  public getIssueofSharesFileUploadedDeleteUpdate() {
    return this._issueofSharesFileUploadedDeleteUpdate;
  }

  // Updated file upload
  public getIssueofShareUpdatedFileUploadUrl() {
    return this._issueofShareUploadUpdate;
  }

  // complete resubmit process...
  public getissueofsharesReSubmitAPI() {
    return this._issueofsharesresubmit;
  }

  /* ---------- Issue of shares -end -----------*/

  /* ---------- sahani -----------*/
  public getResultSocietyAPI(): string {
    return this._getSocietySEUrlPages;
  }

  // ---------------Accounting-Addresses-Change-thilan-------------------------//

  public getCompanyAccoutingAddress() {
    return this._companyAccountingAddress;
  }
  public accountingAddressDataSubmit() {
    return this._companyAccountingAddressDataSubmit;
  }

  public accountingAddressCourtDataSubmit() {
    return this._companyAccountingAddressCourtDataSubmit;
  }

  public accountingAddressDataUpdate() {
    return this._companyAccountingAddressDataUpdate;
  }

  public getAccAdDocumentDownloadAPI() {
    return this._accDocGetDownloadUrl;
  }

  public getAccountingAddressFileUploadUrl() {
    return this._accountingaddresschangeUpload;
  }

  public getACAddresschangeFile() {
    return this._accountingaddresschangeFile;
  }

  public getAcAddresschangeFileUploadedDelete() {
    return this._accountingaddresschangeFileUploadedDelete;
  }

  public getAcAddresschangeFileUpdateUploadUrl() {
    return this._accountingaddresschangeUpdateUpload;
  }

  public getAcAddresschangeFileUploadedDeleteUpdate() {
    return this._accountingaddresschangeFileUploadedDeleteUpdate;
  }

  public getAcAddresschangeReSubmit() {
    return this._accountingaddresschangeReSubmit;
  }

  // ---------------Balance-SheetDate-Change-thilan-------------------------//
  public getCompanyBsdData() {
    return this._companyBsdData;
  }

  public bsdDataSubmit() {
    return this._companyBsdDataSubmit;
  }

  public getBsdDocumentDownloadAPI() {
    return this._bsdDocGetDownloadUrl;
  }

  public getBsdFileUploadUrl() {
    return this._bsdUpload;
  }

  public getbsdFile() {
    return this._bsdFile;
  }

  public getbsdFileUploadedDelete() {
    return this._bsdFileUploadedDelete;
  }

  public bsdReDataSubmit() {
    return this._companyBsdDataReSubmit;
  }

  public getbsdFileUpdateUploadUrl() {
    return this._bsdUpdateUpload;
  }

  public getbsdUploadedDeleteUpdate() {
    return this._bsdUploadedDeleteUpdate;
  }

  public getbsdReSubmit() {
    return this._bsdReSubmit;
  }


  // ---------------RR-Address-Change-thilan-------------------------//

  public getCompanyRRAddress() {
    return this._companyRRAddress;
  }

  public rrAddressDataSubmit() {
    return this._companyRRAddressDataSubmit;
  }

  public rrAddressDelete() {
    return this._companyRRAddressDelete;
  }

  public rrAddressRevert() {
    return this._companyRRAddressRevert;
  }

  public rrAddressChange() {
    return this._companyRRAddressChange;
  }

  public rrAddressCourtDataSubmit() {
    return this._companyRRAddressCourtDataSubmit;
  }

  public getRRDocumentDownloadAPI() {
    return this._rrDocGetDownloadUrl;
  }

  public getRRFileUploadUrl() {
    return this._rrUpload;
  }

  public getrrFile() {
    return this._rrFile;
  }

  public getrrFileUploadedDelete() {
    return this._rrFileUploadedDelete;
  }

  public getrrFileUpdateUploadUrl() {
    return this._rrUpdateUpload;
  }

  public getrrUploadedDeleteUpdate() {
    return this._rrUploadedDeleteUpdate;
  }

  public getrrReSubmit() {
    return this._rrReSubmit;
  }

  // ---------------Satisfaction-Charge-Change-thilan-------------------------//
  public getCompanyScData() {
    return this._companyScData;
  }

  public scDataSubmit() {
    return this._companyScDataSubmit;
  }

  public getScDocumentDownloadAPI() {
    return this._scDocGetDownloadUrl;
  }

  public getScFileUploadUrl() {
    return this._scUpload;
  }

  public getscFile() {
    return this._scFile;
  }

  public getscFileUploadedDelete() {
    return this._scFileUploadedDelete;
  }

  public getscFileUpdateUploadUrl() {
    return this._scUpdateUpload;
  }

  public getscUploadedDeleteUpdate() {
    return this._scUploadedDeleteUpdate;
  }

  public getscReSubmit() {
    return this._scReSubmit;
  }

  public getSettingAPI(){
    return this._getSettingURL;
  }
  public getCapResubmitAPI(){
    return this._setCapResubmitURL;
  }

  public getReductionPenalty() {
    return this._getReduPenalty;
  }

  public getuploadFileAPI(){
    return this._getuploadFileURL;
  }
  public getuploadOtherFileAPI() {
    return this._geteuploadotherFileURL;
  }
  public getuploadOtherResubmitFileAPI() {
    return this._geteuploadotherResubmitFileURL;
  }
  public getuploadOtherFileForNameAPI() {
    return this._geteuploadotherForNameFileURL;
  }
  public getOtherFileListAPI() {
    return this._getOtherFilesList;
  }
  public getOtherFileListForNameAPI() {
    return this._getOtherFilesListForName;
  }
  public getOtherFileRemoveAPI() {
    return this._removeOtherDoc;
  }
  public getfileResubmitDataAPI(){
    return this._getfileResubmitDataUrl;
  }
  public getUpdateCourtAPI() {
    return this._updateCourtOrderDetails;
  }

  public checkCompanyRegNumberForAdmin() {
    return this._checkCompanyForAdmin;
  }



}
