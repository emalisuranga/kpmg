import { APIConnection } from "../../../../../http/services/connections/APIConnection";

export class APISecretaryDelistingConnection {
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
  _uplodeStrikeOffSubmit: string;

  constructor() {
    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/secretary-delisting`;
    this._resubmit = `${this._apiUrl}/api/v1/secretary-delisting-resubmit`;
    // this._submit = `${this._apiUrl}/api/v1/secretary-delisting-submit`;
    this._uploadOther = `${this._apiUrl}/api/v1/secretary-delisting-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/secretary-delisting-remove-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/secretary-delisting-resubmitted-docs`;
    this._uploadForms = `${this._apiUrl}/api/v1/secretary-delisting-uplode`;
    this._uplodeStrikeOffData = `${this._apiUrl}/api/v1/secretary-delisting-uplode-data`;
    this._addressChangeApplicantGetDownloadUrl = `${this._apiUrl}/api/v1/secretary-delisting-document`;
    this._removeDoc = `${this._apiUrl}/api/v1/secretary-delisting-remove-docs`;
    this._uplodeStrikeOffSubmit = `${this._apiUrl}/api/v1/secretary-delisting-submit`;
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
  public uplodeStrikeOffSubmit() {
    return this._uplodeStrikeOffSubmit;
  }

  public uplodeStrikeOffData() {
    return this._uplodeStrikeOffData;
  }
}
