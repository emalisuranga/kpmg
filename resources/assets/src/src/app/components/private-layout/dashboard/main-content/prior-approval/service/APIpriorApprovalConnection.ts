import { APIConnection } from '../../../../../../http/services/connections/APIConnection';

export class APIpriorApprovalConnection {

  _apiUrl: String;

  url: APIConnection = new APIConnection();

  _callOnSharesData: string;
  _submitCallOnShares: string;
  _uploadForms: string;
  _uploadOther: string;
  _removeDoc: string;
  _removeOtherDoc: string;
  _resubmit: string;
  _submit: string;
  _getUserCorrespondence: string;
  _removeRequest: string;
  _uploadOtherResubmitted: string;

  constructor() {
    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/prior-approval`;
    this._resubmit = `${this._apiUrl}/api/v1/prior-approval-resubmit`;
    this._submit = `${this._apiUrl}/api/v1/prior-approval-submit`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/prior-submit`;
    this._uploadOther = `${this._apiUrl}/api/v1/prior-approval-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/prior-approval-remove-other-docs`;
    this._getUserCorrespondence = `${this._apiUrl}/api/v1/list-prior-approval`;
    this._removeRequest = `${this._apiUrl}/api/v1/remove-prior-list`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/prior-approval-resubmitted-docs`;
    this._uploadForms = `${this._apiUrl}/api/v1/prior-approval-uplode`;
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
  public submitURL() {
    return this._submit;
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
  public getUserCorrespondence() {
    return this._getUserCorrespondence;
  }
  public removeList(){
    return this._removeRequest;
  }

  public uploadOtherResubmittedDocsURL(){
    return this._uploadOtherResubmitted;
  }
}
