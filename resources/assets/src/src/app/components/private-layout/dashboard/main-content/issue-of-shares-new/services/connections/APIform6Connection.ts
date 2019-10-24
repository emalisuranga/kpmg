import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APIform6Connection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

 _callOnSharesData: string;
 _submitCallOnShares: string;
 _uploadForms: string;
 _removeDoc: string;
 _resubmit: string;
 _uploadOther: string;
 _uploadOtherResubmitted: string;
 _removeOtherDoc: string;
 _updateCourt: string;
 _submitShareholders: string;
 _submitNewShareholder: string;
 _submitExistShareholder: string;
 _nicCheck: string;
_removeShareholder: string;
_removeShareClassRecord: string;
_updateShareholderCSV: string;

  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/company-issue-of-shares-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/company-issue-of-shares-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/company-issue-of-shares-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/company-issue-of-shares-remove-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/company-issue-of-shares-resubmit`;
    this._uploadOther = `${this._apiUrl}/api/v1/company-issue-of-shares-upload-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/company-issue-of-shares-upload-other-resubmitted-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/company-issue-of-shares-remove-other-docs`;
    this._updateCourt = `${this._apiUrl}/api/v1/company-issue-of-shares-update-court-record`;
    this._submitShareholders = `${this._apiUrl}/api/v1/company-issue-of-shares-submit-shareholders`;
    this._submitNewShareholder = `${this._apiUrl}/api/v1/company-issue-of-shares-submit-new-shareholder`;
    this._submitExistShareholder = `${this._apiUrl}/api/v1/company-issue-of-shares-submit-exist-shareholder`;
    this._nicCheck = `${this._apiUrl}/api/v1/company-issue-of-shares-check-nic`;
    this._removeShareholder = `${this._apiUrl}/api/v1/company-issue-of-shares-remove-shareholder`;
    this._removeShareClassRecord = `${this._apiUrl}/api/v1/company-issue-of-shares-remove-record`;
    this._updateShareholderCSV = `${this._apiUrl}/api/v1/upload-shareholder-csv`;
  }

  public getCallOnSharesData() {
    return this._callOnSharesData;
  }
  public submitCallOnSharesURL() {
    return this._submitCallOnShares;
  }
  public uploadDocsURL() {
    return this._uploadForms;
  }
  public removeDocsURL() {
    return this._removeDoc;
  }
  public resubmitURL() {
    return this._resubmit;
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
  public submitShareholdersURL(){
    return this._submitShareholders;
  }
  public submitNewShareholder() {
    return this._submitNewShareholder;
  }
  public submitExistShareholder() {
    return this._submitExistShareholder;
  }
  public getStakeholderNICData() {
    return this._nicCheck;
  }
  public removeShareholder() {
    return this._removeShareholder;
  }
  public removeShareClassRecord() {
    return this._removeShareClassRecord;
  }
  public shareholderCSV() {
    return this._updateShareholderCSV;
  }
}
