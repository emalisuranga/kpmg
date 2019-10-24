import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class RrAddressChangeService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }

  // to load address  data using company id...
  loadCompanyAddress(data: any): Observable<any> {
    return this.http.post(this.url.getCompanyRRAddress(), data);
  }

  rrAddressDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.rrAddressDataSubmit(), data);
  }

  rrAddressDelete(data: any): Observable<any> {
    return this.http.post(this.url.rrAddressDelete(), data);
  }

  rrAddressRevert(data: any): Observable<any> {
    return this.http.post(this.url.rrAddressRevert(), data);
  }

  rrAddressChange(data: any): Observable<any> {
    return this.http.post(this.url.rrAddressChange(), data);
  }

  rrAddressCourtDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.rrAddressCourtDataSubmit(), data);
  }

  getPDFService(data: any): Observable<any> {
    return this.http.post(this.url.getRRDocumentDownloadAPI(), data, { responseType: 'arraybuffer' });
  }

  rrFiles(data: any): Observable<any> {
    return this.http.post(this.url.getrrFile(), data);
  }

  // to delete uploaded pdf files...
  rrDeleteUploadedPdf(data: any): Observable<any> {
    return this.http.post(this.url.getrrFileUploadedDelete(), data);
  }

  rrDeleteUploadedUpdatePdf(data: any): Observable<any> {
    return this.http.post(this.url.getrrUploadedDeleteUpdate(), data);
  }

  rrReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getrrReSubmit(), data);
  }

}
