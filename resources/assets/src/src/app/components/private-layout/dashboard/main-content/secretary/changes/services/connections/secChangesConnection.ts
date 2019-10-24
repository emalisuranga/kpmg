import { APIConnection } from '../../../../../../../../http/services/connections/APIConnection';
export class APIsecChangesConnection {
  _apiUrl: String;

  url: APIConnection = new APIConnection();
  _getSecHeavyData: string;
  _getSecData: string;
  _updateSecAlterTypes: string;
  _updateSecName: string;
  _updateSecAddress: string;
  _updateSecEmail: string;
  _updateSecContact: string;
  _uploadSecOtherDoc: string;
  _removeSecOtherDoc: string;
  _uploadSecOtherResubmittedDoc: string;
  _submit: string;
  _resubmit: string;


  _getSecFirmHeavyData: string;
  _getSecFirmData: string;
  _updateSecFirmAlterTypes: string;
  _updateSecFirmName: string;
  _updateSecFirmAddress: string;
  _updateSecFirmEmail: string;
  _updateSecFirmContact: string;
  _uploadSecFirmOtherDoc: string;
  _removeSecFirmOtherDoc: string;
  _uploadSecFirmOtherResubmittedDoc: string;
  _addSecFirmPartner: string;
  _updateSecFirmPartner: string;
  _removeSecFirmPartner: string;
  _checkNicPartner: string;
  _submit_firm: string;
  _resubmit_firm: string;

  constructor() {

    this._apiUrl = this.url._apiUrl;

    this._getSecData = `${this._apiUrl}/api/v1/secretory-changes-data`;
    this._getSecHeavyData = `${this._apiUrl}/api/v1/secretory-changes-heavy-data`;
    this._updateSecAlterTypes = `${this._apiUrl}/api/v1/secretory-update-altertypes`;
    this._updateSecName = `${this._apiUrl}/api/v1/secretory-update-name`;
    this._updateSecAddress = `${this._apiUrl}/api/v1/secretory-update-address`;
    this._updateSecEmail = `${this._apiUrl}/api/v1/secretory-update-email`;
    this._updateSecContact = `${this._apiUrl}/api/v1/secretory-update-contact`;
    this._uploadSecOtherDoc = `${this._apiUrl}/api/v1/secretory-upload-other-docs`;
    this._uploadSecOtherResubmittedDoc = `${this._apiUrl}/api/v1/secretory-upload-other-resubmitted-docs`;
    this._removeSecOtherDoc = `${this._apiUrl}/api/v1/secretory-remove-other-docs`;
    this._submit = `${this._apiUrl}/api/v1/secretory-submit`;
    this._resubmit = `${this._apiUrl}/api/v1/secretory-resubmit`;

    this._getSecFirmData = `${this._apiUrl}/api/v1/secretory-firm-changes-data`;
    this._getSecFirmHeavyData = `${this._apiUrl}/api/v1/secretory-firm-changes-heavy-data`;
    this._updateSecFirmAlterTypes = `${this._apiUrl}/api/v1/secretory-firm-update-altertypes`;
    this._updateSecFirmName = `${this._apiUrl}/api/v1/secretory-firm-update-name`;
    this._updateSecFirmAddress = `${this._apiUrl}/api/v1/secretory-firm-update-address`;
    this._updateSecFirmEmail = `${this._apiUrl}/api/v1/secretory-firm-update-email`;
    this._updateSecFirmContact = `${this._apiUrl}/api/v1/secretory-firm-update-contact`;
    this._uploadSecFirmOtherDoc = `${this._apiUrl}/api/v1/secretory-firm-upload-other-docs`;
    this._uploadSecFirmOtherResubmittedDoc = `${this._apiUrl}/api/v1/secretory-firm-upload-other-resubmitted-docs`;
    this._removeSecFirmOtherDoc = `${this._apiUrl}/api/v1/secretory-firm-remove-other-docs`;
    this._addSecFirmPartner = `${this._apiUrl}/api/v1/secretory-firm-partner-add`;
    this._updateSecFirmPartner = `${this._apiUrl}/api/v1/secretory-firm-partner-update`;
    this._removeSecFirmPartner = `${this._apiUrl}/api/v1/secretory-firm-partner-remove`;
    this._checkNicPartner = `${this._apiUrl}/api/v1/secretory-firm-partner-check-nic-record`;
    this._submit_firm = `${this._apiUrl}/api/v1/secretory-firm-submit`;
    this._resubmit_firm = `${this._apiUrl}/api/v1/secretory-firm-resubmit`;

  }

  public getSecData() {
    return this._getSecData;
  }
  public getSecHeavyData() {
    return this._getSecHeavyData;
  }
  public updateSecAlterTypes() {
    return this._updateSecAlterTypes;
  }
  public updateSecName() {
    return this._updateSecName;
  }
  public updateSecAddress() {
    return this._updateSecAddress;
  }
  public updateSecEmail() {
    return this._updateSecEmail;
  }
  public updateSecContact() {
    return this._updateSecContact;
  }
  public uploadSecOtherDocURL() {
    return this._uploadSecOtherDoc;
  }
  public uploadSecOtherResubmittedDocURL() {
    return this._uploadSecOtherResubmittedDoc;
  }
  public removeOtherDocURL(){
    return this._removeSecOtherDoc;
  }
  public submitSecRequest() {
    return this._submit;
  }
  public resubmitSecRequest() {
    return this._resubmit;
  }



  public getSecFirmData() {
    return this._getSecFirmData;
  }
  public getSecFirmHeavyData() {
    return this._getSecFirmHeavyData;
  }
  public updateSecFirmAlterTypes() {
    return this._updateSecFirmAlterTypes;
  }
  public updateSecFirmName() {
    return this._updateSecFirmName;
  }
  public updateSecFirmAddress() {
    return this._updateSecFirmAddress;
  }
  public updateSecFirmEmail() {
    return this._updateSecFirmEmail;
  }
  public updateSecFirmContact() {
    return this._updateSecFirmContact;
  }
  public uploadSecFirmOtherDocURL() {
    return this._uploadSecFirmOtherDoc;
  }
  public uploadSecFirmOtherResubmittedDocURL() {
    return this._uploadSecFirmOtherResubmittedDoc;
  }
  public removeOtherDocFirmURL(){
    return this._removeSecFirmOtherDoc;
  }
  public addSecFirmPartner() {
    return this._addSecFirmPartner;
  }
  public updateSecFirmPartner() {
    return this._updateSecFirmPartner;
  }
  public removeSecFirmPartner() {
    return this._removeSecFirmPartner;
  }
  public checkNicPartner() {
    return this._checkNicPartner;
  }

  public submitSecFirmRequest() {
    return this._submit_firm;
  }
  public resubmitSecFirmRequest() {
    return this._resubmit_firm;
  }

}
