import { APIConnection } from '../../../../../../../../http/services/connections/APIConnection';
export class APIprospectusConnection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

 _callOnSharesData: string;
 _submitCallOnShares: string;
 _uploadForms: string;
 _uploadOther: string;
 _removeDoc: string;
 _removeOtherDoc: string;
 _resubmit: string;
 _uploadOtherResubmitted: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/prospectus-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/prospectus-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/prospectus-upload-docs`;
    this._uploadOther = `${this._apiUrl}/api/v1/prospectus-upload-other-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/prospectus-remove-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/prospectus-remove-other-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/prospectus-resubmit`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/prospectus-upload-other-resubmitted-docs`;
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
  public uploadOtherResubmittedDocsURL() {
    return this._uploadOtherResubmitted;
  }
}
