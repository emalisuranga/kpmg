import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class BalanceSheetdateService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }

  // to load address  data using company id...
  loadCompanyData(data: any): Observable<any> {
    return this.http.post(this.url.getCompanyBsdData(), data);
  }

  bsdDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.bsdDataSubmit(), data);
  }

  getPDFService(data: any): Observable<any> {
    return this.http.post(this.url.getBsdDocumentDownloadAPI(), data, { responseType: 'arraybuffer' });
  }

  bsdFiles(data: any): Observable<any> {
    return this.http.post(this.url.getbsdFile(), data);
  }

  // to delete uploaded pdf files...
  bsdDeleteUploadedPdf(data: any): Observable<any> {
    return this.http.post(this.url.getbsdFileUploadedDelete(), data);
  }

  bsdReDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.bsdReDataSubmit(), data);
  }

  bsdDeleteUploadedUpdatePdf(data: any): Observable<any> {
    return this.http.post(this.url.getbsdUploadedDeleteUpdate(), data);
  }

  bsdReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getbsdReSubmit(), data);
  }
}
