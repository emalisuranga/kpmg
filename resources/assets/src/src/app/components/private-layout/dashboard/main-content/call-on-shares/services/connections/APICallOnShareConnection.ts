import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APICallOnShareConnection {
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

  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/company-calls-on-shares-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/company-calls-on-shares-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/company-calls-on-shares-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/company-calls-on-shares-remove-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/company-calls-on-shares-resubmit`;
    this._uploadOther = `${this._apiUrl}/api/v1/company-calls-on-shares-upload-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/company-calls-on-shares-upload-other-resubmitted-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/company-calls-on-shares-remove-other-docs`;
    this._updateCourt = `${this._apiUrl}/api/v1/company-calls-on-shares-update-court-record`;
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
}
