import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APIRChargesConnection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();

 _callOnSharesData: string;
 _submitCallOnShares: string;
 _uploadForms: string;
 _removeDoc: string;
 _resubmit: string;
 _submitItems: string;
 _submitPersons: string;
 _removeDeedItem: string;
 _removePersonItem: string;

 _uploadOther: string;
 _removeOtherDoc: string;
 _uploadOtherResubmitted: string;
 _updateCourt: string;

 _checkAttachedCompany: string;
 _addCompanies: string;


  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._callOnSharesData = `${this._apiUrl}/api/v1/company-charges-registration-data`;
    this._submitCallOnShares = `${this._apiUrl}/api/v1/company-charges-registration-submit`;
    this._uploadForms  = `${this._apiUrl}/api/v1/company-charges-registration-upload-docs`;
    this._removeDoc = `${this._apiUrl}/api/v1/company-charges-registration-remove-docs`;
    this._resubmit = `${this._apiUrl}/api/v1/company-charges-registration-resubmit`;
    this._submitItems = `${this._apiUrl}/api/v1/company-charges-items-submit`;
    this._submitPersons = `${this._apiUrl}/api/v1/company-charges-persons-submit`;

    this._removeDeedItem = `${this._apiUrl}/api/v1/company-charges-remove-deed`;
    this._removePersonItem = `${this._apiUrl}/api/v1/company-charges-remove-person`;

    this._uploadOther = `${this._apiUrl}/api/v1/company-charges-registration-upload-other-docs`;
    this._removeOtherDoc = `${this._apiUrl}/api/v1/company-charges-registration-remove-other-docs`;
    this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/company-charges-registration-upload-other-resubmitted-docs`;
    this._updateCourt = `${this._apiUrl}/api/v1/company-charges-update-court-record`;

    this._checkAttachedCompany = `${this._apiUrl}/api/v1/company-charges-check-company`;
    this._addCompanies = `${this._apiUrl}/api/v1/company-charges-add-company`;
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
  public submitDeedItems() {
    return this._submitItems;
  }
  public submitEntitledPersons() {
    return this._submitPersons;
  }
  public removeDeed() {
    return this._removeDeedItem;
  }
  public removePerson() {
    return this._removePersonItem;
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

  public checkAttachedCompanies() {
    return this._checkAttachedCompany;
  }
  public addCompanies() {
    return this._addCompanies;
  }


}
