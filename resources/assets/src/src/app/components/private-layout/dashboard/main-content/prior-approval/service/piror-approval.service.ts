import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIpriorApprovalConnection } from './APIpriorApprovalConnection';

@Injectable({
  providedIn: 'root'
})
export class PirorApprovalService {
  url: APIpriorApprovalConnection = new APIpriorApprovalConnection();

  constructor(
    private router: Router,
    private http: HttpClient
  ) { }

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
  removeList(data: any): Observable<any> {
    return this.http.post(this.url.removeList(), data);
  }

}
