import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AccountingAddressChangeService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }

  // to load address  data using company id...
  loadCompanyAddress(data: any): Observable<any> {
    return this.http.post(this.url.getCompanyAccoutingAddress(), data);
  }

  accountingAddressDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.accountingAddressDataSubmit(), data);
  }

  accountingAddressCourtDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.accountingAddressCourtDataSubmit(), data);
  }

  accountingAddressDataUpdate(data: any): Observable<any> {
    return this.http.post(this.url.accountingAddressDataUpdate(), data);
  }

  getPDFService(data: any): Observable<any> {
    return this.http.post(this.url.getAccAdDocumentDownloadAPI(), data, { responseType: 'arraybuffer' });
  }

  accountingaddresschangeFiles(data: any): Observable<any> {
    return this.http.post(this.url.getACAddresschangeFile(), data);
  }

  // to delete uploaded accounting address change pdf files...
  accountingaddresschangeDeleteUploadedPdf(data: any): Observable<any> {
    return this.http.post(this.url.getAcAddresschangeFileUploadedDelete(), data);
  }

  accountingaddresschangeDeleteUploadedUpdatePdf(data: any): Observable<any> {
    return this.http.post(this.url.getAcAddresschangeFileUploadedDeleteUpdate(), data);
  }

  accountingaddresschangeReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getAcAddresschangeReSubmit(), data);
  }






}
