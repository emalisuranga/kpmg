import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APIAnnualReturnConnection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();
  _annualHeavyData: string;
  _annualData: string;
  _nicCheck: string;
  _submitStep1: string;
 _submitDirectors: string;
 _submitSecretories: string;
 _submitShareholders: string;
 _submitShareRegister: string;
 _submitAnnualRecords: string;
 _submitAnnualAuditorRecords: string;
 _submitAnnualChargeRecords: string;
 _submitShareRecords: string;
 _uploadForms: string;
 _removeDoc: string;
 _resubmitProcess: string;
 _shareholderBulkUpload: string;
 _ceasedShareholderBulkUpload: string;

 _uploadOther: string;
 _uploadOtherResubmitted: string;
 _removeOtherDoc: string;
_updateCourt: string;
_submitSharholderTransfers: string;

  constructor() {

    this._apiUrl = this.url._apiUrl;
    this._annualData = `${this._apiUrl}/api/v1/company-annual-data`;
    this._annualHeavyData = `${this._apiUrl}/api/v1/company-annual-heavy-data`;
    this._nicCheck = `${this._apiUrl}/api/v1/company-annual-check-nic`;
    this._submitStep1 = `${this._apiUrl}/api/v1/company-annual-submit-step1`;
    this._submitDirectors = `${this._apiUrl}/api/v1/company-annual-submit-directors`;
    this._submitSecretories = `${this._apiUrl}/api/v1/company-annual-submit-secretories`;
    this._submitShareholders = `${this._apiUrl}/api/v1/company-annual-submit-shareholders`;
    this._submitShareRegister = `${this._apiUrl}/api/v1/company-annual-submit-share-register`;
    this._submitAnnualRecords = `${this._apiUrl}/api/v1/company-annual-submit-annual-records`;
    this._submitAnnualAuditorRecords = `${this._apiUrl}/api/v1/company-annual-submit-annual-auditor-records`;
    this._submitAnnualChargeRecords = `${this._apiUrl}/api/v1/company-annual-submit-annual-charges-records`;
    this._submitShareRecords = `${this._apiUrl}/api/v1/company-annual-submit-share-records`;
    this._uploadForms = `${this._apiUrl}/api/v1/company-annual-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/company-annual-remove-docs`;
    this._resubmitProcess = `${this._apiUrl}/api/v1/company-annual-resubmit`;
    this._shareholderBulkUpload = `${this._apiUrl}/api/v1/company-annual-submit-bulk-shareholder-csv`;
    this._ceasedShareholderBulkUpload = `${this._apiUrl}/api/v1/company-annual-submit-bulk-ceased-shareholder-csv`;

    this._uploadOther = `${this._apiUrl}/api/v1/company-annual-upload-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/company-annual-upload-other-resubmitted-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/company-annual-remove-other-docs`;
    this._updateCourt = `${this._apiUrl}/api/v1/company-annual-update-court-record`;
    this._submitSharholderTransfers = `${this._apiUrl}/api/v1/company-annual-submit-share-transfers`;
  }

  public getAnnualData() {
    return this._annualData;
  }
  public getAnnualHeavyData() {
    return this._annualHeavyData;
  }
  public getStakeholderNICData() {
    return this._nicCheck;
  }
  public submitStep1URL() {
    return this._submitStep1;
  }
  public submitDirectorsURL() {
    return this._submitDirectors;
  }
  public submitSecretoriesURL(){
    return this._submitSecretories;
  }
  public submitShareholdersURL(){
    return this._submitShareholders;
  }
  public submitShareRegisterURL(){
    return this._submitShareRegister;
  }
  public submitAnnualRecordURL(){
    return this._submitAnnualRecords;
  }
  public submitAnnualAuditorRecordURL(){
    return this._submitAnnualAuditorRecords;
  }
  public submitAnnualChargeRecordURL(){
    return this._submitAnnualChargeRecords;
  }
  public submitShareRecordURL(){
    return this._submitShareRecords;
  }
  public uploadDocsURL(){
    return this._uploadForms;
  }
  public removeDocsURL(){
    return this._removeDoc;
  }
  public resubmitProcess() {
    return this._resubmitProcess;
  }

  public shareholderBulkUploadURL() {
    return this._shareholderBulkUpload;
  }
  public CeasedShareholderBulkUploadURL() {
    return this._ceasedShareholderBulkUpload;
  }

  public uploadOtherDocsURL() {
    return this._uploadOther;
  }
  public uploadOtherResubmittedDocsURL() {
    return this._uploadOtherResubmitted;
  }
  public removeOtherDocsURL() {
    return this._removeOtherDoc;
  }
  public updateCourtURL() {
    return this._updateCourt;
  }

  public submitShareholderTransferURL(){
      return this._submitSharholderTransfers;
  }
}
