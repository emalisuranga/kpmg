import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APICorrConnection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

 _callOnSharesData: string;
 _submitCallOnShares: string;
 _uploadForms: string;
 _removeDoc: string;
 _submit: string;
 _resubmit: string;
 _uploadOther: string;
 _removeOtherDoc: string;
 _getCorrCompanies: string;
 _getUserCorrespondence: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/corr-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/corr-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/corr-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/corr-remove-docs`;
    this._submit = `${this._apiUrl}/api/v1/corr-submitt-request`;
    this._resubmit = `${this._apiUrl}/api/v1/corr-resubmit`;
    this._uploadOther = `${this._apiUrl}/api/v1/corr-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/corr-remove-other-docs`;
    this._getCorrCompanies = `${this._apiUrl}/api/v1/corr-get-companies`;
    this._getUserCorrespondence = `${this._apiUrl}/api/v1/corr-get-user-requests`;
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
  public submitURL() {
    return this._submit;
  }
  public uploadOtherDocsURL() {
    return this._uploadOther;
  }
  public removeOtherDocsURL() {
    return this._removeOtherDoc;
  }

  public getCorrCompanies() {
    return this._getCorrCompanies;
  }

  public getUserCorrespondence() {
    return this._getUserCorrespondence;
  }
}
