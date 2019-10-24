import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { StrikeOffConnection } from './StrikeOffConnection';

@Injectable({
  providedIn: 'root'
})
export class StrikeOffService {
  url: StrikeOffConnection = new StrikeOffConnection();
  constructor(private http: HttpClient) {}

  loadCompanyAddress(data: any): Observable<any> {
    return this.http.post(this.url.getCallOnSharesData(), data);
  }

  shareCallSubmit(data: any): Observable<any> {
    return this.http.post(this.url.submitCallOnSharesURL(), data);
  }
  removeOtherDoc(data: any): Observable<any> {
    return this.http.post(this.url.removeOtherDocsURL(), data);
  }
  submit(data: any): Observable<any> {
    return this.http.post(this.url.submitURL(), data);
  }
  resubmit(data: any): Observable<any> {
    return this.http.post(this.url.resubmitURL(), data);
  }
  getUserCorrespondenceList(data: any): Observable<any> {
    return this.http.post(this.url.getUserCorrespondence(), data);
  }
  removeDoc(data: any): Observable<any> {
    return this.http.post(this.url.removeDocsURL(), data);
  }
  getApplicationPDFService(data: any): Observable<any> {
    return this.http.post(
      this.url.getAddressChangeApplicationDownloadAPI(),
      data, { responseType: 'arraybuffer' }
    );
  }
  uplodeStrikeOffData(data: any): Observable<any> {
    return this.http.post(this.url.removeDocsURL(), data);
  }
}
