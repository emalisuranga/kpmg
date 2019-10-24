import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIothersCourtConnection } from './APIothersCourtConnection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../http/models/incorporation.model';
// import { INICchecker } from '../../../../../../../http/models/stakeholder.model';
// import { IremoveAnnualDoc, Iresubmit, ISubmitCallRecords, IremoveCallShareDoc, ISubmitRegisterOfChargesRecords, ISubmitProspectus, IRemoveOtherDoc } from '../models/prospectusModel';


@Injectable({
  providedIn: 'root'
})
export class OtherCourtService {

  url: APIothersCourtConnection = new APIothersCourtConnection();

  constructor(
    private router: Router,
    private http: HttpClient
  ) { }

  loadCompanyAddress(data: any): Observable<any> {
    return this.http.post(this.url.getCallOnSharesData(), data);
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

  getCourtOrderList(data: any): Observable<any> {
    return this.http.post(this.url.getCourtOrder(), data);
  }

  getCompaniesForCourtOrder(data: any): Observable<any> {
    return this.http.post(this.url.getCompanies(), data);
  }

  removeList(data: any): Observable<any> {
    return this.http.post(this.url.removeList(), data);
  }
}
