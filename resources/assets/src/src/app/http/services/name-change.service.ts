import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { APIConnection } from './connections/APIConnection';

@Injectable({
  providedIn: 'root'
})
export class NameChangeService {
  url: APIConnection = new APIConnection();
  constructor(private http: HttpClient) { }

  getChangeNameFormFill(data: any) {
    return this.http.post(this.url.getChangeNameFormAPI(), { data }, { responseType: 'arraybuffer' });
  }

  setReSubmitedData(data: any){
    return this.http.post(this.url.getChReSubmitDataAPI(), { data });
  }
}
