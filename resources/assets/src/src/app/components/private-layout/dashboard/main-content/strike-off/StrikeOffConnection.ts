import { APIConnection } from '../../../../../http/services/connections/APIConnection';

export class StrikeOffConnection {
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
  _addressChangeApplicantGetDownloadUrl: string;
  _uplodeStrikeOffData: string;

  constructor() {
    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/offshore-strike-off`;
    this._resubmit = `${this._apiUrl}/api/v1/offshore-strike-off-resubmit`;
    this._submit = `${this._apiUrl}/api/v1/offshore-strike-off-submit`;
    this._uploadOther = `${this._apiUrl}/api/v1/offshore-strike-off-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/offshore-strike-off-remove-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/offshore-strike-off-resubmitted-docs`;
    this._uploadForms = `${this._apiUrl}/api/v1/offshore-strike-off-uplode`;
    this._uplodeStrikeOffData = `${this._apiUrl}/api/v1/offshore-strike-off-uplode-data`;
    this._addressChangeApplicantGetDownloadUrl = `${this._apiUrl}/api/v1/offshore-strike-off-document`;
    this._removeDoc = `${this._apiUrl}/api/v1/offshore-strike-off-remove-docs`;
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
  public removeList() {
    return this._removeRequest;
  }

  public uploadOtherResubmittedDocsURL() {
    return this._uploadOtherResubmitted;
  }
  public getAddressChangeApplicationDownloadAPI() {
    return this._addressChangeApplicantGetDownloadUrl;
  }
  public uplodeStrikeOffData() {
    return this._uplodeStrikeOffData;
  }

}
