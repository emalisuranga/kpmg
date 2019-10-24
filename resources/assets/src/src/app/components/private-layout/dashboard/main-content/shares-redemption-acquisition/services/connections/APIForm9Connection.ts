import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APIForm9Connection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

 _callOnSharesData: string;
 _submitCallOnShares: string;
 _uploadForms: string;
 _removeDoc: string;
 _resubmit: string;
 _uploadOther: string;
 _removeOtherDoc: string;
 _uploadOtherResubmitted: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/company-form9-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/company-form9-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/company-form9-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/company-form9-remove-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/company-form9-resubmit`;
    this._uploadOther = `${this._apiUrl}/api/v1/company-form9-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/company-form9-remove-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/company-form9-upload-other-resubmitted-docs`;
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
  public uploadOtherDocsURL() {
    return this._uploadOther;
  }
  public removeOtherDocsURL() {
    return this._removeOtherDoc;
  }
  public uploadOtherResubmittedDocsURL() {
    return this._uploadOtherResubmitted;
  }
  public removeDocsURL() {
    return this._removeDoc;
  }
  public resubmitURL() {
    return this._resubmit;
  }
}
