import { APIConnection } from '../../../../../../http/services/connections/APIConnection';

export class APIaffairsConnection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

  _callOnSharesData: string;
  _addressChangeApplicantGetDownloadUrl: string;
  _uploadForms: string;
  _uploadOther: string;
  _removeDoc: string;
  _removeOtherDoc: string;
  _resubmit: string;
  _uploadOtherResubmitted: string;

  constructor() {
    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/statement-of-affairs`;
    this._addressChangeApplicantGetDownloadUrl = `${this._apiUrl}/api/v1/statement-of-affairs-document`;
    this._uploadForms = `${this._apiUrl}/api/v1/statement-of-affairs-uplode`;
    this._removeDoc = `${this._apiUrl}/api/v1/statement-of-affairs-remove-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/statement-of-affairs-resubmit`;
    this._uploadOther = `${this._apiUrl}/api/v1/statement-of-affairs-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/statement-of-affairs-remove-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/statement-of-affairs-resubmitted-docs`;

    // this._getUserCorrespondence = `${this._apiUrl}/api/v1/list-prior-approval`;
  }

  public getCallOnSharesData() {
    return this._callOnSharesData;
  }

  public getAddressChangeApplicationDownloadAPI() {
    return this._addressChangeApplicantGetDownloadUrl;
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
