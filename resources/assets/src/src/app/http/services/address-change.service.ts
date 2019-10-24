import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AddressChangeService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }

  // to load address  data using company id...
  loadCompanyAddress(data: any): Observable<any> {
    return this.http.post(this.url.getCompanyAddress(), data);
  }

  addressSubmit(data: any): Observable<any> {
    return this.http.post(this.url.addressSubmit(), data);
  }

  addressReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.addressReSubmit(), data);
  }

  getApplicationPDFService(data: any): Observable<any> {
    return this.http.post(this.url.getAddressChangeApplicationDownloadAPI(), data, { responseType: 'arraybuffer' });
  }

  addresschangeFiles(data: any): Observable<any> {
    return this.http.post(this.url.getAddresschangeFile(), data);
  }

  // to delete uploaded society pdf files...
  addresschangeDeleteUploadedPdf(data: any): Observable<any> {
    return this.http.post(this.url.getAddresschangeFileUploadedDelete(), data);
  }

  addresschangeDeleteUploadedUpdatePdf(data: any): Observable<any> {
    return this.http.post(this.url.getAddresschangeFileUploadedDeleteUpdate(), data);
  }

  addresschangeReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getAddresschangeReSubmit(), data);
  }



}
