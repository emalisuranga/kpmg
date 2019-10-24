import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APIform8Connection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

 _callOnSharesData: string;
 _submitCallOnShares: string;
 _uploadForms: string;
 _uploadOther: string;
 _uploadOtherResubmitted: string;
 _removeDoc: string;
 _removeOtherDoc: string;
 _resubmit: string;
 _updateCourt: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/form8-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/form8-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/form8-upload-docs`;
    this._uploadOther = `${this._apiUrl}/api/v1/form8-upload-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/form8-upload-other-resubmitted-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/form8-remove-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/form8-remove-other-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/form8-resubmit`;
    this._updateCourt = `${this._apiUrl}/api/v1/form8-update-court-record`;
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
