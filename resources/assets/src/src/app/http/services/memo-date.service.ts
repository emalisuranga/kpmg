import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { APIConnection } from './connections/APIConnection';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class MemoDateService {

  url: APIConnection = new APIConnection();

  constructor(private router: Router, private http: HttpClient) { }

  loadCompanyData(data: any): Observable<any> {
    return this.http.post(this.url.getCompanyScData(), data);
  }

  scDataSubmit(data: any): Observable<any> {
    return this.http.post(this.url.scDataSubmit(), data);
  }

  getPDFService(data: any): Observable<any> {
    return this.http.post(this.url.getScDocumentDownloadAPI(), data, { responseType: 'arraybuffer' });
  }

  scFiles(data: any): Observable<any> {
    return this.http.post(this.url.getscFile(), data);
  }

  scDeleteUploadedPdf(data: any): Observable<any> {
    return this.http.post(this.url.getscFileUploadedDelete(), data);
  }

  scDeleteUploadedUpdatePdf(data: any): Observable<any> {
    return this.http.post(this.url.getscUploadedDeleteUpdate(), data);
  }

  scReSubmit(data: any): Observable<any> {
    return this.http.post(this.url.getscReSubmit(), data);
  }
}
