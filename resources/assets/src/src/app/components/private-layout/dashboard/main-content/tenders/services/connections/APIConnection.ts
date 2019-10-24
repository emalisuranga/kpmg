import {environment} from '../../../../../../../../environments/environment';

export class APIConnection {
  _apiUrl: String;
  _getUserTenders: string;
  _getTender: string;
  _getTenderAdd: string;
  _getTenderItemAdd: string;

  constructor() {
    // this._apiUrl = `http://220.247.219.173/frontend/beta/staging_v4/API/public/`;
    this._apiUrl = environment.apiUrl;
    this._getUserTenders = `${this._apiUrl}/api/v1/get-user-tenders`;
    this._getTender = `${this._apiUrl}/api/v1/get-tender`;
    this._getTenderAdd = `${this._apiUrl}/api/v1/add-tender`;
    this._getTenderItemAdd = `${this._apiUrl}/api/v1/add-tender-items`;

  }

  public getUserTendersURL() {
    return this._getUserTenders;
  }
  public getTenderURL() {
    return this._getTender;
  }

  public getTenderAddURL() {
    return this._getTenderAdd;
  }
  public getTenderItemAddURL() {
    return this._getTenderItemAdd;
  }
}
