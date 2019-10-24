import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APIForm35Connection {
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
 _updateOtherDocDate: string;

 _removeExistingDirector: string;
 _removeChangeDirector: string;
 _updateExistingDirector: string;
 _addNewDirector: string;
 _removeExistingSec: string;
 _updateExistingSec: string;
 _removeChangeSec: string;
 _updateExistingSecFirm: string;
 _addNewSec: string;
 _addNewSecFirm: string;
 _updateAlterType: string;

 _uploadOther: string;
 _removeOtherDoc: string;
 _uploadOtherResubmitted: string;
 _updateCourt: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;
    this._annualData = `${this._apiUrl}/api/v1/company-overseas-annual-data`;
    this._annualHeavyData = `${this._apiUrl}/api/v1/company-overseas-annual-heavy-data`;
    this._nicCheck = `${this._apiUrl}/api/v1/company-overseas-annual-check-nic`;
    this._submitStep1 = `${this._apiUrl}/api/v1/company-overseas-annual-submit-step1`;
    this._submitDirectors = `${this._apiUrl}/api/v1/company-overseas-annual-submit-directors`;
    this._submitSecretories = `${this._apiUrl}/api/v1/company-overseas-annual-submit-secretories`;
    this._submitShareholders = `${this._apiUrl}/api/v1/company-overseas-annual-submit-shareholders`;
    this._submitShareRegister = `${this._apiUrl}/api/v1/company-overseas-annual-submit-share-register`;
    this._submitAnnualRecords = `${this._apiUrl}/api/v1/company-overseas-annual-submit-annual-records`;
    this._submitAnnualAuditorRecords = `${this._apiUrl}/api/v1/company-overseas-annual-submit-annual-auditor-records`;
    this._submitAnnualChargeRecords = `${this._apiUrl}/api/v1/company-overseas-annual-submit-annual-charges-records`;
    this._submitShareRecords = `${this._apiUrl}/api/v1/company-overseas-annual-submit-share-records`;
    this._uploadForms = `${this._apiUrl}/api/v1/company-overseas-annual-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/company-overseas-annual-remove-docs`;
    this._resubmitProcess = `${this._apiUrl}/api/v1/company-overseas-annual-resubmit`;
    this._shareholderBulkUpload = `${this._apiUrl}/api/v1/company-overseas-annual-submit-bulk-shareholder-csv`;
    this._ceasedShareholderBulkUpload = `${this._apiUrl}/api/v1/company-overseas-annual-submit-bulk-ceased-shareholder-csv`;

    this._removeExistingDirector = `${this._apiUrl}/api/v1/company-overseas-remove-existing-director`;
    this._removeChangeDirector = `${this._apiUrl}/api/v1/company-overseas-remove-change-director`;
    this._updateExistingDirector = `${this._apiUrl}/api/v1/company-overseas-update-existing-director`;
    this._addNewDirector = `${this._apiUrl}/api/v1/company-overseas-add-new-director`;
    this._removeExistingSec = `${this._apiUrl}/api/v1/company-overseas-remove-existing-sec`;
    this._updateExistingSec = `${this._apiUrl}/api/v1/company-overseas-update-existing-sec`;
    this._removeChangeSec = `${this._apiUrl}/api/v1/company-overseas-remove-change-sec`;
    this._updateExistingSecFirm = `${this._apiUrl}/api/v1/company-overseas-update-existing-secfirm`;
    this._addNewSec = `${this._apiUrl}/api/v1/company-overseas-add-new-sec`;
    this._addNewSecFirm = `${this._apiUrl}/api/v1/company-overseas-add-new-secfirm`;
    this._updateOtherDocDate = `${this._apiUrl}/api/v1/company-overseas-update-othe-doc-date`;
    this._updateAlterType = `${this._apiUrl}/api/v1/company-overseas-update-alter-type`;

    this._uploadOther = `${this._apiUrl}/api/v1/company-overseas-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/company-overseas-remove-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/company-overseas-upload-other-resubmitted-docs`;
    this._updateCourt = `${this._apiUrl}/api/v1/company-overseas-update-court-record`;
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

  public removeExisingDirector() {
    return this._removeExistingDirector;
  }
  public removeChangeDirector() {
    return this._removeChangeDirector;
  }
  public updateExisingDirector() {
    return this._updateExistingDirector;
  }
  public addNewDirector() {
    return this._addNewDirector;
  }

  public removeExisitingSec() {
    return this._removeExistingSec;
  }
  public updateExistingSec(){
    return this._updateExistingSec;
  }
  public removeChangeSec() {
    return this._removeChangeSec;
  }
  public updateExisitingSecFirm(){
    return this._updateExistingSecFirm;
  }

  public addNewSec() {
    return this._addNewSec;
  }
  public addNewSecFirm() {
    return this._addNewSecFirm;
  }
  public updateOtherDocDate() {
    return this._updateOtherDocDate;
  }

  public updateAlterType() {
    return this._updateAlterType;
  }

  public uploadOtherDocsURL() {
    return this._uploadOther;
  }
  public removeOtherDocsURL() {
    return this._removeOtherDoc;
  }
  public uploadOtherResubmittedDocsURL() {
    return this._uploadOtherResubmitted;
  }

  public updateCourtURL() {
    return this._updateCourt;
  }

}
