import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIaffairsConnection } from './APIaffairsConnection';

@Injectable({
  providedIn: 'root'
})
export class AffairsService {
  url: APIaffairsConnection = new APIaffairsConnection();

  constructor(private router: Router, private http: HttpClient) { }

  loadCompanyAddress(data: any): Observable<any> {
    return this.http.post(this.url.getCallOnSharesData(), data);
  }

  getApplicationPDFService(data: any): Observable<any> {
    return this.http.post(
      this.url.getAddressChangeApplicationDownloadAPI(),
      data, { responseType: 'arraybuffer' }
    );
  }

  removeDoc(data: any): Observable<any> {
    return this.http.post(this.url.removeDocsURL(), data);
  }

  removeOtherDoc(data: any): Observable<any> {
    return this.http.post(this.url.removeOtherDocsURL(), data);
  }

  resubmit(data: any): Observable<any> {
    return this.http.post(this.url.resubmitURL(), data);
  }
}
