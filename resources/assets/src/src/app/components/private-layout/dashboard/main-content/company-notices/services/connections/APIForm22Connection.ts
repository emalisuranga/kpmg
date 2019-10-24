import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APIForm22Connection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

 _callOnSharesData: string;
 _submitCallOnShares: string;
 _uploadForms: string;
 _removeDoc: string;
 _resubmit: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/company-notices-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/company-notices-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/company-notices-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/company-notices-remove-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/company-notices-resubmit`;
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
}
