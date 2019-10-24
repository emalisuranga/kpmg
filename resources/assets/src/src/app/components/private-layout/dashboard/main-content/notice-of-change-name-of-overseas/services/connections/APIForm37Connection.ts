import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APIForm37Connection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

 _callOnSharesData: string;
 _submitCallOnShares: string;
 _uploadForms: string;
 _removeDoc: string;
 _resubmit: string;
  _removeOtherDoc: string;
  _uploadOther: string;
  _uploadOtherResubmitted: string;
  _updateCourt: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/overseas-name-change-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/overseas-name-change-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/overseas-name-change-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/overseas-name-change-remove-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/overseas-name-change-resubmit`;
    this._uploadOther = `${this._apiUrl}/api/v1/overseas-name-change-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/overseas-name-change-remove-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/overseas-name-change-resubmitted-docs`;
    this._updateCourt = `${this._apiUrl}/api/v1/overseas-name-change-update-court-record`;
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
}
