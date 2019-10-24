import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APISocietyConnection {
  _apiUrl: String;
  _getBulkList: string;
  _societyUpload: string;
  _societyFileUpload: string;
  _removeFile: string;
  _removePendingSocieties: string;
  _updateOptional: string;
  _removeSociety: string;


  url: APIConnection = new APIConnection();

  constructor() {

    this._apiUrl = this.url._apiUrl;
    this._getBulkList = `${this._apiUrl}/api/v1/bulk-societies`;
    this._societyUpload =  `${this._apiUrl}/api/v1/upload-bulk-societies`;
    this._societyFileUpload = `${this._apiUrl}/api/v1/upload-bulk-societies-file`;
    this._removeFile = `${this._apiUrl}/api/v1/remove-societies-file`;
    this._removePendingSocieties = `${this._apiUrl}/api/v1/remove-pending-societies-all`;
    this._updateOptional = `${this._apiUrl}/api/v1/update-optional`;
    this._removeSociety =  `${this._apiUrl}/api/v1/remove-society`;

  }

  public getSocietyBulkListURL() {
    return this._getBulkList;
  }

  public getSocietyBulkUploadURL() {
    return this._societyUpload;
  }

  public getSocietyBulkFileUploadURL() {
    return this._societyFileUpload;
  }

  public getRemoveFileURL() {
    return this._removeFile;
  }

  public getRemovePendingSocietiesURL(){
    return this._removePendingSocieties;
  }

  public updateOptionalURL() {
    return this._updateOptional;
  }

  public removeSocietyURL() {
    return this._removeSociety;
  }

}
