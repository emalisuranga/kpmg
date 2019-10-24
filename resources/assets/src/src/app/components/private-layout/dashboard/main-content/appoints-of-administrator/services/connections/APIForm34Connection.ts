import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APIForm34Connection {
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
 _updateCourt: string;

 _checkAdminCompany: string;
 _addAdminCompanies: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/form34-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/form34-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/form34-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/form34-remove-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/form34-resubmit`;
    this._uploadOther = `${this._apiUrl}/api/v1/form34-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/form34-remove-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/form34-upload-other-resubmitted-docs`;
    this._updateCourt = `${this._apiUrl}/api/v1/form34-update-court-record`;
    this._checkAdminCompany = `${this._apiUrl}/api/v1/form34-check-company`;
    this._addAdminCompanies = `${this._apiUrl}/api/v1/form34-add-company`;
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
  public updateCourtURL() {
    return this._updateCourt;
  }

  public checkAdminCompanies() {
    return this._checkAdminCompany;
  }
  public addAdminCompanies() {
    return this._addAdminCompanies;
  }
}
