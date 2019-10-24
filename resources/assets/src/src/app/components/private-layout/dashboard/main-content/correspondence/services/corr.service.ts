import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APICorrConnection } from './connections/APICorrConnection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../http/models/incorporation.model';
import { INICchecker } from '../../../../../../http/models/stakeholder.model';
import {  IremoveAnnualDoc, Iresubmit, ISubmitCallRecords, IremoveCallShareDoc, ISubmitRegisterOfChargesRecords, ISubmitCompanyNotice, IRemoveOtherDoc, ISubmit, IgetCompnies, IsubmitCorrespondenceSearch } from '../models/corrModel';

@Injectable({
  providedIn: 'root'
})
export class CorrService {

 url: APICorrConnection = new APICorrConnection();

  constructor(private router: Router, private http: HttpClient) { }


  callOnShareData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getCallOnSharesData(), data);
  }

  shareCallSubmit(data: ISubmitCompanyNotice): Observable<ISubmitCompanyNotice> {
    return this.http.post<ISubmitCompanyNotice>(this.url.submitCallOnSharesURL(), data);
  }

  removeDoc(data: IremoveCallShareDoc): Observable<IremoveCallShareDoc> {
    return this.http.post<IremoveCallShareDoc>(this.url.removeDocsURL(), data);
  }
  submit(data: ISubmit): Observable<ISubmit> {
    return this.http.post<ISubmit>(this.url.submitURL(), data);
  }
  resubmit(data: Iresubmit): Observable<Iresubmit> {
    return this.http.post<Iresubmit>(this.url.resubmitURL(), data);
  }
  removeOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherDocsURL(), data);
  }

  getCompaniesForCertificates(data: IgetCompnies): Observable<IgetCompnies> {
    return this.http.post<IgetCompnies>(this.url.getCorrCompanies(), data);
  }

  getUserCorrespondenceList(data: IsubmitCorrespondenceSearch): Observable<IsubmitCorrespondenceSearch> {
    return this.http.post<IsubmitCorrespondenceSearch>(this.url.getUserCorrespondence(), data);
  }
}

