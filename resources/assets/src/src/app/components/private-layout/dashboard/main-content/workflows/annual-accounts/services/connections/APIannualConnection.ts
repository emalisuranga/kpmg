import { APIConnection } from '../../../../../../../../http/services/connections/APIConnection';
export class APIannualConnection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

 _callOnSharesData: string;
 _submitCallOnShares: string;
 _uploadForms: string;
 _uploadOther: string;
 _removeDoc: string;
 _removeOtherDoc: string;
 _resubmit: string;
 _checkPreviousYearRecord: string;
 _payForPrivate: string;
 _uploadOtherResubmitted: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/annual-account-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/annual-account-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/annual-account-upload-docs`;
    this._uploadOther = `${this._apiUrl}/api/v1/annual-account-upload-other-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/annual-account-remove-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/annual-account-remove-other-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/annual-account-resubmit`;
    this._checkPreviousYearRecord = `${this._apiUrl}/api/v1/annual-account-check-previous-record`;
    this._payForPrivate = `${this._apiUrl}/api/v1/annual-account-pay-for-private`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/annual-account-upload-other-resubmitted-docs`;
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
  public removeOtherDocsURL() {
    return this._removeOtherDoc;
  }

  public checkPreviousYearRecordURL() {
    return this._checkPreviousYearRecord;
  }
  public completeForPrivateURL() {
    return this._payForPrivate;
  }
  public uploadOtherResubmittedDocsURL() {
    return this._uploadOtherResubmitted;
  }
}
